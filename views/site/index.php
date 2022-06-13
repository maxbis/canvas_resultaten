<?php

$signal_file="/tmp/down";
$down_file=__DIR__ ."/down.php";

if ( file_exists($signal_file) ) {
    readfile($down_file);
    exit;
}

Yii::$app->getResponse()->redirect('resultaat/start');

?>
