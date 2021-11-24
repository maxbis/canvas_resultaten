<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Html;

NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
    ],
]);

echo Nav::widget([
    'options' => ['class' => 'navbar-nav'],
    'items' => [
        [
            'label' => 'Resultaat', 'url' => ['/resultaat']
        ],

        [
            'label' => 'Overig',
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Student', 'url' => ['/student']],
                ['label' => '-----------------------------------'],
                ['label' => 'Actieve studenten', 'url' => ['/query/actief']],
                ['label' => 'Studenten werken aan', 'url' => ['/query/working-on']],
                ['label' => 'Modules voldaan', 'url' => ['/query/modules-finished']],
                ['label' => 'Voortgang', 'url' => ['/query/voortgang']],
            ],
        ],
        
        [
            'label' => 'Klas',
            'visible' => (Yii::$app->controller->id=='query'),
            'items' => [
                ['label' => '1A', 'url' => [Yii::$app->controller->action->id.'?klas=1A']],
                ['label' => '1B', 'url' => [Yii::$app->controller->action->id.'?klas=1B']],
                ['label' => '1C', 'url' => [Yii::$app->controller->action->id.'?klas=1C']],
                ['label' => '1D', 'url' => [Yii::$app->controller->action->id.'?klas=1D']],
            ],
        ],
    ],
]);

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto'],
    'items' => [
        Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => ['/site/login'],]
        ) : (
            '<li>'
            . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>'
        )
    ],
]);

NavBar::end();
?>