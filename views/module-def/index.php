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

            'id',
            'naam',
            'pos',
            'voldaan_rule',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
