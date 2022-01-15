<?php

namespace app\controllers;

use Yii;
use app\models\Resultaat;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;

use yii\helpers\ArrayHelper;

/**
 * BeoordelingController implements the CRUD actions for Beoordeling model.
 * A Query can have three directives for the view, this directive is the first char of a field
 * 
 * + a sum will be calculated of this field
 * 
 * - the filed will not be dispplayed (somethimes you want a sort field not to be displayed)
 * 
 * ! a link, this field concat the data needed to form the link
 *    f.e. concat(link_name_top_be_displayed,'|hyper link or path|first_param|',param_value) '!field_name'
 *         there may be 0,1 or 2 parameters given.
 *    note that for the export the query is filtered to become a 'normal' query without the directives and concats.
 *    note that the complete concat commando may not contain any spaces
 */

class QueryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    // when logged in, any user
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }


    private function executeQuery($sql, $title = "no title", $export = false)
    {

        if ($export) {
            $sql=$this->exportQueryFilter($sql);
        }
        $result = Yii::$app->db->createCommand($sql)->queryAll();

        if ($result) { // column names are derived from query results
            $data['col'] = array_keys($result[0]);
        }
        $data['row'] = $result;

        if ($export) {
            $this->exportExcel($data);
            exit;
        } else {
            $data['title'] = $title;
            return $data;
        }
    }

    private function exportQueryFilter($query) // filter + - en ! column names and concats from sql statement for export - deze speciale tekens zijn indicatoren voor de view
    {
        $components = preg_split("/[\s]/", $query);
        $components = (array_filter($components, function($value) { return !is_null($value) && $value !== ''; }));

        $newQuery = "";
        foreach($components as $item) {
            if (strtolower(substr($item, 0, 6))=='concat') {
                $sub= $components = preg_split("/[,(]/", $item);
                if ( count($sub) < 2 ) {
                    dd('concat in SQL query can not be tranformed for export; unknown syntax in concat.');
                }
                $item=$sub[1];
            }
            if ( substr($item, 1, 1)=='!' || substr($item, 1, 1)=='+' || substr($item, 1, 1)=='-' ) {
                $item = str_replace(['!','+','-'], "", $item);
            }
            if (strtolower(substr($item, 0, 6))=='limit') { // for export we don't have a limit, sincee limit is the last statement return query as is at this moment
                return $newQuery;
            }
            $newQuery .= " ".$item;
        }
 
        return($newQuery);
    }

    public function exportExcel($data)
    {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') . '.csv"');

        $seperator = ";"; // NL version, use , for EN

        foreach ($data['col'] as $key => $value) {
            echo $value . $seperator;
        }
        echo "\n";
        foreach ($data['row'] as $line) {
            foreach ($line as $key => $value) {
                echo $value . $seperator;
            }
            echo "\n";
        }
    }

    public function actionLog($export = false) // show access log (not part of any menu)
    {
        $sql = "select *
                from log
                where subject <> 'Student Rapport' || route <> '82.217.135.153'
                order by timestamp desc limit 200";
        $data = $this->executeQuery($sql, "Log", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
        ]);
    }

    public function actionSubmissions($export = false, $code) // niet in het menu (hidden feature) - export all submissions
    { 
        $sql = "
            SELECT  u.name Student, DATE_FORMAT(s.submitted_at,'%y') Jaar,
            lpad(week(s.submitted_at,1),2,0) Week,
            sum(1) '+Aantal'
            FROM `submission`  s
            inner join user u on u.id = s.user_id
            and u.code='$code'
            and s.submitted_at > '1970-01-01 00:00:00'
            group by 1,2,3
            order by 1,2,3
        ";
        $data = $this->executeQuery($sql, "Submissions", $export);

        return $this->render('/public/chart', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Submissions per week',
            'nocount' => 'True',
            'code' => $code,
        ]);
    }

    public function actionGetAllResultaat($export = true) // export voor Theo - staat onder knop bij Gridview van alle resutlaten
    {  
        $sql = "select * from resultaat order by student_nummer, module_id";
        $data = $this->executeQuery($sql, "", $export);
    }

    public function actionActief($sort = 'desc', $export = false, $klas = '') // menu 3.1 - Student laatst actief op....
    { 

        // $sql="select student_naam Student, klas Klas, max(laatste_activiteit) 'Laatst actief' from resultaat group by 1,2 order by 3 $sort";
        if ($klas) {
            $select = "and o.klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            SELECT
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            o.klas Klas, module Module, laatste_activiteit 'Wanneer', datediff(curdate(), laatste_activiteit) 'Dagen'
            FROM resultaat o
            inner join user u on u.student_nr=o.student_nummer
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            order by 4 desc
        ";

        $data = $this->executeQuery($sql, "Laatste activiteit per student " . $klas, $export);
        
        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }

    public function actionAantalActiviteiten($export = false, $klas = '') // menu 3.2 - 12 wekenoverzicht
    { 

        if ($klas) $select = "where u.klas='$klas'";
        else $select = '';

        $sql = "
            select
            sum(case when (datediff(curdate(),submitted_at)<=21) then 1 else 0 end) '-21',
            u.klas Klas,
            -- concat(u.name,'|/query/submissions|code|',u.code) '!Student',
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            sum(case when (datediff(curdate(),submitted_at)=1) then 1 else 0 end) '+-2d',
            sum(case when (datediff(curdate(),submitted_at)=0) then 1 else 0 end) '+-1d',
            0 'Graph',
            sum(case when (datediff(curdate(),submitted_at)<=84 && datediff(curdate(),submitted_at)>77 ) then 1 else 0 end) '+-12',
            sum(case when (datediff(curdate(),submitted_at)<=77 && datediff(curdate(),submitted_at)>70 ) then 1 else 0 end) '+-11',
            sum(case when (datediff(curdate(),submitted_at)<=70 && datediff(curdate(),submitted_at)>63 ) then 1 else 0 end) '+-10',
            sum(case when (datediff(curdate(),submitted_at)<=63 && datediff(curdate(),submitted_at)>56 ) then 1 else 0 end) '+-9',
            sum(case when (datediff(curdate(),submitted_at)<=56 && datediff(curdate(),submitted_at)>49 ) then 1 else 0 end) '+-8',
            sum(case when (datediff(curdate(),submitted_at)<=49 && datediff(curdate(),submitted_at)>42 ) then 1 else 0 end) '+-7',
            sum(case when (datediff(curdate(),submitted_at)<=42 && datediff(curdate(),submitted_at)>35 ) then 1 else 0 end) '+-6',
            sum(case when (datediff(curdate(),submitted_at)<=35 && datediff(curdate(),submitted_at)>28 ) then 1 else 0 end) '+-5',
            sum(case when (datediff(curdate(),submitted_at)<=28 && datediff(curdate(),submitted_at)>21 ) then 1 else 0 end) '+-4',
            sum(case when (datediff(curdate(),submitted_at)<=21 && datediff(curdate(),submitted_at)>14 ) then 1 else 0 end) '+-3',
            sum(case when (datediff(curdate(),submitted_at)<=14 && datediff(curdate(),submitted_at)>7 ) then 1 else 0 end) '+-2',
            sum(case when (datediff(curdate(),submitted_at)<=7) then 1 else 0 end) '+-1',
            sum(case when (datediff(curdate(),submitted_at)<=84) then 1 else 0 end) '+Totaal'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            $select
            group by 2,3
            order by 1 DESC
        ";
        $data = $this->executeQuery($sql, "Activiteiten over de laatste 12 weken" . $klas, $export);
        $data['show_from']=1;

        return $this->render('studentActivity', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
            'descr' => 'Weken zijn \'rollende\' weken (dus geen kalenderweken). Gesorteerd op activiteiten over laatse drie weken.',
        ]);
    }

    public function actionWorkingOn($sort = 'desc', $export = false, $klas = '') // menu 3.3 - Student werken aan...
    { 

        if ($klas) {
            $select = "and klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            SELECT module Module, sum(1) Studenten
            from resultaat o
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            group by 1
            order by 2 $sort
        ";

        $data = $this->executeQuery($sql, "Studenten " . $klas . " werken aan", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }

    public function actionRanking($sort = 'desc', $export = false, $klas = '') // menu 3.4 - Ranking studenten
    { 

        if ($klas) {
            $select = "and r.klas='$klas'";
        } else {
            $select = '';
        }

        # if a teacher is also student he has no code (code is null), so only get students with a code
        $sql = "
            select
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                r.klas Klas,
                u.ranking_score '+Score',
                SUM(case when r.voldaan='V' and d.generiek=0 then 1 else 0 end) 'V-Dev',
                SUM(case when r.voldaan='V' and d.generiek=1 then 1 else 0 end) 'V-Gen',
                sum(r.punten) '+Punten totaal'
                FROM resultaat r
                INNER JOIN module_def d ON d.id=r.module_id
                INNER JOIN user u ON u.student_nr = r.student_nummer
                where u.code is not null
            $select
            group by 1,2,3
            order by 3 $sort";

        $data = $this->executeQuery($sql, "Voortgang/Ranking " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }

    public function actionModulesFinished($export = false, $klas = '') // menu 3.5 - Module is c keer voldaan
    { 

        if ($klas) {
            $select = "where klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            select
                Module,
                af 'Afgerond door'
                from
                (select course_id, module_id, module Module, sum(case when voldaan='V' then 1 else 0 end) af
            from resultaat o
            join module_def d on d.id=o.module_id
            $select
            group by 1,2,3
            order by d.pos) alias
        ";
        // ToDo: order by werkt niet op server (order by moet in group by zitten)
        $data = $this->executeQuery($sql, "Modules voldaan " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }


    public function actionBeoordeeld($export = false) // menu 3.6 - Laatste beoordeling per module
    { 
        $sql = "
            select module Module, max(laatste_beoordeling) Beoordeeld,  datediff(curdate(), max(laatste_beoordeling)) 'Dagen'
            from resultaat
            group by 1
            order by 2 desc
        ";
        $data = $this->executeQuery($sql, "Laatste beoordeling per module", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Minimaal één opdracht van de module is beoordeeld ... dagen gelden.<br/>Automatisch beoordeeelde opdrachten worden ook geteld.',
        ]);
    }

    public function actionAantalBeoordelingen($export = false, $klas = '') // menu 3.7 - Beoordelingen per module over tijd
    { 
        if ($klas) {
            $select = "where klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            select module Module,
            sum(case when (datediff(curdate(),laatste_beoordeling)<=7) then 1 else 0 end) '+7',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=14  && datediff(curdate(),laatste_beoordeling)>7 ) then 1 else 0 end) '+14',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=21 && datediff(curdate(),laatste_beoordeling)>14 ) then 1 else 0 end) '+21',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=28 && datediff(curdate(),laatste_beoordeling)>21 ) then 1 else 0 end) '+28',
            sum(1) 'Aantal'
            from resultaat
            $select
            group by 1
            order by 2 desc
        ";
        $data = $this->executeQuery($sql, "Beoordelingen over tijd " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
            'descr' => '(minimaal 1 opdracht van) module voor x studenten beoordeeld over 2, 7 en 14 dagen.<br/>Automatisch beoordeeelde opdrachten worden ook geteld.',
        ]);
    }


    public function actionNakijken($export = false) // menu 3.8 - Aantal beoordeligen per docent
    { 

        $sql = "
            SELECT u.name naam, sum(case when (datediff(curdate(),s.graded_at)<=7) then 1 else 0 end) '+laatste 7 dagen',
            sum(case when (datediff(curdate(),s.graded_at)> 7 && datediff(curdate(),s.graded_at)<=14 ) then 1 else 0 end) '+7-14 dagen',
            sum(case when (datediff(curdate(),s.graded_at)>14 && datediff(curdate(),s.graded_at)<=21 ) then 1 else 0 end) '+14-21 dagen',
            sum(1) '+Schooljaar'
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            group by 1
            order by 2 DESC
        ";
        $data = $this->executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
        ]);
    }

    public function actionStudentenLijst($export = false) // menu 6.2 - Studentencodes (export)
    { 
        $sql = "SELECT id 'Canvas Id', name Naam, login_id email, student_nr 'Student nr', klas Klas, code Code FROM user";

        $data = $this->executeQuery($sql, "Studentenlijst", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Studentenlijst (voor Export naar Excel)',
        ]);
    }



    public function actionMenu41($export=false){ // menu 4.1 wrapper voor menu highlight - each menu needs to have a unique function
        return $this->actionNotGraded(isset($export)&&$export, false);
    }

    public function actionMenu42($export=false){ // menu 4.2 wrapper voor menu highlight - each menu needs to have a unique function
        return $this->actionNotGraded(isset($export)&&$export, true);
    }

    public function actionNotGraded($export=false, $regrading=false) // Menu 4.1 - 4.2 - Wachten op beoordeling 
    {
        $sql = "
            SELECT  m.pos '-pos',
            concat(m.naam,'|/query/not-graded-module|moduleId|',m.id,'|regrading|$regrading') '!Module',
            sum(1) '+Aantal'
            FROM assignment a
            left outer join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at ";
        $sql .= $regrading ? '<>' : '=';
        $sql .= "'1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            group by 1, 2
            order by m.pos
        ";

        $reportTitle = $regrading ? "Wachten op herbeoordeling" : "Wachten op eerste beoordeling";

        $data = $this->executeQuery($sql, $reportTitle, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
        ]);
    }

    public function actionNotGradedModule($moduleId = '', $export = false, $regrading = false) // Menu 4.1b - 4.2b Nog beoordelen = ingeleverd en nog geen beoordeling van één module
    {
        $sql = "
            SELECT  
                m.naam Module,
                m.pos '-pos',
                concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                s.attempt poging,
                concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at ";
            $sql .= $regrading ? '<>' : '=';
            $sql .= " '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            and m.id=$moduleId
            order by 3, 5
        ";

        $data = $this->executeQuery($sql, "Wachten op eerste beoordeling per module", $export);
        $data['title']="Wachten op eerste beoordeling voor <i>".$data['row'][0]['Module']."</i>";
        $data['show_from']=1;


        // Create lastLineButton
        $lastLine= "<script>\n";
        $count=0;
        $pagesPerButton=10;
        $buttons=[];

        foreach ($data['row'] as $item) {
            if ( $count%$pagesPerButton == 0) {
                $lastLine.= "function openAllInNewTab".$count."() {\n";
                array_push($buttons, $count);
            }
            // dd( explode('|',$item['!Link'])[1] );
            $lastLine.= "window.open('". explode('|',$item['!Link'])[1] ."', '_blank');\n";
            $count++;
            if ( $count%$pagesPerButton == 0) {
                $lastLine.= "}\n";
            }
        }
        if ( $count%$pagesPerButton != 0) {
            $lastLine.= "}\n";
        }
        $lastLine.= "</script><hr>\n";

        foreach (array_reverse($buttons) as $elem) {
            $start=$elem+1;
            $stop=min($elem+10,count($data['row']));
            $lastLine.=  "<button class=\"btn btn-link\" style=\"float: right;\" onclick=openAllInNewTab".$elem."() title=\"Open all submissions\">Grade ".$start."-".$stop." &#10142;</button>";
        }


        return $this->render('output', [
            'data' => $data,
            'lastLine' => $lastLine,
        ]);
    }


    public function actionNotGradedPerDate($export=false, $regrading=false) // Menu 4.3 - 4.4 - Wachten op beoordeling per datum
    {
        $sql = "
            SELECT  m.pos '-pos',
                    m.naam Module,
                    concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                    concat(u.name,'|/public/index|code|',u.code) '!Student',
                    concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                    s.attempt Poging,
                    concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at ";
        $sql .= $regrading ? '<>' : '=';
        $sql.=" '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            order by 5 ASC
            limit 250
        ";

        $reportTitle = $regrading ? "Wachten op herbeoordeling op datum" : "Wachten op eerste beoordeling op datum";

        $data = $this->executeQuery($sql, $reportTitle, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Rapport (en export) laat maximaal 250 regels zien. Updates zijn pas zichtbaar na update uit Canvas',
        ]);
    }


    public function actionNotGradedModuleAssignment($moduleId, $assignmentId, $export = false) // Overzicht van module en dan één opdracht ingeleverd en nog geen beoordeling van één module 7736 23127
    {
        $sql = "
            SELECT  m.pos '-pos',
                concat(m.naam,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Module',
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                s.submitted_at Ingeleverd,
                concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at = '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            and m.id=$moduleId
            and a.id=$assignmentId
            order by submitted_at DESC
        ";

        $data = $this->executeQuery($sql, "Wachten op eerste beoordeling van een module/opdracht", $export);

        return $this->render('output', [
            'data' => $data,
            'nocount' => True,
            //'action' => Yii::$app->controller->action->id."?moduleId=".$moduleId."&",
        ]);
    }

    public function actionNever($export=false) {
        $sql = "
            SELECT klas Klas, name Student FROM `user` WHERE student_nr not IN
            ( select message from log)
            and klas in ('1A','1B','1C')
            order by 1,2
        ";

        $data = $this->executeQuery($sql, "Student never logged in Canvas Monitor", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
        ]);

    }

    public function actionResubmittedXXX($export = false) // Wachten op herbeoordeling -- wordt niet meer gebruikt
    {
        $sql = "
            SELECT  m.pos '-pos',
                    m.naam Module,
                    concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                    concat(u.name,'|/public/index|code|',u.code) '!Student',
                    s.submitted_at Ingeleverd,
                    concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at > '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            order by 1 ASC, 3 ASC, 4 ASC
            limit 250
        ";

        $data = $this->executeQuery($sql, "Wachten op herbeoordeling", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Rapport (en export) laat maximaal 250 regels zien. Updates zijn pas zichtbaar na update uit Canvas',
        ]);
    }

    public function actionNotRegradedXXX($export = false) // Menu 4.2 Wachten op herbeoordeling
    {

        $sql = "
            SELECT  m.pos '-pos',
            concat(m.naam,'|/query/not-graded-module|moduleId|',m.id,'|regrading|true') '!Module',
            sum(1) Aantal
            FROM assignment a
            left outer join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at <> '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            group by 1, 2
            order by m.pos
        ";

        $data = $this->executeQuery($sql, "Wachten op herbeoordeling", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
        ]);
    }

    public function actionNotRegradedXXX2($export = false) // Wachten op eerste beoordeling = ingeleverd en nog geen beoordelin
    {

        $sql = "
            SELECT  m.pos '-pos',
            concat(m.naam,'|/query/not-graded-module|moduleId|',m.id,'|regrading|true') '!Module',
            sum(1) Aantal
            FROM assignment a
            left outer join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at <> '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            group by 1, 2
            order by m.pos
        ";

        $data = $this->executeQuery($sql, "Wachten op herbeoordeling", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
        ]);
    }
}

