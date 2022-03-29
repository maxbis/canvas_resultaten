<?php

$subDomain = explode('.', $_SERVER['SERVER_NAME'])[0];

if ( $subDomain == 'canvas1') {
    $DB='canvas1';
} elseif ( $subDomain == 'canvas2') {
    $DB='canvas2';
} else {
    $DB='canvas';
}


return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname='.$DB,
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache'
];
