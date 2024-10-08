<?php

/* maintenance mode? */
$subDomain = Yii::$app->params['subDomain'];
$signal_file = \Yii::getAlias('@webroot') .'/../down-'.$subDomain;
$down_file= "down.php";

clearstatcache();
if ( file_exists($signal_file) ) {
    readfile($down_file);
    exit;
}

/* @var $this \yii\web\View */
/* @var $content string */

/* <!-- body { background-color: #fbfdfd; background-image: url("<?=Yii::getAlias("@web")?>/vecteezyfestivityfireworksbackgroundap0521_generated.jpg");} --> */

use app\assets\AppAsset;
use app\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <link rel="icon" type="image/png" href="/favicon/cmon.ico">
    <title><?= "CMON ".$subDomain ?></title>
    <?php $this->head() ?>
    <style type="text/css">
        html { font-size: 1rem;  }
    </style>
    <link rel="stylesheet" href="/css/bootstrap4.css">
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php echo Yii::$app->view->renderFile('@app/views/layouts/menu.php', ['subDomain'=>$subDomain] ); ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>