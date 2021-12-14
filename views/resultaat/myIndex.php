<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ResultaatSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Canvas Resultaten';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="resultaat-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'label' => 'Blok',
                'attribute' => 'course_id',
                'contentOptions' => ['style' => 'width:80px; white-space: normal;'],
                'filter' => ['2101' => 'Blok 1', '2110' => 'Blok 2'],
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->course_id == 2101) return "Blok 1";
                    if ($data->course_id == 2110) return "Blok 2";
                }
            ],
            [
                'attribute' => 'module',
                'contentOptions' => ['style' => 'width:240px; white-space: normal;'],
                'format' => 'raw',
                'filter' => $modules,
                'value' => function ($data) {
                    return str_replace("Opdrachten", "", $data->module);
                }
            ],
            [
                'attribute' => 'student_naam',
                'label' => 'Student'
            ],
            [
                'attribute' => 'voldaan',
                'contentOptions' => ['style' => 'width:100px; white-space: normal;'],
                'options' => ['style' => 'voldaan' == 'V' ? 'color:#b4fac0' : 'color:#ffc7c7'],
                'filter' => ['-' => 'Niet Voldaan', 'V' => 'Voldaan'],
            ],
            [
                'attribute' => 'ingeleverd',
                'label' => 'ingeleverd/eind.',
                'contentOptions' => ['style' => 'width:100px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("<pre>%2d - %2d</pre>", $data->ingeleverd, $data->ingeleverd_eo);
                    return $data->ingeleverd . "/" . $data->ingeleverd_eo;
                }
            ],
            [
                'attribute' => 'punten',
                'label' => 'punten/eind/max',
                'contentOptions' => ['style' => 'width:100px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("<pre>%3d %2d %3d %3d%%</pre>", $data->punten, $data->punten_eo, $data->punten_max,  $data->punten * 100 / $data->punten_max);
                    return $data->punten . "/" . $data->punten_eo . "/" . $data->punten_max;
                }
            ],
            'laatste_activiteit',
            'laatste_beoordeling',
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>