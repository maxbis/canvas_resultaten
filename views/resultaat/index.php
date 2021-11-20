<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ResultaatSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Resultaten';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="resultaat-index">

    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="col-md-auto">
                <?= Html::a('Export', ['resultaat/export'], ['class'=>'btn btn-primary', 'title'=> 'Export to CSV',]) ?>
            </div>
        </div>
    </div>

    <br>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'label' => 'Blok',
                'attribute'=>'course_id',
                'contentOptions' => ['style' => 'width:50px; white-space: normal;'],
                'filter' => ['2101'=>'Blok 1','2110'=>'Blok 2'],
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->course_id==2101) return "B1";
                    if ($data->course_id==2110) return "B2";
                }
            ],
            [   'attribute' => 'module',
                'contentOptions' => ['style' => 'width:120px; white-space: normal;'],
                'format' => 'raw',
                'filter' => $modules,
                'value' => function ($data) {
                    return str_replace( "Opdrachten", "", $data->module);
                }
            ],
            [   'attribute' => 'klas',
                'label' => 'Klas',
                'contentOptions' => ['style' => 'width:50px; white-space: normal;'],
            ],
            [   'attribute' => 'student_naam',
                'label' => 'Student',
                'contentOptions' => ['style' => 'width:160px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->student_naam, ['/resultaat', 'ResultaatSearch[student_nummer]'=>$data->student_nummer], ['title'=> 'Show',]);
                }
            ],
            [
                'attribute'=>'voldaan',
                'label' => 'V',
                'headerOptions' => [ 'style' => 'color:#F0F0F0;' ],
                'contentOptions' => ['style' => 'width:40px; white-space: normal;'],
                'options' => [ 'style' => 'voldaan' == 'V' ? 'color:#b4fac0':'color:#ffc7c7' ],
                'filter' => ['-'=>'Niet Voldaan','V'=>'Voldaan'],
            ],
            [   'attribute' => 'ingeleverd',
                'label' => 'ingel./eind.',
                'contentOptions' => ['style' => 'width:100px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("<pre>%2d %2d</pre>", $data->ingeleverd, $data->ingeleverd_eo);
                    return $data->ingeleverd."/".$data->ingeleverd_eo;
                }
            ],
            [   'attribute' => 'punten',
                'label' => 'punten/eind/max',
                'contentOptions' => ['style' => 'width:100px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("<pre>%3d %2d %3d %3d%%</pre>", $data->punten, $data->punten_eo, $data->punten_max,  $data->punten*100/$data->punten_max);
                }
            ],
            [   'attribute' => 'laatste_activiteit',
                'label' => 'laatste A.',
                'contentOptions' => ['style' => 'width:60px; white-space: normal; '],
                'format' => 'raw',
                'value' => function ($data) {
                    $days = intval((time()-strtotime($data->laatste_activiteit))/86400) ;
                    if ( $days<999) {
                        return $days;
                    } else {
                        return "-";
                    }
                    // ." ". Yii::$app->formatter->asDate($data->laatste_activiteit, 'php:Y-m-d');
                }
            ],
            [   'attribute' => 'laatste_beoordeling',
                'label' => 'beoordeeld',
                'contentOptions' => ['style' => 'width:60px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    $days = intval((time()-strtotime($data->laatste_beoordeling))/86400);
                    if ( $days<999) {
                        return $days;
                    } else {
                        return "-";
                    }
                }
            ],


            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>