<?php

require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();

if($app->user === null || $app->user->email !== ADM_EMAIL) {
	http_response_code(404);
	exit();
}

if(isset($_REQUEST['a']) == true && $_REQUEST['a'] == 'rgstr') {

	if($_REQUEST['pwd'] != $_REQUEST['pwd2']) {
		$mes_type = MESSAGE_TYPE_ERROR;
		$mes = 'Les mots de passe doivent etre identiques.';
	}
	else {
		$sql = 'select pseudo from drivers where pseudo="'.$_REQUEST['pseudo'].'"';
		$res = $app->database->query($sql);
		if($res->num_rows > 0) {
			$mes_type = MESSAGE_TYPE_ERROR;
			$mes = 'Pseudo existe deja.';
		}
		else {
			$sql = 'select email from drivers where email="'.$_REQUEST['email'].'"';
			$res = $app->database->query($sql);
			if($res->num_rows > 0) {
				$mes_type = MESSAGE_TYPE_ERROR;
				$mes = 'Email existe deja.';
			}
			else {
				$hash = password_hash($_REQUEST['pwd'], PASSWORD_BCRYPT);
				// insert new user into db
				$sql = 'INSERT INTO drivers (pseudo, email, password)
						VALUES ("'.$_REQUEST['pseudo'].'",
								"'.$_REQUEST['email'].'",
								"'.$hash.'")';
				$app->database->query($sql);
			}
		}
	}
}

$template = htm_get_template();
echo $app->htm_update_header($template);

?>
<h1 class="text-center mt-4 mb-4">Créer pilote</h1>
<form action="adm_register.php?a=rgstr" method="post">

<div class="row g-3 align-items-center">
	<div class="col-6 text-end">Nom d'utilisateur</div>
	<div class="col-6"><input type="text" name="pseudo" /></div>
</div>
<div class="row g-3 align-items-center">
	<div class="col-6 text-end">Adresse email</div>
	<div class="col-6"><input type="text" name="email" /></div>
</div>
<div class="row g-3 align-items-center">
	<div class="col-6 text-end">Mot de passe</div>
	<div class="col-6"><input autocomplete="off" type="password" name="pwd" /></div>
</div>
<div class="row g-3 align-items-center">
	<div class="col-6 text-end">Confirmer mot de passe</div>
	<div class="col-6"><input autocomplete="off" type="password" name="pwd2" /></div>
</div>
<div class="row g-3 align-items-center">
	<div class="col-6"></div>
	<div class="col-6"><input type="submit" class="btn btn-primary" value="Créer" /></div>
</div>

</form>

<?php
echo $app->htm_update_footer($template);
?>
</body>
</html>