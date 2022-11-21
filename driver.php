<?php

require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$driver_id = req_assert("id");

// display header
$app = new App();

$template = htm_get_template();
echo $app->htm_update_header($template);

// get driver data
$sql = 'SELECT pseudo, photo FROM drivers WHERE id='.$driver_id;
$res = $app->database->query($sql);
$driver_data = $res->fetch_object();
if($driver_data->photo == '') $driver_data->photo = 'black_pixel.png';

echo '<br><h1 class="d-inline me-2">'.$driver_data->pseudo.'</h1>';

// show follow/unfollow icon
if($app->user !== null) {
	if($app->user->id != $driver_id) {
		$sql = 'SELECT follow FROM follow_drivers WHERE driver='.$app->user->id.' AND follow='.$driver_id;
		$res = $app->database->query($sql);

		if($res->num_rows > 0) {
			echo '<a id="Follow_Button" class="following" href="#" data-action="unfollow-driver" data-driver="'.$app->user->id.'" data-follow="'.$driver_id.'">';
		}
		else {
			echo '<a id="Follow_Button" href="#" data-action="follow-driver" data-driver="'.$app->user->id.'" data-follow="'.$driver_id.'">';
		}
		echo '<i class="bi bi-bookmark me-1 follow_icon notfollowing"></i><i class="bi bi-bookmark-star me-1 follow_icon following"></i><i class="bi bi-bookmark-plus-fill me-1 follow_icon follow"></i><i class="bi bi-bookmark-dash-fill me-1 follow_icon unfollow"></i></a>';
	}
}
?>

<div class="row g-2 mt-3 pt-2 border-top border-dark">
	<div class="col-2 text-center d-none d-md-block">
<?php
echo '<img class="rounded-circle" src="'.DRIVER_IMG_PATH.$driver_data->photo.'" alt="'.$driver_data->pseudo.' image" />';

$sql = 'SELECT favourite_track, favourite_sessions, total_time, first_session, latest_session, tracks.name AS track_name
		FROM driver_stats_header
			LEFT OUTER JOIN tracks ON driver_stats_header.favourite_track=tracks.id 
		WHERE driver='.$driver_id;
$res = $app->database->query($sql);
$row = $res->fetch_object();
?>
	</div>
	<div class="col-12 col-md-10 pt-md-1">
		<div class="row mb-2 mt-1 mb-md-3 pt-md-1">
			<div class="col-6 col-md-3">Temps Total:</div>
			<div class="col-6 col-md-7"><?php echo fmt_hours($row->total_time);?></div>
		</div>
		<div class="row mb-2 mt-1 mb-md-3 pt-md-1">
			<div class="col-6 col-md-3">Circuit le + Pratiqué:</div>
			<div class="col-6 col-md-7 text-truncate"><?php echo '<a href="track.php?id='.$row->favourite_track.'">'.$row->track_name.'</a> ('.$row->favourite_sessions.' sessions)'; ?></div>
		</div>
		<div class="row mb-2 mt-1 mb-md-3 pt-md-1">
			<div class="col-6 col-md-3">Dernière Session:</div>
			<div class="col-6 col-md-7"><?php echo fmt_date_long($row->latest_session);?></div>
		</div>
		<div class="row mb-2 mt-1 mb-md-3 pt-md-1">
			<div class="col-6 col-md-3">Première Session:</div>
			<div class="col-6 col-md-7"><?php echo fmt_date_long($row->first_session);?></div>
		</div>
	</div>
</div>

<?php
// display compare option
if($app->user !== null && $app->user->id == $driver_id) {

	echo '<form  class="form-inline" role="form" method="get" action="compare.php">
		<div class="row g-3 align-items-center">
		<div class="col-auto">Comparer avec:</div>
		<div class="col-auto"><select class="form-control" name="driver">';

	$sql = 'SELECT follow, pseudo
			FROM follow_drivers
				JOIN drivers ON follow_drivers.follow=drivers.id
			WHERE driver='.$app->user->id.'
			ORDER BY pseudo ASC';
	$res = $app->database->query($sql);
	while(($driver = $res->fetch_object()) !== NULL) {
		echo '<option value="'.$driver->follow.'">'.$driver->pseudo.'</option>';
	}
	echo '</select></div>
		<div class="col-auto"><button type="submit" class="btn btn-outline-primary">Go</button></div>
		</div></form>';
}
?>
<!-- Track stats -->
<h2 class="mt-5 mb-3">Statistiques par Circuit</h2>

<div class="row g-2 mb-2 d-none d-md-flex">
	<div class="col-5"></div>
	<div class="col-2">Temps en Course</div>
	<div class="col-1">Sessions</div>
	<div class="col-2">Meilleur Temps</div>
	<div class="col-2">Meilleure Moyenne</div>
</div>

<?php
$sql = 'SELECT dst.total_time, dst.session_count, dst.best_time, dst.best_average_time, dst.best_time_rank, dst.average_time_rank,
				tracks.id AS track_id, tracks.name AS track_name,
				karts.id AS kart_id, karts.name AS kart_name,
				tsk.driver_count
		FROM driver_stats_track AS dst
			JOIN tracks ON dst.track=tracks.id
			JOIN karts ON dst.kart=karts.id
			JOIN track_stats_kart AS tsk ON tsk.track=tracks.id AND tsk.kart=karts.id
		WHERE driver='.$driver_id.'
		ORDER BY track_name ASC, kart_name ASC';
