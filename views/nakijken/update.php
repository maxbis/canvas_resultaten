<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\nakijken $model */

$this->title = $model->cohort.' - '.$model->module_name.' - '.$model->assignment_name;
$this->params['breadcrumbs'][] = ['label' => 'Nakijken', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->assignment_id, 'url' => ['view', 'assignment_id' => $model->assignment_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="nakijken-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
