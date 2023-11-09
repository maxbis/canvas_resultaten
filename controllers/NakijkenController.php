<?php

namespace app\controllers;

use app\models\Nakijken;
use app\models\NakijkenSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use Yii;

/**
 * NakijkenController implements the CRUD actions for nakijken model.
 */
class NakijkenController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all nakijken models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new NakijkenSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single nakijken model.
     * @param int $assignment_id Assignment ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($assignment_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($assignment_id),
        ]);
    }

    /**
     * Creates a new nakijken model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Nakijken();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                $model->config = json_encode($model->config);

                if ($model->validate() && $model->save()) {
                    return $this->redirect(['index', 'assignment_id' => $model->assignment_id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing nakijken model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $assignment_id Assignment ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($assignment_id, $alt_return=0)
    {
        $model = $this->findModel($assignment_id);
        $action = Yii::$app->request->post('action');

        # TODO there are two ways to redirect to another page when saved
        # the GET param that is given grade/not-graded-module2 report and report/opdrachten-module?id=29979 report
        # the param given by the submit button in the nakijken _form L72
        # the method shoudl be unified

        if ($this->request->isPost && $model->load($this->request->post()) ) {
            $model->config = json_encode($model->config);

            if ( $model->save() ) {

                if ($action == 'stay') {
                    return $this->redirect(['update', 'assignment_id' => $model->assignment_id]);
                }
                
                # if (alt)return is set this means we came from grading page
                if ($alt_return==1) {
                    return $this->redirect(['/grade/not-graded-module2', 'moduleId' => $model->module_id, 'regrading' => 2]);
                }
            }

            # oterhwise we go back to the complete module overview
            return $this->redirect(['/report/opdrachten-module', 'id' => $model->module_id]);
            
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing nakijken model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $assignment_id Assignment ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($assignment_id)
    {

        $this->findModel($assignment_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the nakijken model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $assignment_id Assignment ID
     * @return nakijken the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($assignment_id)
    {
        if (($model = Nakijken::findOne(['assignment_id' => $assignment_id])) !== null) {
            return $model;
        } else {
            $sql="
                SELECT c.id course_id, m.naam module_name, a.name assignment_name, m.id module_id
                FROM assignment a
                JOIN assignment_group g on g.id=a.assignment_group_id
                JOIN course c on c.id=a.course_id 
                JOIN module_def m on m.id=g.id
                WHERE a.id=$assignment_id
            ";
            $result = Yii::$app->db->createCommand($sql)->queryOne();

            // find existing based on module name and ass. name
            $sql="
                SELECT file_type, words_in_order, instructie, file_name, attachments
                FROM canvas.nakijken
                WHERE module_name = '". $result['module_name']."'
                AND assignment_name = '".$result['assignment_name']."'
                order by timestamp DESC
            ";
            $existing = Yii::$app->db->createCommand($sql)->queryAll();

            // find existing based on module name only
            if ( count($existing) == 0 ) {
                $sql="
                    SELECT file_type, words_in_order, instructie, file_name, attachments
                    FROM canvas.nakijken
                    WHERE module_name = '". $result['module_name']."'
                    order by timestamp DESC
                ";
                $existing = Yii::$app->db->createCommand($sql)->queryAll();
            }

            $databaseName = Yii::$app->db->createCommand('SELECT DATABASE() db')->queryOne()['db'];

            $model = new Nakijken();

            // Default values to store
            $model->course_id = $result['course_id'];
            $model->assignment_id = $assignment_id;
            $model->module_name = $result['module_name'];
            $model->assignment_name = $result['assignment_name'];
            $model->module_id=$result['module_id'];
            $model->cohort=substr($databaseName,-3);

            // populate data from old/previous values
            if( count($existing) >= 1 ){
                $model->file_type=$existing[0]['file_type'];
                $model->words_in_order=$existing[0]['words_in_order'];
                $model->instructie=$existing[0]['instructie'];
                $model->file_name=$existing[0]['file_name'];
                $model->attachments=$existing[0]['attachments'];
            }

            return $model;
        }

    }

}
