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

        <td rowspan=3 style="vertical-align: top;">
        
            <div class="card" style="width: 24rem;">
                <div class="card-body">
                <h5 class="card-title">Courses open for this student</h5>
                <h6 class="card-subtitle mb-2 text-muted">(experimenteel)</h6>
                
                <?php
                    if (count($openCourses)) {
                        echo "<table>";
                        foreach($openCourses as $course) {
                            echo "<tr><td class=\"card-text\">";
                            echo "&bull;".$course['naam']." (id: ".$course['naam']. ") ";
                            // echo " adduser -b ".$course['id']." -s ".$model['student_nr'];
                            // echo "<br>";
                            // echo "/canvas-update/add-user?courseId=".$course['id']."&userId=".$model['id'];
                            // echo "<br>";
                            echo "</td><td><small>";
                            echo Html::a("koppel", ['/canvas-update/add-user', 'courseId'=>$course['id'], 'userId'=>$model['id'] ]);
                            echo "</small></td></tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "None";
                    }
                ?>
                </div>
            </div>

        </td>

    </tr></tr>
    
        <td>
            <div class="row">
                <div class="col-sm-3">
                    <?= $form->field($model, 'id')->textInput(['readonly'=> true])->label('Canvas ID') ?>
                </div>
                <div class="col-sm-7">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true,'readonly'=> true])->label('Code voor student om zijn pagina te bekijken') ?>
                </div>
            </div>
        </td>

    </tr></tr>

        <td>
            <div class="row">
                <div class="col-sm-3">
                    <?= $form->field($model, 'student_nr')->textInput() ?>
                </div>
                <div class="col-sm-7">
                    <?= $form->field($model, 'login_id')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </td>

    </tr><tr>

        <td>

            <div class="row">
                <div class="col-sm-3">
                    <?= $form->field($model,'grade')->dropDownList( array('0' => 'inactief','1' => 'actief'),array('grade' => array('1' => array('selected' => true)))) ->label('Nakijken') ?> 
                </div>
            </div>

        </td>

        <td></td>

    </tr></tr>

        <td colspan=2>
            <div class="row">
                <div class="col-sm-10">
                    <?= $form->field($model, 'comment')->textInput(['maxlength' => true])->label('Aantekening (alleen zichtbaar voor docenten)') ?>
                    <?= $form->field($model, 'message')->textInput(['maxlength' => true])->label('Boodschap (zichtbaar voor studenten)') ?>
                </div>
            </div>
        </td>

    </tr></tr>

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
