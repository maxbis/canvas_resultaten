<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ModuleDef */
/* @var $form yii\widgets\ActiveForm */
?>
<i>Alleen in te vullen door degene die de Canvas koppeling beheerd</i>
<br><br><br>

<div class="module-def-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'id')->textInput()->label('Module ID uit Canvas (niet aanpassen)') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
        <?= $form->field($model, 'naam')->textInput(['maxlength' => true])->label('Module naam in Canvas Monitor') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
        <?= $form->field($model, 'pos')->textInput()->label('Module positie in overzicht in Canvas Monitor') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
           <?= $form->field($model, 'voldaan_rule')->textInput(['maxlength' => true])->label('SQL voldaanregel (zie voorbeelden andere modules)') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'id')->textInput()->label('Module ID uit Canvas (niet aanpassen)') ?>
        </div>
    </div>

    <?= $form->field($model, 'generiek')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        &nbsp;&nbsp;&nbsp;
        <?= Html::a( 'Cancel', Yii::$app->request->referrer , ['class'=>'btn btn-primary']); ?>
        &nbsp;&nbsp;&nbsp;
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to de-couple this module from Canvas?',
                'method' => 'post',
            ],
    ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
