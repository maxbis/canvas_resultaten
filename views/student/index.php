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
            [
                'attribute'=>'grade',
                'contentOptions' => ['style' => 'width:10px;'],
                'format' => 'raw',
                'value' => function ($data) {
                  $status = $data->grade ? '&#10004' : '&#10060';
                  return Html::a($status, ['/student/toggle-actief?id='.$data->id],['title'=> 'Toggle Status',]);
                }
            ],
            [
                'label' => 'Canvas Id',
                'attribute'=>'id',
                'contentOptions' => ['style' => 'width:120px; white-space: normal;'],
            ],
            'name',
            'login_id',
            'student_nr',
            [
                'attribute' => 'klas',
                'label' => 'Klas',
                'contentOptions' => ['style' => 'width:50px; white-space: normal;', 'title'=>'Klas'],
            ],
            [
                'label'=>'Activiteiten',
                'contentOptions' => ['style' => 'width:50px; white-space: normal;', 'title'=>'Klas'],
                'format' => 'raw',
                'value' => function ($data) {
                    return  Html::a('link',['/query/activity', 'studentnr'=>$data['student_nr']]);
                }
            ],

            [
                'label' => 'Status',
                'contentOptions' => ['style' => 'width:40px; white-space: normal;', 'title'=>'Secret public access code'],
                'format' => 'raw',
                'value' => function ($data) {
                    return  Html::a('link',['/public', 'code'=>$data['code']]);
                    return "<a href='https://www.student.ovh/canvas/public?code=".$data['code']."'>link</a>";
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width:120px; white-space: normal;'],
                'visible' =>  (Yii::$app->user->identity->username=='beheer'), 
                'template' => '{delete} - {update}',
            ],
        ],
    ]); ?>


</div>
