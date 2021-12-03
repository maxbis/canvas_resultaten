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
            'label' => 'Zoek', 'url' => ['/resultaat/start'],
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
        ],

        [
            'label' => 'Resultaten', 'url' => ['/resultaat/index'],
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
        ],

        [
            'label' => 'Rapporten',
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Student laatste actief op...',      'url' => ['/query/actief']],
                ['label' => 'Actieve studenten over tijd',       'url' => ['/query/aantal-activiteiten']],
                ['label' => 'Studenten werken aan...',           'url' => ['/query/working-on']],
                ['label' => 'Ranking studenten',                 'url' => ['/query/voortgang']],
                ['label' => 'Module is x keer voldaan',          'url' => ['/query/modules-finished']],
                ['label' => '----------------',  ],
                ['label' => 'Laatste beoordeelding per module',  'url' => ['/query/beoordeeld']],
                ['label' => 'Beoordelingen per module over tijd','url' => ['/query/aantal-beoordelingen']],
                ['label' => 'Aantal beoordelingen per docent','url'    => ['/query/nakijken']],
               

                // ['label' => 'Beoordeeld', 'url' => ['/query/beoordeeld']],
                // ['label' => 'Aantal Beoordelingen', 'url' => ['/query/aantal-beoordelingen']],
            ],
        ],

        [
            'label' => 'Klas',
            'visible' => ( Yii::$app->controller->id == 'query' && array_key_exists( 'klas', Yii::$app->view->context->actionParams ) ),
            'items' => [
                ['label' => '1A', 'url' => [Yii::$app->controller->action->id . '?klas=1A']],
                ['label' => '1B', 'url' => [Yii::$app->controller->action->id . '?klas=1B']],
                ['label' => '1C', 'url' => [Yii::$app->controller->action->id . '?klas=1C']],
                ['label' => '1D', 'url' => [Yii::$app->controller->action->id . '?klas=1D']],
                ['label' => 'Allen', 'url' => [Yii::$app->controller->action->id]],
            ],
        ],

        [
            'label' => 'Beheer',
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Studenten', 'url' => ['/student']],
                ['label' => 'Cursus (Blok)', 'url' => ['/course']],
                ['label' => 'Modules', 'url' => ['/module-def']],
                ['label' => 'Studentenlijst', 'url' => ['/query/studenten-lijst']],
            ],
        ],
    ],
]);

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto'],
    'items' => [

        // [
        //     'label' => 'About', 'url' => ['/site/about']
        // ],
        
            Yii::$app->user->isGuest ? (['label' => 'Login',
                'visible' => (isset(Yii::$app->controller->id ) && Yii::$app->controller->id != 'public'),
                'url' => ['/site/login'],]) : ('<li>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>')
        
    ],
]);

NavBar::end();
