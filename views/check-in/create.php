<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CheckIn */

$this->title = 'Create Check In';
$this->params['breadcrumbs'][] = ['label' => 'Check Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="check-in-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
