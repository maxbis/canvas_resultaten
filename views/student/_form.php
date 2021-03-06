<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Student */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="student-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'login_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'student_nr')->textInput() ?>

    <?= $form->field($model, 'klas')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true,'readonly'=> true])->label('Code voor student om zijn pagina te bekijken') ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true])->label('Comment (alleen zichtbaar voor docenten)') ?>

    <?= $form->field($model, 'message')->textInput(['maxlength' => true])->label('Boodschap (zichtbaar voor studenten)') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
