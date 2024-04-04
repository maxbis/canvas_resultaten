<?php

// Everythink to do with grading (grade menu)
// This controller ens in a view that enables updates via CanvasUpdateController, this controller redirects back to this controller

namespace app\controllers;
// use yii\web\Controller;
use Yii;
use yii\bootstrap4\Html;

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

class GradeController extends QueryBaseController
{
    public function actionNotGraded($export=false, $update=false) 
    {
        if ($update) {
            $hide="";$nHide="-";
        } else {
            $hide="-";$nHide="";
        }
       
        // $sql = "SELECT
        //     m.pos '-pos',
        //     concat(m.naam,'|/grade/not-graded-module|moduleId|',m.id) '!Module',
        //     sum( case when (not m.generiek) then 1 else 0 end ) '$nHide+Dev',
        //     sum( case when (m.generiek) then 1 else 0 end ) '$nHide+Gen',
        //     sum(1) '+Totaal',
        //     concat('&#8634; Update','|/canvas-update/update-grading-status|moduleId|',m.id,'|show_processing|1|') '$hide!Canvas update'
        // FROM assignment a
        // left outer join submission s on s.assignment_id= a.id
        // join user u on u.id=s.user_id
        // join assignment_group g on g.id = a.assignment_group_id
        // join module_def m on m.id = g.id
        // join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
        // where u.grade=1 and s.submitted_at > s.graded_at
        // group by 1, 2, 6
        // order by m.pos";

        // Slightly optimized into:

        $sql = "SELECT
            m.pos '-pos',
            c.korte_naam '#_Blok',
            concat('[ac]','|/grade/not-graded-module2|moduleId|',m.id) '!_alternatieve_link',
            concat(m.naam,'|/grade/not-graded-module|moduleId|',m.id) '!Module',
            sum( case when (not m.generiek) then 1 else 0 end ) '$nHide+Dev',
            sum( case when (m.generiek) then 1 else 0 end ) '$nHide+Gen',
            sum(1) '+Totaal',
            concat(max(datediff(now(), submitted_at)), ' dagen') '${nHide}Oudste',
            -- timediff( now(), greatest(m.last_updated, g.last_updated) ) 'Last Update',
            concat('&#8634; Update','|/canvas-update/update-grading-status|moduleId|',m.id,'|show_processing|1|') '$hide!Update',
            -- concat('&#8634; Update','|/canvas-update/update|assignmentGroup|',m.id,'|show_processing|1|') '$hide!Update',
            ''
        FROM assignment a
        left outer join submission s on s.assignment_id= a.id and s.submitted_at > s.graded_at
        join user u on u.id=s.user_id and u.grade=1
        join course c on c.id=a.course_id
        join module_def m on m.id = a.assignment_group_id
        join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
        join assignment_group g on g.id=m.id
            
        group by 1, 2, 3,4, 9
        order by m.pos";

        $data = parent::executeQuery($sql, "Wachten op beoordeling", $export);

        
        $lastLine =  "<hr>";
        if ($update) {
            $lastLine .= "<a class=\"bottom-button left\" href=\"".Yii::$app->controller->action->id."?update=".abs($update-1)."\"><< Terug</a>"; 
        } else {
            $lastLine .= "<a class=\"bottom-button\" href=\"".Yii::$app->controller->action->id."?update=".abs($update-1)."\">Update</a>"; 
        }
        

        return $this->render('/report/output', [
            'data' => $data,
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'lastLine' => $lastLine,
            'descr' => 'Cohort '.Yii::$app->params['subDomain'],
            'width' => [20,60,5,350,60,60,150,100,100],
        ]);
    }

    public function actionAllModules($export=false, $update=true) // Menu 4.1 - 4.2 - Wachten op beoordeling <TODO> drie overzichten in één tabbed overzicht.
    {
        if ($update) {
            $hide="";
        } else {
            $hide="-";
        }

        if (Yii::$app->user->identity->username=='beheer') {
            $line2="concat(COALESCE(m.korte_naam, c.korte_naam), '|/course/update|id|',c.id) '!#Blok',"; // sub werkt niet omdat de link van een overriden korte naam anders is.
            $line3="concat(m.id, '|/module-def/update|id|',m.id) '!ID',";
        }else{
            $line2=" c.korte_naam '#Blok',";
            $line3="m.id 'id',";;
        }

        $sql = "SELECT  m.pos '-pos',
                        $line2
                        $line3
                        concat(m.naam,'|/grade/not-graded-module|moduleId|',m.id,'|regrading|2') '!Module',
                        timediff( now(), greatest(m.last_updated, g.last_updated) ) 'Last Update',
                        concat('&#8634; Update','|/canvas-update/update|assignmentGroup|',m.id,'|params|7|') '$hide!Canvas update',
                        c.update_prio 'Upd.Prio',
                        date(max(r.laatste_beoordeling)) 'Laatste beoordeling'
                FROM module_def m
                join assignment_group g on g.id=m.id
                join course c on c.id = g.course_id
                left outer join resultaat r on r.module_id = m.id
                where m.pos is not null
                group by 1,2,3,4,5,6,7
                order by m.pos
             ";


        $reportTitle = "Update gehele module";
 
        $lastLine = "<hr>";
        if ($update) {
            $lastLine .= "<a class=\"bottom-button\" href=\"".Yii::$app->controller->action->id."?update=".abs($update-1)."\"><< Terug</a>"; 
        } else {
            $lastLine .= "<a class=\"bottom-button\" href=\"".Yii::$app->controller->action->id."?update=".abs($update-1)."\">Update</a>"; 
        }
    
        $data = parent::executeQuery($sql, $reportTitle, $export);

        return $this->render('output', [
            'data' => $data,
            # 'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=0', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            # 'lastLine' => $lastLine,
            'nocount' => true,
            'descr' => '',
        ]);
    }

