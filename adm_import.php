<?php
require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');
require_once ('./includes/stats_calc.php');

$app = new App();

if($app->user === null || $app->user->email !== ADM_EMAIL) {
	http_response_code(404);
	exit();
}

$template = htm_get_template();
echo $app->htm_update_header($template);

echo '<h1 class="text-center mt-4 mb-4">Import</h1>';
$date = $_REQUEST['d'];
$track = $_REQUEST['t'];
$kart = $_REQUEST['k'];
$drivers_count = $_REQUEST['dc'];
$laps_count = $_REQUEST['lc'];
$drivers = array();		// [4031, 4034, ....]
$laps = array();		// [[4031 times], [4034 times], etc]

for($drivers_index=1; $drivers_index<=$drivers_count; $drivers_index++) {
	$drivers[] = $_REQUEST['d'.$drivers_index.'-id'];
	$t = array();
	for($laps_index=1; $laps_index<=$laps_count; $laps_index++) {
		$t[] = $_REQUEST['d'.$drivers_index.'-l'.$laps_index];
	}
	$laps[] = $t;
}

// create or update existing session
$session_id = 0;
$sql = 'select id from sessions where track='.$track.' and kart='.$kart.' and date="'.$date.'"';
$res = $app->database->query($sql);
if(($session = $res->fetch_object()) != null) {

	$session_id = $session->id;
	echo "<p>Used existing session (id=".$session_id.").</p>";

	for($driver_index=0; $driver_index<count($drivers); $driver_index++) {
		echo "<p>Delete times for drivers (id=".$drivers[$driver_index].").</p>";
		$sql = "delete from times where session=".$session_id." and driver=".$drivers[$driver_index];
		echo "<p><code>".$sql."</code></p>";
		$res = $app->database->query($sql);
		echo "<p>Deleted ".$app->database->affected_rows." times.</p>";
	}
}
else {
	echo "<p>Create new session.</p>";
	$sql = 'insert into sessions (track, kart, date) values ('.$track.', '.$kart.', "'.$date.'")';
	echo "<p><code>".$sql."</code></p>";
	$res = $app->database->query($sql);
	echo "<p>Created ".$app->database->affected_rows." session.</p>";
	$session_id = $app->database->insert_id;
}


// insert times
echo "<p>Insert times.</p>";
$sql = 'insert into times (`session`, `driver`, `lap`, `time`) values ';

for($driver_index=0; $driver_index<count($drivers); $driver_index++) {

	$driver_id = $drivers[$driver_index];
	for($lap_index=0; $lap_index<count($laps[$driver_index]); $lap_index++) {

		if($laps[$driver_index][$lap_index] !== "") {
			$time = Time_To_Float($laps[$driver_index][$lap_index]);
			$sql .= '('.$session_id.', '.$driver_id.', '.($lap_index + 1).', '.$time.'),';
		}
	}
}
$sql = substr($sql, 0, strlen($sql) - 1);
echo "<p><code>".$sql."</code></p>";
$app->database->query($sql);
echo "<p>Inserted ".$app->database->affected_rows." times.</p>";

// update session stats
Session_Stats_Update($session_id);

// update drivers
foreach($drivers as $driver_id) {
	echo "<p>Driver stats session update (driver=".$driver_id." | session=".$session_id.")</p>";
	Driver_Stats_Session_Update($driver_id, $session_id);
	echo "<p>Driver stats track update (driver=".$driver_id." | track=".$track." | kart=".$kart.")</p>";
	Driver_Stats_Track_Update($driver_id, $track, $kart);
	echo "<p>Driver stats header update (driver=".$driver_id.")</p>";
	Driver_Stats_Header_Update($driver_id);
}
Driver_Ranking_Update($session_id);

// update tracks
echo "<p>Track stats kart update (track=".$track." | kart=".$kart.")</p>";
Track_Stats_Kart_Update($track, $kart);
Track_Driver_Ranking_Update($track, $kart);

echo "<p>Track stats header update (track=".$track.")</p>";
Track_Stats_Header_Update($track);

//===============================================================================================================

function Time_To_Float($lap_str) {

	$lap_float = 0;
	$lap = explode(":", $lap_str);

	if(count($lap) > 1)	{
		$lap_float = intval($lap[0]) * 60 + floatval($lap[1]);
	}
	else {
		$lap_float = floatval($lap_str);
	}

	return $lap_float;
}

echo $app->htm_update_footer($template);
?>

</body>
</html>