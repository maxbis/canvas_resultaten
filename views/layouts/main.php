<?php

/* maintenance mode? */

$signal_file="/tmp/down";
$down_file= "../../down.php";

clearstatcache();
if ( is_file($signal_file) ) {
    readfile($down_file);
    exit;
}


/* @var $this \yii\web\View */
/* @var $content string */

/* <!-- body { background-color: #fbfdfd; background-image: url("<?=Yii::getAlias("@web")?>/vecteezyfestivityfireworksbackgroundap0521_generated.jpg");} --> */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style type="text/css">
        html { font-size: 1rem;  }
    </style>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php echo Yii::$app->view->renderFile('@app/views/layouts/menu.php'); ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>