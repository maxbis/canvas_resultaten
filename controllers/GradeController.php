<?php

// Everythink to do with grading (grade menu)
// This controller ens in a view that enables updates via CanvasUpdateController, this controller redirects back to this controller

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

class GradeController extends QueryBaseController
{

    public function actionMenu41($export=false, $update=false){ // menu 4.1 wrapper voor menu highlight - each menu needs to have a unique function
        return $this->actionNotGraded(isset($export)&&$export, false, isset($update)&&$update);
    }

    public function actionMenu42($export=false, $update=false){ // menu 4.2 wrapper voor menu highlight - each menu needs to have a unique function
        return $this->actionNotGraded(isset($export)&&$export, true, isset($update)&&$update);
    }

    public function actionMenu43($export=false, $update=false){ // menu 4.3 wrapper voor menu highlight - each menu needs to have a unique function
        return $this->actionNotGraded(isset($export)&&$export, 2, isset($update)&&$update);
    }

    public function actionNotGraded($export=false, $regrading=false, $update=false) // Menu 4.1 - 4.2 - Wachten op beoordeling 
    {

        if (!$update){
            $sql = "
            SELECT  m.pos '-pos',
            -- concat('&#8634;','|/canvas-update/update-grading-status|moduleId|',m.id,'|regrading|$regrading') '!Upd',
            concat(m.naam,'|/grade/not-graded-module|moduleId|',m.id,'|regrading|$regrading') '!Module',
            sum( case when (not m.generiek) then 1 else 0 end ) '+Dev',
            sum( case when (m.generiek) then 1 else 0 end ) '+Gen',
            sum(1) '+Totaal'
            FROM assignment a
            left outer join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where s.submitted_at > s.graded_at";
            if ( $regrading <= 1 ) {
                $sql .= " and s.graded_at ";
                $sql .= $regrading ? '<>' : '=';
                $sql .= "'1970-01-01 00:00:00'";
            }
            $sql .= "
            group by 1, 2
            order by m.pos
            ";

        } else {
            $sql = "
            SELECT
            m.pos '-pos',
            concat(m.naam,'|/grade/not-graded-module|moduleId|',m.id,'|regrading|$regrading') '!Module',
            sum(1) '+Totaal',
            concat('&#8634; Update','|/canvas-update/update-grading-status|moduleId|',m.id,'|regrading|$regrading|show_processing') '!Canvas update'
            FROM assignment a
            left outer join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where s.submitted_at > s.graded_at";
            if ( $regrading <= 1 ) {
                $sql .= " and s.graded_at ";
                $sql .= $regrading ? '<>' : '=';
                $sql .= "'1970-01-01 00:00:00'";
            }
            $sql .= "
            group by 1, 2, 4
            order by m.pos
            ";
        }

        if ($regrading == 0 ) {
            $reportTitle = "Wachten op eerste beoordeling";
        } elseif ( $regrading == 1) {
            $reportTitle = "Wachten op herbeoordeling";
        } else {
            $reportTitle = "Wachten op beoordeling";
        }

        $data = parent::executeQuery($sql, $reportTitle, $export);

        $lastLine =  "<hr><div style=\"float: right;\"><a href=\"".Yii::$app->controller->action->id."?update=".abs($update-1)."\">Update</a></div"; 

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'lastLine' => $lastLine,
        ]);
    }

    public function actionNotGradedModule($moduleId = '', $export = false, $regrading = false) // Menu 4.1b - 4.2b Nog beoordelen = ingeleverd en nog geen beoordeling van één module
    {
        //$this->actionUpdateModuleGrading($moduleId, $regrading);

        $sql = "
            SELECT
                m.naam Module,
                m.pos '-pos',
                concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                concat(u.name,'|/public/index|code|',u.code) '!Student',
                concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                s.attempt poging,
                concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where s.submitted_at > s.graded_at";
            if ( $regrading <= 1 ) {
                $sql .= " and s.graded_at ";
                $sql .= $regrading ? '<>' : '=';
                $sql .= "'1970-01-01 00:00:00'";
            }
            $sql .="
            and m.id=$moduleId
            order by 3, 5
        ";

        $data = parent::executeQuery($sql, "Wachten op eerste beoordeling per module", $export);
        if ($regrading) {
            $data['title']="Wachten op herbeoordeling voor <i>".$data['row'][0]['Module']."</i>";
        } else {
            $data['title']="Wachten op eerste beoordeling voor <i>".$data['row'][0]['Module']."</i>";
        }
        
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

        foreach (array_reverse($buttons) as $elem) {
            $start=$elem+1;
            $stop=min($elem+10,count($data['row']));
            $lastLine.=  "<button class=\"btn btn-link\" style=\"float: right;\" onclick=openAllInNewTab".$elem."() title=\"Open all submissions\">Grade ".$start."-".$stop." &#10142;</button>";
        }


        return $this->render('output', [
            'data' => $data,
            'lastLine' => $lastLine,
        ]);
    }

    public function actionNotGradedPerDate($export=false, $regrading=false) // Menu 4.3 - 4.4 - Wachten op beoordeling per datum
    {
        $sql = "
            SELECT  m.pos '-pos',
                    m.naam Module,
                    concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                    concat(u.name,'|/public/index|code|',u.code) '!Student',
                    concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                    s.attempt 'Poging',
                    concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where s.graded_at ";
        $sql .= $regrading ? '<>' : '=';
        $sql.=" '1970-01-01 00:00:00' and s.submitted_at > s.graded_at
            order by 5 ASC
            limit 250
        ";

        $reportTitle = $regrading ? "Wachten op herbeoordeling op datum" : "Wachten op eerste beoordeling op datum";

        $data = parent::executeQuery($sql, $reportTitle, $export);

        return $this->render('output', [
            'data' => $data,
            'descr' => 'Rapport (en export) laat maximaal 250 regels zien. Updates zijn pas zichtbaar na update uit Canvas',
        ]);
    }

    public function actionNotGradedPerStudent($export=false) // Menu 4.3 - 4.4 - Wachten op beoordeling per datum
    {
        $sql = "
            SELECT  m.pos '-pos',
                    m.naam Module,
                    concat(a.name,'|/public/details-module|moduleId|',m.id,'|code|',u.code) '!Opdracht',
                    concat(u.name,'|/public/index|code|',u.code) '!Student',
                    concat(date(s.submitted_at),' (',datediff(now(), s.submitted_at),')') 'Ingeleverd',
                    s.attempt Poging,
                    concat('Grade&#10142;','|https://talnet.instructure.com/courses/',a.course_id,'/gradebook/speed_grader?assignment_id=',a.id,'&student_id=',u.id) '!Link'
            FROM assignment a
            join submission s on s.assignment_id= a.id
            join user u on u.id=s.user_id
            join assignment_group g on g.id = a.assignment_group_id
            join module_def m on m.id = g.id
            join resultaat r on  module_id=m.id and r.student_nummer = u.student_nr and r.minpunten >= 0
            where s.submitted_at > s.graded_at
            order by 4 ASC
            limit 250
        ";

        $reportTitle = "Alle beoordelingen gesorteerd op student";

        $data = parent::executeQuery($sql, $reportTitle, $export);

        return $this->render('output', [
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

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
            'descr' => 'Modules met opdrachten met een negatieve score worden niet in de beoordelingsoverzichten gegeven.',
        ]);

    }


}