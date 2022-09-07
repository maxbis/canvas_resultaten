<?php

define('YII_ENV', 'dev');
define('YII_DEBUG', true);

return [
    'id' => 'async-app',
    'basePath' => __DIR__,
    'runtimePath' => __DIR__ . '/runtime',
    'aliases' => [
        '@frontend' => dirname(__DIR__, 2) . '/frontend',
        '@backend' => dirname(__DIR__, 2) . '/backend'
    ]
];