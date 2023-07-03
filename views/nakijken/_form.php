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
            <div class="col-sm-2">
                <?= $form->field($model, 'cohort')->textInput() ?>
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
            <div class="col-sm-3">
                <?= $form->field($model, 'file_type')->dropDownList(['png' => 'no auto correct (png/jpg/pdf)','php' => 'php','sql' => 'sql','js'  => 'js', 'txt' => 'txt']);?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'file_name',)->textInput(['title' => '(part of) the file name to match for auto-correct)']) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, 'attachments',)->textInput(['title' => 'Number of requested attachments (empty=no check)']) ?>
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

   
    <div class="row" style="padding:10px;background-color:#ffffff;font-size:14px;color:#808080;margin-top:80px;margin-bottom:80px;">
        <div class="col-sm-8">
        <hr>
            <p></p>
            <h4>Auto Grading</h4>
            <p>For each submission, only one attachment will be auto-graded. This attachment must be a text file. Currently, we support files with the extensions php, sql, js, txt.</p>
            <p>Any png/jpg/pdf attachments will be displayed on the grading screen.</p>
            <p>Auto-grading is based on occurrences of words. In its simplest form, words are matched in order. Matching is based on case-insensitive partial matches, for example, 'Word' will match 'word01'.</p>
            <p>When a match is found, any subsequent word will be scanned for in the text from the position of the last match.</p>
            <h4>Negative Search</h4>
            <p>When a word starts with a <b>'!'</b>, this word <b>must not</b> occur in the text (negative search). If the word must not occur in the entire text, start with the negative match (place it at the beginning of your word list).</p>
            <h4>Any-order Search</h4>
            <p>When a group of words is placed between <b>( and )</b>, the words may occur in <b>any order</b>. So 'word1 word2' will match the text 'word2 word1'.</p>
            <h4>Or Search</h4>
            <p>When a group of words is placed between <b>[ and ], one or more</b> of these words must match. When a word matches, the algorithm will not try to match the next word in this group, hence the search position is advanced to the first match.</p>
            <hr>
        </div>
    </div>

</div>

