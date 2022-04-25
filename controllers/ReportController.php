<?php

// report controller, child of QueryBase. Standard reports.

namespace app\controllers;
use yii\web\Controller;
use Yii;

class ReportController extends QueryBaseController
{

    public function actionActief($export = false, $klas = '') { // menu 3.1 - Student laatst actief op....

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
            and o.klas is not NULL
            $select
            order by 4 desc
        ";

        $data = parent::executeQuery($sql, "Laatste activiteit per student " . $klas, $export);
        
        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }

    public function actionAantalActiviteiten($export = false, $klas = '') // menu 3.2 - 12 wekenoverzicht
    { 

        if ($klas) $select = "and u.klas='$klas'";
        else $select = '';

        $sum_column="";
        for($i=12; $i>=2; $i--){
            $sum_column .= "sum(case when (datediff(curdate(),submitted_at)<=";
            $sum_column .= $i*7;
            $sum_column .= " && datediff(curdate(),submitted_at)>";
            $sum_column .= ($i-1)*7;
            $sum_column .= ") then 1 else 0 end) '+-$i',";
            $sum_column .= "\n";
        }
       

        $sql = "
            select
            sum(case when (datediff(curdate(),submitted_at)<=21) then 1 else 0 end) '-21',
            u.klas Klas,
            -- concat(u.name,'|/query/submissions|code|',u.code) '!Student',
            -- u.name TEST,
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
            where klas is not null
            $select
            group by 2,3
            order by 1 DESC
        ";

        $sql2 = "
            select
            sum(case when (datediff(curdate(),submitted_at)<=21) then 1 else 0 end) '-21',
            u.klas Klas,
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            u.student_nr '-student_nr',
            sum(case when (datediff(curdate(),submitted_at)=1) then 1 else 0 end) '+-2d',
            sum(case when (datediff(curdate(),submitted_at)=0) then 1 else 0 end) '+-1d',
            0 'Graph',
            $sum_column
            sum(case when (datediff(curdate(),submitted_at)<=7) then 1 else 0 end) '+-1',
            sum(case when (datediff(curdate(),submitted_at)<=84) then 1 else 0 end) '+Totaal'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where klas is not null
            $select
            group by 2,3,4
            order by sum(case when (datediff(curdate(),submitted_at)<=84) then 1 else 0 end)  DESC
            limit 200
        ";

        // echo "<pre>";
        // echo $sql;
        // echo "<br>";
        // echo $sql2;
        // exit;

        $data = parent::executeQuery($sql2, "Activiteiten over de laatste 12 weken" . $klas, $export);
        $data['show_from']=1;

        return $this->render('studentenActivity', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
            'descr' => 'Weken zijn \'rollende\' weken (dus geen kalenderweken). Gesorteerd op activiteiten over laatse drie weken.',
        ]);
    }

    public function actionActivity($studentnr='99', $export=false){
        $sql = "
            select u.name 'student', u.student_nr 'student_nr', u.klas 'klas', g.name module, a.name opdracht, s.submitted_at ingeleverd, s.attempt poging,
            s.course_id 'course_id', a.id 'assignment_id', u.id 'student_id',
            case when s.submitted_at <= s.graded_at then 1 else 0 end 'graded',
            a.points_possible 'max_points', s.entered_score 'points'
            from submission s
            join assignment a on a.id=s.assignment_id
            join user u on u.id = s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where u.student_nr = $studentnr
            and s.submitted_at <> '1970-01-01 00:00:00'
            order by submitted_at DESC
            limit 400
        ";

        $data = $this->executeQuery($sql, "place_holder", $export);

        if ( $data ) {
            $data['title'] = "Activity report for ".$data['row'][0]['student']." / ".$data['row'][0]['klas'];
        } 

        return $this->render('studentActivity', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?studentnr=".$data['row'][0]['student_nr']."&",
            'descr' => 'Laaste 400 inzendingen. Geel geacceerd is nog niet beoordeeld.',
        ]);
    }

    public function actionWorkingOn($sort = 'asc', $export = false, $klas = '') // menu 3.3 - Student werken aan...
    { 

        if ($klas) {
            $select = "where klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            select m.pos, m.naam Module,
            sum(case when (datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end) '+Ingeleverd',
            sum(case when (s.workflow_state='graded' && datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end) '+Waarvan nagekeken'
            -- round ( (sum(case when (s.workflow_state='graded' && datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end)*100)/ ( sum(case when (datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end) ) ,0) '+Test'
            from module_def m
            join assignment_group g on g.id = m.id
            join assignment a on a.assignment_group_id=g.id
            left outer join submission s on s.assignment_id=a.id
            join user u on u.id = s.user_id
            $select
            group by 1,2
            order by m.pos $sort
        ";

        $data = parent::executeQuery($sql, "Studenten " . $klas . " werken aan", $export);

        $data['show_from']=1;

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
            'descr' => 'Aantal opdrachten ingeleverd en nageken per module over de afgelopen 14 dagen <br><i>Nakijken</i> telt alleen de modules die ook over de laatste twee weken zijn ingeleverd.',
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
                u.ranking_score '+Score', u.student_nr,
                SUM(case when r.voldaan='V' and d.generiek=0 then 1 else 0 end) 'V-Dev',
                SUM(case when r.voldaan='V' and d.generiek=1 then 1 else 0 end) 'V-Gen',
                sum(r.punten) '+Punten totaal'
                FROM resultaat r
                INNER JOIN module_def d ON d.id=r.module_id
                INNER JOIN user u ON u.student_nr = r.student_nummer
                where u.code is not null
            $select
            group by 1,2,3,4
            order by 3 $sort";

        $data = parent::executeQuery($sql, "Voortgang/Ranking " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }

    public function actionRanking2($sort = 'desc', $export = false, $klas = '') // menu 3.4 - Ranking studenten
    { 

        if ($klas) {
            $select = "and r.klas='$klas'";
        } else {
            $select = '';
        }

        # if a teacher is also student he has no code (code is null), so only get students with a code
        $sql = "
            select
                '...',
                r.klas Klas,
                u.ranking_score '+Score'
                FROM resultaat r
                INNER JOIN module_def d ON d.id=r.module_id
                INNER JOIN user u ON u.student_nr = r.student_nummer
                where u.code is not null
            $select
            group by 1,2,3
            order by 3 $sort";

        $data = parent::executeQuery($sql, "Voortgang/Ranking " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }

    public function actionModulesFinished($export = false, $klas = '') // menu 3.5 - Module is c keer voldaan
    { 

        if ($klas) {
            $select = "and klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            select
                Module,
                concat(af, '|/report/modules-open|moduleId|', module_id, '|voldaan|1') '!Afgerond',
                concat(naf, '|/report/modules-open|moduleId|', module_id) '!Niet Afgerond'
                from
                    (select course_id, module_id, module Module, sum(case when voldaan='V' then 1 else 0 end) af, sum(case when voldaan!='V' then 1 else 0 end) naf
                    FROM resultaat o
                    JOIN module_def d on d.id=o.module_id
                    JOIN user u on u.student_nr=o.student_nummer
                    where u.grade = 1
                    $select
                    group by 1,2,3
                    order by d.pos) alias
        ";
        // ToDo: order by werkt niet op server (order by moet in group by zitten)
        $data = parent::executeQuery($sql, "Modules voldaan " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
            'descr' => 'In het overzicht staan aleen studenten die waarvna de grade aan staat'
        ]);
    }

    public function actionModulesOpen($moduleId, $voldaan=0, $export = false) // report accessible via ModuleFinished
    { 
        if ($voldaan) {
            $voldaanQuery="voldaan = 'V'";
        } else {
            $voldaanQuery="voldaan != 'V'";
        }
        $sql = "
            SELECT r.module_pos '-c1', r.module_id  '-c2', r.module '-Module',
            u.comment 'Comment',
            u.klas 'Klas',
            concat(r.student_naam,'|/public/details-module|code|',u.code,'|moduleId|',r.module_id) '!Student',
            r.ingeleverd ingeleverd, round(r.punten*100/r.punten_max) 'Punten %'
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE $voldaanQuery
            and r.module_id=$moduleId
            and u.grade=1
            order by r.ingeleverd, r.punten
        ";
        $data = parent::executeQuery($sql, "placeholder", $export);

        $data['title'] = ( $voldaan ? 'Afgeronde' : 'Open' )." opdrachten voor ".$data['row'][0]['-Module'];

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?moduleId=".$moduleId."&",
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
        $data = parent::executeQuery($sql, "Laatste beoordeling per module", $export);

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
        $data = parent::executeQuery($sql, "Beoordelingen over tijd " . $klas, $export);

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
        $data = parent::executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
        ]);
    }

    public function actionNakijkenWie($export = false) // menu 3.8 - Aantal beoordeligen per docent
    { 

        $sql = "
            SELECT u.name naam, g.name, sum(case when (datediff(curdate(),s.graded_at)<=7) then 1 else 0 end) '+laatste 7 dagen',
            sum(case when (datediff(curdate(),s.graded_at)> 7 && datediff(curdate(),s.graded_at)<=14 ) then 1 else 0 end) '+7-14 dagen',
            sum(case when (datediff(curdate(),s.graded_at)>14 && datediff(curdate(),s.graded_at)<=21 ) then 1 else 0 end) '+14-21 dagen',
            sum(1) '+Schooljaar'
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            inner join assignment_group g on g.id = a.assignment_group_id
            group by 1,2
            order by 1,2 DESC
        ";
        $data = parent::executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
        ]);
    }

    public function actionLastReportByStudent($export=false, $klas = '') {
        if ($klas) {
            $select = "and klas='$klas'";
        } else {
            $select = '';
        }

        $sql=  "SELECT u.name Student, u.klas Klas, min( case when (isnull(l.timestamp)) then 999 else datediff(curdate(),l.timestamp) end) 'Dagen geleden'
                FROM user u
                LEFT OUTER JOIN log l on ( u.name = l.message and  l.subject = \"Student /public/index\" )
                WHERE u.klas in ('1A','1B','1C','1D')";
        $sql.=  $select;
        $sql.= " group by 1,2
                order by 3 ASC, 1";

        $data = parent::executeQuery($sql, "Studenten keken in Canvas Monitor", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Hoeveel dagen is het geleden dat het studentrapport is opgevraagd door iemand die <i>niet</i> is aangelogd in de Canvas Monitor?',
        ]);
    }

    public function actionVoortgang($export=false) {

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
            ranking_score 'Rank',
            u.student_nr Nummer,
            u.klas Klas,
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            u.comment Comment,
            sum( case when r.voldaan='V' then 1 else 0 end) 'Tot'
            $query
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE d.generiek = 0
            GROUP BY 1,2,3,4,5
            ORDER BY 1 DESC
        ";
        $data = $this->executeQuery($sql, "Voortgang Dev Modules (voor BSA)", $export);

        $data['show_from']=1;

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Alle dev modules het getal geeft % compleet. 100% geeft aan dat module is voldaan.',
        ]);
    }

    public function actionVoortgangPunten($export=false) {

        $sql = "SELECT id, naam, substring(naam,1,4) 'mod' from module_def where generiek = 0 order by pos";

        $modules = Yii::$app->db->createCommand($sql)->queryAll();

        $query = "";
        $count = 0;
        foreach($modules as $module) {
            $count++;
            // $query.=",sum( case when r.module_id=".$module['id']." && r.voldaan='V' then 1 else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
            // $query.=",sum( case when r.module_id=".$module['id']." then (case  when r.voldaan='V' then 100 else round(r.punten*100/r.punten_max,0) end) else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
            $query.=",sum( case when r.module_id=".$module['id']." then (case  when r.voldaan='V' then 0 else (r.aantal_opdrachten - r.ingeleverd) end) else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
        }

        $sql = "
            SELECT
            ranking_score 'Rank',
            u.student_nr Nummer,
            u.klas Klas,
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            u.comment Comment,
            sum( case when r.voldaan='V' then 0 else(r.aantal_opdrachten - r.ingeleverd) end) 'Tot'
            $query
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE d.generiek = 0 AND d.pos < 50
            GROUP BY 1,2,3,4,5
            ORDER BY 6
        ";
        $data = $this->executeQuery($sql, "Voortgang Dev Modules (voor BSA)", $export);

        $data['show_from']=1;

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Alle dev modules het getal geeft % compleet. 100% geeft aan dat module is voldaan.',
        ]);
    }

    public function actionClusterSubmissions($clusterSize=8, $clusterTime=300, $export=false){
        $sql = "
            select u.name Student, g.name Module, UNIX_TIMESTAMP(s.submitted_at) unix_ts, s.submitted_at Submitted
            from submission s
            join assignment a on a.id=s.assignment_id
            join user u on u.id = s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where s.submitted_at <> '1970-01-01 00:00:00'
            -- and g.name like 'PHP - Level 1'
            -- and u.name like 'Daniel%'
            order by 1,2,3
        ";

        $result =  Yii::$app->db->createCommand($sql)->queryAll();

        $clusters=[];
        $cStudent='';
        $cModule='';
        $prevTime=0;
        $thisCluster=0;
        $start=0;
        $prevDate='';

        foreach($result as $item) {
            // d([$thisCluster, $item, ($item['unix_ts']-$prevTime)]);
            if($cStudent!=$item['Student'] || $cModule!=$item['Module'] ) {
                $cStudent=$item['Student'];
                $cModule=$item['Module'];
                $thisCluster=0;
                $start=$item['Submitted'];
            } else {
                if ( ($item['unix_ts']-$prevTime) < $clusterTime) {
                    $thisCluster++;
                } else {
                    if ($thisCluster >= $clusterSize) {
                        //d([$start, $thisCluster, $item]);
                        array_push($clusters, [ 'Student'=>$item['Student'], 'Module'=>$item['Module'], 'Start'=>$start, 'Eind'=>$prevDate, 'Aantal'=>$thisCluster ]);
                    }
                    $thisCluster=0;
                    $start=$item['Submitted'];
                }
            }
            $prevTime = $item['unix_ts'];
            $prevDate = $item['Submitted'];
        }

        if ($result) $data['col'] = array_keys($clusters[0]);
        $data['row'] = $clusters;
        $data['title'] = "Cluster Submissions";
        //dd(['end']);

        // $data['col'] = array_keys($result[0]);
        // $data['row'] = $result;
        // $data['title'] = "Cluster submissions";

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Een cluster is een serie opdrachten van minimaal 5 waarbij er minimaal elke 5 minuten een opdracht is ingeleverd.<br>Change URL params f.e. ..report/cluster-submissions?clusterSize=3&clusterTime=180'
        ]);
    }

    public function actionModules($export=false){
        $sql = "
        select  distinct c.id 'Cursus ID', c.korte_naam 'Korte Naam', c.naam 'Naam',
                r.module_id 'Module ID', d.naam 'Module Naam',
                case when d.naam is null then concat(r.module,'|/module-def/create|id|',r.module_id,'|name|',r.module) else concat(r.module,'|/module-def/update|id|',r.module_id) end '!Module C-aam',
                d.pos 'Positie'
        from resultaat r
        left outer join module_def d on r.module_id=d.id
        join course c on c.id=r.course_id
        order by d.pos
        ";

        $data = parent::executeQuery($sql, "Laat alle cursuusen en modules in alle cursussen zien", $export);

        return $this->render('output', [
            'data' => $data,
            'descr' => 'Overzicht voor beheer; aanmaken cursussen en modules.',
        ]);
    }

    public function actionHerkansen($export = false, $klas='') 
    { 
        if ($klas) {
            $select = "and klas='$klas'";
        } else {
            $select = '';
        }
        
        $sql = "
            select
            u.name Student,
            u.klas Klas,
            sum(case when (datediff(curdate(),submitted_at)<=21 && s.attempt=1) then 1 else 0 end) '+Poging1 R',
            sum(case when (datediff(curdate(),submitted_at)<=21 && s.attempt>1) then 1 else 0 end) '+Herkansingen R',
            sum(case when s.attempt=1 then 1 else 0 end) '+Poging1 T',
            sum(case when s.attempt>1 then 1 else 0 end) '+Herkansingen T',
            round(sum(case when (datediff(curdate(),submitted_at)<=21 && s.attempt>1) then 1 else 0 end) * 100 / sum(case when (datediff(curdate(),submitted_at)<=21 && s.attempt=1) then 1 else 0 end) ,0) '% R',
            round(sum(case when s.attempt>1 then 1 else 0 end) * 100 / sum(case when s.attempt=1 then 1 else 0 end) ,0) '% T'    
            from submission s
            join assignment a on a.id=s.assignment_id
            join user u on u.id = s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where s.submitted_at <> '1970-01-01 00:00:00'
            $select
            group by 1,2 
            order by 5 desc
        ";
        $data = parent::executeQuery($sql, "Aantal opdrachten ingeleverd", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Percentage is herkansingen ten opzichte van 1ste poging. R staat voor recent en T voor totaal.<br>De laatset twee percentages laten zien of het aantal herkansingen per kandidaat groeit, daalt of gelijk blijft.
                        <br>Alles van de laatste 21 dagen wordt als recent beschouwd.',
        ]);
    }

}

