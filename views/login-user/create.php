<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\LoginUser */

$this->title = 'Create Login User';
$this->params['breadcrumbs'][] = ['label' => 'Login Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="login-user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
