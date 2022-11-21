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

echo "<pre>";

// update session stats
$sql = 'select id from sessions';
$res = $app->database->query($sql);
while(($session = $res->fetch_object()) != null) {
	echo "Session update ".$session->id.PHP_EOL;
	Session_Stats_Update($session->id);
}

// update drivers
$sql = 'select session, driver from times group by session, driver order by driver, session asc';
$res = $app->database->query($sql);
while(($row = $res->fetch_object()) != null) {
	echo "Driver stats session update ".$row->driver." ".$row->session.PHP_EOL;
	Driver_Stats_Session_Update($row->driver, $row->session);
}

$sql = 'select id from sessions';
$res = $app->database->query($sql);
while(($session = $res->fetch_object()) != null) {
	echo "Session update ".$session->id.PHP_EOL;
	Driver_Ranking_Update($session->id);
}

$sql = 'select driver, track, kart from times join sessions on times.session=sessions.id group by driver, track, kart';
$res = $app->database->query($sql);
while(($row = $res->fetch_object()) != null) {
	echo "Driver stats track update ".$row->driver." ".$row->track." ".$row->kart.PHP_EOL;
	Driver_Stats_Track_Update($row->driver, $row->track, $row->kart);
}

$sql = 'select id from drivers';
$res = $app->database->query($sql);
while(($row = $res->fetch_object()) != null) {
	echo "Driver stats header update ".$row->id.PHP_EOL;
	Driver_Stats_Header_Update($row->id);
}

// update tracks
$sql = 'select track, kart from sessions group by track, kart';
$res = $app->database->query($sql);
while(($session = $res->fetch_object()) != null) {
	echo "Track stats kart update ".$session->track." ".$session->kart.PHP_EOL;
	Track_Stats_Kart_Update($session->track, $session->kart);
	Track_Driver_Ranking_Update($session->track, $session->kart);
}

$sql = 'select id from tracks';
$res = $app->database->query($sql);
while(($row = $res->fetch_object()) != null) {
	echo "Track stats header update ".$row->id.PHP_EOL;
	Track_Stats_Header_Update($row->id);
}

echo "</pre>";
echo $app->htm_update_footer($template);

?>