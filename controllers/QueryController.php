<?php

// test queries apart from GUI


namespace app\controllers;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Yii;
use DateTime;

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

class QueryController extends QueryBaseController
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

    public function actionNakijken($export = false) { 

        $weekday=['ma','di','wo','do','vr','za','zo'];
        $date = new DateTime();
        $dayNr = $date->format( 'N' ) - 1; // 7 for zondag

        $select='';
        for($i=0; $i<7; $i++){  
            $select .= "\n,sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -".$i." DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+".$weekday[$dayNr]."'";
            $dayNr--;
            if ($dayNr < 0) $dayNr=6; 
        }

        $sql = "
            SELECT u.name naam
            $select
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            where datediff(curdate(),s.graded_at)<=7
            group by 1
            order by 1
        ";

        return $this->render('output', [
            'data' => $this->executeQuery($sql, "Aantal opdrachten beoordeeld door", $export),
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
        ]);
    }

    // (case  when r.voldaan='V' then 1 else round(r.punten*100/r.punten_max,0) end)
    public function actionOverview($export=false) {

        $sql = "SELECT id, naam, substring(naam,1,4) 'mod' from module_def where generiek = 0 order by pos";

        $modules = Yii::$app->db->createCommand($sql)->queryAll();

        $query = "";
        $count = 0;
        foreach($modules as $module) {
            $count++;
            // $query.=",sum( case when r.module_id=".$module['id']." && r.voldaan='V' then 1 else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
            $query.=",sum( case when r.module_id=".$module['id']." then (case  when r.voldaan='V' then 100 else round(r.punten*100/r.punten_max,0) end) else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";

        }

        $sql = "
            SELECT 
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            sum( case when r.voldaan='V' then 1 else 0 end) 'Tot'
            $query
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE d.generiek = 0
            GROUP BY 1
            ORDER BY 2 DESC, 1
        ";
        $data = $this->executeQuery($sql, "Overview Voortgang", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Alle dev modules het getal geeft % compleet. 100% geeft aan dat module is voldaan.',
            'nocount' => 'True',
        ]);
    }

    public function actionAttempts($export=false) {
        $sql = "
            select u.name Student, klas Klas, g.name Module, round(sum(s.attempt)/sum(1),1) 'Gemiddeld', max(s.attempt) 'Max'
            from submission s
            inner join user u on u.id = s.user_id
            inner join assignment a on a.id = s.assignment_id
            inner join assignment_group g on g.id = a.assignment_group_id
            group by 1,2, 3
            having sum(s.attempt)/sum(1) >1.5
            order by 4 desc
        ";

        $data = $this->executeQuery($sql, "Aantal pogingen", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Gemiddeld aantal pogingen en maximaal aantal per student/module.<br>Alleen als het gemiddel aantal pogingen > 1.5 voor de module is.'
        ]);
    }

    public function actionDayOfWeek($export=false) {
        $sql = "
        select weekday(s.submitted_at) nr, dayname(s.submitted_at) Dag,
        sum(case when (datediff(curdate(),s.graded_at)<=7) then 1 else 0 end) '+laatste 7 dagen',
        sum(case when (datediff(curdate(),s.graded_at)> 7 && datediff(curdate(),s.graded_at)<=14 ) then 1 else 0 end) '+7-14 dagen',
        sum(case when (datediff(curdate(),s.graded_at)>14 && datediff(curdate(),s.graded_at)<=21 ) then 1 else 0 end) '+14-21 dagen',
        sum(case when (datediff(curdate(),s.graded_at)>21 && datediff(curdate(),s.graded_at)<=28 ) then 1 else 0 end) '+21-21 dagen',
        sum(case when (datediff(curdate(),s.graded_at)>28 && datediff(curdate(),s.graded_at)<=35 ) then 1 else 0 end) '+28-35 dagen',
        sum(1) '+Schooljaar'
        from submission s
        inner join user u on u.id = s.user_id
        inner join assignment a on a.id = s.assignment_id
        inner join assignment_group g on g.id = a.assignment_group_id
        where datediff(curdate(),s.graded_at) < 400
        group by 1,2
        order by 1
        ";

        $data = $this->executeQuery($sql, "Productiefste dagen studenten", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Op welke dag van de week zijn de meeste opdrachten ingeleverd.',
            'nocount' => 'True',
        ]);
    }

    public function actionDagdeel($export=false) {
        $sql = "
        select u.name Student,
        sum(case when (hour(s.submitted_at)>=7 && hour(s.submitted_at)<9 ) then 1 else 0 end) '+Morgens 0700-0900',
        sum(case when (hour(s.submitted_at)>=9 && hour(s.submitted_at)<15 ) then 1 else 0 end) '+Overdag 0900-1500',
        sum(case when (hour(s.submitted_at)>=15 && hour(s.submitted_at)<18 ) then 1 else 0 end) '+Namiddag 1500-1800',
        sum(case when (hour(s.submitted_at)>=18 && hour(s.submitted_at)<=23 ) then 1 else 0 end) '+Avond 1800-2400',
        sum(case when (hour(s.submitted_at)>=0 && hour(s.submitted_at)<6 ) then 1 else 0 end) '+Nacht 2400-0600',
        sum(1) '+Totaal'
        from submission s
        inner join user u on u.id = s.user_id
        inner join assignment a on a.id = s.assignment_id
        inner join assignment_group g on g.id = a.assignment_group_id
        where datediff(curdate(),s.graded_at) < 400
        group by 1
        ";

        $data = $this->executeQuery($sql, "Productiefste dagen studenten", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Op welke dag van de week zijn de meeste opdrachten ingeleverd.',
            'nocount' => 'True',
        ]);
    }

    public function actionMoeilijk($export=false) {
        $sql = "
        select g.name,
        sum( s.entered_score ) '+score behaald',
        sum( a.points_possible ) '+score mogelijk',
        round(sum( s.entered_score )*100 / sum( a.points_possible ),1) 'perc',
        avg(s.attempt) '+gem_pogingen',
        count(*) 'opdrachten'
        from submission s
        inner join user u on u.id = s.user_id
        inner join assignment a on a.id = s.assignment_id
        inner join assignment_group g on g.id = a.assignment_group_id
        inner join module_def d on d.id=g.id
        -- inner join resultaat r on r.module_id = d.id and r.student_nummer = u.student_nr and voldaan = 'V'
        where s.submitted_at < s.graded_at and d.generiek=0
        group by 1
        having count(*) > 50 and sum( a.points_possible ) > 0
        order by 5 DESC
        ";

        $data = $this->executeQuery($sql, "Moeilijke dev modules ", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal behaalde punten als percentage van het totaal voor alle afgeronde modules.'
        ]);
    }

    // http://localhost:8080/query/activity?studentnr=2153757
    public function actionActivity($studentnr='99', $export=false){
        $sql = "
            select u.name Student, u.klas Klas, g.name Module, a.name Opdracht, s.submitted_at Ingeleverd, s.attempt Poging
            from submission s
            join assignment a on a.id=s.assignment_id
            join user u on u.id = s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where u.student_nr = $studentnr
            order by submitted_at DESC
            limit 120
        ";

        $data = $this->executeQuery($sql, "Activity report for $studentnr", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Laaste 120 inzendingen van '.$data['row'][0]['Student']. '.'
        ]);
    }

    public function actionRapid($export=false){
        $sql = "
        select Student, Module,sum(1) Clusters, sum(aantal) Aantal
        from
        (
        select u.name Student, g.name Module, CAST(UNIX_TIMESTAMP(s.submitted_at)/180 AS UNSIGNED INTEGER) Ingeleverd, sum(1) aantal
        from submission s
        join assignment a on a.id=s.assignment_id
        join user u on u.id = s.user_id
        join assignment_group g on g.id = a.assignment_group_id
        group by 1,2,3
        having Ingeleverd <> 0 and aantal>2
        order by 4 desc
        ) alias
        group by 1,2
        having clusters > 2 or aantal > 10
        order by 3 DESC, 4 DESC
        ";

        $data = $this->executeQuery($sql, "Snel inleveren", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Een cluster is een tijdsslot van 3 minuten waarin 3 of meer opgaven zijn ingeleverd. Totaal geeft het totaal aantal opgaven van alle clusters aan.'
        ]);
    }

    public function actionGetAllResultaat($export = true) // export voor Theo - staat onder knop bij Gridview van alle resutlaten
    {  
        $sql = "select * from resultaat order by student_nummer, module_id";
        $data = $this->executeQuery($sql, "", $export);
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

}
