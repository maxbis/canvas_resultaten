<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\nakijken $model */

$this->title = $model->assignment_id;
$this->params['breadcrumbs'][] = ['label' => 'Nakijkens', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="nakijken-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'assignment_id' => $model->assignment_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'assignment_id' => $model->assignment_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'course_id',
            'assignment_id',
            'module_name',
            'assignment_name',
            'file_type',
            'words_in_order',
            'points_possible',
            'instructie',
            'label',
        ],
    ]) ?>

</div>
