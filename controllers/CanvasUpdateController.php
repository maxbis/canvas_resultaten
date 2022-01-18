<?php

namespace app\controllers;

use Yii;
use app\models\Resultaat;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;

use yii\helpers\ArrayHelper;

//use vxm\async\Task;
use Spatie\Async\Pool;


class CanvasUpdateController extends Controller {

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

    private function getSubmissionFromApi($id) {

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

    private function convertCanvasApiDate($canvasDate) {
        // Convert date format eg. '2022-01-11T10:28:50+01:00' into '2022-01-11 11:28:50'
        // and if date is NULL, then return 'default date'
        if ( $canvasDate == NULL ) return '1970-01-01 00:00:00';
        $thisDate = new \DateTime($canvasDate);
        $hour = (date_interval_create_from_date_string("1 hour"));
        date_sub($thisDate, $hour);
        return $thisDate->format('Y-m-d H:i:s');
    }

    public function actionIndex() {
        echo "Hello World!";
    }

    public function actionUpdateGradingStatus($moduleId, $regrading) {

        $sql = "
            SELECT a.course_id cours, a.id assignment, s.id submission, submitted_at submitted, graded_at graded, m.naam module
            FROM assignment a
            JOIN submission s on s.assignment_id= a.id
            JOIN assignment_group g on g.id = a.assignment_group_id
            JOIN module_def m on m.id = g.id
            WHERE s.graded_at ";
            $sql .= $regrading ? '<>' : '=';
            $sql .= " '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            AND m.id=$moduleId
        ";

        $sqlResult = Yii::$app->db->createCommand($sql)->queryAll();
        $count=0;

        $pool = Pool::create();
        $timerStart=round(microtime(true) * 1000);

        foreach ($sqlResult as $elem) {
            $pool->add(function () use ($elem) {
                $apiResult = $this->getSubmissionFromApi($elem['submission']);
                return $apiResult;
            })->then(function ($apiResult) use ($timerStart, $count, $elem) {
                $gradedAt = $this->convertCanvasApiDate($apiResult['gradedAt']);
                $submittedAt = $this->convertCanvasApiDate($apiResult['submittedAt']);

                if ( $elem['graded']<> $gradedAt ) {
                    $count++;
                    // Update submission
                    // $sql = "update submission set graded_at=:gradedAt, entered_score=:score, submitted_at=:submittedAt, workflow_state=:state where id=:id";
                    // $params=[':gradedAt'=>$gradedAt, ':score'=>$apiResult['score'], ':submittedAt'=>$submittedAt, ':id'=>$elem['submission'], ':state'=>$apiResult['state']];
                    if ( $apiResult['score'] ) {
                        $sql = "update submission set graded_at='".$gradedAt."', entered_score=".$apiResult['score'].", submitted_at='".$submittedAt."', workflow_state='".$apiResult['state']."' where id=".$elem['submission'];
                    } else {
                        $sql = "update submission set graded_at='".$gradedAt."', submitted_at='".$submittedAt."', workflow_state='".$apiResult['state']."' where id=".$elem['submission'];
                    }
                    $result = Yii::$app->db->createCommand($sql)->execute();
                }
            })->catch(function (Throwable $exception) {
                writeLog("Error async: ".$exception );
            });

        }

        await($pool); 
        writeLog("Async Pool(".$count." threads) ready, uS passed: ".strval(round(microtime(true) * 1000)-$timerStart));
        
        // dd('end');
        // return $this->actionNotGraded(false, $regrading);
        Yii::$app->session->setFlash('success', "Updated $count assignments in <i>".$elem['module']."</i>");
        return $this->redirect(['query/not-graded?regrading='.$regrading]);
    }


}