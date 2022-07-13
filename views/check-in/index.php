<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CheckInSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Check Ins';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="check-in-index">

    <h1>Alle check-ins</h1>
<!-- 
    <p>
        <?= Html::a('Create Check In', ['create'], ['class' => 'btn btn-success']) ?>
    </p> 
-->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            [
                'attribute' => 'name',
                'label' => 'Student',
                'contentOptions' => ['style' => 'width:220px;'],
                'format' => 'raw',
                'value' => 'student.name',
            ],
            [
                'attribute' => 'klas',
                'label' => 'Klas',
                'contentOptions' => ['style' => 'width:40px;'],
                'format' => 'raw',
                'value' => 'student.klas',
            ],
            [
                'attribute' => 'timestamp',
                'label' => 'Check-In',
                'contentOptions' => ['style' => 'width:220px;'],
            ],
            [
                'attribute' => 'browser_hash',
                'label' => 'BrowserID',
                'contentOptions' => ['style' => 'width:120px;'],

            ],


            ['class' => 'yii\grid\ActionColumn', 'contentOptions' => ['style' => 'width:120px;'],],
        ],
    ]); ?>


</div>

