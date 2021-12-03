<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ModuleDefSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Module Defs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-def-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Module Def', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [   'attribute' => 'id',
                'label' => 'ID',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;'],
            ],
            [   'attribute' => 'naam',
                'label' => 'Naam',
            ],
            [   'attribute' => 'pos',
                'label' => 'Pos',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;'],
            ],
            [   'attribute' => 'voldaan_rule',
                'label' => 'Voldaan Rule',
                'contentOptions' => ['style' => 'width:260px; white-space: normal;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width:120px; white-space: normal;'],
                'template' => '{view} - {delete} - {update}',
            ],
        ],
    ]); ?>


</div>

