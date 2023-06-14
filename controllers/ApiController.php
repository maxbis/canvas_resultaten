<?php

// Stadard Yii CRUD controller

namespace app\controllers;

use Yii;
use yii\web\Controller;


/**
 * StudentController implements the CRUD actions for Student model.
 */
class ApiController extends Controller
{

    public function actionModules($s='') {

        $search_criteria='';
        if ( $s != '' ) {
            $search_criteria="AND ( c.naam like '%$s%' OR m.naam like '%$s%' )";
        }
        $sql = "
        select
            a.id assignment_id,
            m.pos mpos,
            a.position apos,
            a.name assignment_naam,
            m.id module_id,
            m.naam module_naam,
            c.id course_id,
            c.korte_naam 'korte_naam',
            c.naam course_naam
            from module_def m
        JOIN assignment a ON a.assignment_group_id = m.id
        JOIN course c ON c.id = a.course_id
        WHERE a.published=1
        $search_criteria
        ORDER BY m.pos, a.position
        ";

        $result = Yii::$app->db->createCommand($sql)->queryAll();

        $output = json_encode($result, JSON_PRETTY_PRINT);
        $output = array_map(function($arr) {
            return json_encode($arr);
        }, $result);
        $output = "[\n" . implode(",\n", $output) . "\n]";

        return $output  ;
    }

    public function actionNakijken($s='') {

        $search_criteria='';
        if ( $s != '' ) {
            $search_criteria="AND ( g.name like '%$s%' or n.label like '%$s%')";
        }
        $sql = "
        select c.id FROM nakijken
        join examen
        $search_criteria
        ORDER BY m_position, a_position
        ";
 
        $sql="
            SELECT a.course_id course_id, c.naam course_name, c.pos cpos, a.position apos, m.pos mpos,
		            a.id assignment_id, g.name module_name, a.name assignment_name,
                    m.id module_id,
                    n.words_in_order words_in_order, n.file_type file_type, n.instructie hint
            FROM assignment a
            JOIN assignment_group g on g.id=a.assignment_group_id
            JOIN course c on c.id=a.course_id 
            JOIN module_def m on m.id=g.id
            JOIN nakijken n on n.assignment_id = a.id
            WHERE g.name='Yii - Level 2'
            $search_criteria
            ORDER by g.name, cpos, apos";

        $result = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($result as $key => $value) {
            $result[$key]['words_in_order'] = explode(' ', $result[$key]['words_in_order']);
        }

        $output = json_encode($result, JSON_PRETTY_PRINT);
        $output = array_map(function($arr) {
            return json_encode($arr);
        }, $result);
        $output = "[\n" . implode(",\n", $output) . "\n]";

        return $output;
    }

}
