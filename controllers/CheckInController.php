<?php

namespace app\controllers;

use Yii;
use app\models\CheckIn;
use app\models\CheckInSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CheckInController implements the CRUD actions for CheckIn model.
 */
class CheckInController extends Controller
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
     * Lists all CheckIn models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CheckInSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CheckIn model.
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
     * Creates a new CheckIn model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CheckIn();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CheckIn model.
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
     * Deletes an existing CheckIn model.
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

    public function actionCheckIn() {
        $code = Yii::$app->request->post('code', null);
        $check = Yii::$app->request->post('check', null);
        $action = Yii::$app->request->post('action', null);

        if ( $check != md5(date("Ymd")) || MyHelpers::CheckIP(true) == false ) {
            return;
        }

        $sql = "select id from user where code='$code'";
        $studentId = Yii::$app->db->createCommand($sql)->queryOne()['id'];
        if ( $studentId ) {
            $sql = "select timediff(now(),max(timestamp)) timediff from check_in where studentId=".$studentId;
            $timeDiff = Yii::$app->db->createCommand($sql)->queryOne()['timediff'];
            $hourDiff=(int)(explode(':',$timeDiff)[0]);

            if ( (! $timeDiff) || $hourDiff>0 || true) {
                $sql="insert into check_in (studentId,action) values ($studentId, 'i')";
                $result = Yii::$app->db->createCommand($sql)->execute();
                setcookie('check-in', 'i', time()+3600, '/');
            }
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the CheckIn model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CheckIn the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CheckIn::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
