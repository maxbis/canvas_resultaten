<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ResultaatSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="resultaat-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'course_id') ?>

    <?= $form->field($model, 'module') ?>

    <?= $form->field($model, 'student_nummer') ?>

    <?= $form->field($model, 'student_naam') ?>

    <?php // echo $form->field($model, 'ingeleverd') ?>

    <?php // echo $form->field($model, 'ingeleverd_eo') ?>

    <?php // echo $form->field($model, 'punten') ?>

    <?php // echo $form->field($model, 'punten_max') ?>

    <?php // echo $form->field($model, 'punten_eo') ?>

    <?php // echo $form->field($model, 'voldaan') ?>

    <?php // echo $form->field($model, 'laatste_activiteit') ?>

    <?php // echo $form->field($model, 'laatste_beoordeling') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
