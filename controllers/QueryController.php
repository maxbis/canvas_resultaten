<?php

namespace app\controllers;

use Yii;
use app\models\Resultaat;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;

use yii\helpers\ArrayHelper;

/**
 * BeoordelingController implements the CRUD actions for Beoordeling model.
 */
class QueryController extends Controller
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
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }


    private function executeQuery($sql, $title = "no title", $export = false)
    {

        $result = Yii::$app->db->createCommand($sql)->queryAll();

        if ($result) { // column names are derived from query results
            $data['col'] = array_keys($result[0]);
        }
        $data['row'] = $result;

        if ($export) {
            $this->exportExcel($data);
            exit;
        } else {
            $data['title'] = $title;
            return $data;
        }
    }

    public function exportExcel($data)
    {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') . '.csv"');

        $seperator = ";"; // NL version, use , for EN

        foreach ($data['col'] as $key => $value) {
            echo $value . $seperator;
        }
        echo "\n";
        foreach ($data['row'] as $line) {
            foreach ($line as $key => $value) {
                echo $value . $seperator;
            }
            echo "\n";
        }
    }



    public function actionLog($export = false)
    {
        $sql = "select *
                from log
                where subject <> 'Student Rapport' || route <> '82.217.135.153'
                order by timestamp desc limit 200";
        $data = $this->executeQuery($sql, "Log", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    private function addLogSql($sql, $subject = '', $message = '')
    {
        $route = Yii::$app->requestedRoute;
        $sql .= ";INSERT INTO log (subject, message, route) VALUES ('" . $subject . "', '" . $message . "', '" . $route . "');";
        return $sql;
    }

    public function actionActief($sort = 'desc', $export = false, $klas = '')
    { // menu Rapporten - Student laatst actief op....

        // $sql="select student_naam Student, klas Klas, max(laatste_activiteit) 'Laatst actief' from resultaat group by 1,2 order by 3 $sort";
        if ($klas) {
            $select = "and o.klas='$klas'";
        } else {
            $select = '';
        }
        $sql = "
            SELECT
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            o.klas Klas, module Module, laatste_activiteit 'Wanneer', datediff(curdate(), laatste_activiteit) 'Dagen'
            from resultaat o
            inner join user u on u.student_nr=o.student_nummer
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            order by 4 desc
        ";

        $data = $this->executeQuery($sql, "Laatste activiteit per student " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionAantalActiviteiten($export = false, $klas = '')
    { // menu Rapporten - Actieve studenten over tijd

        if ($klas) $select = "where u.klas='$klas'";
        else $select = '';

        $sql = "
            select u.klas klas, concat(u.name,'|/query/submissions|code|',u.code) '!Student',
            sum(case when (datediff(curdate(),submitted_at)<=2) then 1 else 0 end)  '+-2',
            sum(case when (datediff(curdate(),submitted_at)<=7) then 1 else 0 end)  '+-7',
            sum(case when (datediff(curdate(),submitted_at)<=14) then 1 else 0 end) '+-14',
            sum(case when (datediff(curdate(),submitted_at)<=21) then 1 else 0 end) '+-21',
            sum(case when (datediff(curdate(),submitted_at)<=28) then 1 else 0 end) '+-28',
            sum(case when (datediff(curdate(),submitted_at)<=60) then 1 else 0 end) '+-60',
            sum(case when (datediff(curdate(),submitted_at)<=90) then 1 else 0 end) '+-90'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            $select
            group by 1,2
            order by 4 DESC, 5 DESC, 6 DESC
        ";
        $data = $this->executeQuery($sql, "Aantal activiteiten per student over tijd " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Aantal activiteiten (ingeleverde opdrachten) per student over de laatste dagen en weken',
        ]);
    }

    public function actionWorkingOn($sort = 'desc', $export = false, $klas = '')
    { // menu Rapporten - Student werken aan...

        if ($klas) {
            $select = "and klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            SELECT module Module, sum(1) Studenten
            from resultaat o
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            group by 1
            order by 2 $sort
        ";

        $data = $this->executeQuery($sql, "Studenten " . $klas . " werken aan", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionVoortgang($sort = 'desc', $export = false, $klas = '')
    { // menu Rapporten - Ranking studenten

        if ($klas) {
            $select = "where r.klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            select
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                r.klas Klas,
                u.ranking_score 'Score',
                SUM(case when r.voldaan='V' and d.generiek=0 then 1 else 0 end) 'V-Dev',
                SUM(case when r.voldaan='V' and d.generiek=1 then 1 else 0 end) 'V-Gen',
                sum(r.punten) 'Punten totaal'
                FROM resultaat r
                INNER JOIN module_def d ON d.id=r.module_id
                INNER JOIN user u ON u.student_nr = r.student_nummer
            $select
            group by 1,2,3
            order by 3 $sort";

        $data = $this->executeQuery($sql, "Voortgang/Ranking " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionModulesFinished($export = false, $klas = '')
    { // menu Rapporten - Module is c keer voldaan

        if ($klas) {
            $select = "where klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            select
                Module,
                af 'Afgerond door'
                from
                (select course_id, module_id, module Module, sum(case when voldaan='V' then 1 else 0 end) af
            from resultaat o
            join module_def d on d.id=o.module_id
            $select
            group by 1,2,3
            order by d.pos) alias
        ";
        // ToDo: order by werkt niet op server (order by moet in group by zitten)
        $data = $this->executeQuery($sql, "Modules voldaan " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }


    public function actionBeoordeeld($export = false)
    { // menu Rapporten - Laatste beoordeling per module
        $sql = "
            select module Module, max(laatste_beoordeling) Beoordeeld,  datediff(curdate(), max(laatste_beoordeling)) 'Dagen'
            from resultaat
            group by 1
            order by 2 desc
        ";
        $data = $this->executeQuery($sql, "Laatste beoordeling per module", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Minimaal één opdracht van de module is beoordeeld ... dagen gelden.<br/>Automatisch beoordeeelde opdrachten worden ook geteld.',
        ]);
    }

    public function actionAantalBeoordelingen($export = false, $klas = '')
    { // menu Rapporten - Beoordelingen per module over tijd
        $sql = "
            select module Module, max(laatste_beoordeling) Beoordeeld,  datediff(curdate(), max(laatste_beoordeling)) 'Dagen'
            from resultaat
            group by 1
            order by 2 desc
        ";
        $data = $this->executeQuery($sql, "Laatste beoordeling per module", $export);

        if ($klas) {
            $select = "where klas='$klas'";
        } else {
            $select = '';
        }

        $sql = "
            select module Module,
            sum(case when (datediff(curdate(),laatste_beoordeling)<=2) then 1 else 0 end) '-2',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=7) then 1 else 0 end) '-7',
            sum(case when (datediff(curdate(),laatste_beoordeling)<=14) then 1 else 0 end) '-14',
            sum(1) 'Aantal'
            from resultaat
            $select
            group by 1
            order by 2 desc
        ";
        $data = $this->executeQuery($sql, "Beoordelingen over tijd " . $klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => '(minimaal 1 opdracht van) module voor x studenten beoordeeld over 2, 7 en 14 dagen.<br/>Automatisch beoordeeelde opdrachten worden ook geteld.',
        ]);
    }

    public function actionGetAllResultaat($export = true)
    {  // export voor Theo - staat onder knop bij Gridview van alle resutlaten
        $sql = "select * from resultaat order by student_nummer, module_id";
        $data = $this->executeQuery($sql, "", $export);
    }

    public function actionNakijken($export = false)
    { // menu Rapporten - Aantal beoordeligen per docent

        $sql = "
            SELECT u.name, sum(case when (datediff(curdate(),s.graded_at)<=2) then 1 else 0 end) '+week',
            sum(case when (datediff(curdate(),s.graded_at)<=14) then 1 else 0 end) '+twee weken',
            sum(case when (datediff(curdate(),s.graded_at)<=21) then 1 else 0 end) '+drie weken',
            sum(case when (datediff(curdate(),s.graded_at)<=84) then 1 else 0 end) '+12 weken'
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            group by 1
            order by 1
        ";
        $data = $this->executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Aantal (handmatige) beoordelingen per beoordeelaar over 1, 2, 3 en 12 weken',
        ]);
    }

    public function actionStudentenLijst($export = false)
    { // menu Beheer - Studentencodes export
        $sql = "SELECT id 'Canvas Id', name Naam, login_id email, student_nr 'Student nr', klas Klas, code Code FROM user";

        $data = $this->executeQuery($sql, "Studentenlijst", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Studentenlijst (voor Export naar Excel)',
        ]);
    }

    public function actionSubmissions($export = false, $code)
    { // tijdelijk om een export te krijgen - niet in het menu (hidden feature)
        $sql = "
            SELECT  u.name Student, DATE_FORMAT(s.submitted_at,'%y') Jaar,
            lpad(week(s.submitted_at,1),2,0) Week,
            sum(1) '+Aantal'
            FROM `submission`  s
            inner join user u on u.id = s.user_id
            and u.code='$code'
            and s.submitted_at > '1970-01-01 00:00:00'
            group by 1,2,3
            order by 1,2,3
        ";
        $data = $this->executeQuery($sql, "Submissions", $export);

        return $this->render('/public/chart', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Submissions per week',
            'nocount' => 'True',
            'code' => $code,
        ]);
    }


    public function actionResubmitted($export = false)
    {
        $sql = "
            SELECT  concat(m.naam, '|/public/details-module|moduleId|', m.id, '|code|', u.code) '!Module',
                    concat(u.name,'|/public/index|code|',u.code) '!Student',
                    max(s.submitted_at) Ingeleverd,
                    sum(1) Aantal
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at > '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            group by 1,2
            order by 1,2
        ";

        $data = $this->executeQuery($sql, "Wachten op herbeoordeling", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Gesorteerd van meest recent naar ouder',
        ]);
    }

    public function actionNotGraded($export = false)
    {
        $sql = "
            SELECT  m.pos '#', m.naam 'Module',
                    sum(1) Aantal
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            where s.graded_at = '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            group by 1
            order by m.pos
        ";

        $data = $this->executeQuery($sql, "Wachten op eerste beoordeling", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'nocount' => True
        ]);
    }
}
