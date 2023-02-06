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
           <?= $form->field($model, 'norm_uren')->textInput(['maxlength' => true])->label('Norm Uren') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
           <?= $form->field($model, 'voldaan_rule')->textInput(['maxlength' => true])->label('SQL voldaanregel') ?>
        </div>
    </div>

    <br/>

    <?= $form->field($model, 'generiek')->checkbox() ?>

    <br/>

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

    <br/><hr>
    <table  class="table-sm text-muted small">
        <tr>
            <th colspan=2>SQL voldaan regels</th>
        </tr>
        <tr>
            <td>punten</td>
            <td> totaal aantal behaalde punten voor alle opdrachten.</td>
        </tr>
        <tr>
            <td>punten_eo</td>
            <td> totaal aantal behaalde punten voor alle eindopdrachten (=opdracht met woord <i>eind</i> in de naam van de opdracht).</td>
        </tr>
        <tr>
            <td>ingeleverd</td>
            <td> totaal aantal ingeleverde opdrachten.</td>
        </tr>
        <tr>
            <td>ingeleverd_eo</td>
            <td> totaal aantal ingeleverde eindopdrachten (=opdracht met woord <i>eind</i> in de naam van de opdracht).</td>
        </tr>
        <tr>
            <td>minpunten</td>
            <td> laagste score voor een opdracht uit deze module.</td>
        </tr>
        <tr>
            <td>voorbeeld</td>
            <td><i>punten > 90 and punten_eo > 20 and ingeleverd>=10 and minpunten >=1</i></td>
        </tr>
        <tr>
            <td></td>
            <td>Totaal aantal punten moet meer dan 90 zijn en totaal aantal punten voor eidnopdrachten moet meer dan 20 zijn. Daarbij moeten alle 10 opdrachten zijn ingeleverd en voor elke opdracht minimaal 1 punt worden gescoord.</td>
        </tr>
    </table>

</div>
