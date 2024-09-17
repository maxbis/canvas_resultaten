<?php

// report controller, child of QueryBase. Standard reports.

namespace app\controllers;

use Yii;

use DateTime;

class ReportController extends QueryBaseController
{

    private function getKlas($klas)
    {
        return parent::getKlasQueryPart($klas);
    }



    // Menu Rapporten

    public function actionActief($export = false, $klas = '')
    { // menu 3.1 - Student laatst actief op....

        $sql = "
            SELECT
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            o.klas Klas, module Module, laatste_activiteit 'Wanneer', datediff(curdate(), laatste_activiteit) 'Dagen'
            FROM user u
            inner join resultaat o on u.student_nr=o.student_nummer
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) >= 2020
            and o.klas is not NULL
            " . $this->getKlas($klas) . "
        UNION
            SELECT
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            u.klas, '-', '-' , '∞'
            FROM user u where u.student_nr not in (select distinct student_nummer from resultaat where year(laatste_activiteit) > 2000 )
            AND u.student_nr > 0
        order by 4 desc
        ";

        return $this->render('/report/output', [
            'data' => parent::executeQuery($sql, "Laatste activiteit per student " . $klas, $export),
            'action' => parent::exportButton($klas ??= 'false'),
        ]);
    }

    public function actionAantalActiviteitenWeek($export = false, $klas = '', $sort = '')
    { // menu 3.2 - Week overzicht 

        $weekday = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];
        $date = new DateTime();
        $dayNr = $date->format('N') - 1; // 7 for zondag

        $selectQuery = '';
        for ($i = 0; $i < 7; $i++) {
            $selectQuery .= "\n,sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -" . $i . " DAY) as date) = CAST(convert_tz(s.submitted_at, '+00:00', '+00:00') as date) ) then 1 else 0 end) '+" . $weekday[$dayNr] . "'";
            $dayNr--;
            if ($dayNr < 0)
                $dayNr = 6;
        }

        $sortQuery = "";
        if ($sort) {
            $sort = "order by $sort";
        } else {
            $sortQuery = "ORDER BY SUBSTRING_INDEX(u.name, ' ', -1)";
        }

        // for($i=7; $i<9; $i++){  
        //     $select .= "\n,sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -".$i." DAY) as date) = CAST(s.submitted_at as date) ) then 1 else 0 end) '+.".$weekday[$dayNr]."'";
        //     $dayNr--;
        //     if ($dayNr < 0) $dayNr=6; 
        // }

        $sql = "
            SELECT u.klas Klas, concat(u.name,'|/public/index|code|',u.code) '!Student'
            $selectQuery
            ,sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -7 DAY) as date) < CAST(convert_tz(s.submitted_at, '+00:00', '+00:00') as date) ) then 1 else 0 end) '+wk'
            FROM user u
            left outer join submission s on u.id=s.user_id
            where student_nr > 0
            " . $this->getKlas($klas) . "
            group by 1,2
            $sortQuery
        ";

        // ORDER BY SUBSTRING_INDEX(u.name, ' ', -1)


        return $this->render('weekoverzicht', [
            'data' => parent::executeQuery($sql, "Ingeleverd afgelopen week*", $export),
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => 'Aantal opdrachten per student over de laatste 7 dagen.',
            'width' => [60, 280, 40, 40, 40, 40, 40, 40, 90],
            'sort' => $sort,
        ]);
    }

    public function actionAantalActiviteiten($export = false, $klas = '') // menu 3.2 - 12 wekenoverzicht -- export does not work!
    {
        $weekNumber = date("W");

        $weekNumbersArray = [];
        for ($i = 11; $i >= 0; $i--) {
            $thisWeekNumber = $weekNumber - $i;
            if ($thisWeekNumber <= 0) {
                $thisWeekNumber = $thisWeekNumber + 52; // if year has 53 weeks, week 53 will be added to week 52
            }
            array_push($weekNumbersArray, $thisWeekNumber);
        }
        //dd($weekNumbersArray);

        $sum_column = "";
        $i = 11;
        foreach ($weekNumbersArray as $thisWeek) {
            $sum_column .= "
                sum(case when week(submitted_at,1)=$thisWeek then 1 else 0 end  ) '+$thisWeek',";
            $i--;
        }
        // dd($sum_column);

        $sql = "
            select
            u.klas Klas,
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            u.student_nr '-student_nr',
            0 'Graph',
            $sum_column
            sum(1) '+Totaal'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where u.klas <> '0'
            " . $this->getKlas($klas) . "
            and datediff(curdate(),submitted_at)<81
            group by 1,2,3,4
            order by sum(case when (datediff(curdate(),submitted_at)<=84) then 1 else 0 end)  DESC
            limit 200
        ";

        // echo "<pre>";
        // echo $sql;
        // exit;

        $data = parent::executeQuery($sql, "Activiteiten over de laatste 12 weken" . $klas, $export);
        $data['show_from'] = 1;

        return $this->render('studentenActivity', [
            'data' => $data,
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => '',
            'color' => ['', '', '', '#f0f0f0', '', '', '', '', '',]
        ]);
    }

    public function actionAantalActiviteiten2($export = false, $klas = '') // menu 3.2 - 12 wekenoverzicht -- meer weken )expirimenteel
    {
        $weekNumber = date("W");

        $weekNumbersArray = [];
        for ($i = 21; $i >= 0; $i--) {
            $thisWeekNumber = $weekNumber - $i;
            if ($thisWeekNumber <= 0) {
                $thisWeekNumber = $thisWeekNumber + 52; // if year has 53 weeks, week 53 will be added to week 52
            }
            array_push($weekNumbersArray, $thisWeekNumber);
        }
        //dd($weekNumbersArray);

        $sum_column = "";
        $i = 11;
        foreach ($weekNumbersArray as $thisWeek) {
            $sum_column .= "
                sum(case when week(submitted_at,1)=$thisWeek then 1 else 0 end  ) '+$thisWeek',";
            $i--;
        }
        // dd($sum_column);

        $sql = "
            select
            -- sum(case when (datediff(curdate(),submitted_at)<=21) then 1 else 0 end) '-21',
            u.klas Klas,
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            u.student_nr '-student_nr',
            0 'Graph',
            $sum_column
            sum(1) '+Totaal'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where u.klas <> '0'
            " . $this->getKlas($klas) . "
            and datediff(curdate(),submitted_at)<365
            group by 1,2,3,4
            order by sum(case when (datediff(curdate(),submitted_at)<=84) then 1 else 0 end)  DESC
            limit 200
        ";

        // echo "<pre>";
        // echo $sql;
        // exit;

        $data = parent::executeQuery($sql, "Activiteiten over de laatste 12 weken" . $klas, $export);
        $data['show_from'] = 1;

        return $this->render('studentenActivity', [
            'data' => $data,
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => '',
        ]);
    }

    public function actionVoortgang($export = false, $klas = '')
    { // menu 3.3 - Voorgang (kleuren) rendered outputVoortgang.php

        $sql = "SELECT m.id, m.naam, substring(m.naam,1,4) 'mod',
                CASE
                    WHEN m.korte_naam IS NULL OR m.korte_naam = '' THEN c.korte_naam
                    ELSE m.korte_naam
                END AS blok
                FROM module_def m
                JOIN assignment_group g on g.id=m.id
                JOIN course c on c.id = g.course_id
                WHERE generiek = 0
                AND actief = 1
                ORDER BY m.pos";

        $modules = Yii::$app->db->createCommand($sql)->queryAll();

        $query = "";
        $count = 0;
        foreach ($modules as $module) {
            $count++;
            // $query.=",sum( case when r.module_id=".$module['id']." && r.voldaan='V' then 1 else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
            $query .= ",sum( case when r.module_id=" . $module['id'] . " then (case  when r.voldaan='V' then 100 else round(r.punten*100/r.punten_max,0) end) else 0 end) '" . str_pad($count, 3, "0", STR_PAD_LEFT) . "'";
        }

        $sql = "
            SELECT
            ranking_score 'Rank',
            u.student_nr '-Nummer',
            u.klas Klas,
            concat((CONCAT(SUBSTRING_INDEX(SUBSTRING(u.name,1,10),char(32),1),'',SUBSTRING(SUBSTRING_INDEX(u.name,char(32),-1),1,1))),'|/public/index|code|',u.code) '!Student',
            u.name '-Student-naam',
            sum( case when r.voldaan='V' then 1 else 0 end) '-Tot'
            $query
            ,concat((CONCAT(SUBSTRING_INDEX(SUBSTRING(u.name,1,10),char(32),1),'',SUBSTRING(SUBSTRING_INDEX(u.name,char(32),-1),1,1))),'|/public/index|code|',u.code) '!Naam'
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE d.generiek = 0
            and u.student_nr > 10
            " . $this->getKlas($klas) . "
            GROUP BY 1,2,3,4,5
            ORDER BY 1 DESC
        ";
        $data = $this->executeQuery($sql, "Voortgang Dev Modules", $export);
        $data['show_from'] = 1;

        return $this->render('outputVoortgang', [
            'data' => $data,
            // 'action' => Yii::$app->controller->action->id."?",
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => 'Alle dev modules het getal geeft % compleet. 100% geeft aan dat module is voldaan.',
            'modules' => $modules,
            'lastLine' => "<hr><a href=\"/report/voortgang2\" class=\"btn bottom-button\">Voortgang (alleen ingeleverd)</a>",
            # 'nocount' => 1,
        ]);
    }
    public function actionVoortgang2($export = false, $klas = '')
    { // menu 3.3 - Voorgang (kleuren) rendered outputVoortgang.php

        $sql = "SELECT m.id, m.naam, substring(m.naam,1,4) 'mod',
                CASE
                    WHEN m.korte_naam IS NULL OR m.korte_naam = '' THEN c.korte_naam
                    ELSE m.korte_naam
                END AS blok
                from module_def m
                join assignment_group g on g.id=m.id
                join course c on c.id = g.course_id
                where generiek = 0
                order by m.pos";

        $modules = Yii::$app->db->createCommand($sql)->queryAll();

        $query = "";
        $count = 0;
        foreach ($modules as $module) {
            $count++;
            // $query.=",sum( case when r.module_id=".$module['id']." && r.voldaan='V' then 1 else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
            $query .= ",sum( case when r.module_id=" . $module['id'] . " then (case  when r.voldaan='V' then 100 else round(r.ingeleverd*100/r.aantal_opdrachten,0) end) else 0 end) '" . str_pad($count, 3, "0", STR_PAD_LEFT) . "'";
        }

        $sql = "
            SELECT
            ranking_score 'Rank',
            u.student_nr '-Nummer',
            u.klas Klas,
            concat((CONCAT(SUBSTRING_INDEX(SUBSTRING(u.name,1,10),char(32),1),'',SUBSTRING(SUBSTRING_INDEX(u.name,char(32),-1),1,1))),'|/public/index|code|',u.code) '!Student',
            u.name '-Student-naam',
            sum( case when r.ingeleverd=r.aantal_opdrachten then 1 else 0 end) '-Tot'
            $query
            ,concat((CONCAT(SUBSTRING_INDEX(SUBSTRING(u.name,1,10),char(32),1),'',SUBSTRING(SUBSTRING_INDEX(u.name,char(32),-1),1,1))),'|/public/index|code|',u.code) '!naam'
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE d.generiek = 0
            and u.student_nr > 10
            " . $this->getKlas($klas) . "
            GROUP BY 1,2,3,4,5
            ORDER BY 1 DESC
        ";
        $data = $this->executeQuery($sql, "Dev Modules Ingeleverd", $export);
        $data['show_from'] = 1;

        return $this->render('outputVoortgang', [
            'data' => $data,
            // 'action' => Yii::$app->controller->action->id."?",
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => 'Alle dev modules het getal geeft % ingeleverd.',
            'modules' => $modules,
            'lastLine' => "<hr><a href=\"/report/voortgang\" class=\"btn bottom-button\">Voortgang (beoordeeld)</a>",
            # 'nocount' => 1,
        ]);
    }

    public function getStudentYear()
    {
        # determine how many years old this cohort is 
        $thisCohort = explode('.', $_SERVER['SERVER_NAME'])[0];
        $currentYearLastTwoDigits = (int) substr(date("Y"), -2);
        return $currentYearLastTwoDigits - (int) substr($thisCohort, 1);
    }

    public function actionRanking($export = false, $klas = '') // menu 3.4 - Ranking studenten
    {

        # if a teacher is also student he has no code (code is null), so only get students with a code
        $sql = "
            select
                r.klas Klas,
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                min(concat(r.module_pos,' ',r.module)) 'Module',
                max(ranking_score) '+Score'
            FROM resultaat r
            JOIN user u on u.student_nr=r.student_nummer
            JOIN module_def d ON d.id=r.module_id
            WHERE r.voldaan != 'V'
            AND d.generiek = 0
            AND u.code is not null
            AND length(u.klas) > 1
            " . $this->getKlas($klas) . "
            group by 1,2
            order by 4 Desc, 3 desc
        ";

        $data = parent::executeQuery($sql, "Ranking Dev " . $klas, $export);

        if ($this->getStudentYear() < 2) {
            $lastLine = "<hr><a href=\"/report/predictions\" class=\"btn bottom-button\">Voorspellingen</a>";
        } else {
            $lastLine = "";
        }


        return $this->render('output', [
            'data' => $data,
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => "Genoemde module is de <b>eerste</b> dev-module die nog niet af is. Score is %punten per module + 100 voor elke afgeronde module.",
            'lastLine' => $lastLine,
        ]);
    }

    // menu devider -----------------

    public function actionAantalOpdrachten($export = false, $generiek = 0)
    { // menu 3.5 - Module overzicht
        if ($generiek == 0) {
            $where = "and m.generiek=0";
        } else {
            $where = "";
        }

        $sql = "
            select
            CASE
                WHEN m.korte_naam IS NULL OR m.korte_naam = '' THEN c.korte_naam
                ELSE m.korte_naam
            END AS'#Blok',
            concat('<a target=_blank title=\"Naar Module\" href=\"https://talnet.instructure.com/courses/',c.id,'/modules\">',substr(c.naam,1,12),' &#129062;</a>') 'Course (Track)',
            concat(m.naam,'|/report/opdrachten-module|id|',m.id) '!Naam',
            m.pos 'Pos',
            sum(1) '+Aantal<br>Opg',
            norm_uren 'Uren',
            norm_uren '++Uren',
            concat('(scores)','|/report/opdrachten-scores|id|',m.id) '!Sco',
            concat('(voortgang)','|/report/opdrachten-verdeling|id|',m.id) '!Voo',
            ceil( sum(a.points_possible) ) '+Aantal<br>Punten',
            concat(m.voldaan_rule,'|module-def/update|id|',m.id) '!Voldaan'
            
            from module_def m
            left join assignment a on a.assignment_group_id = m.id
            left join course c on c.id = a.course_id
            where
            a.published = 1
            $where
            group by 1,2,3
            order by m.pos
        ";

        $data = parent::executeQuery($sql, "Module-overzicht", $export);
        if ($generiek == 0) {
            $lastLine = "<hr><a href=\"/report/aantal-opdrachten?generiek=1\" class=\"btn bottom-button right\">Alles</a>";
            $button1 = ['name' => 'Alles', 'link' => '/report/aantal-opdrachten', 'param' => 'generiek=1', 'class' => 'btn btn-secondary', 'title' => 'Toon Alle Blokken',];
            $descr = 'Overzicht van alle dev blokken';
        } else {
            $lastLine = "<hr><a href=\"/report/aantal-opdrachten?generiek=0\" class=\"btn bottom-button right\">Dev</a>";
            $button1 = ['name' => 'Dev', 'link' => '/report/aantal-opdrachten', 'param' => 'generiek=0', 'class' => 'btn btn-secondary', 'title' => 'Toon Dev Blokken',];
            $descr = 'Overzicht van alle blokken (dev plus generiek)';
        }

        return $this->render('output', [
            'data' => $data,
            // 'action' => Yii::$app->controller->action->id."?",
            'action' => [
                $button1,
                parent::exportButton($klas ??= 'false'),
            ],
            'lastLine' => $lastLine,
            'descr' => $descr,
            'width' => [40, 180, 240, 40, 40, 40, 40, 40, 40, 60],
            'bgcolor' => ['', '', '#f6f6f6', '', '', '', '', '', '', '#f6f6f6'],
            'color' => ['', '', '', '#8080b0', '#8080b0', '#8080b0', '#8080b0', '#a0a0a0', '', '', '#8080b0'],
        ]);
    }

    public function actionModulesFinished($export = false, $klas = '') // menu 3.6 - Module is c keer voldaan
    {

        $sql = "
            select
                Blok '#Blok',
                Module,
                CASE WHEN af > 1 THEN concat(af,'|/report/modules-open|moduleId|',module_id,'|voldaan|1') ELSE '0' END '!Afgerond',
                CASE WHEN naf > 1 THEN concat(naf,'|/report/modules-open|moduleId|',module_id) ELSE '0' END '!Niet Afgerond'
                from
                    (select course_id, c.korte_naam Blok, module_id, module Module, sum(case when voldaan='V' then 1 else 0 end) af, sum(case when voldaan!='V' then 1 else 0 end) naf
                    FROM resultaat o
                    JOIN module_def d on d.id=o.module_id
                    JOIN course c on c.id = course_id
                    JOIN user u on u.student_nr=o.student_nummer
                    where u.grade = 1
                    " . $this->getKlas($klas) . "
                    group by 1,2,3,4
                    order by d.pos) alias
        ";
        // ToDo: order by werkt niet op server (order by moet in group by zitten)
        $data = parent::executeQuery($sql, "Voortgang per Module " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => 'In het overzicht staan aleen studenten waarvan de grading aan staat',
            'nocount' => true,
        ]);
    }

    public function actionModulesOpen($moduleId, $voldaan = 0, $export = false) // menu 3.6.1 - report accessible via ModuleFinished report
    {
        if ($voldaan) {
            $voldaanQuery = "voldaan = 'V'";
        } else {
            $voldaanQuery = "voldaan != 'V'";
        }
        $sql = "
            SELECT r.module_pos '-c1', r.module_id  '-c2', r.module '-Module',
            u.klas 'Klas',
            concat(r.student_naam,'|/public/details-module|code|',u.code,'|assGroupId|',r.module_id) '!Student',
            r.ingeleverd ingeleverd,
            round( r.ingeleverd*100/r.aantal_opdrachten) 'Opdrachten %',
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

        $data['title'] = ($voldaan ? 'Afgeronde' : 'Open') . " opdrachten voor " . $data['row'][0]['-Module'];

        return $this->render('output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1&moduleId=' . $moduleId,
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'descr' => 'In het overzicht staan aleen studenten waarvan de grading aan staat',
            # 'width' => [40,40,60,160,80,80,80 ], 
        ]);
    }

    public function actionLastReportByStudent($export = false, $klas = '')
    { // menu 3.7 - Student keek in monitor

        $sql = "SELECT u.name Student, u.klas Klas, min( case when (isnull(l.timestamp)) then 999 else datediff(curdate(),l.timestamp) end) 'Dagen geleden'
                FROM user u
                LEFT OUTER JOIN log l on ( u.name = l.message and  l.subject = \"Student /public/index\" )
                WHERE LENGTH(u.klas) = 2 ";
        $sql .= $this->getKlas($klas);
        $sql .= " group by 1,2
                order by 3 ASC, 1";

        $data = parent::executeQuery($sql, "Studenten keken in Canvas Monitor", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => 'Hoeveel dagen is het geleden dat het studentrapport is opgevraagd door iemand die <i>niet</i> is aangelogd in de Canvas Monitor?',
        ]);
    }

    public function actionPogingen($export = false, $klas = '') // menu 3.8 - Pogingen
    {
        $sql = "select cast( (select sum(1)from submission) / (select sum(1) from user where grade=1) as unsigned integer) average";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        $average = $result[0]['average']; // average of number of submitted assignements

        $sql = "
            select
            u.klas Klas,
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            round(sum(case when (datediff(curdate(),submitted_at)<=42 && s.attempt>1) then 1 else 0 end) * 100 / sum(case when (datediff(curdate(),submitted_at)<=42 && s.attempt=1) then 1 else 0 end) ,0) 'Herkansingen % Recent',
            round(sum(case when s.attempt>1 then 1 else 0 end) * 100 / sum(case when s.attempt=1 then 1 else 0 end) ,0) '% Totaal',
            '',
            sum(case when (datediff(curdate(),submitted_at)<=42) then 1 else 0 end) 'Gemaakt Recent',
            sum(1) 'Totaal',
            ''
            from submission s
            join assignment a on a.id=s.assignment_id
            join user u on u.id = s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where s.submitted_at <> '1970-01-01 00:00:00'
            and s.workflow_state='graded'
            " . $this->getKlas($klas) . "
            group by 1,2
            order by 3 desc
        ";
        $data = parent::executeQuery($sql, "Meerdere pogingen", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => 'Percentage is herkansingen ten opzichte van 1ste poging. 100% betekent dat de student gemiddeld 2 pogingen nodig heeft.<br>De laatset twee percentages laten zien of het aantal herkansingen per kandidaat groeit, daalt of gelijk blijft.
                        <br>Recent is van de laatste 6 weken.',
            'width' => [80, 300, 100, 100, 120, 100, 100],
        ]);
    }

    // menu devider --------------------
    // menu 3.9 -> resultaat index

    public function actionNakijkenWeek($export = false) // Menu 3.10 - Aantal beoordeligen per docent
    {
        $schooljaarStart = date("Y") - 1;
        if (date('n') >= 8)
            $schooljaarStart++;
        $schooljaarStart .= '-08-01';

        $sql = "
            SELECT concat(u.name,'|/report/nakijken-wie|user_id|',u.id) '!Naam', 
            sum(case when (datediff(curdate(),s.graded_at)<=7) then 1 else 0 end) '+laatste 7 dagen',
            sum(case when (datediff(curdate(),s.graded_at)> 7 && datediff(curdate(),s.graded_at)<=14 ) then 1 else 0 end) '+7-14 dagen',
            sum(case when (datediff(curdate(),s.graded_at)>14 && datediff(curdate(),s.graded_at)<=21 ) then 1 else 0 end) '+14-21 dagen',
            sum(case when ( s.graded_at>'$schooljaarStart' ) then 1 else 0 end) '+Schooljaar',
            sum(1) '+Alles'
            FROM submission s
            inner join user u on u.id=s.grader_id
            group by 1
            order by 2 DESC
        ";
        $data = parent::executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);
        $lastLine = "<hr><a href=\"/report/nakijken-dag\" class=\"btn bottom-button\">Dagoverzicht</a>";

        return $this->render('output', [
            'data' => $data,
            'action' => parent::exportButton($klas ??= 'false'),
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
            'lastLine' => $lastLine,
            'width' => [250, 150, 150, 150, 150],
        ]);
    }

    // Nakijken sub-reports

    public function actionNakijkenDag($export = false)
    { // menu 3.10.1

        $weekday = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];
        $date = new DateTime();
        $dayNr = $date->format('N') - 1; // 7 for zondag

        $select = '';
        for ($i = 0; $i < 7; $i++) {
            $select .= "\n,sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -" . $i . " DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+" . $weekday[$dayNr] . "'";
            $dayNr--;
            if ($dayNr < 0)
                $dayNr = 6;
        }

        // inner join on assignment and module_def is added to filter out assignements that are not part (any more) of the Canvas Monitor
        $sql = "
            SELECT u.name naam
            $select
            FROM submission s
            inner join user u on u.id=s.grader_id
            inner join assignment a on a.id=s.assignment_id
            inner join module_def d on d.id=a.assignment_group_id
            where datediff(curdate(),s.graded_at)<=7
            group by 1
            order by 1
        ";

        $data = parent::executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);
        $lastLine = "<a href=\"/report/nakijken-week\" class=\"btn bottom-button left\"><< terug</a>";
        $lastLine .= "<a href=\"/report/nakijken-dag-all\" class=\"btn bottom-button\" >Alle cohorten</a>";

        return $this->render('output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1',
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
            'lastLine' => $lastLine,
            'width' => [250, 60, 60, 60, 60, 60, 60],
        ]);
    }

    public function actionNakijkenDagAll($export = false)
    { // menu 3.10.2 - Aantal beoordelingen per docent

        // $schooljaarStart = date("Y")-1;
        // if(date('n') >= 8) $schooljaarStart++;
        // $schooljaarStart.='-08-01';

        $weekday = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];
        $date = new DateTime();
        $dayNr = $date->format('N') - 1; // 7 for zondag

        $select = '';
        for ($i = 0; $i < 7; $i++) {
            $select .= "\n,sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -" . $i . " DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+" . $weekday[$dayNr] . "'";
            $dayNr--;
            if ($dayNr < 0)
                $dayNr = 6;
        }
        // $select .= "\n,sum(case when ( s.graded_at>'$schooljaarStart' ) then 1 else 0 end) '+Schooljaar'";
        $select .= "\n,sum(1) '+Week'";

        $sql = "
            SELECT grader_name naam
            $select
            FROM all_submissions s
            where grader_name is not null
            and datediff(curdate(),s.graded_at)<=7
            group by 1
            order by 1
        ";

        $data = parent::executeQuery($sql, "Totaal aantal opdrachten beoordeeld door", $export);
        $lastLine = "<hr><a href=\"" . Yii::$app->request->referrer . "\" class=\"btn bottom-button left\"><< terug</a>";

        return $this->render('output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1',
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'descr' => 'Aantal beoordelingen over alle cohorten',
            'lastLine' => $lastLine,
            'width' => [250, 60, 60, 60, 60, 60, 60],
        ]);
    }

    // END of MENU Rapporten


    // *** Menu Beheer ***

    public function actionStudentenLijst($export = false, $klas = '') // menu 6.2 - Studentencodes (export)
    {
        $sql = "SELECT  klas Klas,
                        id '-Canvas Id',
                        student_nr 'Student nr',
                        name Naam,
                        login_id '-email',
                        code '-Code',
                        comment Comment,
                        message Message
                FROM user u
                where length(u.klas)>1 " . $this->getKlas($klas);

        $data = parent::executeQuery($sql, "Studentenlijst", $export);

        return $this->render('/report/output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1',
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'descr' => 'Studentenlijst (voor Export naar Excel)',
        ]);
    }

    public function actionAdvies($export = false, $klas = '') // Menu 6.3 - Dev Voortgang (Adviezen)
    {

        $sql = " select 
            u.id id, u.name name, u.message message, u.comment comment, u.code, sum(case when voldaan='V' then 1 else 0 end) 'voldaan', sum(ingeleverd) ingeleverd
            from resultaat r
            JOIN user u on u.student_nr= r.student_nummer
            where module_pos <= 100 ";
        $sql .= $this->getKlas($klas);
        $sql .= " group by 1,2,3,4 order by 5 desc, 6 desc;";

        $data = parent::executeQuery($sql, "Advies", $export);

        return $this->render('advies', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id . "?",
            'descr' => "Dev modules voldaan , opdrachten gemaakt en BSA-boodschap",
            'width' => [80, 80, 80],
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1',
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
        ]);
    }

    public function actionModules($export = false)
    { // menu 6.5 - Modules
        $sql = "
        select  
                CASE
                    WHEN d.korte_naam IS NULL OR d.korte_naam = '' THEN c.korte_naam
                    ELSE d.korte_naam
                END AS '#Blok', 
                c.naam 'Naam',
                count(r.id) 'Res',
                concat(c.id,'➞','|https://talnet.instructure.com/courses/',c.id,'/modules') '!Cursus ID',
                a.id 'Module ID',
                case when d.id is null then '&#10060;' else '&nbsp;&#10003;' end 'In CM',
                case when d.actief = 0 then '&#10060;' else '&nbsp;&#10003;' end 'Actief',
                case when d.naam is null then
                    concat(a.name,'|/module-def/create|id|',a.id,'|name|',a.name)
                else
                    concat(d.naam,'|/module-def/update|id|',d.id)
                end '!Module Canvas-naam',
                -- d.naam 'Module Monitor Naam',
                d.pos 'Positie'
        from course c
        join assignment_group a on c.id=a.course_id
        left outer join module_def d on a.id=d.id
        left outer join resultaat r on r.module_id=a.id
        where substring(a.name,1,1) != '!'
        group by 1,2,4,5,6,7,8
        order by (d.pos IS NULL), d.pos ASC, a.id ASC
        ";

        $lastLine = "<hr><a href=\"/report/modules2\" class=\"btn bottom-button\">Volgorde aanpassen...</a>";
        $data = parent::executeQuery($sql, "Koppeling van modules tussen Canvas en Canvas Monitor", $export);

        return $this->render('output', [
            'data' => $data,
            'descr' => 'Overzicht voor beheer; aanmaken cursussen en modules.',
            'nocount' => true,
            'lastLine' => $lastLine,
            'width' => [50, 160, 60, 80, 80, 80, 80, 240],
        ]);
    }

    public function actionModules2($export = false)
    { // menu 6.5 - Modules
        $sql = "
        select  d.id,
                CASE
                    WHEN d.korte_naam IS NULL OR d.korte_naam = '' THEN c.korte_naam
                    ELSE d.korte_naam
                END 'blok',
                d.naam 'naam',
                c.id 'cursus_id',
                c.naam 'cursus_naam',
                d.norm_uren 'uren',
                case when d.actief = 0 then '&#10060;' else '&nbsp;&#10003;' end 'actief',
                case when d.nakijken = 0 then '&#10060;' else '&nbsp;&#10003;' end 'nakijken'
        from course c
        join assignment_group a on c.id=a.course_id
        join module_def d on a.id=d.id
        order by (d.pos IS NULL), d.pos ASC
        ";

        $items = Yii::$app->db->createCommand($sql)->queryAll();


        return $this->render('/module-def/sortModules', [
            'items' => $items,
        ]);
    }


    // END of MENU Beheer



    // Complette activity list per student (accessed from student home page by admin user)
    public function actionActivity($studentnr = '99', $export = false)
    { // activity report per user when/what - click on graph on users home page - needs to be here becasue students cannot access this.
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

        if ($data && isset($data['row'][0]['student'])) {
            $studentNr = $data['row'][0]['student_nr'];
            $klas = $data['row'][0]['klas'];
        } else {
            $studentNr = 0;
            $klas = "";
        }

        $data['title'] = "Activity report for " . $studentNr . " / " . $klas;


        return $this->render('studentActivity', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1&studentnr=' . $studentNr,
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'descr' => 'Laaste 400 inzendingen. Geel geacceerd is nog niet beoordeeld.',
        ]);
    }


    public function actionOpdrachtenScores($id, $export = false)
    {

        $sql = " select a.position pos, u.klas klas, u.name '#Student', a.name Opdracht, s.entered_score Score,
                    CASE
                    WHEN s.graded_at >= '1971-01-01' THEN s.graded_at
                    ELSE '-'
                    END AS graded,
                    s.attempt Poging
                from assignment a
                left join course c on c.id = a.course_id
                left join submission s on s.assignment_id = a.id
                left join user u on u.id = s.user_id
                where assignment_group_id=$id
                and a.published=1
                and u.id is not null
                order by u.name ASC, a.position";

        $data = parent::executeQuery($sql, "Opdrachten voor module", $export);
        $data['show_from'] = 1;

        return $this->render('output', [
            'data' => $data,
            'descr' => 'Opdrachten en punten voor deze module',
            'width' => [0, 30, 250, 300, 60, 200, 60],
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1&id=' . $id,
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
        ]);
    }

    # accessed via actionAantalOpdrachten()
    public function actionOpdrachtenVerdeling($id, $export = false)
    {
        $sql = "select naam from module_def where id = $id";
        $moduleNaam = Yii::$app->db->createCommand($sql)->queryOne()['naam'];

        $sql = "
            select
            u.klas Klas,
            u.name Student,
            COALESCE( case  when r.voldaan='V' then 100 else round(r.ingeleverd*100/r.aantal_opdrachten,0) end,0 ) 'Perc Af',
            round(r.punten, 0) Punten,
            ranking_score 'Score'
            FROM user u
            LEFT OUTER JOIN resultaat r on u.student_nr=r.student_nummer and r.module_id =$id
            where u.student_nr > 100
            order by 3 DESC, 4 DESC;
        ";

        $data = parent::executeQuery($sql, "Voortgang \"" . $moduleNaam . "\"", $export);
        $data['show_from'] = 0;

        return $this->render('output', [
            'data' => $data,
            'descr' => '',
            'width' => [20, 300, 80, 80, 80],
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1&id=' . $id,
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
        ]);
    }

    // called form module overzicht
    public function actionOpdrachtenModule($id, $export = false, $test = 0)
    {
        if ($test == 1) {
            $cohort = explode('.', $_SERVER['SERVER_NAME'])[0];
            $link = ", concat('ac', '|http://localhost:5000/correcta/$cohort/',a.id) '!ac'";
        } else {
            $link = "";
        }

        $sql = "
            select c.id 'course_id', c.naam 'cursus_naam',
                korte_naam '#Blok',
                concat(REPLACE(a.name, '|', ':'),'|https://talnet.instructure.com/courses/',a.course_id,'/assignments/',a.id) '!Naam',
                a.points_possible '+Punten', '',
                CASE 
                    WHEN n.assignment_id is null THEN '-' 
                    ELSE '&#10003;'
                END Nakijken,
                concat('✎ ','|/nakijken/update/|assignment_id|',a.id) '!Update'
                $link
            from assignment a
            left join course c on c.id=a.course_id
            left outer join canvas.nakijken n on n.assignment_id=a.id
            where assignment_group_id=$id
            and a.published=1
            order by a.position
        ";

        $data = parent::executeQuery($sql, "Opdrachten voor module", $export);
        $data['show_from'] = 2;
        $lastLine = "<a href=\"/report/aantal-opdrachten\" class=\"btn bottom-button left\"><< terug</a>";

        $subDomain = explode('.', $_SERVER['SERVER_NAME'])[0];

        if (isset($data['row'][0]['cursus_naam'])) {
            $data['title'] = $data['row'][0]['cursus_naam'];
            $lastLine .= "<a target=_blank class=\"button btn bottom-button\" title=\"Naar Module\" href=\"https://talnet.instructure.com/courses/" . $data['row'][0]['course_id'] . "\">Naar module &#129062;</a>";
            # $lastLine.="<a target=_blank class=\"button btn bottom-button\" title=\"Naar Module\" href=\"http://localhost:5000/section/refresh/".$subDomain.'/'.$id."\">Naar nakijken &#129062;</a>";
        }

        return $this->render('output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1&id=' . $id,
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'lastLine' => $lastLine,
            'descr' => 'Opdrachten en punten voor dit blok',
            'width' => [0, 0, 80, 600, 80],
        ]);
    }

    // *** hidden features ***
    public function actionNakijkenWie($export = false, $user_id = false) // Wie beoordeeld wat
    {

        if ($user_id) {
            $select = "where u.id=$user_id";
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
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1',
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
        ]);
    }

    public function actionNakijkenWie2($export = false) // Wie beoordeeld wat de laatste week
    {

        $sql = "
            select g.name Docent, u.name Student , s.graded_at Beoordeeld, s.entered_score Score, m.naam Module
            from submission s
            join user u on u.id=s.user_id
            join user g on g.id=s.grader_id
            join assignment a on a.id=s.assignment_id
            join module_def m on m.id = a.assignment_group_id
            WHERE datediff(now(), s.graded_at)<7
            order by s.graded_at DESC
            ";
        $data = parent::executeQuery($sql, "Modules nagekeken door", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1',
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
        ]);
    }

    public function actionClusterSubmissions($clusterSize = 8, $clusterTime = 300, $export = false)
    {
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

        $result = Yii::$app->db->createCommand($sql)->queryAll();

        $clusters = [];
        $cStudent = '';
        $cModule = '';
        $prevTime = 0;
        $thisCluster = 0;
        $start = 0;
        $prevDate = '';

        foreach ($result as $item) {
            // d([$thisCluster, $item, ($item['unix_ts']-$prevTime)]);
            if ($cStudent != $item['Student'] || $cModule != $item['Module']) {
                $cStudent = $item['Student'];
                $cModule = $item['Module'];
                $thisCluster = 0;
                $start = $item['Submitted'];
            } else {
                if (($item['unix_ts'] - $prevTime) < $clusterTime) {
                    $thisCluster++;
                } else {
                    if ($thisCluster >= $clusterSize) {
                        //d([$start, $thisCluster, $item]);
                        array_push($clusters, ['Student' => $item['Student'], 'Module' => $item['Module'], 'Start' => $start, 'Eind' => $prevDate, 'Aantal' => $thisCluster]);
                    }
                    $thisCluster = 0;
                    $start = $item['Submitted'];
                }
            }
            $prevTime = $item['unix_ts'];
            $prevDate = $item['Submitted'];
        }

        if ($result)
            $data['col'] = array_keys($clusters[0]);
        $data['row'] = $clusters;
        $data['title'] = "Cluster Submissions";
        //dd(['end']);

        // $data['col'] = array_keys($result[0]);
        // $data['row'] = $result;
        // $data['title'] = "Cluster submissions";

        return $this->render('output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1',
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'descr' => 'Een cluster is een serie opdrachten van minimaal 5 waarbij er minimaal elke 5 minuten een opdracht is ingeleverd.<br>Change URL params f.e. ..report/cluster-submissions?clusterSize=3&clusterTime=180'
        ]);
    }

    public function actionWorkingOn($sort = 'asc', $export = false, $klas = '') // ??? - Student werken aan...
    {

        $sql = "
            select  m.pos, c.korte_naam '#&nbsp',m.naam Module,
            sum(case when (datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end) '+Ingeleverd',
            sum(case when (s.workflow_state='graded' && datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end) '+Waarvan nagekeken'
            -- round ( (sum(case when (s.workflow_state='graded' && datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end)*100)/ ( sum(case when (datediff(curdate(),s.submitted_at)<=14) then 1 else 0 end) ) ,0) '+Test'
            from module_def m
            join assignment_group g on g.id = m.id
            join assignment a on a.assignment_group_id=g.id
            left outer join submission s on s.assignment_id=a.id
            left join course c on c.id = a.course_id
            join user u on u.id = s.user_id
            " . $this->getKlas($klas) . "
            group by 1,2
            order by m.pos $sort
        ";

        $data = parent::executeQuery($sql, "Studenten " . $klas . " werken aan", $export);

        $data['show_from'] = 1;

        return $this->render('output', [
            'data' => $data,
            'action' => [
                'link' => Yii::$app->controller->action->id,
                'param' => 'export=1&klas=' . $klas,
                'class' => 'btn btn-primary',
                'title' => 'Export to CSV',
            ],
            'descr' => 'Aantal opdrachten ingeleverd en nageken per module over de afgelopen 14 dagen <br><i>Nakijken</i> telt alleen de modules die ook over de laatste twee weken zijn ingeleverd.',
        ]);
    }

    public function actionPredictions($klas = '')
    {

        $sql = "select u.id, u.name, u.klas, u.code, max(ranking_score) score, min(concat(r.module_pos,' ',r.module)) 'module'
                from user u
                join resultaat r on u.student_nr=r.student_nummer
                join module_def d on d.id=r.module_id
                where student_nr > 100 
                " . $this->getKlas($klas) . "
                and r.voldaan != 'V'
                AND u.code is not null
                AND length(u.klas) > 1
                group by 1, 2, 3, 4
                order by klas, name";

        $students = Yii::$app->db->createCommand($sql)->queryAll();

        $result = [];

        foreach ($students as $student) {
            $id = $student['id'];
            $query = "select u.name name, s.submitted_at date, s.entered_score achievement
                from submission s
                join user u on u.id = s.user_id
                where entered_score != 0
                and YEAR(s.submitted_at) > 1970  
                and u.id = $id 
                order by date";
            $data = Yii::$app->db->createCommand($query)->queryAll();

            $prediction = new PredictionController;
            $thisPrediction = $prediction->predict($id);

            $thisCohort = explode('.', $_SERVER['SERVER_NAME'])[0];
            $currentYearLastTwoDigits = (int) substr(date("Y"), -2);
            $studentYear = $currentYearLastTwoDigits - (int) substr($thisCohort, 1);

            if ($studentYear < 2) {
                $result[] = [
                    'Slope' => $thisPrediction['slope'],
                    'Klas' => $student['klas'],
                    '!Student' => $student['name'] . '|/public/index|code|' . $student['code'],
                    '~Score' => $student['score'],
                    '~Percentage' => $thisPrediction['percCompleted'],
                    '~Mdls/Wk' => $thisPrediction['mod/week'],
                    'Stage op' => $thisPrediction['predictedDate'],
                    'Studieduur' => $thisPrediction['studieDuur'],
                    'Module' => $student['module'],
                ];
            } else {
                $result[] = [
                    'Slope' => $thisPrediction['slope'],
                    'Klas' => $student['klas'],
                    '!Student' => $student['name'] . '|/public/index|code|' . $student['code'],
                    '~Score' => $student['score'],
                    '~Percentage' => $thisPrediction['percCompleted'],
                    'Module' => $student['module'],
                ];
            }
        }

        usort($result, function ($a, $b) {
            $num1 = (int) ($a['~Percentage'] * 10);
            $num2 = (int) ($b['~Percentage'] * 10);
            return $num2 - $num1;
        });

        $data['col'] = array_keys($result[0]);
        $data['row'] = $result;
        $data['title'] = "Ranking and Predictions";
        $data['show_from'] = 1;

        return $this->render('output', [
            'data' => $data,
            'descr' => 'Prediction is based an data from c22 which is put into an exponential regression model.',
            'lastLine' => "<hr><a href=\"/report/ranking\" class=\"btn bottom-button\">Ranking</a>",
            'bgcolor' => ['', '', '', '', '', '', '', '#f5fff9', '#'],
            'color' => ['', '', '', '#707070', '', '', '#707070', '', '#707070'],
        ]);
    }
}
