<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ModuleDef */

$this->title = 'Update Module Definitie ' . $model->naam;
$this->params['breadcrumbs'][] = ['label' => 'Module Defs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="module-def-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
