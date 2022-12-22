<?php

namespace app\controllers;

use Yii;
use app\models\Resultaat;
use app\models\ResultaatSearch;
use app\models\ModuleDef;
use app\models\Course;
use app\models\Student;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\helpers\ArrayHelper;

use yii\filters\AccessControl;

use yii\bootstrap4\Html;
//use vxm\async\Task;
use Spatie\Async\Pool;

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

        $courses = ArrayHelper::map(Course::find()->orderBy('pos')->asArray()->all(), 'id', 'korte_naam');
        $klas = ArrayHelper::map(Student::find()->where(['not', ['klas' => '']])->orderBy('klas')->asArray()->all(), 'klas', 'klas');
        $modules =  ArrayHelper::map(Resultaat::find()->orderBy('module_pos')->asArray()->all(), 'module_id', 'module');
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'courses' => $courses,
            'klas' => $klas,
            'modules' => $modules,
            'updates_available_for'=>array_keys( $this->getVoldaanCriteria() ),
        ]);
    }

    public function actionStart() {
  
        if (Yii::$app->request->post() ) {
            $search=Yii::$app->request->post()['search'];
            $resultaten = student::find()->select(['code','name', 'klas'])->distinct()->where(['like', 'name', $search])->andWhere(['>','student_nr','1'])->orderBy(['name' => 'SORT_ASC'])->all();
            $found=count($resultaten);
        } else {
            $resultaten=[];
            $found=-1;
        }

        # $sql="SELECT min(greatest(m.last_updated, g.last_updated)) 'timestamp' FROM module_def m join assignment_group g on g.id=m.id where m.pos is not null";
        $sql = "select max(timestamp) timestamp from log where subject='Import'";
        #$sql="select max(timestamp) timestamp from log where subject='Import'";
        $timestamp = Yii::$app->db->createCommand($sql)->queryOne();

        if ($found == 1) { // one student found, redirects to the students page
            return $this->redirect([
                'public/index','code'=>$resultaten[0]['code'],
            ]);
        } else {
            return $this->render('start', [
                'resultaten' => $resultaten,
                'found' => $found,
                'timestamp' => $timestamp['timestamp']
            ]);
        }

    }

    public function actionRotate() {
        # $controller = Yii::$app->controller->id; 
        # $action = Yii::$app->controller->action->id;

        if ( str_contains($_SERVER['HTTP_REFERER'], 'resultaat/start') == false ) {
            $this->redirect(Yii::$app->homeUrl);
            return;
        }

        $actualLink = strtolower('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);

        if (str_contains($actualLink, 'c22')) { 
            $newUrl=str_replace("c22","c21",$actualLink);
        }
        if (str_contains($actualLink, 'c21')) { 
            $newUrl=str_replace("c21","c20",$actualLink);
        }
        if (str_contains($actualLink, 'c20')) { 
            $newUrl=str_replace("c20","c22",$actualLink);
        }

        $this->redirect($newUrl);
        return;
    }

    public function actionAjaxNakijken() {
        $sql="select
                m.pos,
                m.naam,
                m.id,
                sum(1) aantal,
                max(datediff(now(), submitted_at)) oudste
            FROM assignment a
            left outer join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join module_def m on m.id = a.assignment_group_id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where u.grade=1 and s.submitted_at > s.graded_at
         group by 1,2,3
         order by 1";
        $nakijken = Yii::$app->db->createCommand($sql)->queryAll();

        $html="<tr><th></th><th>module</th><th>aantal</th><th>oudste</th></tr>";
        $prev="";
        foreach($nakijken as $item) {
            $html .= "<tr>";
            $html .= "<td>&nbsp;</td>";

            $html .= "<td>";
            $html .= Html::a($item['naam'], ['/grade/not-graded-module', 'moduleId'=>$item['id']] );
            $html .= "</td>";
            
            $html .= "<td>".$item['aantal']."</td>";

            $html .= "<td>(".$item['oudste'].")</td>";

            $html .= "</tr>";
        }
        return $html;
    }

    protected function getVoldaanCriteria() {
        // create $voldaan_criteria from DB
        return ArrayHelper::map( ModuleDef::find()->asArray()->all(), 'id','voldaan_rule');
    }

    public function actionExport(){    
        // Code had bug: first line is replaced by headers and therefor teh first line is omited in download.
        // Code not used, export redirected to query export.
        
        // header('Content-type: text/csv');
        // header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') .'.csv"');
         echo "<pre>";
        $data = Resultaat::find()->asArray()->all();

        $firstLine=True;
        $header="";
        foreach ($data as $line) {                  //regel
            foreach ( $line as $key => $value) {    // column
                if ($firstLine) {
                    $header.= $key.",";
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

        include __DIR__.'/../config/secrets.php';

        $authorization = "Authorization: Bearer ".$API_KEY;

        $query="query {
            submission(id: \"$id\") {
              _id
              score
              state
              submittedAt
              gradedAt
              submissionStatus
              excused
              assignment {
                _id
              }
            }
          }";

        $ch = curl_init($URI);

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
        $params = array(':score'=> $data['score'] ?: 0, ':submitted_at' => $data['submittedAt'] ?: '1970-01-01T00:00:00', ':graded_at' => $data['gradedAt'] ?: '1970-01-01T00:00:00', ':state' => $data['submissionStatus'] ?: '1970-01-01T00:00:00', ':id'=>$data['_id']);
        $result = Yii::$app->db->createCommand($sql)->bindValues($params)->execute();
        return $result;
    }

    public function actionUpdateAssignment($student_nr, $module_id) { 
        // Live (async) update from GUI 
        # get submission_ids, let's get all submissions that are from this assignment group and this user
        $sql="  select s.id from submission s
                inner join assignment a on a.id=s.assignment_id
                inner join assignment_group g on g.id=a.assignment_group_id
                inner join user u on u.id=user_id
                where g.id = :module_id and u.student_nr=:user_id";
        $params = array(':user_id'=> $student_nr, ':module_id' => $module_id );
        $listOfSubmissions = Yii::$app->db->createCommand($sql)->bindValues($params)->queryAll();               

        # update the list of submissions, these are all submissions that are part of this assignment_group (=module)
        # https://github.com/spatie/async
        # async only works on Linux server, on windows async will be converted into sync processing
        $count=0;
        $pool = Pool::create();

        $timerStart=round(microtime(true) * 1000);
        foreach ($listOfSubmissions as $submission) { // get all submissions async since api responce is quite slow
            $count++;
            $pool->add(function () use ($submission, $count, $timerStart) {
                $data = $this->getSubmissionFromApi($submission['id']);
                $this->updateSubmission($data);
                return([$count, $data]);
            })->then(function ($data) use ($timerStart) {
                // do nothing for now
                // writeLog("asycn ".$data[0]." succes: ".$data[1]['_id'].": ".$data[1]['submittedAt']." ".strval(round(microtime(true) * 1000)-$timerStart));
            })->catch(function (Throwable $exception) {
                writeLog("Error async: ".$exception );
            });

        } // endfor

        await($pool); 
        writeLog("Async Pool(".$count." threads) ready, uS passed: ".strval(round(microtime(true) * 1000)-$timerStart));

        // update resultaat for user (based on login_id) and module
        // note that voldaan or not is not yet determined

        // delete and insert (todo: tranform insert into update statement in order to get rid of the delete)
        $sql="delete from resultaat where student_nummer=$student_nr and module_id=$module_id;";

        // Insert new values 
        $sql.="
            insert into resultaat (course_id, module_id, module, module_pos, student_nummer, klas, student_naam, ingeleverd, ingeleverd_eo, punten, punten_max, punten_eo, laatste_activiteit,laatste_beoordeling, aantal_opdrachten)
            SELECT
                a.course_id course_id,
                g.id module_id,
                case when d.naam is null then g.name else d.naam end module,
                case when d.pos is null then 999 else d.pos end module_pos,
                SUBSTRING_INDEX(u.login_id,'@',1) student_nummer,
                u.klas klas,
                u.name student_naam,
                SUM(case when s.workflow_state<>'unsubmitted' then 1 else 0 end) ingeleverd,
                SUM(case when s.workflow_state<>'unsubmitted' and a.name like '%eind%' then 1 else 0 end) ingeleverd_eo,
                sum(s.entered_score) punten,
                sum(a.points_possible) punten_max,
                sum(case when a.name like '%eind%' then s.entered_score else 0 end) punten_eo,
                max(submitted_at),
                max(case when s.grader_id>0 then graded_at else '1970-01-01 00:00:00' end),
                sum(1) aantal_opdrachten
            FROM assignment a
            join submission s on s.assignment_id= a.id join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            left outer join module_def d on d.id=g.id
            where SUBSTRING_INDEX(u.login_id,'@',1) = $student_nr
            and g.id = $module_id
            group by 1, 2, 3, 4, 5, 6,7;
        ";

        $voldaan_criteria = $this->getVoldaanCriteria(); // read voldaan criteria from DB (mapped in array)
        if ( array_key_exists($module_id,$voldaan_criteria) ) {
            $sql.="update resultaat set voldaan = 'V' WHERE module_id=$module_id and $voldaan_criteria[$module_id] and student_nummer=$student_nr;";
        }

        $result = Yii::$app->db->createCommand($sql)->execute();

        return $this->redirect(['index',
                    'ResultaatSearch[student_nummer]'=>$student_nr,
                ]);
    }

      
}


