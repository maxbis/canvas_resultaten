<?php

namespace app\controllers;

use Yii;
use app\models\Resultaat;
use app\models\ResultaatSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
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
    public function actionIndex($code = 0)
    {
        // if($code==0) exit;
        // ipcheck for testing off
        // MyHelpers::CheckIP();
        // Create report for one student(status)
        $sql = "SELECT r.module_id, r.student_naam Student, c.korte_naam Blok ,r.module Module, r.voldaan Voldaan,
                    round( r.ingeleverd*100/r.aantal_opdrachten) 'Opdrachten %',
                    round(r.punten*100/r.punten_max) 'Punten %',
                    r.laatste_activiteit 'Laatste Actief',
                    r.ingeleverd Opdrachten,
                    r.module_id,
                    r.student_nummer,
                    r.aantal_opdrachten,
                    r.punten Punten,
                    u.code Code,
                    d.voldaan_rule voldaanRule
                FROM resultaat r
                LEFT OUTER JOIN course c on c.id = r.course_id
                INNER JOIN module_def d on d.id=r.module_id
                INNER JOIN user u on u.student_nr=r.student_nummer
                WHERE code='$code'
                ORDER BY c.pos, r.module_pos
            ";

        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if (!count($data)) {
            $sql = "INSERT INTO log (subject, message, route) VALUES ('Wrong code', '" . $code . "', '" . $_SERVER['REMOTE_ADDR'] . "');";
            Yii::$app->db->createCommand($sql)->execute();
            sleep(3);
            return $this->redirect('https://whatismyipaddress.com/');
            exit(0);
        }

        $sql = "
            SELECT DATE_FORMAT(s.submitted_at,'%y') Jaar,
            lpad(week(s.submitted_at,1),2,0) Week,
            sum(1) 'Aantal'
            FROM `submission`  s
            inner join user u on u.id = s.user_id
            and u.code='$code'
            and s.submitted_at > '1970-01-01 00:00:00'
            group by 1,2
        ";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        $chart = $this->chart($result);

        $sql = "
        select (count(id)+1) 'rank'
            from user u1
            where u1.ranking_score>
            (select u2.ranking_score from user u2 where u2.code='$code')
        ";
        $ranking = Yii::$app->db->createCommand($sql)->queryOne();

        $sql = "select max(timestamp) timestamp from log where subject='Import'";
        $sql .= ";INSERT INTO log (subject, message, route) VALUES ('Student Rapport', '" . $data[0]['Student'] . "', '" . $_SERVER['REMOTE_ADDR'] . "');";
        $timestamp = Yii::$app->db->createCommand($sql)->queryOne();

        return $this->render('index', [
            'data' => $data,
            'timeStamp' => $timestamp['timestamp'],
            'rank' => $ranking['rank'],
            'chart' => $chart,
        ]);
    }

    public function actionDetailsModule($code, $moduleId)
    {
        $sql = "
            SELECT u.id u_id, a.id a_id, a.course_id, u.name naam, m.naam module, a.name Opdrachtnaam, s.workflow_state 'Status',
            CASE s.submitted_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.submitted_at END 'Ingeleverd',
            s.entered_score Score,
            a.points_possible MaxScore,
            CASE s.graded_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.graded_at END Beoordeeld, r.name 'Door', s.preview_url Link
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            left outer join user r on r.id=s.grader_id
            join assignment_group g on g.id = a.assignment_group_id
            left outer join module_def m on m.id = g.id
            where g.id=$moduleId
            and u.code='$code'
            order by  a.position
        ";

        $data = Yii::$app->db->createCommand($sql)->queryAll();

        return $this->render('details-module', [
            'data' => $data,
        ]);
    }

    // generate  hash codes used to access overview for a student. Run this to (re) set all hash codes.
    public function actionGenerate($code = 0)
    {
        // if you want new code, change the $salt, everyone will get a new code
        if ($code == "doehetmaar") {
            echo "<pre>";
            MyHelpers::CheckIP();
            $sql = "select student_nr studentNummer, name from user where student_nr > 100";
            $data = Yii::$app->db->createCommand($sql)->queryAll();

            echo "Lines to be updated: " . count($data);
            $count = 0;
            $sql = "";
            foreach ($data as $item) {
                $count += 1;
                echo "<br>Line " . $count . " update " . $item['name'];
                $salt = "MaxBis";
                $code = md5($salt . $item['studentNummer']);
                $sql .= "update user set code='" . $code . "' where student_nr=" . $item['studentNummer'] . ";\n";
            }
            echo "\n\nExecute now\n";
            Yii::$app->db->createCommand($sql)->execute();
            echo "<b>Done</b><br>";
        }
    }

    private function getIsoWeeksInYear($year)
    {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

    private function chart($data)
    {
        $workLoadperWeek = [];

        foreach ($data as $item) { // read all weeks from query into ass. array.
            $workLoadperWeek[$item['Week']] = intval($item['Aantal']);
        }

        $aantalWeken = 10;                                  // Number of weeks in graph
        $weekNumber = date("W");                            // This week number
        $weeksThisYear = $this->getIsoWeeksInYear(date("Y"));    // max. week number of this year

        $start = $weekNumber - $aantalWeken;
        if ($start < 0) { // roll over to last year
            $start += $weeksThisYear;
        }

        $chartArray = [['Week', 'norm 5/week']];

        for ($i = 0; $i < $aantalWeken; $i++) {
            $week = $start + $i;
            if ($week > $weeksThisYear) { // roll over to next year
                $week -= $weeksThisYear;
            }
            if (array_key_exists($week, $workLoadperWeek)) {
                array_push($chartArray, [strval($week), intval($workLoadperWeek[$week])]); // value from query
            } else {
                array_push($chartArray, [strval($week), 0]); // no value means 0
            }
        }

        //dd($chartArray);

        $chart = [
            'visualization' => 'ColumnChart',
            'data' => $chartArray,
            'options' => [
                'title' => 'Wekelijkse Activiteiten',
                'height' => '160',
                'width' => '600',
                'hAxis' => array('title' => 'Weeknummer'),
                'vAxis' => array('title' => 'Aantal Taken', 'ticks' => [0, 5, 10, 15]),
                'legend' => array('position' => 'top'),
                'colors' => ['#82b0ff'],
            ]
        ];

        return $chart;
        //use scotthuangzl\googlechart\GoogleChart;

        //echo GoogleChart::widget($chart);
    }
}
