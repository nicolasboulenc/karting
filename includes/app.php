<?php

class App {

	public $timer;
	public $database;
	public $user;

	public function __construct() {

		$this->timer = microtime(true);
		$this->database = new Database();
		$this->user = $this->user_update();
	}

	function htm_update_header($fc) {

		$fc = explode("<!-- Header end -->", $fc);
		$fc = $fc[0];

		// process header
		$title = "Karting Statistiques";
		$fc = str_replace('%TITLE%', $title, $fc);

		$script = explode("/", $_SERVER['SCRIPT_NAME']);
		$script = $script[count($script)-1].'?'.$_SERVER['QUERY_STRING'];

		if($this->user !== null) {
			// need to display user menu
			$fc = str_replace('id="dashboard_button" class="d-none ', 'id="dashboard_button" class="', $fc);
			$fc = str_replace('id="account_button" class="d-none ', 'id="account_button" class="', $fc);
			$fc = str_replace('id="logout_button" class="d-none ', 'id="logout_button" class="', $fc);
			$fc = str_replace('id="login_button" class="', 'id="login_button" class="d-none ', $fc);
			$fc = str_replace('id="user_follow_list" class="d-none', 'id="user_follow_list" class="', $fc);
			$fc = str_replace('%DRIVER_PSEUDO%', $this->user->pseudo, $fc);
			$fc = str_replace('%DRIVER_ID%', $this->user->id, $fc);

			// display driver followed list
			$sql = 'SELECT id, pseudo
					FROM follow_drivers
						JOIN drivers ON follow_drivers.follow=drivers.id
					WHERE driver='.$this->user->id.'
					ORDER BY pseudo ASC';
			$res = $this->database->query($sql);
			$follow_list = '';
			if($res->num_rows > 0) {
				while(($driver = $res->fetch_object()) !== NULL) {
					$follow_list .= '<a id="d'.$driver->id.'" class="nav-link" href="driver.php?id='.$driver->id.'">@'.$driver->pseudo.'</a>';
				}
			}
			$fc = str_replace('%DRIVER_FOLLOW_LIST%', $follow_list, $fc);

			// display track follow list
			$sql = 'SELECT id, name
					FROM follow_tracks
						JOIN tracks ON follow_tracks.follow=tracks.id
					WHERE driver='.$this->user->id.'
					ORDER BY name ASC';
			$res = $this->database->query($sql);
			$follow_list = '';
			if($res->num_rows > 0) {
				$follow_list .= '<li class="divider"></li>';
				while(($track = $res->fetch_object()) !== NULL) {
					$follow_list .= '<a id="t'.$track->id.'" class="nav-link" href="track.php?id='.$track->id.'">@'.$track->name.'</a>';
				}
			}
			$fc = str_replace('%TRACK_FOLLOW_LIST%', $follow_list, $fc);
		}
		else {
			// need display login buttons
			$fc = str_replace('id="login-buttons" style="display: none"', 'id="login-buttons"', $fc);
		}

		return $fc;
	}


	public function htm_update_footer($template) {

		$footer = explode("<!-- Header end -->", $template);
		$footer = $footer[1];

		$this->timer = microtime(true) - $this->timer;
		$php_time = $this->timer - $this->database->timer;

		$footer = str_replace('%PHP%', round($php_time, 3), $footer);
		$footer = str_replace('%SQL%', round($this->database->timer, 3), $footer);
		$footer = str_replace('%MEM%', fmt_memory(memory_get_usage()), $footer);
		return $footer;
	}


	public function user_update() {

		// validate cookies
		if(empty($_COOKIE["token"]) === true || empty($_COOKIE["data"]) === true) {
			usr_clear();
			return null;
		}

		// validate session
		if(empty($_SESSION["token"]) === true || empty($_SESSION["data"]) === true) {

			$data = urldecode($_COOKIE["data"]);
			$data = json_decode($data);
			$sql = 'SELECT id, email, pseudo, token FROM drivers WHERE id='.$data->id;
			$res = $this->database->query($sql);

			if($res === false) {
				usr_clear();
				return null;
			}

			if($res->num_rows === 0) {
				usr_clear();
				return null;
			}

			$user = $res->fetch_object();
			if($user === false) {
				usr_clear();
				return null;
			}

			if($user->token !== $_COOKIE["token"]) {
				usr_clear();
				return null;
			}

			$session_data = array(	"id" => $user->id,
									"email" => $user->email,
									"pseudo" => $user->pseudo	);
			$session_data = json_encode($session_data);
			$session_data = urlencode($session_data);
			$_SESSION["data"] = $session_data;
			$_SESSION["token"] = $user->token;
		}

		if($_SESSION["token"] === $_COOKIE["token"]) {

			return (object)array("id" => $user->id, "email" => $user->email, "pseudo" => $user->pseudo);
		}
		else {
			usr_clear();
			return null;
		}
	}
}

