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

if(isset($_REQUEST["action"]) == true && $_REQUEST["action"] == "view") {
	// display session
	$sql = "SELECT tracks.name AS track_name, karts.name AS kart_name, sessions.date
			FROM sessions
				JOIN tracks ON tracks.id=sessions.track
				JOIN karts ON karts.id=sessions.kart
			WHERE sessions.id=" . $_REQUEST["s"];
	$res = $app->database->query($sql);
	$row = $res->fetch_object();

	echo '<h1 class="text-center mt-4 mb-4">'.$row->track_name.' ('.$row->kart_name.') '.$row->date.'</h1>';

	$sql = "SELECT drivers.pseudo AS pseudo, lap, time
			FROM times
				JOIN drivers ON drivers.id=times.driver
			WHERE session=" . $_REQUEST["s"] . "
			ORDER BY drivers.pseudo, lap ASC";
	$res = $app->database->query($sql);

	$driver = '';
	$driver_index = -1;
	$drivers = array();
	$data = array();
	while(($row = $res->fetch_object()) != null) {

		if($driver != $row->pseudo) {
			$driver_index++;
			$driver = $row->pseudo;
			$drivers[] = $driver;
		}

		$data[$row->lap - 1][$driver_index] = $row->time;
		if(count($data[$row->lap - 1]) < $driver_index) {
			$data[$row->lap - 1] = array_fill(0, $driver_index, 0);
			$data[$row->lap - 1][$driver_index] = $row->time;
		}
	}

	$col_width = floor(12 / count($drivers));
	echo '<div class="row g-2 mb-2 mt-1 border-top border-dark">';
	foreach($drivers as $driver) {
		echo '<div class="col-'.$col_width.' ps-md-4">'.$driver.'</div>';
	}
	echo '</div>';

	foreach($data as $row) {
		echo '<div class="row g-2 mb-2 mt-1 border-top">';
		foreach($row as $time) {
			echo '<div class="col-'.$col_width.' ps-md-4">'.$time.' => '.fmt_millisec($time).'</div>';
		}
		echo '</div>';
	}
}
else {
	// display list
	echo '<h1 class="text-center mt-4 mb-4">Liste des Sessions</h1>';

	$sql = "SELECT sessions.id, sessions.date, tracks.name AS track_name, karts.name AS kart_name, sessions.driver_count
			FROM sessions
				JOIN tracks ON tracks.id=sessions.track
				JOIN karts ON karts.id=sessions.kart
			ORDER BY track_name, kart_name ASC, sessions.date DESC";
	$res = $app->database->query($sql);

	$track_kart = '';
	$border_dark = "border-dark";

	while(($row = $res->fetch_object()) != null) {

		if($row->track_name . $row->kart_name != $track_kart) {
			$track_kart = $row->track_name . $row->kart_name;
			echo '<div class="row g-2 mb-2 mt-1 border-top '.$border_dark.'">'.$row->track_name.' ('.$row->kart_name.')</div>';
		}

		echo '<div class="row g-2 mb-2 mt-1 border-top">
				<div class="col-12 ps-md-4"><a href="adm_session_list.php?action=view&s='.$row->id.'">Date: '.fmt_date_med($row->date, true).' | Pilotes: ' .$row->driver_count.'</a></div>
			</div>';
		$border_dark = "";
	}
}

echo $app->htm_update_footer($template);

?>

</body>
</html>
