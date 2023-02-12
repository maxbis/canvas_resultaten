<?php

// report controller, child of QueryBase. Standard reports.

namespace app\controllers;
use Yii;



class PresentController extends QueryBaseController
{

    private function getKlas($klas) {
        return parent::getKlasQueryPart($klas);
    }


    // Menu 2 -- presentie

    public function actionTodayOverzicht($export=false,$klas='') {

        $sql="
        SELECT  u.klas '#Klas',
        concat(u.name,'|/report/check-in-student|id|',u.id) '!#Student',
        min(DATE_FORMAT(c.timestamp,'%H:%i')) 'Eerste',
        max(DATE_FORMAT(c.timestamp,'%H:%i')) 'Laatste',
        max(TIMESTAMPDIFF(HOUR, c.timestamp, now())) 'Uren geleden',
        CASE WHEN (TIMESTAMPDIFF(HOUR, c.timestamp, now()) < 8) THEN 1 ELSE NULL END '+Telling'
        FROM user u
        left join check_in c  on c.studentId=u.id and c.action='i' and TIMESTAMPDIFF(HOUR, c.timestamp, now()) < 8
        where LENGTH(u.code) > 10
        ".$this->getKlas($klas)."
        group by 1,2
        order by 1 ASC,2 ASC, 3 DESC";

        $data = parent::executeQuery($sql, "Alle check-ins", $export);

        return $this->render('/report/output', [
            'data' => $data,
            'action' => parent::exportButton($klas??='false'),
            'descr' => 'Eerste en laatste check-in van de afgelopen 8 uur',
        ]);

    }

    public function actionTodayCheckIn($export=false,$klas='') {

        $sql="
        SELECT  u.klas '#Klas',
                u.name 'Student', DATE_FORMAT(c.timestamp,'%H:%i') 'Check-in', max(TIMESTAMPDIFF(HOUR, c.timestamp, now())) 'Uren geleden'
        FROM check_in c
        join user u on u.id=c.studentId
        where c.action='i'
        and TIMESTAMPDIFF(HOUR, c.timestamp, now()) < 8
        ".$this->getKlas($klas)."
        group by 1,2
        order by 1 ASC,2 ASC, 3 DESC";

        $data = parent::executeQuery($sql, "Laatste check-in", $export);

        return $this->render('/report/output', [
            'data' => $data,
            'action' => parent::exportButton($klas??='false'),
            'descr' => 'Meest recente check in van de afgelopen 8 uur',
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

        return $this->render('/report/output', [
            'data' => $data,
            'action' => parent::exportButton($klas??='false'),
            'descr' => 'Niet ingecheckt afgelopen 8 uur',
            'lastLine' => $lastLine,
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
        and DATEDIFF(now(), c.timestamp) < 8
        ".$this->getKlas($klas)."
        group by 1,2,3,4
        order by 1,2,4";


        $data = parent::executeQuery($sql, "Alle check-ins", $export);

        $lastLine = "<hr><a href=\"/check-in/index\" class=\"btn btn-light\" style=\"float: right;\">Alle check-ins</a>";

        return $this->render('/report/output', [
            'data' => $data,
            'action' => parent::exportButton($klas??='false'),
            'descr' => 'Eerste en laatste check-in per dag van de afgelopen week',
            'lastLine' => $lastLine,
            'nocount' => true,
        ]);

    }

}

?>