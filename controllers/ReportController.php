<?php

// report controller, child of QueryBase. Standard reports.

namespace app\controllers;
use Yii;

use DateTime;

class ReportController extends QueryBaseController
{

    private function getKlas($klas) {
        if ($klas) {
            if ($klas=='all') {
                $select = "";
                setcookie('klas', null, -1, '/'); 
            } else {
                $select = "and u.klas='$klas'";
                setcookie('klas', $klas, 0, '/');
            }
        } else {
            if ( isset($_COOKIE['klas']) ){
                $select = "and u.klas='". $_COOKIE['klas']."'";
            } else {
                $select = '';
            }
        }
        return $select;
    }

    public function actionActief($export = false, $klas = '') { // menu 3.1 - Student laatst actief op....

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
            ".$this->getKlas($klas)."
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
            ".$this->getKlas($klas)."
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
            ".$this->getKlas($klas)."
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
            ".$this->getKlas($klas)."
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

    public function actionRanking2($sort = 'desc', $export = false, $klas = '') // menu 3.4 - Ranking studenten
    { 

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
                ".$this->getKlas($klas)."
            group by 1,2,3,4
            order by 3 $sort";

        $data = parent::executeQuery($sql, "Voortgang/Ranking " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
        ]);
    }

    public function actionRanking($export = false, $klas = '') // menu 3.4 - Ranking studenten
    { 

        # if a teacher is also student he has no code (code is null), so only get students with a code
        $sql = "
            select
                r.klas Klas,
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                min(concat(r.module_pos,' ',r.module)) 'Module',
                max(ranking_score) 'Score'
            FROM resultaat r
            JOIN user u on u.student_nr=r.student_nummer
            JOIN module_def d ON d.id=r.module_id
            WHERE r.voldaan != 'V'
            AND r.module_pos < 100
            AND u.code is not null
            ".$this->getKlas($klas)."
            group by 1,2
            order by 3 Desc, 4 desc
        ";

        $data = parent::executeQuery($sql, "Ranking Dev" . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
            'descr'=> "Genoemde module is de <b>eerste</b> dev-module die nog niet af is. Score is %punten per module + 100 voor elke afgeronde module.",
        ]);
    }

    public function actionModulesFinished($export = false, $klas = '') // menu 3.5 - Module is c keer voldaan
    { 

        $sql = "
            select
                Blok '#Blok' , Module,
                concat(af,'|/report/modules-open|moduleId|',module_id,'|voldaan|1') '!Afgerond',
                concat(naf,'|/report/modules-open|moduleId|',module_id) '!Niet Afgerond'
                from
                    (select course_id, c.korte_naam Blok, module_id, module Module, sum(case when voldaan='V' then 1 else 0 end) af, sum(case when voldaan!='V' then 1 else 0 end) naf
                    FROM resultaat o
                    JOIN module_def d on d.id=o.module_id
                    JOIN course c on c.id = course_id
                    JOIN user u on u.student_nr=o.student_nummer
                    where u.grade = 1
                    ".$this->getKlas($klas)."
                    group by 1,2,3,4
                    order by d.pos) alias
        ";
        // ToDo: order by werkt niet op server (order by moet in group by zitten)
        $data = parent::executeQuery($sql, "Voortgang per Module " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?klas=".$klas."&",
            'descr' => 'In het overzicht staan aleen studenten waarvan de grading aan staat',
            'nocount' => true,
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
            r.ingeleverd ingeleverd,
            round(r.punten*100/r.punten_max) 'Punten %'
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
            'descr' => 'In het overzicht staan aleen studenten waarvan de grading aan staat',
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

        $sql = "
            select module Module,
            sum(case when (datediff(curdate(),laatste_beoordeling)<=7) then 1 else 0 end) '+7',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=14  && datediff(curdate(),laatste_beoordeling)>7 ) then 1 else 0 end) '+14',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=21 && datediff(curdate(),laatste_beoordeling)>14 ) then 1 else 0 end) '+21',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=28 && datediff(curdate(),laatste_beoordeling)>21 ) then 1 else 0 end) '+28',
            sum(1) 'Aantal'
            from resultaat
            where 1
            ".$this->getKlas($klas)."
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

    public function actionNakijkenWeek($export = false) // Aantal beoordeligen per docent
    { 
        $schooljaarStart = date("Y")-1;
        $schooljaarStart.='-08-01';

        $sql = "
            SELECT concat(u.name,'|/report/nakijken-wie|user_id|',u.id) '!Naam', 
            sum(case when (datediff(curdate(),s.graded_at)<=7) then 1 else 0 end) '+laatste 7 dagen',
            sum(case when (datediff(curdate(),s.graded_at)> 7 && datediff(curdate(),s.graded_at)<=14 ) then 1 else 0 end) '+7-14 dagen',
            sum(case when (datediff(curdate(),s.graded_at)>14 && datediff(curdate(),s.graded_at)<=21 ) then 1 else 0 end) '+14-21 dagen',
            sum(case when ( s.graded_at>'$schooljaarStart' ) then 1 else 0 end) '+Schooljaar',
            sum(1) '+Alles'
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            group by 1
            order by 2 DESC
        ";
        $data = parent::executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);
        $lastLine = "<hr><a href=\"/report/nakijken-dag\" class=\"btn btn-light\" style=\"float: right;\">Dagoverzicht</a>";

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
            'lastLine' => $lastLine,
        ]);
    }

    public function actionNakijkenDag($export = false) { 

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

        $data = parent::executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);     
        $lastLine = "<hr><a href=\"/report/nakijken-week\" class=\"btn btn-light\" style=\"float: right;\">Weekoverzicht</a>";

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
            'lastLine' => $lastLine,
        ]);
    }

    public function actionNakijkenWie($export = false, $user_id=false) // Wie beoordeeld wat
    { 

        if ($user_id) {
            $select="where u.id=$user_id";
        } else {
            $select = "";
        }
        $sql = "
            SELECT u.name '#naam', m.pos '-pos', m.naam Module, sum(case when (datediff(curdate(),s.graded_at)<=7) then 1 else 0 end) '+laatste 7 dagen',
            sum(case when (datediff(curdate(),s.graded_at)> 7 && datediff(curdate(),s.graded_at)<=14 ) then 1 else 0 end) '+7-14 dagen',
            sum(case when (datediff(curdate(),s.graded_at)>14 && datediff(curdate(),s.graded_at)<=21 ) then 1 else 0 end) '+14-21 dagen',
            sum(1) '+Schooljaar'
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            inner join assignment_group g on g.id = a.assignment_group_id
            inner join module_def m on m.id = g.id
            $select
            group by 1,2,3
            order by 1,2
        ";
        $data = parent::executeQuery($sql, "Modules nagekeken door", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
        ]);
    }

    public function actionLastReportByStudent($export=false, $klas = '') {

        $sql=  "SELECT u.name Student, u.klas Klas, min( case when (isnull(l.timestamp)) then 999 else datediff(curdate(),l.timestamp) end) 'Dagen geleden'
                FROM user u
                LEFT OUTER JOIN log l on ( u.name = l.message and  l.subject = \"Student /public/index\" )
                WHERE u.klas in ('1A','1B','1C','1D')";
        $sql.=  $this->getKlas($klas);
        $sql.= " group by 1,2
                order by 3 ASC, 1";

        $data = parent::executeQuery($sql, "Studenten keken in Canvas Monitor", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Hoeveel dagen is het geleden dat het studentrapport is opgevraagd door iemand die <i>niet</i> is aangelogd in de Canvas Monitor?',
        ]);
    }

    public function actionVoortgang($export=false, $klas = '') {

        $sql = "SELECT m.id, m.naam, substring(m.naam,1,4) 'mod', c.korte_naam 'blok'
                from module_def m
                join assignment_group g on g.id=m.id
                join course c on c.id = g.course_id
                where generiek = 0
                order by m.pos";

        $modules = Yii::$app->db->createCommand($sql)->queryAll();

        $query = "";
        $count = 0;
        foreach($modules as $module) {
            $count++;
            // $query.=",sum( case when r.module_id=".$module['id']." && r.voldaan='V' then 1 else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
            $query.=",sum( case when r.module_id=".$module['id']." then (case  when r.voldaan='V' then 100 else round(r.punten*100/r.punten_max,0) end) else 0 end) '".str_pad($count,3,"0", STR_PAD_LEFT)."'";
        }

        $sql = "
            SELECT
            ranking_score 'Rank',
            u.student_nr '-Nummer',
            u.klas Klas,
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            u.comment '-Comment',
            u.message '-Message',
            sum( case when r.voldaan='V' then 1 else 0 end) '-Tot'
            $query
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE d.generiek = 0
            ".$this->getKlas($klas)."
            GROUP BY 1,2,3,4,5,6
            ORDER BY 1 DESC
        ";
        $data = $this->executeQuery($sql, "Voortgang Dev Modules", $export);
        $data['show_from']=1;

        return $this->render('outputVoortgang', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Alle dev modules het getal geeft % compleet. 100% geeft aan dat module is voldaan.',
            'modules' => $modules,
            'nocount' => 1,
        ]);
    }

    public function actionVoortgangDev($export = false) // menu 3.8 - Aantal beoordeligen per docent
    { 

        $sql = "
        select concat('&#9998;','|/student/update|id|',u.id) '!Actie',
               concat(u.name,'|/public/index|code|',u.code) '!Student',
               comment Comment,
               u.message 'Message',
               count(*) 'Dev Modules Voldaan'
        from resultaat r
        JOIN user u on u.student_nr= r.student_nummer
        where voldaan='V'
        and module_pos <= 70
        group by 1,2,3,4
        order by 5 desc, 1;
        ";
        $data = parent::executeQuery($sql, "Aantal dev modules voldaan en studie advies", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
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
        select  distinct  c.korte_naam '#Korte Naam', c.naam 'Naam',
                concat(c.id,'➞','|https://talnet.instructure.com/courses/',c.id,'/modules') '!Cursus ID',
                r.module_id 'Module ID',
                case when d.naam is null then concat(r.module,'|/module-def/create|id|',r.module_id,'|name|',r.module) else concat(r.module,'|/module-def/update|id|',r.module_id) end '!Module Canvas-naam',
                d.naam 'Module Monitor Naam',
                d.pos 'Positie'
        from resultaat r
        left outer join module_def d on r.module_id=d.id
        join course c on c.id=r.course_id
        order by c.korte_naam, c.id, r.module_id, d.pos
        ";

        $data = parent::executeQuery($sql, "Koppeling van modules tussen Canvas en Canvas Monitor", $export);

        return $this->render('output', [
            'data' => $data,
            'descr' => 'Overzicht voor beheer; aanmaken cursussen en modules.',
            'nocount' => true,
        ]);
    }

    public function actionCursus($export=false){
        $sql = "
        select id, 
        concat(naam,'|/course/update|id|',id) '!Cursus naam',
        korte_naam, pos, update_prio
        from course
        order by pos
        ";

        $data = parent::executeQuery($sql, "Koppeling van Canvas cursus en Canvas Monitor blok", $export);

        return $this->render('output', [
            'data' => $data,
            'descr' => 'Overzicht voor beheer; overzicht en aanpassen cursus.',
        ]);
    }

    public function actionHerkansen($export = false, $klas='') 
    { 

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
            ".$this->getKlas($klas)."
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

    public function actionTodayCheckIn($export=false,$klas='') {

        $sql="
        SELECT  u.klas '#Klas',
                u.name 'Student', DATE_FORMAT(c.timestamp,'%H:%i') 'Check-in', max(TIMESTAMPDIFF(HOUR, c.timestamp, now())) 'Uren geleden'
        FROM check_in c
        join user u  on u.id=c.studentId
        where c.action='i'
        and TIMESTAMPDIFF(HOUR, c.timestamp, now()) < 8
        ".$this->getKlas($klas)."
        group by 1,2
        order by 1 ASC,2 ASC, 3 DESC";

        $data = parent::executeQuery($sql, "Laatste check-in", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Meest recente check in van de afgelopen 8 uur',
        ]);

    }

    public function actionTodayMinMaxCheckIn($export=false,$klas='') {

        $sql="
        SELECT u.klas '#Klas', u.name '#Student',
        min(DATE_FORMAT(c.timestamp,'%H:%i')) 'Eerste',
        max(DATE_FORMAT(c.timestamp,'%H:%i')) 'Laatste'
        FROM check_in c
        join user u  on u.id=c.studentId
        where c.action='i'
        and TIMESTAMPDIFF(HOUR, c.timestamp, now()) < 8
        ".$this->getKlas($klas)."
        group by 1,2
        order by 1 ASC,2 ASC, 3 DESC";

        $data = parent::executeQuery($sql, "Alle check-ins", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Eerste en laatste check-in van de afgelopen 8 uur',
        ]);

    }

    public function actionWeekAllCheckIn($export=false,$klas='') {

        $sql="
        SELECT  u.klas '#Klas', 
                concat(u.name,'|/report/check-in-student|id|',u.id) '!#Student',
                left(dayname(c.timestamp),2) 'Dag',
                DATE_FORMAT(c.timestamp,'%d-%c') 'Datum',
                min(DATE_FORMAT(c.timestamp,'%H:%i')) 'Eerste',
                max(DATE_FORMAT(c.timestamp,'%H:%i')) 'Laatste'
        FROM check_in c
        join user u  on u.id=c.studentId
        where c.action='i'
        and DATEDIFF(c.timestamp, now()) < 8
        ".$this->getKlas($klas)."
        group by 1,2,3,4
        order by 1,2,4";


        $data = parent::executeQuery($sql, "Alle check-ins", $export);

        $lastLine = "<hr><a href=\"/check-in/index\" class=\"btn btn-light\" style=\"float: right;\">Alle check-ins</a>";

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Eerste en laatste check-in per dag van de afgelopen week',
            'lastLine' => $lastLine,
            'nocount' => true,
        ]);

    }

    public function actionCheckInStudent($export=false,$klas='',$id) {

        $sql="
        SELECT u.name '#Student',
            DATE_FORMAT(c.timestamp,'%v') '#week',
            left(dayname(c.timestamp),2) 'Dag',
            DATE_FORMAT(c.timestamp,'%c-%m') 'Datum',
            DATE_FORMAT(c.timestamp,'%H:%i') 'Tijd'
        FROM check_in c
        join user u  on u.id=c.studentId
        where c.action='i'
        and DATEDIFF(c.timestamp, now()) < 90
        and u.id=$id
        ".$this->getKlas($klas)."
        order by 4 DESC";

        $data = parent::executeQuery($sql, "Alle check-ins", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'over de afgelopen 90 dagen',
        ]);

    }

    public function actionTodayNoCheckIn($export=false,$klas='') {

        $sql="
        SELECT u.klas '#Klas', u.name 'Student'
        FROM user u
        where u.id not in ( select cc.studentId from check_in cc where TIMESTAMPDIFF(HOUR, cc.timestamp, now()) < 8 )
        ".$this->getKlas($klas)."
        and CHAR_LENGTH(u.code)>8
        order by 1,2";

        $data = parent::executeQuery($sql, "Niet aanwezig", $export);

        $lastLine = "<hr><a href=\"/check-in/index\" class=\"btn btn-light\" style=\"float: right;\">Alle check-ins</a><br>";

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Niet ingecheckt afgelopen 8 uur',
            'lastLine' => $lastLine,
        ]);

    }

}

