<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\nakijkenSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="nakijken-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'course_id') ?>

    <?= $form->field($model, 'assignment_id') ?>

    <?= $form->field($model, 'module_name') ?>

    <?= $form->field($model, 'assignment_name') ?>

    <?= $form->field($model, 'file_type') ?>

    <?php // echo $form->field($model, 'words_in_order') ?>

    <?php // echo $form->field($model, 'points_possible') ?>

    <?php // echo $form->field($model, 'instructie') ?>

    <?php // echo $form->field($model, 'label') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
