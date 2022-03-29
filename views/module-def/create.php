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

    <?php
        $model->generiek=1;
        $model->voldaan_rule='punten > 95';
        if (isset($_GET['id'])) $model->id=$_GET['id'];
        if (isset($_GET['name'])) $model->naam=$_GET['name'];
    ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
