<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\StudentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Studenten / Docenten';
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
                'contentOptions' => ['style' => 'width:80px; white-space: normal;'],
            ],
            [
                'label' => 'Student',
                'attribute'=>'name',
                'contentOptions' => ['style' => 'width:200px; white-space: normal;', 'title'=>'Secret public access link'],
                'format' => 'raw',
                'value' => function ($data) {
                    if ( $data->code ) {
                        return  Html::a($data->name,['/public', 'code'=>$data['code']]);
                    } else {
                        return $data->name;
                    }
                    return "<a href='https://www.student.ovh/canvas/public?code=".$data['code']."'>$data->name</a>";
                }
            ],
            [
                'attribute' => 'student_nr',
                'label' => 'Student Nr',
                'contentOptions' => ['style' => 'width:100px; white-space: normal;', 'title'=>'Klas'],
            ],
            [
                'attribute' => 'klas',
                'label' => 'Klas',
                'contentOptions' => ['style' => 'width:100px; white-space: normal;', 'title'=>'Klas'],
            ],
            [
                'label'=>'Comment',
                'attribute'=>'comment',
                'contentOptions' => ['style' => 'width:180px; white-space: normal;', 'title'=>'Comment'],
                'format' => 'raw',
                'value' => function ($data) {
                    return substr($data->comment,0,10);
                }
            ],
            [
                'label'=>'Message',
                'attribute'=>'message',
                'contentOptions' => ['style' => 'white-space: normal;', 'title'=>'Comment'],
                'format' => 'raw',
                'value' => function ($data) {
                    return substr($data->message,0,30);
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width:80px; white-space: normal;'],

                'template' => '{update} - {delete}',
            ],
        ],
    ]); ?>


</div>
