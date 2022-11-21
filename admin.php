<?php
require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();

if($app->user === null || $app->user->email !== ADM_EMAIL) {
	http_response_code(404);
	exit();
}

$template = htm_get_template();
echo $app->htm_update_header($template);
?>

<h1 class="text-center mt-4 mb-4">Admin</h1>
<div class="row mb-1">
	<div class="col text-end">Creation de pilote</div>
	<div class="col">
		<a href="adm_register.php" class="btn btn-primary btn-sm" role="button"><i class="bi bi-arrow-right-circle"></i></a>
	</div>
</div>
<div class="row mb-1">
	<div class="col text-end">Changer mot de passe</div>
	<div class="col">
		<a href="adm_pwd_reset.php" class="btn btn-primary btn-sm" role="button"><i class="bi bi-arrow-right-circle"></i></a>
	</div>
</div>
<div class="row mb-1">
	<div class="col text-end">Liste sessions</div>
	<div class="col">
		<a href="adm_session_list.php" class="btn btn-primary btn-sm" role="button"><i class="bi bi-arrow-right-circle"></i></a>
	</div>
</div>
<div class="row mb-1">
	<div class="col text-end">Saisie de session</div>
	<div class="col">
		<a href="adm_input_session.php" class="btn btn-primary btn-sm" role="button"><i class="bi bi-arrow-right-circle"></i></a>
	</div>
</div>
<div class="row mb-1">
	<div class="col text-end">Effacer toutes les stats</div>
	<div class="col">
		<a href="adm_stats_clear.php" class="btn btn-danger btn-sm" role="button"><i class="bi bi-arrow-right-circle"></i></a>
	</div>
</div>
<div class="row mb-1">
	<div class="col text-end">Calculer toutes les stats</div>
	<div class="col">
		<a href="adm_stats_calc.php" class="btn btn-warning btn-sm" role="button"><i class="bi bi-arrow-right-circle"></i></a>
	</div>
</div>
<?php
echo $app->htm_update_footer($template);
?>

</body>
</html>