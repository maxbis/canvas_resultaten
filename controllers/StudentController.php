<?php

// Stadard Yii CRUD controller

namespace app\controllers;

use Yii;
use app\models\Student;
use app\models\StudentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;

/**
 * StudentController implements the CRUD actions for Student model.
 */
class StudentController extends Controller
{
    public function beforeAction($action) {
        return parent::beforeAction($action);
    }

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
     * Lists all Student models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StudentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Student model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $data = $this->findModel($id);
        if ( isset($data['code']) && strlen($data['code'])>10 ) {
            return $this->redirect('/public/index?code='.$data['code']);
        } else {
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new Student model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Student();

        if ($model->load(Yii::$app->request->post()) ) {
            if ( $model->student_nr ) {
                $salt = "MaxBiss23";
                $model->code = md5($salt . $model->student_nr);
            }
            if ( $model->save() ) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
 
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Student model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionXUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $sql = "update resultaat set klas=:klas, student_naam=:naam  where student_nummer=:student_nummer";
            $params = array(':klas' => $model->klas, ':naam' => $model->name, ':student_nummer' => $model->student_nr);
            $result = Yii::$app->db->createCommand($sql)->bindValues($params)->execute();
            return $this->redirect(['/student']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/public/index', 'code' => $model->code]);
        }

        $sql="select id, naam from course where id not in ( select distinct r.course_id from resultaat r join course c on c.id=r.course_id where r.student_nummer=".$model['student_nr']." ) order by pos";
        $openCourses = Yii::$app->db->createCommand($sql)->queryAll();

        return $this->render('update', [
            'model' => $model,
            'openCourses' => $openCourses,
        ]);
    }

    /**
     * Deletes an existing Student model.
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
     * Finds the Student model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Student the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Student::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionToggleActief($id) {
        // function toggles boolean actief
        $sql="update user set grade=abs(grade-1) where id = :id;";
        $params = array(':id'=> $id);
        Yii::$app->db->createCommand($sql)->bindValues($params)->execute();
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionSetMessage() {
        $data = Yii::$app->request->post();
        $id=$data['id'];
        $message=$data['message'];
        $message = trim(preg_replace('/\s\s+/', ' ', $message)); // filter multi spaces and tabs and the like
        // function set message
        $sql="update user set message=:message where id = :id;";
        $params = [':id'=>$id, ':message'=>$message,];
        Yii::$app->db->createCommand($sql)->bindValues($params)->execute();
        return;
    }

    // generate  hash codes used to access overview for a student. Run this to (re) set all hash codes.
    public function actionGenerate($code = 0)
    {
        // if you want new code, change the $salt, everyone will get a new code
        if ($code == "EXE") {
            echo "<pre>";
            MyHelpers::CheckIP();
            $sql = "select student_nr studentNummer, name from user where student_nr > 100";
            $data = Yii::$app->db->createCommand($sql)->queryAll();

            echo "Lines to be updated: " . count($data);
            $count = 0;
            $sql = "";
            foreach ($data as $item) {
                $count += 1;
                $salt = "MaxBiss";
                $code = md5($salt . $item['studentNummer']);
                # echo "<br>Line " . $count . " new code: ". $code . " for " . $item['name'];
                $sql .= "update user set code='" . $code . "' where student_nr=" . $item['studentNummer'] . ";\n";
            }
            Yii::$app->db->createCommand($sql)->execute();
            echo "<br><b>Done</b><br>";
        }
    }
}
