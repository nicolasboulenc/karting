<?php

require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

// error_reporting(E_ALL);	// Reports all errors
ini_set("display_errors", "Off");	// Do not display errors for the end-users (security issue)
// ini_set("error_log", "/logs/api-" . date("Y-m-d") . ".txt"); // Set a logging file

$action = req_assert("action");

$app = new App();

//==============================================================================================

if($action === 'autocomplete') {

	$param = req_assert("param");

	if($param === "tracks") {
		$sql = "SELECT id, name FROM tracks";
		$res = $app->database->query($sql);
		$data = array();
		while(($track = $res->fetch_object()) != null) {
			$data[] = array("id"=>$track->id, "text"=>$track->name, "type"=>"track");
		}
	}
	else if ($param === "drivers") {
		$sql = "SELECT id, pseudo FROM drivers";
		$res = $app->database->query($sql);
		$data = array();
		while(($driver = $res->fetch_object()) != null) {
			$data[] = array("id"=>$driver->id, "text"=>$driver->pseudo, "type"=>"driver");
		}
	}
	else if ($param === "both") {
		$sql = "SELECT id, pseudo FROM drivers";
		$res = $app->database->query($sql);
		$data = array();
		while(($driver = $res->fetch_object()) != null) {
			$data[] = array("id"=>$driver->id, "text"=>$driver->pseudo, "type"=>"driver");
		}
		$sql = "SELECT id, name FROM tracks";
		$res = $app->database->query($sql);
		while(($track = $res->fetch_object()) != null) {
			$data[] = array("id"=>$track->id, "text"=>$track->name, "type"=>"track");
		}
	}

	// $json = json_encode($data, JSON_HEX_APOS);
	$json = json_encode($data);
	echo $json;
}
else if($action == 'follow') {

	$driver_id = req_assert("d");
	$driver_followed = req_safe("df", 0);
	$track_followed = req_safe("tf", 0);

	if($driver_followed != 0) {
		$sql = 'INSERT INTO follow_drivers (driver, follow) VALUES ('.$driver_id.', '.$driver_followed.')';
		$res = $app->database->query($sql);

		$sql = 'SELECT pseudo FROM drivers WHERE id='.$driver_followed;
		$res = $app->database->query($sql);
		$row = $res->fetch_object();

		$json = array("sucess"=>true, "name"=>$row->pseudo);
	}

	else if($track_followed != 0) {
		$sql = 'INSERT INTO follow_tracks (driver, follow) VALUES ('.$driver_id.', '.$track_followed.')';
		$res = $app->database->query($sql);

		$sql = 'SELECT name FROM tracks WHERE id='.$track_followed;
		$res = $app->database->query($sql);
		$row = $res->fetch_object();

		$json = array("sucess"=>true, "name"=>$row->name);
	}

	$json = json_encode($data);
	echo $json;
}
else if($action == 'unfollow') {

	$driver_id = req_assert("d");
	$driver_followed = req_safe("df", 0);
	$track_followed = req_safe("tf", 0);
	if($driver_followed != 0) {
		$sql = 'DELETE FROM follow_drivers WHERE driver='.$driver_id.' AND follow='.$driver_followed;
		$res = $app->database->query($sql);
		$json = array("sucess"=>true);
	}

	else if($track_followed != 0) {
		$sql = 'DELETE FROM follow_tracks WHERE driver='.$driver_id.' AND follow='.$track_followed;
		$res = $app->database->query($sql);
		$json = array("sucess"=>true);
	}

	$json = json_encode($data);
	echo $json;
}
else if($action === 'driver') {

	$chart = req_assert("id");

}
else if($action == 'chart') {

	$chart = req_assert("c");

	if($chart == "dp") {
		// driver progress
		$driver_id = req_assert("d");
		$track_id = req_assert("t");
		$kart_id = req_assert("k");

		$data = Chart_Driver_Progress($driver_id, $track_id, $kart_id);
		echo json_encode($data);
	}
	elseif($chart == "ds") {
		// driver session
		$driver_id = req_assert("d");
		$session_id = req_assert("s");

		$data = Chart_Driver_Session($driver_id, $session_id);
		echo json_encode($data);
	}
	elseif($chart == "sbtr") {
		// session best time ranking
		$session_id = req_assert("s");

		$data = Chart_Session_Best_Time_Ranking($session_id);
		echo json_encode($data);
	}
	elseif($chart == "satr") {
		// session average time ranking
		$session_id = req_assert("s");

		$data = Chart_Session_Average_Time_Ranking($session_id);
		echo json_encode($data);
	}
	elseif($chart == "tbth") {
		// track best time history
		$track_id = req_assert("t");
		$kart_id = req_assert("k");

		$data = Chart_Track_Best_Time_History($track_id, $kart_id);
		echo json_encode($data);
	}
	elseif($chart == "tbtr") {
		// track best time ranking
		$track_id = req_assert("t");
		$kart_id = req_assert("k");

		$data = Chart_Track_Best_Time_Ranking($track_id, $kart_id);
		echo json_encode($data);
	}
	elseif($chart == "tatr") {
		// track average time ranking
		$track_id = req_assert("t");
		$kart_id = req_assert("k");

		$data = Chart_Track_Average_Time_Ranking($track_id, $kart_id);
		echo json_encode($data);
	}
	elseif($chart == "ts") {
		// track session
		$session_id = req_assert("s");

		$data = Chart_Track_Session($session_id);
		echo json_encode($data);
	}

}

