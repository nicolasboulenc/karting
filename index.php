<?php

require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();

$template = htm_get_template();
echo $app->htm_update_header($template);

echo '<h1 class="driver-list">Pilotes</h1>
		<div class="row">';

// if not connected select most active drivers
if(isset($_COOKIE['driver']) == false) {
	$sql = 'SELECT t.driver AS id, drivers.pseudo, drivers.photo
			FROM (SELECT driver, COUNT(*) FROM driver_stats_session GROUP BY driver ORDER BY COUNT(*) DESC LIMIT 0, 6) AS t
				JOIN drivers ON t.driver=drivers.id';
}
else {
	// display follows and potential follows
	$sql = 'SELECT t.driver AS id, drivers.pseudo, drivers.photo
			FROM (SELECT driver, COUNT(*) FROM driver_stats_session GROUP BY driver ORDER BY COUNT(*) DESC LIMIT 0, 6) AS t
				JOIN drivers ON t.driver=drivers.id';
}

$res = $app->database->query($sql);

while(($driver = $res->fetch_object()) != null) {
	echo '<div class="col text-center mb-3">
		<a href="driver.php?id='.$driver->id.'">
		<p class="mb-1"><img src="'.DRIVER_IMG_PATH.$driver->photo.'" alt="'.$driver->pseudo.'" class="rounded-circle"/></p>
		<p>'.$driver->pseudo.' <i class="bi bi-arrow-right-circle"></i></p></a>
		</div>';
}

echo '</div>
	<h1 class="track-list">Circuits</h1>
	<div class="row">';

if(isset($_COOKIE['driver']) == false) {
	$sql = 'SELECT track_stats_header.track, tracks.name AS track_name, tracks.lon_lat
			FROM track_stats_header
				JOIN tracks ON tracks.id=track_stats_header.track
			ORDER BY track_stats_header.session_count DESC
			LIMIT 0, 3';
}
else {
	// display follows and potential follows
	$sql = 'SELECT track_stats_header.track, tracks.name AS track_name, tracks.lon_lat
			FROM track_stats_header
				JOIN tracks ON tracks.id=track_stats_header.track
			ORDER BY track_stats_header.session_count DESC
			LIMIT 0, 3';
}

$res = $app->database->query($sql);

while(($track = $res->fetch_object()) != null) {
	$lon = explode(",", $track->lon_lat);
	$lat = $lon[1];
	$lon = $lon[0];
	echo '<div class="col-sm-4 mb-3">
			<h4>'.$track->track_name.'</h4>
			<p class="mb-1">
			<iframe width="100%" height="200" frameborder="0" class="rounded"
  				src="https://www.google.com/maps/embed/v1/view?key='.MAP_KEY.'&center='.$lon.','.$lat.'&zoom=16&maptype=satellite" allowfullscreen>
			</iframe></p>
			<p><a class="btn btn-outline-primary btn-sm" href="track.php?id='.$track->track.'">En savoir plus &raquo;</a></p>
		</div>';
}

echo '</div><hr>';

echo $app->htm_update_footer($template);

?>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
</body>
</html>