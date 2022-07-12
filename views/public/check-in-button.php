<?php

namespace app\controllers;
use yii\helpers\Html;
use Yii;

$code = "999";
$code = $CODE; //$CODE is global :(

$today = date("Ymd"); //e.g. 20200728, this is an extra security to avoid fake posts
$date_hash=md5($today);

//dd(isset($_COOKIE['check-in']));

if (! isset($_COOKIE['check-in']) || 1 ) {
    if (MyHelpers::CheckIP(true)) {
        echo Html::a(' Check-in ',  ['/check-in/check-in'], ['class'=>"btn btn-success", 'style'=>'background-color:#a7e68e;color:#164a01;float:right;', 'data-method' => 'POST','data-params' =>
                [ 'code' => $code, 'check' => $date_hash, 'action' => 'i' ], ]);
    }
}



?>