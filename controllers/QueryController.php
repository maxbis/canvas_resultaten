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
                    [ 'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }


    private function executeQuery($sql, $title="no title", $export=false) {

        $result = Yii::$app->db->createCommand($sql)->queryAll();

       

        if ($result) { // column names are derived from query results
            $data['col']=array_keys($result[0]);
        }
        $data['row']=$result;
        
        if ($export) {
            $this->exportExcel($data);
            exit;
        } else {
            $data['title']=$title;
            return $data;
        }

    }

    public function exportExcel($data) {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') .'.csv"');

        foreach ($data['col'] as $key => $value) {
            echo $value.", ";
        }
        echo "\n";
        foreach ($data['row'] as $line) {
            foreach ( $line as $key => $value) {
                echo $value.", ";
            }
            echo "\n";
        }
        
    }

    public function actionActief($sort='desc', $export=false, $klas='') {

        // $sql="select student_naam Student, klas Klas, max(laatste_activiteit) 'Laatst actief' from resultaat group by 1,2 order by 3 $sort";
        if ($klas) {
            $select="and klas='$klas'";
        } else {
            $select='';
        }
        $sql="
            SELECT student_naam, klas, module, laatste_activiteit
            from resultaat o
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            order by 4 desc
        ";

        $data=$this->executeQuery($sql, "Laatste activiteit per student", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionWorkingOn($sort='desc', $export=false, $klas='') {

        if ($klas) {
            $select="and klas='$klas'";
        } else {
            $select='';
        }

        $sql="
            SELECT module Module, sum(1) studenten
            from resultaat o
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            group by 1
            order by 2 $sort
        ";

        $data=$this->executeQuery($sql, "Studenten werken aan", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionModulesFinished($export=false, $klas='') {

        if ($klas) {
            $select="where klas='$klas'";
        } else {
            $select='';
        }

        $sql="
            select Module, af 'Afgerond door' from
                (select course_id, module_id, module Module, sum(case when voldaan='V' then 1 else 0 end) af
            from resultaat o
            $select
            group by 1,2,3
            order by 1,2) alias
        ";
        // ToDo: order by werkt niet op server (order by moet in group by zitten)
        $data=$this->executeQuery($sql, "Modules voldaan", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionVoortgang($sort='desc', $export=false, $klas='') {

        if ($klas) {
            $select="where klas='$klas'";
        } else {
            $select='';
        }

        $sql="select student_nummer stdntnr, student_naam Student, klas Klas, SUM(case when voldaan='V' then 1 else 0 end) 'Modules voldaan' from resultaat $select group by 1,2, 3 order by 4 $sort";

        $data=$this->executeQuery($sql, "Voortgang", $export);
        
        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }


}

