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

    public function getKlasQueryPart($klas) {
        if ($klas) {
            if ($klas=='all') {
                $select = "";
                setcookie('klas', null, -1, '/'); 
            } else {
                $select = "and u.klas='$klas'";
                setcookie('klas', $klas, 0, '/');
            }
        } else {
            if ( isset($_COOKIE['klas']) ){
                $select = "and u.klas='". $_COOKIE['klas']."'";
            } else {
                $select = '';
            }
        }
        return $select;
    }

    public function executeQuery($sql, $title = "no title", $export = false)
    {

        if ($export) {
            $sql=$this->exportQueryFilter($sql);
        }

        if ($result = Yii::$app->db->createCommand($sql)->queryAll()) {
            $data['col'] = array_keys($result[0]);
            $data['row'] = $result;
        // } else {
        //     echo "<h2>oops, the query returned an empty result</h2>";
        //     echo "<br><hr>";
        //     echo "<pre>";
        //     echo "controller : ".Yii::$app->controller->id;
        //     echo "<br>";
        //     echo "action     : ".Yii::$app->controller->action->id;
        //     echo "<hr>";
        //     echo "Query<br>".$sql;
        //     echo "</pre>";
        //     echo "<hr>";
        //     exit;
        }

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
        # return($query);
        $components = preg_split("/[\s]/", $query);
        $components = (array_filter($components, function($value) { return !is_null($value) && $value !== ''; }));

        $newQuery = "";

        // $test = preg_split("/[\s]/",$query);
        // dd($test);

        foreach($components as $item) { // itterate through the whole query *** !be aware, no spaces in concat! ***
            if ( str_contains(substr($item,0,4), "|") ) { // if the first 4 chars of this item contains | then we probably have a space in the concat string of the !item column
                dd('Part of Query ('.$item.') has |, possible space in concat() of the !row in select?');
            }

            if (strtolower(substr($item, 0, 6))=='concat') {
                $sub= $components = preg_split("/[,(]/", $item);
                if ( count($sub) <= 2 ) {
                    // cannot filter becasue we have spaces in the concat, defaults back to Simple filter
                    return $this->exportQueryFilterSimple($query);
                    dd('concat in SQL query can not be tranformed for export; unknown syntax in concat.');
                }
                $item=$sub[1];
            }
            // if first char = +,-, or ! and 2nd char is alpha then remove first char (note that in time conversions the $item can be +01:00 this item is not allwed to be filtered
            if ( ctype_alpha(substr($item,2,1)) && (substr($item, 1, 1)=='!' || substr($item, 1, 1)=='+' || substr($item, 1, 1)=='-') ) {
                $item = str_replace(['!','+','-'], "", $item);
            }
            if (strtolower(substr($item, 0, 6))=='limit') { // for export we don't have a limit, since limit is the last statement return query as is at this moment
                break;
            }
            $newQuery .= " ".$item;
        }

        return($newQuery);
    }

    private function exportQueryFilterSimple($query) // filter + - en ! column names and concats from sql statement for export - deze speciale tekens zijn indicatoren voor de view
    {
        # return($query);
        $components = preg_split("/[\s]/", $query);
        $components = (array_filter($components, function($value) { return !is_null($value) && $value !== ''; }));

        $newQuery = "";
        
        foreach($components as $item) { // itterate through the whole query *** !be aware, no spaces in concat! ***
            if ( str_contains(substr($item,0,4), "|") ) { // if the first 4 chars of this item contains | then we probably have a space in the concat string of the !item column
                dd('Part of Query ('.$item.') has |, possible space in concat() of the !row in select?');
            }

            // if first char = +,-, or ! and 2nd char is alpha then remove first char (note that in time conversions the $item can be +01:00 this item is not allwed to be filtered
            if ( ctype_alpha(substr($item,2,1)) && (substr($item, 1, 1)=='!' || substr($item, 1, 1)=='+' || substr($item, 1, 1)=='-' || substr($item, 1, 1)=='#') ) {
                $item = str_replace(['!','+','-','#'], "", $item);
            }
            if (strtolower(substr($item, 0, 6))=='limit') { // for export we don't have a limit, since limit is the last statement return query as is at this moment
                break;
            }
            $newQuery .= " ".$item;
            
        }
        return(strip_tags($newQuery));
    }

    public function exportButton($klas='false') {
        if ($klas <> '' ) {
            return( ['link' => Yii::$app->controller->action->id , 'param' => 'export=1&klas='.$klas, 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,]);
        } else {
            return( ['link' => Yii::$app->controller->action->id , 'param' => 'export=1', 'class' => 'btn btn-primary', 'title' => 'Export to CSV' ,]);
        }
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
                // echo preg_replace('/[\s+,;]/', ' ', $value) . $seperator;
                echo  "\"". $value ."\"". $seperator;
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

