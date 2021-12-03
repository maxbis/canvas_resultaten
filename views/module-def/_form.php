<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ModuleDef */
/* @var $form yii\widgets\ActiveForm */
?>
<h3><i>Alleen in te vullen door degene die de Canvas koppeling beheerd</i></h3>
<div class="module-def-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'naam')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pos')->textInput() ?>

    <?= $form->field($model, 'voldaan_rule')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'generiek')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