//==============================================================================================

function usr_clear() {

	setcookie('data', '', time()-3600, COOKIE_PATH, COOKIE_DOMAIN);
	setcookie('token', '', time()-3600, COOKIE_PATH, COOKIE_DOMAIN);
	$_SESSION['data'] = '';
	unset($_SESSION['data']);
	$_SESSION['token'] = '';
	unset($_SESSION['data']);
}

//==============================================================================================


function req_assert($param) {
	if (isset($_REQUEST[$param]) !== true) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	return $_REQUEST[$param];
}

//==============================================================================================

function req_safe($param, $safe_value="") {
	if (isset($_REQUEST[$param]) !== true) {
		return $safe_value;
	}
	return $_REQUEST[$param];
}

//==============================================================================================

function htm_get_template() {
	return file_get_contents('./includes/html_template.html');
}

//==============================================================================================

function fmt_hours($time, $show_seconds=false) {

	// return 3 Hr 9 Min 8 Sec
	$time = floor($time);

	$hh = floor($time/3600);
	$mm = floor(($time-$hh*3600)/60);
	$ss = $time - ($hh*3600) - ($mm*60);

	$formatted_time = ($hh>0?$hh.' Hr ':'').($mm>0?$mm.' Min ':'').($show_seconds==true && $ss>0?$ss.' Sec':'');
	return $formatted_time;
}

//==============================================================================================

function fmt_hours_short($time, $show_seconds=false) {

	// return 3h 9m 8s
	$time = floor($time);

	$hh = floor($time/3600);
	$mm = floor(($time-$hh*3600)/60);
	$ss = $time - ($hh*3600) - ($mm*60);

	$formatted_time = ($hh>0?$hh.'h ':'').($hh==0 && $mm==0?'':$mm.'m ').($show_seconds==true && $ss>0?$ss.'s':'');
	return $formatted_time;
}

//==============================================================================================

function fmt_date($date, $with_time=false) {

	// return 03/03/2012 09:06
	$dd = substr($date, 8, 2);
	$mo = substr($date, 5, 2);
	$yy = substr($date, 0, 4);

	$formatted_date = $dd.'/'.$mo.'/'.$yy;

	if($with_time == true) {
		$hh = substr($date, 11, 2);
		$mi = substr($date, 14, 2);
		$formatted_date .= ' '.$hh.':'.$mi;
	}

	return $formatted_date;
}

//==============================================================================================

function fmt_date_med($date, $with_time=false) {

	// return 3 Mar 12, 17:09
	$dd = substr($date, 8, 2);
	$mo = substr($date, 5, 2);
	$yy = substr($date, 2, 2);

	$months = array("", "Jan", "Fev", "Mar", "Avr", "Mai", "Juin", "Juil", "Aou", "Sep", "Oct", "Nov", "Dec");

	$formatted_date = intval($dd).' '.$months[intval($mo)].' '.$yy;

	if($with_time == true) {
		$hh = substr($date, 11, 2);
		$mi = substr($date, 14, 2);
		$formatted_date .= ', '.$hh.':'.$mi;
	}

	return $formatted_date;
}

//==============================================================================================

function fmt_date_long($date, $with_time=false) {

	// return 3 Mars 2012, 17:09
	$dd = substr($date, 8, 2);
	$mo = substr($date, 5, 2);
	$yy = substr($date, 0, 4);

	$months = array("", "Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Dececembre");

	$formatted_date = intval($dd).' '.$months[intval($mo)].' '.$yy;

	if($with_time == true) {
		$hh = substr($date, 11, 2);
		$mi = substr($date, 14, 2);
		$formatted_date .= ', '.$hh.':'.$mi;
	}

	return $formatted_date;
}

//==============================================================================================

function fmt_date_short($date, $with_time=false) {

	// return 3/3/12 9:6
	$dd = substr($date, 8, 2);
	$mo = substr($date, 5, 2);
	$yy = substr($date, 2, 2);

	$formatted_date = intval($dd).'/'.intval($mo).'/'.intval($yy);

	if($with_time == true) {
		$hh = substr($date, 11, 2);
		$mi = substr($date, 14, 2);
		$formatted_date .= ' '.intval($hh).':'.intval($mi);
	}

	return $formatted_date;
}

//==============================================================================================

function fmt_millisec($time) {

	// return 1:06.130
	$formatted_time = '';
	if($time == 0) {
		$formatted_time = '-';
	}
	else {
		$min = intval($time / 60);
		$sec = intval($time % 60);
		$mmm = intval(round($time - intval($time), 2) * 100);

		$formatted_time = ($min>0?$min.':':'').($sec<10?'0'.$sec:$sec).'.'.($mmm<10?'0'.$mmm:$mmm);
	}

	return $formatted_time;
}

//==============================================================================================

function fmt_memory($mem) {
	$KB = $mem / 1024;
	return number_format($KB, 1, '.', ',');
}

?>