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

    public function actionSetMessage() { // AJAX update
        // functions updates message or comment in user table.
        // the posted field contains the column name to be updated
        $data = Yii::$app->request->post();
        $id=$data['id'];            // id of user to be updated
        $message=$data['message'];  // content of the field
        $field=$data['field'];      // field name to be updated

        $message = trim(preg_replace('/\s\s+/', ' ', $message)); // filter multi spaces and tabs and the like
        // function set message
        $sql="update user set $field=:message where id = :id;";
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

    public function actionPredict($id) {
        $sql ="select s.submitted_at date, s.entered_score achievement from submission s
            where  s.user_id = $id
            and entered_score != 0
            and YEAR(s.submitted_at) > 1970
            order by date";
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        $prediction = $this->predictAchievementDate($data, 3900);
        dd($prediction);
    }

    function predictAchievementDate($dataset, $targetAchievement) {
        // Calculate cumulative achievements
        $cumulativeAchievement = 0;
        foreach ($dataset as &$data) {
            $cumulativeAchievement += $data['achievement'];
            $data['cumulative'] = $cumulativeAchievement;
        }
        
        // array is sorted so first element is oldest
        $startDate = isset($dataset[0]['date']) ? $dataset[0]['date'] : null;

        $daysPassed = $this->countWorkingDays($startDate, date('Y-m-d')); 

        $slope = $cumulativeAchievement / $daysPassed;

        $daysToGo = ( $targetAchievement - $cumulativeAchievement ) / $slope;

        $predictedDate = $this->getDateAfterWorkingDays($startDate, $daysToGo);
        
        echo "<pre>";
        echo "\n cumulativeAchievement: ".$cumulativeAchievement;
        echo "\n startDate: ".$startDate;
        echo "\n endDate: ".date('Y-m-d');
        echo "\n daysPassed: ".$daysPassed;
        echo "\n slope: ".$slope;
        echo "\n daysToGo: ".$daysToGo;
        echo "\n predictedDate: ".$predictedDate;
        exit();
     
        return $predictedDate;
    }

    function countWorkingDays($startDate, $endDate) {

        // toDo vallidate the dates!
        $vacationPeriods = [
            ['start' => '2023-10-23', 'end' => '2023-10-27', 'name'=>'Herfst'],
            ['start' => '2023-12-25', 'end' => '2024-01-05', 'name'=>'Kerst'],
            ['start' => '2024-02-29', 'end' => '2024-02-23', 'name'=>'Krokus'],
            ['start' => '2024-03-29', 'end' => '2024-03-29', 'name'=>'Goede vrijdag'],
            ['start' => '2024-04-01', 'end' => '2024-04-01', 'name'=>'Paasmaandag'],
            ['start' => '2024-04-29', 'end' => '2024-05-10', 'name'=>'Mei'],
            ['start' => '2024-05-20', 'end' => '2024-05-20', 'name'=>'Pinkstermaandag'],
            ['start' => '2024-07-15', 'end' => '2024-08-16', 'name'=>'Zomer'],
        ];

        // Generate vacation dates
        $vacationDates = [];
        foreach ($vacationPeriods as $period) {
            $vacationDates = array_merge($vacationDates, $this->generateDatesInRange($period['start'], $period['end']));
        }

        // Count working days excluding weekends and vacation dates
        $workingDaysCount = 0;
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {

            if ($this->isWeekday($currentDate) && !in_array($currentDate, $vacationDates)) {
                $workingDaysCount++;
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' + 1 day'));
        }
    
        return $workingDaysCount;
    }

    function getDateAfterWorkingDays($startDate, $N) {
        // toDo vallidate the dates!
        $vacationPeriods = [
            ['start' => '2023-10-23', 'end' => '2023-10-27', 'name'=>'Herfst'],
            ['start' => '2023-12-25', 'end' => '2024-01-05', 'name'=>'Kerst'],
            ['start' => '2024-02-29', 'end' => '2024-02-23', 'name'=>'Krokus'],
            ['start' => '2024-03-29', 'end' => '2024-03-29', 'name'=>'Goede vrijdag'],
            ['start' => '2024-04-01', 'end' => '2024-04-01', 'name'=>'Paasmaandag'],
            ['start' => '2024-04-29', 'end' => '2024-05-10', 'name'=>'Mei'],
            ['start' => '2024-05-20', 'end' => '2024-05-20', 'name'=>'Pinkstermaandag'],
            ['start' => '2024-07-15', 'end' => '2024-08-16', 'name'=>'Zomer'],
        ];

        $vacationDates = [];
        foreach ($vacationPeriods as $period) {
            $vacationDates = array_merge($vacationDates, $this->generateDatesInRange($period['start'], $period['end']));
        }
    
        // Find the date after N working days excluding weekends and vacation dates
        $workingDaysCount = 0;
        $currentDate = $startDate;
        while ($workingDaysCount < $N) {
            if ($this->isWeekday($currentDate) && !in_array($currentDate, $vacationDates)) {
                $workingDaysCount++;
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' + 1 day'));
        }
    
        return $currentDate;
    }
    

    function isWeekday($date) {
        $dayOfWeek = date('w', strtotime($date));
        return ($dayOfWeek >= 1 && $dayOfWeek <= 5); // 1 for Monday and 5 for Friday
    }

    function generateDatesInRange($startDate, $endDate) {
        $dates = [];
        $currentDate = $startDate;
    
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime($currentDate . ' + 1 day'));
        }
    
        return $dates;
    }

}
