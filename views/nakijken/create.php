<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\nakijken $model */

$this->title = 'Create Nakijken';
$this->params['breadcrumbs'][] = ['label' => 'Nakijkens', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="nakijken-create">

    <h2><?= Html::encode($this->title) ?></h2>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
