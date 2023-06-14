<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\nakijken $model */

$this->title = 'Create Nakijken';
$this->params['breadcrumbs'][] = ['label' => 'Nakijkens', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="nakijken-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
