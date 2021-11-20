<?php

namespace app\controllers;

use Yii;
use app\models\Resultaat;
use app\models\ResultaatSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\helpers\ArrayHelper;

use yii\filters\AccessControl;

/**
 * ResultaatController implements the CRUD actions for Resultaat model.
 */
class ResultaatController extends Controller
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
                    [   'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Resultaat models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ResultaatSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $modules = ArrayHelper::map(Resultaat::find()->asArray()->all(), 'module', 'module');
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modules' => $modules,
        ]);
    }


    /**
     * Displays a single Resultaat model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Resultaat model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Resultaat();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Resultaat model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Resultaat model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Resultaat model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Resultaat the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Resultaat::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionExport(){       
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') .'.csv"');
         
        $data = Resultaat::find()->asArray()->all();

        $firstLine=True;
        foreach ($data as $line) {
            foreach ( $line as $key => $value) {
                if ($firstLine) {
                    echo $key.",";
                } else {
                    echo "$value, ";
                }
            }
            $firstLine=False;
            echo "\n";
        }
        exit;      
    }

    public function getSubmissionFromApi($id) {
        $uri = 'https://talnet.instructure.com/api/graphql';
        $api_key  = "17601~LZ3pktnGAYnvWPXvIsqjGNY1bg1LfSH1fOfVvmoCAG9AmKX3mDZIyzPBsmnO1iZw";
        $authorization = "Authorization: Bearer ".$api_key;

        $query="query {
            submission(id: \"$id\") {
              _id
              score
              state
              submittedAt
              gradedAt
              submissionStatus
            }
          }";

        $ch = curl_init($uri);

        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER  => array($authorization),
            CURLOPT_RETURNTRANSFER  =>true,
            CURLOPT_VERBOSE     => 1
        ));

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "query=".$query);

        $out = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $out['data']['submission'];
    }

    public function updateSubmission($data) {
        $sql="update submission set entered_score=:score, submitted_at=:submitted_at, graded_at=:graded_at, workflow_state=:state where id=:id";
        $params = array(':score'=> $data['score'], ':submitted_at' => $data['submittedAt'], ':graded_at' => $data['gradedAt'], ':state' => $data['submissionStatus'], ':id'=>$data['_id']);
        $result = Yii::$app->db->createCommand($sql)->bindValues($params)->execute();
        return $result;
    }

    public function actionUpdateAssignment($student_nr, $module_id) { #this is too slow.....
        # get submission_ids, let's get all submissions that are from this assignment group and this user
        $sql="  select s.id from submission s
                inner join assignment a on a.id=s.assignment_id
                inner join assignment_group g on g.id=a.assignment_group_id
                inner join user u on u.id=user_id
                where g.id = :module_id and u.student_nr=:user_id";
        $params = array(':user_id'=> $student_nr, ':module_id' => $module_id );
        $listOfSubmissions = Yii::$app->db->createCommand($sql)->bindValues($params)->queryAll();               

        # update the list of submissions, these are all submissions that are part of this assignment_group (=module)
        foreach ($listOfSubmissions as $submission) { // get all submissions async since api responce is quite slow
            // $data = $this->getSubmissionFromApi($submission['id']); // do this async
            // $this->updateSubmission($data); // and then do this

            Yii::$app->async->run(function() {
                return $this->getSubmissionFromApi($submission['id']);
           },
           [    'success' => function ($result) {
                    $this->updateSubmission($result);
                },
                'error' => function() {
                    dd('async timeout');
                },
                'timeout' => function() {
                    dd('async timeout');
                },
            ]
            );
        } // endfor
        return $this->redirect(['index']);
    }
      
}


