<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Course */
/* @var $form yii\widgets\ActiveForm */
?>
<h3><i>Alleen in te vullen door degene die de Canvas koppeling beheerd</i></h3>
<div class="course-form">

    <?php $form = ActiveForm::begin(); ?>

    Dit is het cursus ID uit canvas
    <?= $form->field($model, 'id')->textInput() ?>

    Deze naam wordt (nog) nergens gebruikt
    <?= $form->field($model, 'naam')->textInput(['maxlength' => true]) ?>

    Deze naam staat op het studenten overzicht
    <?= $form->field($model, 'korte_naam')->textInput(['maxlength' => true]) ?>

    Sort order in cursus overzicht
    <?= $form->field($model, 'pos')->textInput() ?>

    Prio voor update: 1 most frequent
    <?= $form->field($model, 'update_prio')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
