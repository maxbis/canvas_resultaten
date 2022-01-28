<?php

// test queries apart from GUI


namespace app\controllers;
use yii\web\Controller;
use Yii;

/**
 * BeoordelingController implements the CRUD actions for Beoordeling model.
 * A Query can have three directives for the view, this directive is the first char of a field
 * 
 * + a sum will be calculated of this field
 * 
 * - the filed will not be dispplayed (somethimes you want a sort field not to be displayed)
 * 
 * ! a link, this field concat the data needed to form the link
 *    f.e. concat(link_name_top_be_displayed,'|hyper link or path|first_param|',param_value) '!field_name'
 *         there may be 0,1 or 2 parameters given.
 *    note that for the export the query is filtered to become a 'normal' query without the directives and concats.
 *    note that the complete concat commando may not contain any spaces
 */

class QueryController extends QueryBaseController
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

    public function actionAantal($export = false) { 

        $sql = "
            SELECT u.name naam,
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -1 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+1dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -2 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+2dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -3 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+3dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -4 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+4dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -5 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+5dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -6 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+6dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -7 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+7dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -8 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+8dag',
            sum(case when ( CAST( DATE_ADD(curdate(), INTERVAL -9 DAY) as date) = CAST(s.graded_at as date) ) then 1 else 0 end) '+9dag'
            FROM submission s
            inner join assignment a on s.assignment_id=a.id
            inner join user u on u.id=s.grader_id
            where datediff(curdate(),s.graded_at)<=7
            group by 1
            order by 1
        ";

        return $this->render('output', [
            'data' => $this->executeQuery($sql, "Aantal opdrachten beoordeeld door", $export),
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Aantal beoordelingen per beoordeelaar.',
        ]);
    }

    public function actionOverview($export=false) {

        $sql = "SELECT id, naam, substring(naam,1,4) 'mod' from module_def where generiek = 0 order by pos";

        $modules = Yii::$app->db->createCommand($sql)->queryAll();

        $query = "";
        $count = 0;
        foreach($modules as $module) {
            $count++;
            $query.=",sum( case when r.module_id=".$module['id']." && r.voldaan='V' then 1 else 0 end) '".str_pad($count,2,"0", STR_PAD_LEFT)."'";
        }

        $sql = "
            SELECT 
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            sum( case when r.voldaan='V' then 1 else 0 end) 'Tot'
            $query
            FROM resultaat r
            LEFT OUTER JOIN course c on c.id = r.course_id
            INNER JOIN module_def d on d.id=r.module_id
            INNER JOIN user u on u.student_nr=r.student_nummer
            WHERE d.generiek = 0
            GROUP BY 1
            ORDER BY 1
        ";
        $data = $this->executeQuery($sql, "Overview Dev Modules", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'nocount' => 'True',
        ]);
    }

    public function actionAttempts($export=false) {
        $sql = "
            select u.name Student, klas Klas, g.name Module, round(sum(s.attempt)/sum(1),1) 'Gemiddeld', max(s.attempt) 'Max'
            from submission s
            inner join user u on u.id = s.user_id
            inner join assignment a on a.id = s.assignment_id
            inner join assignment_group g on g.id = a.assignment_group_id
            group by 1,2, 3
            having sum(s.attempt)/sum(1) >1.5
            order by 4 desc
        ";

        $data = $this->executeQuery($sql, "Aantal pogingen", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Gemiddeld aantal pogingen en maximaal aantal per student/module.<br>Alleen als het gemiddel aantal pogingen > 1.5 voor de module is.'
        ]);
    }

}
