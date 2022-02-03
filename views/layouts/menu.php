<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Html;
use yii\helpers\Url;

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
                ['label' => 'Student laatste actief op...',      'url' => ['/report/actief']],
                ['label' => '12 wekenoverzicht',                 'url' => ['/report/aantal-activiteiten']],
                ['label' => 'Studenten werken aan...',           'url' => ['/report/working-on']],
                ['label' => 'Ranking studenten',                 'url' => ['/report/ranking']],
                ['label' => 'Module is x keer voldaan',          'url' => ['/report/modules-finished']],
                ['label' => 'Student keek in monitor',           'url' => ['/report/last-report-by-student']],
                ['label' => '----------------',],
                ['label' => 'Laatste beoordeelding per module',  'url'  => ['/report/beoordeeld']],
                ['label' => 'Beoordelingen per module over tijd', 'url' => ['/report/aantal-beoordelingen']],
                ['label' => 'Aantal beoordelingen per docent',    'url' => ['/report/nakijken']],
            ],
        ],

        [
            'label' => 'Klas',
            'visible' => (Yii::$app->controller->id == 'report' && array_key_exists('klas', Yii::$app->view->context->actionParams)),
            'items' => [
                ['label' => '1A', 'url' => [Yii::$app->controller->action->id . '?klas=1A']],
                ['label' => '1B', 'url' => [Yii::$app->controller->action->id . '?klas=1B']],
                ['label' => '1C', 'url' => [Yii::$app->controller->action->id . '?klas=1C']],
                ['label' => '1D', 'url' => [Yii::$app->controller->action->id . '?klas=1D']],
                ['label' => 'Allen', 'url' => [Yii::$app->controller->action->id]],
            ],
        ],

        [
            'label' => 'Beoordelen',
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Eerste beoordelingen',        'url' => ['/grade/menu41']],
                ['label' => 'Herbeoordelingen',            'url' => ['/grade/menu42']],
                ['label' => 'Alle beoordelingen',          'url' => ['/grade/menu43']],
                ['label' => '----------------',],
                ['label' => 'Eerste beoordelingen op datum', 'url' => ['/grade/not-graded-per-date']],
                ['label' => 'Herbeoordelingen beoordelingen op datum', 'url' => ['/grade/not-graded-per-date?regrading=true']],
                ['label' => 'All beoordelingen per student', 'url' => ['/grade/not-graded-per-student']],
            ],

        ],

        [
            'label' => 'Beheer',
            'visible' => (isset(Yii::$app->user->identity->username) && Yii::$app->user->identity->username == 'beheer'),
            'items' => [
                ['label' => 'Studenten', 'url' => ['/student']],
                ['label' => 'Studentencodes (export)', 'url' => ['/query/studenten-lijst']],
                ['label' => 'Cursus (Blok)', 'url' => ['/course']],
                ['label' => 'Modules', 'url' => ['/module-def']],
            ],
        ],
    ],
]);

if (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin') {
    //echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<div class=\"col-1\"></div>";
    echo "<div style=\"color:#d0d0d0;\">Search: </div>";

    echo "<div class=\"col-1\"><form class=\"col-1\" method=\"post\" action=\"" . Url::toRoute(['/resultaat/start']) . "\"><input placeholder=\"Studentnaam\" size=\"8\" type=\"text\" minlength=\"2\" name=\"search\">";
    echo "<input type=\"hidden\" name=\"_csrf\" value=\"" . Yii::$app->request->getCsrfToken() . "\" />";
    echo "</input></form></div>";
}


echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto'],
    'items' => [

        // [
        //     'label' => 'About', 'url' => ['/site/about']
        // ],

        Yii::$app->user->isGuest ? ([
            'label' => 'Login',
            'visible' => (isset(Yii::$app->controller->id) && Yii::$app->controller->id != 'public'),
            'url' => ['/site/login'],
        ]) : ('<li>'
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
