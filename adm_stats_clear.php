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

// delete driver stats
$sql = "DELETE FROM driver_stats_header";
$res = $app->database->query($sql);
if($res !== false) echo "Driver stats header delete.<br>";

$sql = "DELETE FROM driver_stats_track";
$res = $app->database->query($sql);
if($res !== false) echo "Driver stats track delete.<br>";

$sql = "DELETE FROM driver_stats_session";
$res = $app->database->query($sql);
if($res !== false) echo "Driver stats session delete.<br>";

// DO NOT delete sessions
// clear sessions stats
$sql = "UPDATE sessions SET
		driver_count=0";
$res = $app->database->query($sql);
if($res !== false) echo "Session stats cleared.<br>";

// delete track stats
$sql = "DELETE FROM track_stats_header";
$res = $app->database->query($sql);
if($res !== false) echo "Track stats header delete.<br>";

$sql = "DELETE FROM track_stats_kart";
$res = $app->database->query($sql);
if($res !== false) echo "Track stats kart delete.<br>";

echo "</pre>";
echo $app->htm_update_footer($template);
?>