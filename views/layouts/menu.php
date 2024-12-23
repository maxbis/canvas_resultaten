<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Html;
use yii\helpers\Url;

$route=Yii::$app->controller->action->controller->module->requestedRoute;

if ( $route == 'public/index' ) {
    $title="C<span style=\"color: #03a4ed;\">anvas</span> MON<span style=\"color: #03a4ed;\">itor</span>";
} else {
    $title=$subDomain;
}

#determine klassen menu (config in config/params.php)
$klassen = Yii::$app->params['klassen'];
$myKlassenMenu=[];
foreach ($klassen as $klas) {
    $item['label'] =  $klas;
    $item['url'] = [ Yii::$app->controller->action->id.'?klas='.$klas];
    array_push($myKlassenMenu, $item);
}
$item['label'] =  'Allen';
$item['url'] = [ Yii::$app->controller->action->id.'?klas=all'];
array_push($myKlassenMenu, $item);

//dd($myKlassenMenu);

// dd(Yii::$app->controller->action->controller->module->requestedRoute);
// $title = $route;

// NavBar::begin([
//     'brandLabel' => $title,
//     'brandUrl' =>   '/resultaat/rotate',
//     'brandLabel' => '<img src="/favicon/cmon.ico" class="img-responsive"/>&nbsp&nbsp&nbsp'.$title,
//     'options' => [
//         'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
//         'title' => 'menu item'
//     ],
// ]);

NavBar::begin([
    'brandLabel' => '<img src="/favicon/cmon.ico" class="img-responsive"/>&nbsp;&nbsp;&nbsp;' . $title,
    'brandUrl' => Yii::$app->user->isGuest ? Yii::$app->request->referrer : '/resultaat/rotate',
    'options' => [
        'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
        'title' => 'menu item'
    ],
]);

echo Nav::widget([
    'options' => ['class' => 'navbar-nav'],
    'items' => [

        // [
        //     'label' => 'Zoek', 'url' => ['/resultaat/start'],
        //     'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
        // ],

        // [
        //     'label' => 'Resultaten', 'url' => ['/resultaat/index'],
        //     'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
        // ],

        [
            'label' => 'Rapporten',
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Student laatste actief op...',      'url' => ['/report/actief']],
                ['label' => 'Week overzicht',                    'url' => ['/report/aantal-activiteiten-week']],
                ['label' => '12 wekenoverzicht',                 'url' => ['/report/aantal-activiteiten']],
                ['label' => 'Voortgang',                         'url' => ['/report/voortgang']],
                //['label' => 'Studenten werken aan...',           'url' => ['/report/working-on']],
                ['label' => 'Ranking and Predictions',           'url' => ['/report/ranking']],
                ['label' => '----------------',],
                ['label' => 'Module-overzicht',                  'url' => ['/report/aantal-opdrachten']],
                ['label' => 'Voortgang per Module',              'url' => ['/report/modules-finished']],
                ['label' => 'Student keek in monitor',           'url' => ['/report/last-report-by-student']],
                ['label' => 'Aantal pogingen',                      'url' => ['/report/pogingen']],
                // ['label' => 'Cluster Submissions',               'url' => ['/report/cluster-submissions']],
                ['label' => 'Alle resultaten (in Grid)',         'url' => ['/resultaat/index']],
                ['label' => 'Kerntaken',                         'url' => ['/report/kerntaken']],
                ['label' => '----------------',],
                // ['label' => 'Laatste beoordeelding per module',  'url'  => ['/report/beoordeeld']],
                // ['label' => 'Beoordelingen per module over tijd', 'url' => ['/report/aantal-beoordelingen']],
                ['label' => 'Aantal beoordelingen per docent',    'url' => ['/report/nakijken-week']],
            ],
        ],

        [
            'label' => 'Presentie',
            // && $this->context->route == 'resultaat/start' 
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Overzicht vandaag',        'url' => ['/present/today-overzicht']],
                ['label' => 'Aanwezig vandaag',         'url' => ['/present/today-check-in']],
                ['label' => 'Absent vandaag',           'url' => ['/present/today-no-check-in']],
                ['label' => '----------------',],
                ['label' => 'Weekoverzicht',            'url' => ['/present/week-all-check-in']],
            ],

        ],

        [
            'label' => 'Klas',
            'visible' => (Yii::$app->controller->id == 'report' && array_key_exists('klas', Yii::$app->view->context->actionParams)),
            'items' => $myKlassenMenu,
        ],

        [
            'label' => 'Beoordelen',
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Alle beoordelingen',            'url' => ['/grade/not-graded']],
                ['label' => 'Update modules',                'url' => ['/grade/all-modules']],
                ['label' => '----------------',],
                ['label' => 'Alle beoordelingen op datum',   'url' => ['/grade/not-graded-per-date']],
                ['label' => 'All beoordelingen per student', 'url' => ['/grade/not-graded-per-student']],
                ['label' => '----------------',],
                ['label' => 'Geblokkeerde beoordelingen',    'url' => ['/grade/blocked']],
            ],

        ],

        [
            'label' => 'Beheer',
            'visible' => (isset(Yii::$app->user->identity->username) && Yii::$app->user->identity->username == 'beheer'),
            'items' => [
                ['label' => 'Studenten',                 'url' => ['/student']],
                ['label' => 'Studenten (export)',        'url' => ['/report/studenten-lijst']],
                ['label' => 'Dev Voortgang (adviezen)',  'url' => ['/report/advies']],
                ['label' => '----------------',],
                ['label' => 'Cursus',  'url' => ['/course']],
                ['label' => 'Modules',        'url' => ['/report/modules']],
            ],
        ],
    ],
]);

if (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin') {
    //echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<div class=\"col-1\"></div>";
    echo "<div style=\"color:#d0d0d0;\">Search: </div>";

    echo "<div class=\"col-1\"><form class=\"col-1\" method=\"post\" action=\"" . Url::toRoute(['/resultaat/start']) . "\"><input placeholder=\"naam\" size=\"8\" type=\"text\" minlength=\"2\" name=\"search\">";
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