<?php

require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();

// check if user authentified
if($app->user === null) {
	http_response_code(404);
	exit();
}

$template = htm_get_template();
echo $app->htm_update_header($template);

$driver_id = $app->user->id;

$action = req_safe('action');

// deal with actions
if(isset($_REQUEST['a']) == true) {
	// change pseudo
	if ($_REQUEST['a'] == 'upd') {
		$sql = 'UPDATE drivers SET pseudo="'.$_REQUEST['pseudo'].'"
				WHERE id='.$driver_id;
		$res = $app->database->query($sql);

		if ($app->database->affected_rows > 0) {
			$expire = time() + 60 * 60 * 24 * 30 * 3;
			setcookie("pseudo", $_REQUEST['pseudo'], $expire);
			header('Location: account.php?m=pc_s');
			exit;
		}
		else {
			$mes = 'Erreur lors de la sauvegarde du profil.';
			// echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
		}
	}

	//change password
	elseif ($_REQUEST['a'] == 'pwd') {
		$sql = 'SELECT password FROM drivers WHERE id='.$driver_id;
		$res = $app->database->query($sql);
		$driver_data = $res->fetch_object();

		// Calc_Hash($_REQUEST['old_pwd'], $driver_data->created)

		if (password_verify($_REQUEST['old_pwd'], $driver_data->password) === true && $_REQUEST['new_pwd1'] == $_REQUEST['new_pwd2']) {
			$hash = $hash = password_hash($_REQUEST['new_pwd1'], PASSWORD_BCRYPT);
			$sql = 'UPDATE drivers SET password="'.$hash.'"
					WHERE id='.$driver_id;
			$res = $app->database->query($sql);

			$mes = 'Mot de passe change avec succes.';
			// echo Html_Get_Message(MESSAGE_TYPE_SUCCESS, $mes);
		}
		else {
			$mes = 'Erreur de mot de passe.';
			// echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
		}
	}
}
if(isset($_REQUEST['m']) == TRUE) {
	// pseudo change successful
	if($_REQUEST['m'] == 'pc_s') {
		$mes = 'Pseudo change avec succes.';
		// echo Html_Get_Message(MESSAGE_TYPE_SUCCESS, $mes);
	}
}


$sql = 'select * from drivers where id='.$driver_id;
$res = $app->database->query($sql);
$driver = $res->fetch_object();
?>

<h1 class="text-center mt-4 mb-4">Compte</h1>

<form action="account.php" method="get">
	<input type="hidden" name="action" value="upd">
	<div class="row mb-3 align-items-center">
		<div class="col-12 col-md-6 text-start text-md-end">Email</div>
		<div class="col-auto"><input class="form-control" type="text" name="email" value="<?php echo $driver->email; ?>" disabled /></div>
	</div>
	<div class="row mb-3 align-items-center">
		<div class="col-12 col-md-6 text-start text-md-end">Nom d'utilisateur</div>
		<div class="col-auto"><input class="form-control" type="text" name="pseudo" value="<?php echo $driver->pseudo; ?>"/></div>
	</div>
	<div class="row align-items-center">
		<div class="col-6 d-none d-md-block"></div>
		<div class="col-auto"><input type="submit" value="Sauvegarder" class="btn btn-primary" /></div>
	</div>
</form>


<h1 class="text-center mt-5 mb-4">Mot de passe</h1>

<form action="account.php" method="post">
	<input type="hidden" name="action" value="pwd">
	<div class="row mb-3 align-items-center">
		<div class="col-12 col-md-6 text-start text-md-end">Mot de passe</div>
		<div class="col-auto"><input class="form-control" type="password" name="old_pwd" /></div>
	</div>
	<div class="row mb-3 align-items-center">
		<div class="col-12 col-md-6 text-start text-md-end">Nouveau mot de Passe</div>
		<div class="col-auto"><input class="form-control" type="password" name="new_pwd1" /></div>
	</div>
	<div class="row mb-3 align-items-center">
		<div class="col-12 col-md-6 text-start text-md-end">Re-Nouveau mot de Passe</div>
		<div class="col-auto"><input class="form-control" type="password" name="new_pwd2" /></div>
	</div>
	<div class="row mb-3 align-items-center">
		<div class="col-6 d-none d-md-block"></div>
		<div class="col-auto"><input type="submit" value="Sauvegarder" class="btn btn-primary" /></div>
	</div>
</form>

<h1 class="text-center mt-5 mb-4">Qui me suis?</h1>
<?php

$sql = 'select driver as id, pseudo from follow_drivers inner join drivers on drivers.id=driver where follow='.$driver_id;
$res = $app->database->query($sql);
while(($driver = $res->fetch_object()) != null) {
	echo '<div class="row">
		<div class="col"><a href="driver.php?id='.$driver->id.'">'.$driver->pseudo.'</a></div>
		</div>';
}

echo $app->htm_update_footer($template);
?>