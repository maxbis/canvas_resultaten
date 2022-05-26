<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>


<div class="login-user-form">

  <?php $form = ActiveForm::begin(); ?>

      <?php echo $form->field($model,'old_password')->textInput(['maxlength' => true]); ?>

      <?php echo $form->field($model,'new_password')->textInput(['maxlength' => true]); ?>

      <?php echo $form->field($model,'repeat_password')->textInput(['maxlength' => true]); ?>

      <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
      </div>

    <?php ActiveForm::end(); ?>

</div>