<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CourseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Courses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="course-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Course', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute'=>'id',
                'contentOptions' => ['style' => 'width:40px;'],
                'format' => 'raw',
                    'value' => function ($data) {
                        return "<a href=\"https://talnet.instructure.com/courses/".$data->id."/modules\" target=\"_blank\">". $data->id."➞</a>";
                     }
              ],
            'naam',
            'korte_naam',
            'pos',
            'update_prio',
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width:120px; white-space: normal;'],
                'visible' =>  (Yii::$app->user->identity->username=='beheer'), 
                'template' => '{view} - {delete} - {update}',
            ],
        ],
    ]); ?>


</div>
