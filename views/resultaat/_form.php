<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Resultaat */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="resultaat-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'course_id')->textInput() ?>

    <?= $form->field($model, 'module')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'student_nummer')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'student_naam')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ingeleverd')->textInput() ?>

    <?= $form->field($model, 'ingeleverd_eo')->textInput() ?>

    <?= $form->field($model, 'punten')->textInput() ?>

    <?= $form->field($model, 'punten_max')->textInput() ?>

    <?= $form->field($model, 'punten_eo')->textInput() ?>

    <?= $form->field($model, 'voldaan')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
