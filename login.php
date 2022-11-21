<?php
require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();
$action = req_safe("action");

// login
if($action == "login") {

	$email = req_assert("email");
	$password = req_assert("password");

	$sql = 'SELECT id, email, pseudo, password
			FROM drivers
			WHERE email="'.$email.'"';
	$res = $app->database->query($sql);

	// no user found
	if($res === false) {
		echo "impossible de se connecter (1)";
		exit();
	}
	else if($res->num_rows === 0) {
		echo "email ou mot de passe incorrect (1)";
		exit();
	}

	// unable to fetch result
	$user = $res->fetch_object();
	if($user === false) {
		echo "impossible de se connecter (2)";
		exit();
	}

	$match = password_verify($password, $user->password);

	// password not matching
	if($match === false) {
		echo "email ou mot de passe incorrect (2)";
		exit();
	}

	// generate token & update db
	$token = uniqid("kdfhgdfhg", true);

	$sql = "UPDATE drivers SET token=\"$token\" WHERE email=\"$email\"";
	$res = $app->database->query($sql);

	if($res === false || $app->database->affected_rows === 0) {
		echo "impossible de se connecter (3)";
		exit();
	}

	$session_data = array(	"id" => $user->id,
							"email" => $user->email,
							"pseudo" => $user->pseudo	);
	$session_data = json_encode($session_data);
	$session_data = urlencode($session_data);
	$_SESSION["data"] = $session_data;
	$_SESSION["token"] = $token;

	$cookie_data = array(	"id" => $user->id,
							"email" => $user->email,
							"pseudo" => $user->pseudo	);
	$cookie_data = json_encode($cookie_data);
	$cookie_data = urlencode($cookie_data);

	setcookie("data", $cookie_data, 0, COOKIE_PATH, COOKIE_DOMAIN);
	setcookie("token", $token, 0, COOKIE_PATH, COOKIE_DOMAIN);

	header("Location: index.php");
	exit();
}

// logout
elseif($action == 'logout') {

	setcookie("data", "", time()-3600, COOKIE_PATH, COOKIE_DOMAIN);
	setcookie("token", "", time()-3600, COOKIE_PATH, COOKIE_DOMAIN);
	$_SESSION["data"] = "";
	unset($_SESSION["data"]);
	$_SESSION["token"] = "";
	unset($_SESSION["data"]);

	header("Location: index.php");
	exit();
}


$template = htm_get_template();
echo $app->htm_update_header($template);

?>
<br>
<br>
<div class="form-signin">
	<form action="login.php?action=login" method="post">
		<input type="hidden" name="page" value="<?php if(isset($_SERVER["HTTP_REFERER"])) echo $_SERVER["HTTP_REFERER"];?>">
		<h1 class="h3 mb-3 fw-normal text-center">Se Connecter</h1>

		<div class="form-floating">
			<input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
			<label for="email">Adresse email</label>
		</div>
		<div class="form-floating">
			<input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe">
			<label for="password">Password</label>
		</div>

		<div class="checkbox mb-3 text-center">
			<label><input type="checkbox" value="remember-me"> Rester connect&eacute;</label>
		</div>
		<button class="w-100 btn btn-lg btn-primary" type="submit">Se Connecter</button>
		<p class="mt-5 mb-3 text-muted text-center">&copy; 2009–2021</p>
	</form>
</div>

<?php
echo $app->htm_update_footer($template);
?>

</body>
</html>
