<?php
use yii\helpers\Html;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <form method="post" action="login">

		<div class="card shadow table-responsive" style="width: 24rem;">

			<div class="container">
				<div class="row align-items-end">
					<div class="col-sm"><label for="uname"><b>Username</b></label></div>
					<div class="col-sm"><input type="text" placeholder="Enter Username" name="name" required></div>
				</div>	
				<div class="row align-items-end">
					<div class="col-sm"><label for="psw"><b>Password</b></label></div>
					<div class="col-sm"><input type="password" placeholder="Enter Password" name="password" required></div>
				</div>
			</div>
		</div>
		<input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />

		<br><button type="submit">Login</button><br></div>

	</form>

</div>
