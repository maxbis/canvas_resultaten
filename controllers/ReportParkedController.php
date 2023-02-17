<?php

// report controller, child of QueryBase. Standard reports.

namespace app\controllers;
use Yii;

use DateTime;

class ReportParkedController extends QueryBaseController
{

    private function getKlas($klas) {
        return parent::getKlasQueryPart($klas);
    }

    public function actionWegBeoordeeld($export = false) // Laatste beoordeling per module
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
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => 'Minimaal één opdracht van de module is beoordeeld ... dagen gelden.<br/>Automatisch beoordeeelde opdrachten worden ook geteld.',
        ]);
    }

    public function actionWegAantalBeoordelingen($export = false, $klas = '') // menu 3.7 - Beoordelingen per module over tijd
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
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1&klas='.$klas, 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => '(minimaal 1 opdracht van) module voor x studenten beoordeeld over 2, 7 en 14 dagen.<br/>Automatisch beoordeeelde opdrachten worden ook geteld.',
        ]);
    }

    public function actionWegVoortgangDev($export = false, $klas='') // not used anymore -> actionAdvies
    { 

        $sql = " select concat('&#9998;','|/student/update|id|',u.id) '!Actie',
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            comment Comment,
            u.message 'Message',
            count(*) 'Dev Modules Voldaan'
            from resultaat r
            JOIN user u on u.student_nr= r.student_nummer
            where voldaan='V'";
        $sql.=  $this->getKlas($klas);
        $sql.=" and module_pos <= 100
            group by 1,2,3,4
            order by 5 desc, 1;";

        $data = parent::executeQuery($sql, "Aantal dev modules voldaan en studie advies", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1&klas='.$klas, 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
        ]);
    }

    public function actionWegVoortgangPunten($export=false) {

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
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => 'Alle dev modules het getal geeft % compleet. 100% geeft aan dat module is voldaan.',
        ]);
    }

    public function actionWegTodayCheckIn2($export=false,$klas='') {

        $sql="
        SELECT  u.klas '#Klas',
                u.name 'Student',
                CASE WHEN (TIMESTAMPDIFF(HOUR, c.timestamp, now()) < 8) THEN DATE_FORMAT(c.timestamp,'%H:%i') ELSE '-' END 'Check-in',
                CASE WHEN (TIMESTAMPDIFF(HOUR, c.timestamp, now()) < 8) THEN max(TIMESTAMPDIFF(HOUR, c.timestamp, now())) ELSE '-' END 'Uren geleden'
        FROM check_in c
        join user u  on u.id=c.studentId
        where c.action='i'
        ".$this->getKlas($klas)."
        group by 1,2
        order by 1 ASC,2 ASC, 3 DESC";

        $data = parent::executeQuery($sql, "Laatste check-in", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1&klas='.$klas, 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
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
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1&klas='.$klas, 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => 'Eerste en laatste check-in van de afgelopen 8 uur',
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
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1&klas='.$klas, 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => 'over de afgelopen 90 dagen',
        ]);

    }

    public function actionAantalOpdrachten2($export=false){
        $sql = "
            select
            concat('<a target=_blank title=\"Naar Module\" href=\"https://talnet.instructure.com/courses/',c.id,'/modules\">',c.korte_naam,' &#129062;</a>') '#Blok',
            m.pos 'Pos',
            c.naam 'Naam',
            concat(m.naam,'|/report/opdrachten-module|id|',m.id) '!Naam',
            sum(CASE WHEN u.id is null OR s.id is null THEN 0 ELSE 1 END) '+Nakijken',
            max(datediff(now(), submitted_at)) 'Oudste',
            concat( '&#8594|/grade/not-graded-module|moduleId|',m.id) '!Link'


            from module_def m
            left join assignment a on a.assignment_group_id = m.id
            left join course c on c.id = a.course_id
            left outer join submission s on s.assignment_id= a.id and s.submitted_at > s.graded_at
            left join user u on u.id=s.user_id and u.grade=1
            left join resultaat r on module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0

            group by 1,2,3
            order by m.pos
        ";

        $data = parent::executeQuery($sql, "Module-overzicht", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => 'Blok, modulenaam en aantal opdrachten per module',
            'width' => [80,80,160,300],
        ]);
    }

    public function actionTest() {
        $assGroupId="8131";
        $code="879906e1182be0feb8066e270443988b";

        $sql="
        SELECT mi.title, mi.html_url,
		u.id u_id, a.id a_id, a.course_id, u.name naam, md.naam module, a.name Opdrachtnaam, s.workflow_state 'Status',
        CASE s.submitted_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.submitted_at END 'Ingeleverd',
        s.entered_score Score,
        a.points_possible MaxScore,
        s.attempt Poging,
        CASE s.graded_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.graded_at END Beoordeeld, r.name 'Door', s.preview_url Link,
        md.voldaan_rule VoldaanRule
        FROM module m
        join module_items mi on mi.module_id=m.id
        left outer join assignment a on a.id=mi.content_id
        left outer join submission s on s.assignment_id= a.id
        left join user u on u.id=s.user_id
        left outer join user r on r.id=s.grader_id
        left outer join module_def md on md.id = a.assignment_group_id
        where mi.published=1
        and ( a.assignment_group_id is null or (a.assignment_group_id=$assGroupId and  u.code='$code'))
        order by m.position, mi.position
        ";

        $data = parent::executeQuery($sql, "Test", false);

        return $this->render('output', [
            'data' => $data,
        ]);
    }
    
}

