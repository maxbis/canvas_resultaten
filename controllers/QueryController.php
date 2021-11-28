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
                    [ 'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }


    private function executeQuery($sql, $title="no title", $export=false) {

        $result = Yii::$app->db->createCommand($sql)->queryAll();

       

        if ($result) { // column names are derived from query results
            $data['col']=array_keys($result[0]);
        }
        $data['row']=$result;
        
        if ($export) {
            $this->exportExcel($data);
            exit;
        } else {
            $data['title']=$title;
            return $data;
        }

    }

    public function exportExcel($data) {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') .'.csv"');

        foreach ($data['col'] as $key => $value) {
            echo $value.", ";
        }
        echo "\n";
        foreach ($data['row'] as $line) {
            foreach ( $line as $key => $value) {
                echo $value.", ";
            }
            echo "\n";
        }
        
    }

    public function actionActief($sort='desc', $export=false, $klas='') {

        // $sql="select student_naam Student, klas Klas, max(laatste_activiteit) 'Laatst actief' from resultaat group by 1,2 order by 3 $sort";
        if ($klas) {
            $select="and klas='$klas'";
        } else {
            $select='';
        }
        $sql="
            SELECT student_naam Student, klas Klas, module Module, laatste_activiteit 'Wanneer', datediff(curdate(), laatste_activiteit) 'Dagen'
            from resultaat o
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            order by 4 desc
        ";

        $data=$this->executeQuery($sql, "Laatste activiteit per student ".$klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionAantalActiviteiten($export=false, $klas='') {

        if ($klas) $select="where klas='$klas'"; else $select='';
        
        $sql="
            select student_naam Student,
            sum(case when (datediff(curdate(),laatste_activiteit)<=2) then 1 else 0 end) '-2',
            sum(case when (datediff(curdate(),laatste_activiteit)<=7) then 1 else 0 end) '-7',
            sum(case when (datediff(curdate(),laatste_activiteit)<=14) then 1 else 0 end) '-14',
            sum(1) 'Aantal'   
            from resultaat
            $select
            group by 1
            order by 2 desc
        ";
        $data=$this->executeQuery($sql, "Aantal activiteiten per student over tijd ".$klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Aantal activiteiten per student over de laatste 2, 7 en 14 dagen',
        ]);
    }

    public function actionWorkingOn($sort='desc', $export=false, $klas='') {

        if ($klas) {
            $select="and klas='$klas'";
        } else {
            $select='';
        }

        $sql="
            SELECT module Module, sum(1) Studenten
            from resultaat o
            where laatste_activiteit =
            (select max(laatste_activiteit) from resultaat i where i.student_nummer=o.student_nummer)
            and year(laatste_activiteit) > 2020
            $select
            group by 1
            order by 2 $sort
        ";

        $data=$this->executeQuery($sql, "Studenten ".$klas." werken aan", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionModulesFinished($export=false, $klas='') {

        if ($klas) {
            $select="where klas='$klas'";
        } else {
            $select='';
        }

        $sql="
            select Module, af 'Afgerond door' from
                (select course_id, module_id, module Module, sum(case when voldaan='V' then 1 else 0 end) af
            from resultaat o
            $select
            group by 1,2,3
            order by 1,2) alias
        ";
        // ToDo: order by werkt niet op server (order by moet in group by zitten)
        $data=$this->executeQuery($sql, "Modules voldaan ".$klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionVoortgang($sort='desc', $export=false, $klas='') {

        if ($klas) {
            $select="where klas='$klas'";
        } else {
            $select='';
        }

        $sql="select student_nummer Stdntnr, student_naam Student, klas Klas, SUM(case when voldaan='V' then 1 else 0 end) 'Voldaan' from resultaat $select group by 1,2, 3 order by 4 $sort";

        $data=$this->executeQuery($sql, "Voortgang ".$klas, $export);
        
        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
        ]);
    }

    public function actionBeoordeeld($export=false) {
        $sql="
            select module Module, max(laatste_beoordeling) Beoordeeld,  datediff(curdate(), max(laatste_beoordeling)) 'Dagen'
            from resultaat
            group by 1
            order by 2 desc
        ";
        $data=$this->executeQuery($sql, "Laatste beoordeling per module", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Minimaal één opdracht van de module is beoordeeld ... dagen gelden.',
        ]);
    }

    public function actionAantalBeoordelingen($export=false, $klas='') {
        if ($klas) {
            $select="where klas='$klas'";
        } else {
            $select='';
        }

        $sql="
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
        $data=$this->executeQuery($sql, "Beoordelingen over tijd ".$klas, $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => '(minimaal 1 opdracht van) module voor x studenten beoordeeld over 2, 7 en 14 dagen.',
        ]);
    }

    public function actionDetailsModule($studentNummer, $moduleId, $export=false){
        $sql="
            SELECT u.name naam, m.naam module, a.name Opdrachtnaam, s.workflow_state 'Status',
            CASE s.submitted_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.submitted_at END Ingeleverd,
            s.entered_score Score, 
            CASE s.graded_at WHEN '1970-01-01 00:00:00' THEN '' ELSE s.graded_at END Beoordeeld, r.name 'Door', s.preview_url Link
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            left outer join user r on r.id=s.grader_id
            join assignment_group g on g.id = a.assignment_group_id
            left outer join module_def m on m.id = g.id
            where g.id=$moduleId
            and u.student_nr=$studentNummer
            order by  a.position
        ";

        $data=$this->executeQuery($sql, "Module", $export);
        $data['title'] = 'Module <i> '.$data['row'][0]['module'].'</i> van '.$data['row'][0]['naam'];
        $data['show_from'] = 2; // show from colum 2 

        return $this->render('assignments', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'nocount' => 'True',
        ]);
    }

    public function actionNakijken($export=false) {

        $sql="
            SELECT u.name, sum(case when (datediff(curdate(),s.graded_at)<=2) then 1 else 0 end) 'week',
            sum(case when (datediff(curdate(),s.graded_at)<=14) then 1 else 0 end) 'twee weken',
            sum(case when (datediff(curdate(),s.graded_at)<=21) then 1 else 0 end) 'drie weken',
            sum(case when (datediff(curdate(),s.graded_at)<=82) then 1 else 0 end) '82 dagen'
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            group by 1
        ";
        $data=$this->executeQuery($sql, "Aantal opdrachten beoordeeld door", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'descr' => 'Aantal beoordelingen per beoordeelaar over 1, 2, 3 en 12 weken',
        ]);
    }

  
    public function actionStudent($studentNummer, $export=false) {

        $sql="SELECT r.student_naam Student, c.korte_naam Blok ,r.module Module, r.voldaan Voldaan, r.ingeleverd Ingeleverd, round(r.punten*100/r.punten_max) 'Punten %', r.laatste_activiteit 'Laatste Act.'
                FROM resultaat r
                LEFT OUTER JOIN course c on c.id = r.course_id
                WHERE student_nummer=$studentNummer
                ORDER BY c.pos, r.module_pos";

        $data=$this->executeQuery($sql, "Overzicht voor ", $export);

        $data['title'] = 'Overzicht voor '.$data['row'][0]['Student'];
        $data['show_from'] = 1; // show from colum 2 

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id,
            'nocount' => 'True',
            'params' => 'studentNummer='.$studentNummer,
        ]);
    }


}


