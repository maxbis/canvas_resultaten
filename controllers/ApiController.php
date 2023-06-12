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

}
