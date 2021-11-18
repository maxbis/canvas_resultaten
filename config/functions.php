<?php

/**
 * Debug function
 * d($var);
 */
function d($var,$caller=null)
{
    if(!isset($caller)){
        $caller = debug_backtrace(1)[0];
    }
    echo '<code>Line: '.$caller['line'].'<br>';
    echo 'File: '.$caller['file'].'</code>';
    echo '<pre>';
    yii\helpers\VarDumper::dump($var, 10, true);
    echo '</pre>';
}

/**
 * Debug function with die() after
 * dd($var);
 */
function dd($var)
{
    $caller = debug_backtrace(1)[0];
    d($var,$caller);
    die();
}


function HTMLInclude($file)
{
    return \Yii::$app->view->renderFile('@app/views/layouts/'.$file.'.php');
}

function writeLog($msg="")
{
    $log  = date("j-m-Y,H:i")." "
            .$_SERVER['REMOTE_ADDR']." "
            .Yii::$app->controller->id."Controller "
            ."action".Yii::$app->controller->action->id." "
            .$msg;
    $result = file_put_contents('../log/audit_'.date("dmY").'.log', $log.PHP_EOL, FILE_APPEND);
}
