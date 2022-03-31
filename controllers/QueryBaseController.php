<?php

// Base controller for 'reports' used as parent for GradeController and ReportController

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;

/**
 * A Query can have three directives for the view, this directive is the first char of a field
 * 
 * + a sum will be calculated of this field
 * 
 * - the field will not be dispplayed (somethimes you want a sort field not to be displayed)
 * 
 * ! a link, this field concat the data needed to form the link
 *    f.e. concat(link_name_top_be_displayed,'|hyper link or path|first_param|',param_value) '!field_name'
 *         there may be 0,1 or 2 parameters given.
 *    note that for the export the query is filtered to become a 'normal' query without the directives and concats.
 *    note that the complete concat commando may not contain any spaces
 */

class QueryBaseController extends Controller
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


    public function executeQuery($sql, $title = "no title", $export = false)
    {

        if ($export) {
            $sql=$this->exportQueryFilter($sql);
        }

        $result = Yii::$app->db->createCommand($sql)->queryAll();

        if (! $result) { // column names are derived from query results
            return null;
        }

        $data['col'] = array_keys($result[0]);
        $data['row'] = $result;

        if ($export) {
            $this->exportExcel($data);
            exit;
        } else {
            $data['title'] = $title;
            return $data;
        }
    }

    private function exportQueryFilter($query) // filter + - en ! column names and concats from sql statement for export - deze speciale tekens zijn indicatoren voor de view
    {
        $components = preg_split("/[\s]/", $query);
        $components = (array_filter($components, function($value) { return !is_null($value) && $value !== ''; }));

        $newQuery = "";
        foreach($components as $item) { // itterate through the whole query
            if (strtolower(substr($item, 0, 6))=='concat') {
                $sub= $components = preg_split("/[,(]/", $item);
                if ( count($sub) < 2 ) {
                    dd('concat in SQL query can not be tranformed for export; unknown syntax in concat.');
                }
                $item=$sub[1];
            }
            if ( substr($item, 1, 1)=='!' || substr($item, 1, 1)=='+' || substr($item, 1, 1)=='-' ) {
                $item = str_replace(['!','+','-'], "", $item);
            }
            if (strtolower(substr($item, 0, 6))=='limit') { // for export we don't have a limit, since limit is the last statement return query as is at this moment
                break;
            }
            $newQuery .= " ".$item;
        }

        return($newQuery);
    }

    public function exportExcel($data)
    {
        header('Content-type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') . '.csv"');
        // header("Pragma: no-cache");
        // header("Expires: 0");
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF";

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

    public function actionLog($export = false) // show access log (not part of any menu)
    {
        $sql = "select *
                from log
                where subject <> 'Student Rapport' || route <> '82.217.135.153'
                order by timestamp desc limit 200";
        $data = parent::executeQuery($sql, "Log", $export);

        return $this->render('output', [
            'data' => $data,
            'action' => Yii::$app->controller->action->id."?",
        ]);
    }

}

