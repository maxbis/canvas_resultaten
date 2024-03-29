<?php

use app\models\nakijken;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\nakijkenSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Nakijken';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="nakijken-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Nakijken', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'course_id',
                'label' => 'cid',
                'contentOptions' => ['style' => 'width:40px;'],
            ],
            [
                'attribute'=>'assignment_id',
                'label' => 'aid',
                'contentOptions' => ['style' => 'width:40px;'],
            ],
            [
                'attribute'=>'module_id',
                'label' => 'mid',
                'contentOptions' => ['style' => 'width:40px;'],
            ],
            [
                'attribute'=>'cohort',
                'label' => 'Cohort',
                'contentOptions' => ['style' => 'width:40px;'],
            ],
            'module_name',
            'assignment_name',
            [
                'attribute'=>'file_type',
                'contentOptions' => ['style' => 'width:40px;'],
            ],
            //'words_in_order',
            //'points_possible',
            //'instructie',
            //'label',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, nakijken $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'assignment_id' => $model->assignment_id]);
                 }
            ],
        ],
    ]); ?>


</div>
