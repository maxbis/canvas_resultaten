<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\StudentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Students';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Student', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'login_id',
            'student_nr',
            [
                'attribute' => 'klas',
                'label' => 'Klas',
                'contentOptions' => ['style' => 'width:50px; white-space: normal;', 'title'=>'Klas'],
            ],
            'code',
            [
                'label' => 'Link',
                'attribute'=>'code',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;', 'title'=>'Secret public access code'],
                'format' => 'raw',
                'value' => function ($data) {
                    return "<a href='https://www.student.ovh/canvas/public?code=".$data['code']."'>link</a>";
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
