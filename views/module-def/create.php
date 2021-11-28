<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ModuleDef */

$this->title = 'Create Module Def';
$this->params['breadcrumbs'][] = ['label' => 'Module Defs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="module-def-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
