<?php
require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();

if($app->user === null || $app->user->email !== ADM_EMAIL) {
	http_response_code(404);
	exit();
}

$action = req_safe('action');

if($action == 'rst') {
	$email = req_assert('email');
	$pwd = req_assert('pwd');
	$pwd2 = req_assert('pwd2');
}

$template = htm_get_template();
echo $app->htm_update_header($template);

$message_type = "danger";
$message = "";

if($action == 'rst') {
	if($pwd == $pwd2) {
		$hash = password_hash($pwd, PASSWORD_BCRYPT);
		$sql = 'UPDATE drivers SET password="'.$hash.'" WHERE email="'.$email.'"';
		$res = $app->database->query($sql);

		$message_type = "success";
		$message = 'Mot de passe chang&eacute avec succ&egrave;s.';
	}
	else {

		$message_type = "danger";
		$message = 'Les mots de passe doivent etre identiques!';
	}
}

?>
<h1 class="text-center mt-4 mb-4">Changer Mot de Passe</h1>
<form action="adm_pwd_reset.php?action=rst" method="post">

<div class="row mb-3">
	<div class="col-6 text-end">Adresse email</div>
	<div class="col-6"><input type="text" name="email" id="email" /></div>
</div>
<div class="row mb-3">
	<div class="col-6 text-end">Mot de passe</div>
	<div class="col-6"><input type="password" name="pwd" id="pwd" /></div>
</div>
<div class="row mb-3">
	<div class="col-6 text-end">Confimer mot de passe</div>
	<div class="col-6"><input type="password" name="pwd2" id="pwd2" /></div>
</div>
<div class="row mb-3">
	<div class="col-6"></div>
	<div class="col-6"><input type="submit" value="Changer" class="btn btn-primary" /></div>
</div>

</form>


<?php

if($message !== "") {
	echo '<div class="row col-md-6 offset-md-3 mt-5 mb-3 text-center">
			<div class="alert alert-' . $message_type . '" role="alert">' . $message . '</div>
			</div>';
}


echo $app->htm_update_footer($template);
?>
</body>
</html>