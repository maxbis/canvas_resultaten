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
            [   'attribute' => 'name',
                'label' => 'Student',
                'contentOptions' => ['style' => 'width:160px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->name, ['/resultaat', 'ResultaatSearch[student_nummer]'=>$data->student_nr], ['title'=> 'Show',]);
            }
        ],
            'login_id',
            'student_nr',
            'klas',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
