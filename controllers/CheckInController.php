<?php

namespace app\controllers;

use Yii;
use app\models\CheckIn;
use app\models\CheckInSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;

use yii\filters\AccessControl;

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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['check-in'],
                        'roles' => ['?'],
                    ],
                    // when logged in, any user
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
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
        $browserHash = substr(md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['HTTP_ACCEPT'].$_SERVER['HTTP_ACCEPT_LANGUAGE']),-10);


        if ( $check != md5(date("Ymd")) || MyHelpers::CheckIP(true) == false ) {
            return;
        }

        $sql = "select id from user where code='$code'";
        $studentId = Yii::$app->db->createCommand($sql)->queryOne()['id'];
        if ( $studentId ) {
            $sql = "select timediff(now(),max(timestamp)) timediff from check_in where studentId=".$studentId;
            $timeDiff = Yii::$app->db->createCommand($sql)->queryOne()['timediff'];
            $hourDiff=(int)(explode(':',$timeDiff)[0]);
            $count=0;

            if (isset($_COOKIE['cic'])) $count=(int)$_COOKIE['cic']+1;

            if ( (! $timeDiff) || $hourDiff>0 || true) {
                $sql="insert into check_in (studentId,action,browser_hash) values ($studentId, 'i', '$browserHash-$count')";
                $result = Yii::$app->db->createCommand($sql)->execute();
                setcookie('chin', $browserHash, time()+3600, '/');
                setcookie('cic', $count , 0, '/');
            }
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function button($code, $test=false) {

        $today = date("Ymd"); //e.g. 20200728, this is an extra security to avoid fake posts
        $date_hash=md5($today);

        if ($test || ! isset($_COOKIE['chin']) && Yii::$app->user->isGuest ) {
            if (MyHelpers::CheckIP(true)) {
                return Html::a(' Present ',  ['/check-in/check-in'], ['class'=>"btn btn-success", 'style'=>'background-color:#a7e68e;color:#164a01;', 'data-method' => 'POST','data-params' =>
                        [ 'code' => $code, 'check' => $date_hash, 'action' => 'i' ], ]);
            }
        }

    }


    public function actionTest() {  
        $sql="  select klas, name, code from user
                WHERE CHAR_LENGTH(code)>2
                order by 1,2";
        $result = Yii::$app->db->createCommand($sql)->queryAll();

        return $this->render('test', [
            'result' => $result,
        ]);

    }

    public function actionTest2() {  
        dd($_COOKIE);
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
