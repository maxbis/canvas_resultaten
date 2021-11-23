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

    public function actionActief($sort='desc', $export=false) {

        $sql="select student_naam Student, klas Klas, max(laatste_activiteit) 'Laatst actief' from resultaat group by 1,2 order by 3 $sort";

        $data=$this->executeQuery($sql, "Laatste activiteit per student", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionActiefModule($sort='desc', $export=false) {

        $sql="select student_naam Student, klas Klas,  module Module, max(laatste_activiteit) 'Laatst actief' from resultaat group by 1,2,3 order by 4 $sort limit 100";

        $data=$this->executeQuery($sql, "Laatste activiteit per student per module", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => '(max 100 regels)',
        ]);
    }

    public function actionVoortgang($sort='desc', $export=false) {

        $sql="select student_nummer stdntnr, student_naam Student, klas Klas, SUM(case when voldaan='V' then 1 else 0 end) Voldaan from resultaat group by 1,2 order by 4 $sort";

        $data=$this->executeQuery($sql, "Laatste activiteit per student", $export);
        
        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }


}

