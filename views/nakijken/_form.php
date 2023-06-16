<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\nakijken $model */
/** @var yii\widgets\ActiveForm $form */
?>

<br>

<div class="nakijken-form">

    <div class="container">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-sm-2">
                <?= $form->field($model, 'course_id')->textInput() ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, 'assignment_id')->textInput() ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, 'module_id')->textInput() ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($model, 'module_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'assignment_name')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-2">
                <?= $form->field($model, 'file_type')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-8">
                <?= $form->field($model, 'words_in_order')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-8">
                <?= $form->field($model, 'instructie')->textArea(['maxlength' => true, 'rows'=>2]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-8 text-right">
                &nbsp;&nbsp;&nbsp;
                <?= Html::a( 'Cancel', Yii::$app->request->referrer , ['class'=>'btn btn-primary']); ?>
                &nbsp;&nbsp;&nbsp;
                <?= Html::submitButton('&nbsp;&nbsp;Save&nbsp;&nbsp;', ['class' => 'btn btn-success']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <br><br>

        <div class="row">
            <div class="col-sm-8 text-right btn-sm">
                <?= Html::a('Delete', ['delete', 'assignment_id' => $model->assignment_id], [
                'class' => 'btn btn-light',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
                ]) ?>
            </div>
        </div>

    </div>

   

</div>

