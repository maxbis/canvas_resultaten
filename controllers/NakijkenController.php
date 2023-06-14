<?php

namespace app\controllers;

use app\models\nakijken;
use app\models\nakijkenSearch;
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
        $searchModel = new nakijkenSearch();
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
        $model = new nakijken();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'assignment_id' => $model->assignment_id]);
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
    public function actionUpdate($assignment_id)
    {
        $model = $this->findModel($assignment_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'assignment_id' => $model->assignment_id]);
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
        if (($model = nakijken::findOne(['assignment_id' => $assignment_id])) !== null) {
            return $model;
        } else {
            $sql="
                SELECT c.id course_id, m.naam module_name, a.name assignment_name
                FROM assignment a
                JOIN assignment_group g on g.id=a.assignment_group_id
                JOIN course c on c.id=a.course_id 
                JOIN module_def m on m.id=g.id
                WHERE a.id=110623
            ";
            $result = Yii::$app->db->createCommand($sql)->queryOne();
            $model = new nakijken();
            $model->course_id = $result['course_id'];
            $model->assignment_id = $assignment_id;
            $model->module_name = $result['module_name'];
            $model->assignment_name = $result['assignment_name'];

            return $model;
        }

    }

}
