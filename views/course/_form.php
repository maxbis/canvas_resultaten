<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Course */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="course-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'naam')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'korte_naam')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pos')->textInput() ?>

    <?= $form->field($model, 'update_prio')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
