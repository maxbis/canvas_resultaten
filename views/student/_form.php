<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Student */
/* @var $form yii\widgets\ActiveForm */
?>

<style>
td { padding-left:10px;padding-right:10px;padding-top:2px;padding-bottom:2px; }
</style>

<div class="student-form">

    <?php $form = ActiveForm::begin(); ?>

    <table border=0 style="width:1200px;">
        <tr>

            <td>
                <div class="row">
                    <div class="col-sm-3">
                        <?= $form->field($model, 'klas')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-sm-7">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Naam') ?>
                    </div>
                </div>
            </td>

            <td rowspan=5 style="vertical-align: top;">
                <?php if ( $model->student_nr>0) { ?>
                    <div class="card" style="width: 24rem;">
                        <div class="card-body">
                        <h5 class="card-title">Courses open for this student</h5>
                        <h6 class="card-subtitle mb-2 text-muted"></h6>
                        
                        <?php
                            if (isset($openCourses) && count($openCourses) ) {
                                echo "<table>";
                                foreach($openCourses as $course) {
                                    echo "<tr><td class=\"card-text\">";
                                    echo "&bull;".$course['naam']." (id: ".$course['naam']. ") ";
                                    echo "</td><td><small>";
                                    echo Html::a("koppel", ['/canvas-update/add-user', 'courseId'=>$course['id'], 'userId'=>$model['id'] ]);
                                    echo "</small></td></tr>";
                                }
                                echo "</table>";
                            } else {
                                echo "None";
                            }
                        ?>

                        <small style="color:#b0b0b0;font-style: italic;margin-left:20px;">
                            <details>
                                <summary>Prediction (experimenteel)</summary>
                                <?= $prediction ?>
                            </details>
                        </small>

                    </div>
                <?php } ?>

            </td>

        </tr>

        <tr>
        
            <td>
                <div class="row">
                    <div class="col-sm-3">
                        <?php
                            if ( isset($model['id']) && $model['id'] > 10 ) {
                                echo $form->field($model, 'id')->textInput(['maxlength' => true,'readonly'=> true])->label('Canvas ID');
                            } else {
                                echo $form->field($model, 'id')->textInput(['maxlength' => true,'readonly'=> false])->label('Canvas ID');
                            }
                        ?>
                    </div>
                    <div class="col-sm-7">
                        <?= $form->field($model, 'code')->textInput(['maxlength' => true,'readonly'=> false])->label('Code voor student om zijn pagina te bekijken') ?>
                    </div>
                </div>
            </td>

        </tr>

        <tr>

            <td>
                <div class="row">
                    <div class="col-sm-5">
                        <?= $form->field($model, 'student_nr')->textInput()->label('Studentennummer (0 indien docent)') ?>
                    </div>
                    <div class="col-sm-5">
                        <?= $form->field($model, 'login_id')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
            </td>

        </tr>

        <tr>

            <td>

                <div class="row">
                    <div class="col-sm-3">
                        <?= $form->field($model,'grade')->dropDownList( array('0' => 'inactief','1' => 'actief'),array('grade' => array('1' => array('selected' => true)))) ->label('Nakijken') ?> 
                    </div>
                </div>

            </td>

        </tr>

        <tr>

            <td colspan=>
                <div class="row">
                    <div class="col-sm-10">
                        <?= $form->field($model, 'comment')->textInput(['maxlength' => true])->label('Aantekening (alleen zichtbaar voor docenten)') ?>
                        <?= $form->field($model, 'message')->textArea(['maxlength' => true, 'rows'=>2])->label('Boodschap (zichtbaar voor studenten)') ?>
                    </div>
                </div>
            </td>

        </tr>

        <tr>

            <td colspan=2 style="text-align: left;" >
                <div class="form-group">
                    <br>
                    <?= Html::a( 'Cancel', Yii::$app->request->referrer , ['class'=>'btn btn-primary']); ?>
                    &nbsp;&nbsp;&nbsp;
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                </div>
            </td>

        </tr>

    </table>

    <?php ActiveForm::end(); ?>

    <br><br>

</div>
<br><br>
