<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Course */
/* @var $form yii\widgets\ActiveForm */
?>

<style>
    .control-label {
        color: #404040;
        font-size: smaller;
    }
</style>

<p>
    <i>Alleen in te vullen door degene die de Canvas koppeling beheerd</i>
</p>
<div class="course-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'id')->textInput(['readonly' => !$model->isNewRecord])->label('Dit is het cursus ID uit canvas.') ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'naam')->textInput(['maxlength' => true])->label('Cursusnaam (staat in het moduleoverzicht).') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'pos')->textInput()->label('Sort order in cursus overzicht.') ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'korte_naam')->textInput(['maxlength' => true])->label('Bloknaam (B1, B2, B3,..).') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'update_prio')->textInput()->label('Prio voor update (1, meest).') ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>