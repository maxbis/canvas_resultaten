<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\nakijken $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="nakijken-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'course_id')->textInput() ?>

    <?= $form->field($model, 'assignment_id')->textInput() ?>

    <?= $form->field($model, 'module_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'assignment_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'file_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'words_in_order')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'points_possible')->textInput() ?>

    <?= $form->field($model, 'instructie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