//==============================================================================================

function Chart_Driver_Progress($driver_id, $track_id, $kart_id) {

	global $app;

	$sql = 'SELECT sessions.date AS session_date, dss.best_time, dss.average_time
			FROM driver_stats_session AS dss
				JOIN sessions ON dss.session=sessions.id
			WHERE sessions.track='.$track_id.' AND sessions.kart='.$kart_id.' AND dss.driver='.$driver_id.'
			ORDER BY date ASC';
	$res = $app->database->query($sql);
	$data = array();

	while (($session = $res->fetch_assoc()) !== NULL) {
		$data[] = $session;
	}
	return $data;
}

//==============================================================================================

function Chart_Driver_Session($driver_id, $session_id) {

	global $app;

	$sql = 'SELECT lap, time
			FROM times
			WHERE session='.$session_id.' AND driver='.$driver_id.'
	 		ORDER BY lap ASC';
	$res = $app->database->query($sql);
	$data = array();

	while (($row = $res->fetch_assoc()) !== NULL) {
		$data[] = $row;
	}
	return $data;
}

//==============================================================================================

function Chart_Session_Best_Time_Ranking($session_id) {

	global $app;

	$sql = 'SELECT drivers.pseudo AS driver_pseudo, dss.best_time_rank, dss.best_time
			FROM driver_stats_session AS dss
				JOIN drivers ON drivers.id=dss.driver
			WHERE dss.session='.$session_id.'
			ORDER BY dss.best_time_rank ASC';
	$res = $app->database->query($sql);
	$data = array();

	while (($session = $res->fetch_assoc()) !== NULL) {
		$data[] = $session;
	}
	return $data;
}

//==============================================================================================

function Chart_Session_Average_Time_Ranking($session_id) {

	global $app;

	$sql = 'SELECT drivers.pseudo AS driver_pseudo, dss.average_time_rank, dss.average_time
			FROM driver_stats_session AS dss
				JOIN drivers ON drivers.id=dss.driver
			WHERE dss.session='.$session_id.'
			ORDER BY dss.average_time_rank ASC';
	$res = $app->database->query($sql);
	$data = array();

	while (($session = $res->fetch_assoc()) !== NULL) {
		$data[] = $session;
	}
	return $data;
}

//==============================================================================================

function Chart_Track_Best_Time_History($track_id, $kart_id) {

	global $app;

	// $sql = 'SELECT DATE(sessions.date) AS date, drivers.pseudo, MIN(`best_time`) AS best_time
	// 		FROM driver_stats_session AS dss
	// 			JOIN sessions ON sessions.id=dss.session
	// 			JOIN drivers ON dss.driver=drivers.id
	// 		WHERE sessions.track='.$track_id.' AND sessions.kart='.$kart_id.'
	// 		GROUP BY date, drivers.pseudo
	// 		ORDER BY date ASC';
	$sql = 'SELECT DATE(sessions.date) AS date, MIN(`best_time`) AS best_time
			FROM driver_stats_session AS dss
				JOIN sessions ON sessions.id=dss.session
			WHERE sessions.track='.$track_id.' AND sessions.kart='.$kart_id.'
			GROUP BY date
			ORDER BY date ASC';
	$res = $app->database->query($sql);

	$pseudo = '';
	$best_time = PHP_INT_MAX;
	$date = '';

	$data = array();

	while (($row = $res->fetch_object()) !== null) {
		if($row->best_time <= $best_time) {
			$best_time = $row->best_time;
			// $pseudo = $row->pseudo;
		}
		$data[] = array("date" => $row->date,
						"best_time" => $best_time,
						"pseudo" => $pseudo);
	}
	return $data;
}

//==============================================================================================

function Chart_Track_Best_Time_Ranking($track_id, $kart_id) {

	global $app;

	$sql = 'SELECT drivers.pseudo AS driver_pseudo, dst.best_time_rank, dst.best_time
			FROM driver_stats_track AS dst
				JOIN drivers ON drivers.id=dst.driver
			WHERE dst.track='.$track_id.' AND dst.kart='.$kart_id.'
			ORDER BY dst.best_time_rank ASC';
	$res = $app->database->query($sql);
	$data = array();

	while (($session = $res->fetch_assoc()) !== NULL) {
		$data[] = $session;
	}
	return $data;
}

//==============================================================================================

function Chart_Track_Average_Time_Ranking($track_id, $kart_id) {

	global $app;

	$sql = 'SELECT drivers.pseudo AS driver_pseudo, dst.average_time_rank, dst.best_average_time
			FROM driver_stats_track AS dst
				JOIN drivers ON drivers.id=dst.driver
			WHERE dst.track='.$track_id.' AND dst.kart='.$kart_id.'
			ORDER BY dst.average_time_rank ASC';
	$res = $app->database->query($sql);
	$data = array();

	while (($session = $res->fetch_assoc()) !== NULL) {
		$data[] = $session;
	}
	return $data;
}

//==============================================================================================

function Chart_Track_Session($session_id) {

	global $app;

	$sql = 'SELECT drivers.pseudo, times.lap, times.time
			FROM times
				JOIN drivers ON drivers.id=times.driver
			WHERE session='.$session_id.'
			ORDER BY drivers.pseudo ASC, times.lap ASC';
	$res = $app->database->query($sql);
	$data = array();

	while (($row = $res->fetch_assoc()) !== NULL) {
		$data[] = $row;
	}
	return $data;
}

?>