    public function actionNotGradedPerDate($export=false) // Menu 4.3 - 4.4 - Wachten op beoordeling per datum
    {
        $sql = "
            SELECT  m.pos '-pos',
                    m.naam '#Module',
                    concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                    concat(u.name,'|/public/index|code|',u.code) '!Student',
                    substring(u.comment,1,3) 'Code',
                    concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                    s.attempt 'Poging',
                    concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where u.grade=1 and s.submitted_at > s.graded_at
            order by 6 ASC
            limit 250
        ";

        $data = parent::executeQuery($sql, "Beoordelingen op datum", $export);

        return $this->render('/report/output', [
            'data' => $data,
            'descr' => 'Rapport (en export) laat maximaal 250 regels zien. Updates zijn pas zichtbaar na update uit Canvas',
        ]);
    }

    public function actionNotGradedPerStudent($export=false) // Menu 4.3 - 4.4 - Wachten op beoordeling per datum
    {
        $sql = "
            SELECT  concat(u.name,'|/public/index|code|',u.code) '!Student',
                    m.pos '-pos',
                    m.naam '#Module',
                    concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                    substring(u.comment,1,3) 'Code',
                    concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                    s.attempt Poging,
                    concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where u.grade=1 and s.submitted_at > s.graded_at
            order by 4 ASC
            limit 250
        ";

        $reportTitle = "Alle beoordelingen gesorteerd op student";

        $data = parent::executeQuery($sql, $reportTitle, $export);

        return $this->render('/report/output', [
            'data' => $data,
            'descr' => 'Rapport (en export) laat maximaal 250 regels zien. Updates zijn pas zichtbaar na update uit Canvas',
        ]);
    }

    public function actionBlocked($export=false) { // Show Modules Blocked for Grading
        $sql = "
            SELECT  m.pos '-pos',
            concat(u.name,'|/public/index|code|',u.code) '!Student',
            m.naam Modue
            FROM assignment a
            left outer join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten < 0
            group by 1, 2, 3
            order by m.pos
        ";

        $data = parent::executeQuery($sql, 'Geblokkeerde modules', $export);

        return $this->render('/report/output', [
            'data' => $data,
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => 'Modules met opdrachten met een negatieve score worden niet in de beoordelingsoverzichten gegeven.',
        ]);

    }






    public function actionNotGradedAll($export=false, $update=false) 
    {
        if ($update) {
            $hide="";$nHide="-";
        } else {
            $hide="-";$nHide="";
        }

        $sql = "SELECT
            module_pos '-pos',
            concat(cohort,'|https://',cohort,'.cmon.ovh/grade/not-graded') '!#Cohort',
            module_name 'Module',
            sum( case when (not generiek) then 1 else 0 end ) '$nHide+Dev',
            sum( case when (generiek) then 1 else 0 end ) '$nHide+Gen',
            sum(1) '+Totaal',
            concat(max(datediff(now(), submitted_at)), ' dagen') 'Oudste',
            ''
        FROM all_submissions
        
        WHERE graded_at < submitted_at
        AND grading_enabled=1
        AND minpunten >= 0
        group by 1, 2, 3
        order by cohort DESC, module_pos";

        $data = parent::executeQuery($sql, "Wachten op beoordeling", $export);

        return $this->render('/report/output', [
            'data' => $data,
            'action' => ['link' => Yii::$app->controller->action->id , 'param' => 'export=1', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,],
            'descr' => 'Alle cohorten',
            'width' => [20,60,320,60,60,200],
        ]);
    }


