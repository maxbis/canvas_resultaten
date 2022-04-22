<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LoginUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Login Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="login-user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Login User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'password',
            'authKey',
            'role',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
