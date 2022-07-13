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
$item['url'] = [ Yii::$app->controller->action->id];
array_push($myKlassenMenu, $item);

//dd($myKlassenMenu);

// dd(Yii::$app->controller->action->controller->module->requestedRoute);
// $title = $route;

NavBar::begin([
    'brandLabel' => $title,
    'brandUrl' => Yii::$app->homeUrl,
    'brandLabel' => '<img src="/favicon/cmon.ico" class="img-responsive"/>&nbsp&nbsp&nbsp'.$title,
    'options' => [
        'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
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
                ['label' => '12 wekenoverzicht',                 'url' => ['/report/aantal-activiteiten']],
                ['label' => 'Voortgang',                         'url' => ['/report/voortgang']],
                //['label' => 'Studenten werken aan...',           'url' => ['/report/working-on']],
                ['label' => 'Ranking Dev',                       'url' => ['/report/ranking']],
                ['label' => 'Voortgang per Module',              'url' => ['/report/modules-finished']],
                ['label' => 'Student keek in monitor',           'url' => ['/report/last-report-by-student']],
                // ['label' => 'Herkansingen',                      'url' => ['/report/herkansen']],
                // ['label' => 'Cluster Submissions',               'url' => ['/report/cluster-submissions']],
                ['label' => 'Alle resultaten (in Grid)',         'url' => ['/resultaat/index']],
                ['label' => '----------------',],
                // ['label' => 'Laatste beoordeelding per module',  'url'  => ['/report/beoordeeld']],
                // ['label' => 'Beoordelingen per module over tijd', 'url' => ['/report/aantal-beoordelingen']],
                ['label' => 'Aantal beoordelingen per docent',    'url' => ['/report/nakijken-week']],
            ],
        ],

        [
            'label' => 'Presentie',
            'visible' => (isset(Yii::$app->user->identity->role) && Yii::$app->user->identity->role == 'admin'),
            'items' => [
                ['label' => 'Vandaag (laaste)',      'url' => ['/report/today-check-in']],
                ['label' => 'Vandaag (min/max) ',    'url' => ['/report/today-min-max-check-in']],
                ['label' => 'Vandaag niet aanwezig', 'url' => ['/report/today-no-check-in']],
                ['label' => 'Deze week alle',        'url' => ['/report/week-all-check-in']],
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
                ['label' => 'Studenten (export)',        'url' => ['/query/studenten-lijst']],
                ['label' => 'Dev Voortgang (BSA-edit)',  'url' => ['/report/voortgang-dev']],
                ['label' => '----------------',],
                ['label' => 'Cursus (Blok)',  'url' => ['/course']],
                ['label' => 'Modules',        'url' => ['/report/modules']],
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