$res = $app->database->query($sql);

$border_dark = "border-dark";

while(($row = $res->fetch_object()) != null) {
	$params = '{"driver": '.$driver_id.', "track": '.$row->track_id.', "kart": '.$row->kart_id.'}';
	echo '<div class="row g-2 mb-2 mt-1 border-top '.$border_dark.'">
			<div class="col-12 col-md-5"><a href="track.php?id='.$row->track_id.'">'.$row->track_name.'</a> ('.$row->kart_name.')</div>
			<div class="col-6 d-md-none">Temps en Course:</div>
			<div class="col-6 col-md-2">
				<a class="text-success d-none d-md-inline me-2" href="#" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="dp" data-params=\''.$params.'\' data-track_name="'.$row->track_name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-graph-up"></i></a>'.fmt_hours_short($row->total_time).'</div>
			<div class="col-6 d-md-none">Sessions:</div>
			<div class="col-6 col-md-1">'.$row->session_count.'</div>
			<div class="col-6 d-md-none">Meilleur Temps:</div>
			<div class="col-6 col-md-2">
				<a class="text-danger d-none d-md-inline me-2" href="#" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="tbtr" data-params=\''.$params.'\' data-track_name="'.$row->track_name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line"></i></a>'.fmt_millisec($row->best_time).' ('.$row->best_time_rank.'/'.$row->driver_count.')</div>
			<div class="col-6 d-md-none">Meilleure Moyenne:</div>
			<div class="col-6 col-md-2">
				<a class="text-primary d-none d-md-inline me-2" href="#" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="tatr" data-params=\''.$params.'\' data-track_name="'.$row->track_name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line"></i></a>'.fmt_millisec($row->best_average_time).' ('.$row->average_time_rank.'/'.$row->driver_count.')</div>
		</div>';
	$border_dark = "";
}
?>

<!-- Sessions stats -->
<h2 class="mt-5 mb-3">Statistiques par Session</h2>

<div class="row g-2 mb-2 d-none d-md-flex">
	<div class="col-5"></div>
	<div class="col-2">Temps en Course</div>
	<div class="col-1">Tours</div>
	<div class="col-2">Meilleur Temps</div>
	<div class="col-2">Temps Moyen</div>
</div>

<?php
$track_kart = '';

$sql = 'SELECT dss.average_time, dss.best_time, dss.lap_count, dss.total_time, dss.best_time_rank, dss.average_time_rank,
				sessions.driver_count, sessions.date, sessions.id AS session_id,
				tracks.id AS track_id, tracks.name AS track_name,
				karts.name AS kart_name
		FROM driver_stats_session AS dss
			JOIN sessions ON dss.session=sessions.id
			JOIN tracks ON sessions.track=tracks.id
			JOIN karts ON sessions.kart=karts.id
		WHERE dss.driver='.$driver_id.'
		ORDER BY track_name ASC, kart_name ASC, date DESC';
$res = $app->database->query($sql);
$border_dark = "border-dark";

while(($row = $res->fetch_object()) != null) {

	if($row->track_name . $row->kart_name != $track_kart) {
		$track_kart = $row->track_name . $row->kart_name;
		echo '<div class="row g-2 mb-2 mt-1 border-top '.$border_dark.'"><div class="col"><a href="track.php?id='.$row->track_id.'">'.$row->track_name.'</a> ('.$row->kart_name.')</div></div>';
	}

	$params = '{"driver": '.$driver_id.', "date": "'.fmt_date_long($row->date).'", "session": '.$row->session_id.'}';
	echo '<div class="row g-2 mb-2 mt-1 border-top">
			<div class="col-6 d-md-none">Date:</div>
			<div class="col-6 col-md-5 ps-md-4">'.fmt_date_med($row->date, true).'</div>
			<div class="col-2 d-none d-md-flex">
				<a class="text-success me-2" href="#" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="ds" data-params=\''.$params.'\' data-track_name="'.$row->track_name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-graph-up"></i></a>'.fmt_hours_short($row->total_time).'</div>
			<div class="col-1 d-none d-md-flex">'.$row->lap_count.'</div>
			<div class="col-6 d-md-none">Meilleur Temps:</div>
			<div class="col-6 col-md-2">
				<a class="text-danger d-none d-md-inline me-2" href="#" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="sbtr" data-params=\''.$params.'\' data-track_name="'.$row->track_name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line"></i></a>'.fmt_millisec($row->best_time).' ('.$row->best_time_rank.'/'.$row->driver_count.')</div>
			<div class="col-6 d-md-none">Temps Moyen:</div>
			<div class="col-6 col-md-2">
				<a class="text-primary d-none d-md-inline me-2" href="#" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="satr" data-params=\''.$params.'\' data-track_name="'.$row->track_name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line"></i></a>'.fmt_millisec($row->average_time).' ('.$row->average_time_rank.'/'.$row->driver_count.')</div>
		</div>';
	$border_dark = "";
}
?>

<!-- Modal Start -->
<div class="modal" id="Chart_Modal" tabindex="-1" aria-labelledby="Chart_Modal_Label">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" arial-label="Close"></button>
			</div>
			<div class="modal-body">
				<canvas id="Chart_Driver" width="850" height="425"></canvas>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- Modal End -->

<?php
	echo $app->htm_update_footer($template);
?>
<script src="driver.js"></script>
</body>
</html>