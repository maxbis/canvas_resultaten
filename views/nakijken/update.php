<style>
    .black-link {
        color: #000000;
        text-decoration: none;
    }

    .black-link:hover {
        color: #777777;
    }
</style>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\nakijken $model */

$this->title = $model->module_name.', '.$model->assignment_name. ' (' . $model->cohort.')';
$this->params['breadcrumbs'][] = ['label' => 'Nakijken', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->assignment_id, 'url' => ['view', 'assignment_id' => $model->assignment_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="nakijken-update">

    <?php $link = "https://talnet.instructure.com/courses/" . $model->course_id . "/assignments/" . $model->assignment_id; ?>

    <h3><a href="<?= $link ?>" class="black-link" target="_blank"><?= Html::encode($this->title) ?></a></h2>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
