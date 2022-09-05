<?php

// This class is used to update the grading status.
// This class connects to Canvas via GraphIQ it calls the API n times where n is the number of submissions open for grading.
// This class should also update information about the grader(id) but atm the GraphIQ interface does not return thsi info nor can we select more than one ID at a time 

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;

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
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_VERBOSE => 1
        ));

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "query=".$query);

        $out = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        // dd($out);

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

    public function actionUpdateGradingStatus($moduleId, $regrading=1) {

        $sql = "
            SELECT a.course_id cours, a.id assignment, s.id submission, s.submitted_at submitted, graded_at graded, m.naam module
            FROM assignment a
            JOIN submission s on s.assignment_id= a.id
            JOIN assignment_group g on g.id = a.assignment_group_id
            JOIN module_def m on m.id = g.id
            WHERE s.submitted_at > s.graded_at
            AND m.id=$moduleId
            ORDER BY a.id, s.id limit 10
        ";

        $sqlResult = Yii::$app->db->createCommand($sql)->queryAll();
        $countUpdates=0;
        $countThreads=0;

        # dd($sqlResult);

        $pool = Pool::create();
        $timerStart=round(microtime(true) * 1000);

        $limit = 25;
        foreach ($sqlResult as $elem) {
            if ( --$limit == 0 ) {
                break;
            }

            $result=$pool->add(function () use ($elem, &$countThreads) {
                $countThreads++;
                $apiResult = $this->getSubmissionFromApi($elem['submission']);
                // dd($apiResult);
                return $apiResult;
            })->then(function ($apiResult) use ($timerStart, &$countUpdates, $elem) {
                $gradedAt = $this->convertCanvasApiDate($apiResult['gradedAt']);
                $submittedAt = $this->convertCanvasApiDate($apiResult['submittedAt']);

                if ( $elem['graded']<> $gradedAt ) {
                    $countUpdates++;
                    $sql = "update submission set graded_at='".$gradedAt."', entered_score=".($apiResult['score'] ? $apiResult['score'] : '0').", submitted_at='".$submittedAt."', workflow_state='".$apiResult['state']."' where id=".$elem['submission'];
                    $result = Yii::$app->db->createCommand($sql)->execute();
                }
            })->catch(function (Throwable $exception) {
                writeLog("Error async: ".$exception );
            });

        }

        await($pool); 
        writeLog("Async Pool(".$countThreads." threads, ".$countUpdates." updates) ready, uS passed: ".strval(round(microtime(true) * 1000)-$timerStart));
        // dd("Async Pool(".$countThreads." threads, ".$countUpdates." updates) ready, uS passed: ".strval(round(microtime(true) * 1000)-$timerStart));
        // exit;

        if ( $limit == 0 ) {
            Yii::$app->session->setFlash('error', "Update may not be completed");
        }
        Yii::$app->session->setFlash('success', "Updated, updated $countUpdates assignments in <i>".(isset($elem['module']) ? $elem['module'] : $moduleId)."</i>");
    
        return $this->redirect(Yii::$app->request->referrer);
        //return $this->redirect(['grade/not-graded?update=1&regrading='.$regrading]);
    }

    public function actionUpdate($assignmentGroup) {
        $database='canvas-'.Yii::$app->params['subDomain'];
        $cmd = "python3 ../import/import.py --database $database -l 0 -a $assignmentGroup";
        $cmd = escapeshellcmd($cmd);
        $shellOutput = shell_exec($cmd);

        Yii::$app->session->setFlash('success', "<pre>$shellOutput</pre>");
        return $this->redirect(Yii::$app->request->referrer);
    }


}