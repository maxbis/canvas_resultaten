<?php

namespace app\controllers;

use Yii;
use app\models\Resultaat;
use app\models\ResultaatSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
    public function actionIndex($code=0, $export=false) {

        // ipcheck for testing off
        // MyHelpers::CheckIP();
        // Create report for one student(status)
        $sql="SELECT r.module_id, r.student_naam Student, c.korte_naam Blok ,r.module Module, r.voldaan Voldaan, r.ingeleverd Opdrachten, round(r.punten*100/r.punten_max) 'Punten %', r.laatste_activiteit 'Laatste Actief'
                FROM resultaat r
                LEFT OUTER JOIN course c on c.id = r.course_id
                INNER JOIN module_def d on d.id=r.module_id
                INNER JOIN user u on u.student_nr=r.student_nummer
                WHERE code='$code'
                ORDER BY c.pos, r.module_pos
            ";

        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if (! count($data)) {
            sleep(3);
            exit(0);
        }

        $sql="
        select (count(id)+1) 'rank'
            from user u1
            where u1.ranking_score>
            (select u2.ranking_score from user u2 where u2.code='$code')
        ";
        $ranking = Yii::$app->db->createCommand($sql)->queryOne();
        $sql="select max(timestamp) timestamp from log where subject='Import'";
        $timestamp = Yii::$app->db->createCommand($sql)->queryOne();

        return $this->render('index', [
            'data' => $data,
            'timeStamp' => $timestamp['timestamp'],
            'rank' => $ranking['rank'],
        ]);
    }

    // generate codes
    public function actionGenerate($code=0) {
        // if you want new code, change the $salt, everyone will get a new code
        if ($code=="doehetmaar") {
            echo "<pre>";
            MyHelpers::CheckIP();
            $sql = "select student_nr studentNummer, name from user where student_nr > 100";
            $data = Yii::$app->db->createCommand($sql)->queryAll();

            echo "Lines to be updated: ".count($data);
            $count=0;
            $sql="";
            foreach( $data as $item) {
                $count+=1;
                echo "<br>Line ".$count." update ".$item['name'];
                $salt="MaxBis";
                $code=md5($salt.$item['studentNummer']);
                $sql.="update user set code='".$code."' where student_nr=".$item['studentNummer'].";\n";
            }
            echo "\n\nExecute now\n";
            Yii::$app->db->createCommand($sql)->execute();
            echo "<b>Done</b><br>";
        }
    }

}
