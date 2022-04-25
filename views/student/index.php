<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\StudentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Studenten';
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
                'filter' => [ '1' => 'Ja', '0' => 'Nee'],
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => '...'
                ],
                'contentOptions' => ['style' => 'width:5px;'],
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
            [
                'label' => 'Student',
                'attribute'=>'name',
                'contentOptions' => ['style' => 'width:240px; white-space: normal;', 'title'=>'Secret public access link'],
                'format' => 'raw',
                'value' => function ($data) {
                    return  Html::a($data->name,['/public', 'code'=>$data['code']]);
                    return "<a href='https://www.student.ovh/canvas/public?code=".$data['code']."'>$data->name</a>";
                }
            ],
            'login_id',
            'student_nr',
            [
                'attribute' => 'klas',
                'label' => 'Klas',
                'contentOptions' => ['style' => 'width:50px; white-space: normal;', 'title'=>'Klas'],
            ],
            [
                'label'=>'Comment',
                'attribute'=>'comment',
                'contentOptions' => ['style' => 'width:50px; white-space: normal;', 'title'=>'Comment'],
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