    public function actionNotGradedModule2($moduleId = '', $export = false) // Menu 4.1b - 4.2b Nog beoordelen = ingeleverd en nog geen beoordeling van één module
    {
        //$this->actionUpdateModuleGrading($moduleId, $regrading);
        $cohort = explode('.', $_SERVER['SERVER_NAME'])[0];
        $sql = "
            SELECT
                m.naam module_name,
                m.id module_id,
                u.code student_code,
                a.id assignment_id,
                a.position assignment_pos,
                a.name assignment_name,
                a.course_id course_id,
                n.assignment_id nakijken_id,
                u.name student_name,
                u.klas student_klas,
                concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'ingeleverd',
                case when s.attempt <=3 then s.attempt else concat(s.attempt,'**') end 'poging',
                s.attempt poging,
                CASE 
                    WHEN n.assignment_id is null
                    THEN '-' 
                    ELSE '&#10003;'
                END nakijken,
                concat('✎ ','|/nakijken/update/|assignment_id|',a.id,'|alt_return|',1) '!Upd',
                concat('http://localhost:5000/correcta/$cohort','/',a.id) 'ac_link',
                concat('https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) 'canvas_link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            left outer join canvas.nakijken n on n.assignment_id=a.id
            where u.grade=1 and s.submitted_at > s.graded_at
            and m.id=$moduleId
            order by a.position, s.attempt, s.submitted_at
        ";

        $result = Yii::$app->db->createCommand($sql)->queryAll();
        if (empty($result)) {
            // The result is empty, redirect back to start (reload)
            return Yii::$app->response->redirect(['resultaat/start']);
        }

        # return page is wrong

        // $lastLine  = "<hr>";
        // $lastLine .= "<a class=\"btn bottom-button\" href=\"https://c23.cmon.ovh/canvas-update/update?assignmentGroup=".$moduleId."&show_processing=1\">Update</a>"; 
        // $lastLine  .= "<br>&nbsp;";

        $lastLine = "";

        return $this->render('/grade/gradeModule', [ 'data' => $result, 'lastLine' => $lastLine ]);

    }

    public function actionNotGradedModule($moduleId = '', $export = false) // Menu 4.1b - 4.2b Nog beoordelen = ingeleverd en nog geen beoordeling van één module
    {
        //$this->actionUpdateModuleGrading($moduleId, $regrading);
        $cohort = explode('.', $_SERVER['SERVER_NAME'])[0];
        $sql = "
            SELECT
                m.naam Module,
                a.position '-pos',
                concat(a.name,'|/public/details-module|assGroupId|',m.id,'|code|',u.code) '!Opdracht',
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                u.klas Klas,
                substring(u.comment,1,3) 'Code',
                concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                case when s.attempt <=3 then s.attempt else concat(s.attempt,'**') end 'poging',
                s.attempt poging,
                concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            left outer join canvas.nakijken n on n.assignment_id=a.id
            where u.grade=1 and s.submitted_at > s.graded_at
            and m.id=$moduleId
            order by a.position, s.attempt, s.submitted_at
        ";

        $data = parent::executeQuery($sql, "Wachten op beoordeling ", $export);

        if (! isset($data['row']) ) {
            return $this->render('/report/output', [
                'data' => $data,
            ]);
        }

        $data['title'].=" voor <i>".$data['row'][0]['Module']."</i>";
        $data['show_from']=1;


        // Create lastLineButton with buttons to open $pagesPerButton in one go
        $lastLine= "<script>\n";
        $count=0;
        $pagesPerButton=10;
        $buttons=[];

        foreach ($data['row'] as $item) {
            if ( $count%$pagesPerButton == 0) {
                $lastLine.= "function openAllInNewTab".$count."() {\n";
                array_push($buttons, $count);
            }
            // dd( explode('|',$item['!Link'])[1] );
            $lastLine.= "window.open('". explode('|',$item['!Link'])[1] ."', '_blank');\n";
            $count++;
            if ( $count%$pagesPerButton == 0) {
                $lastLine.= "}\n";
            }
        }
        if ( $count%$pagesPerButton != 0) {
            $lastLine.= "}\n";
        }
        $lastLine.= "</script><hr>\n";

        // $lastLine.= Html::a("↺", ['canvas-update/update-grading-status', 'moduleId'=>$moduleId, 'regrading'=>'2'], ['title'=>'Update and back', 'class'=>'btn btn-link', 'style'=>'float: right'] );
        $count=count($buttons);
        foreach (array_reverse($buttons) as $elem) {
            if($count-- < 7) { // max number of buttons at bottom of page
                $start=$elem+1;
                $stop=min($elem+10,count($data['row']));
                $lastLine.="&nbsp;&nbsp;<button class=\"btn btn-link\" style=\"float: right;\" onclick=openAllInNewTab".$elem."() title=\"Open all submissions\">Grade ".$start."-".$stop." &#10142;</button>";
            }
        }
        $lastLine.="&nbsp;&nbsp;&nbsp;<div style=\"float: left;\"><a class=\"btn btn-light\" href=\"".Yii::$app->request->referrer."\"><< Back</a></div><br><br>";

        return $this->render('/report/output', [
            'data' => $data,
            'descr' => '** student heeft meer dan 3 pogingen, maximaal aantal punten -/- 20%',
            'lastLine' => $lastLine,
        ]);
    }

}