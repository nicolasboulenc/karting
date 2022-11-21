<?php

require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$track_id = req_assert("id");

$app = new App();

$template = htm_get_template();
echo $app->htm_update_header($template);

$sql = 'SELECT `name`, `lon_lat` FROM tracks WHERE id='.$track_id;
$res = $app->database->query($sql);
$track_data = $res->fetch_object();

echo '<br><h1 class="d-inline me-2">'.$track_data->name.'</h1>';

// show follow/unfollow icon
if($app->user !== null) {
	$sql = 'SELECT follow FROM follow_tracks WHERE driver='.$app->user->id.' AND follow='.$track_id;
	$res = $app->database->query($sql);


	if($res->num_rows > 0) {
		echo '<a id="Follow_Button" class="following" href="#" data-action="unfollow-track" data-driver="'.$app->user->id.'" data-follow="'.$track_id.'">';
	}
	else {
		echo '<a id="Follow_Button" href="#" data-action="follow-track" data-driver="'.$app->user->id.'" data-follow="'.$track_id.'">';
	}
	echo '<i class="bi bi-bookmark me-1 follow_icon notfollowing"></i><i class="bi bi-bookmark-star me-1 follow_icon following"></i><i class="bi bi-bookmark-plus-fill me-1 follow_icon follow"></i><i class="bi bi-bookmark-dash-fill me-1 follow_icon unfollow"></i></a>';
}

$sql = 'SELECT tsh.total_time, tsh.latest_session, tsh.first_session
		FROM track_stats_header AS tsh
		WHERE tsh.track='.$track_id;
$res = $app->database->query($sql);
$stats_header = $res->fetch_object();
?>

<div class="row g-2 mt-3 pt-3 border-top border-dark">
	<div class="col">
		<iframe class="rounded" width="100%" style="height: 25vh" frameborder="0" src="https://www.google.com/maps/embed/v1/view?key=<?php echo MAP_KEY ?>&center=<?php echo $track_data->lon_lat;?>&zoom=16&maptype=satellite" allowfullscreen></iframe>
	</div>
</div>

<div class="row g-2 mb-2 mt-1">
	<div class="col-6 col-md-3">Temps Total:</div>
	<div class="col col-md-3"><?php if($stats_header != null) echo fmt_hours($stats_header->total_time);?></div>
</div>
<div class="row g-2 mb-2">
	<div class="col-6 col-md-3">Première Session:</div>
	<div class="col col-md-3"><?php if($stats_header != null) echo fmt_date_long($stats_header->first_session); ?></div>
</div>
<div id="history_anchor" class="row g-2 mb-2">
	<div class="col-6 col-md-3">Dernière Session:</div>
	<div class="col col-md-3"><?php if($stats_header != null) echo fmt_date_long($stats_header->latest_session); ?></div>
</div>

<h2 class="mt-5 mb-3">Statistiques par Kart</h2>

<div class="row g-2 mb-2 d-none d-md-flex">
	<div class="col"></div>
	<div class="col">Temps en Course</div>
	<div class="col">Meilleur Temps</div>
	<div class="col">Meilleure Moyenne</div>
</div>

<?php
$sql = 'SELECT tsk.total_time, tsk.session_count, tsk.best_time, tsk.best_time_driver, tsk.best_average_time, tsk.best_average_driver, karts.id AS kart_id, karts.name AS kart_name, drivers_session.pseudo AS driver_session_pseudo, drivers_time.pseudo AS driver_time_pseudo
		FROM track_stats_kart AS tsk
			JOIN karts ON tsk.kart=karts.id
			JOIN drivers AS drivers_session ON tsk.best_average_driver=drivers_session.id
			JOIN drivers AS drivers_time ON tsk.best_time_driver=drivers_time.id
		WHERE track='.$track_id.'
		ORDER BY kart DESC';
$res = $app->database->query($sql);

$border_dark = "border-dark";
$default = ' id="Track_Kart_Default"';
while(($row = $res->fetch_object()) != null) {
	$params = '{"track": '.$track_id.', "kart": '.$row->kart_id.'}';
	echo '<div class="row g-2 mb-2 mt-1 border-top '.$border_dark.'">
			<div class="col-3 d-none d-md-block">'.$row->kart_name.'</div>
			<div class="col-12 d-md-none border-bottom pb-2 fw-bold">'.$row->kart_name.'</div>
			<div class="col-6 d-md-none">Temps en Course:</div>
			<div class="col col-md-3 text-truncate">
				<a'.$default.' class="text-success d-none d-md-inline history_button me-2" href="#history_anchor" data-track="'.$track_id.'" data-kart="'.$row->kart_id.'" data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-graph-up" data-bs-toggle="tooltip" data-bs-placement="top" title="Historique meilleurs temps"></i></a>'.fmt_hours_short($row->total_time).' ('.$row->session_count.')</div>
			<div class="col-6 d-md-none">Meilleur Temps:</div>
			<div class="col col-md-3 text-truncate">
				<a href="#" class="text-danger d-none d-md-inline me-2" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="tbtr" data-params=\''.$params.'\' data-track_name="'.$track_data->name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Classement meilleurs temps"></i></a>'.fmt_millisec($row->best_time).' (<a href="driver.php?id='.$row->best_time_driver.'">'.$row->driver_time_pseudo.'</a>)</div>
			<div class="col-6 d-md-none">Meilleure Moyenne:</div>
			<div class="col col-md-3 text-truncate">
				<a href="#" class="text-primary d-none d-md-inline me-2" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="tatr" data-params=\''.$params.'\' data-track_name="'.$track_data->name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Classement temps moyens"></i></a>'.fmt_millisec($row->best_average_time).' (<a href="driver.php?id='.$row->best_average_driver.'">'.$row->driver_session_pseudo.'</a>)</div>
		</div>';
	$border_dark = "";
	$default = "";
}
?>


<h2 class="mt-5 mb-3 d-none d-md-block">Historique Meilleur Temps</h2>

<div class="row g-2 mb-2 d-none d-md-flex">
	<canvas id="Chart_Best_Time_History" height="400" width="1140"></canvas>
</div>

<h2 class="mt-5 mb-3">Statistiques par Session</h2>

<div class="row g-2 mb-2">
	<div class="col-3 d-none d-md-block"></div>
	<div class="col-3 d-none d-md-block">Pilotes</div>
	<div class="col-3 d-none d-md-block">Meilleur Temps</div>
	<div class="col-3 d-none d-md-block">Meilleure Moyenne</div>
</div>

<?php

$kart = '';

$sql = 'SELECT sessions.id, sessions.driver_count, karts.name AS kart_name, date, dss1.best_time, dss1.driver AS best_time_driver, bt_drivers.pseudo AS bt_driver, dss2.average_time AS best_average_time, dss2.driver AS best_average_time_driver, at_drivers.pseudo AS at_driver
		FROM sessions
			JOIN karts ON sessions.kart=karts.id
			JOIN driver_stats_session AS dss1 ON sessions.id=dss1.session AND dss1.best_time_rank=1
			JOIN driver_stats_session AS dss2 ON sessions.id=dss2.session AND dss2.average_time_rank=1
			JOIN drivers AS bt_drivers ON dss1.driver=bt_drivers.id
			JOIN drivers AS at_drivers ON dss2.driver=at_drivers.id
		WHERE track='.$track_id.'
		ORDER BY kart_name ASC, date DESC';
$res = $app->database->query($sql);
$border_dark = "border-dark";

while(($row = $res->fetch_object()) != null) {

	if($row->kart_name != $kart) {
		$kart = $row->kart_name;
		echo '<div class="col-12 border-top '.$border_dark.' pt-2 pb-2 fw-bold">'.$row->kart_name.'</div>';
	}
	$params = '{"date": "'.fmt_date_long($row->date).'", "track": '.$track_id.', "session": '.$row->id.'}';
	echo '<div class="row g-2 mb-2 mt-1 border-top">
			<div class="col-6 d-md-none align-middle">Date:</div>
			<div class="col col-md-3 ps-md-4">'.fmt_date_med($row->date, true).'</div>
			<div class="col col-md-3 d-none d-md-block">
				<a class="text-success me-2" href="#" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="ts" data-params=\''.$params.'\' data-track_name="'.$track_data->name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-graph-up"></i></a>'.$row->driver_count.'</div>
			<div class="col-6 d-md-none align-middle">Meilleur Temps:</div>
			<div class="col col-md-3 text-truncate">
				<a href="#" class="text-danger d-none d-md-inline me-2" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="sbtr" data-params=\''.$params.'\' data-track_name="'.$track_data->name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line"></i></a>'.fmt_millisec($row->best_time).' (<a href="driver.php?id='.$row->best_time_driver.'">'.$row->bt_driver.'</a>)</div>
			<div class="col-6 d-md-none align-middle">Meilleure Moyenne:</div>
			<div class="col col-md-3 text-truncate">
				<a href="#" class="text-primary d-none d-md-inline me-2" data-bs-toggle="modal" data-bs-target="#Chart_Modal" data-type="satr" data-params=\''.$params.'\' data-track_name="'.$track_data->name.'"  data-kart_name="'.$row->kart_name.'">
				<i class="bi bi-bar-chart-line"></i></a>'.fmt_millisec($row->best_average_time).' (<a href="driver.php?id='.$row->best_average_time_driver.'">'.$row->at_driver.'</a>)</div>
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
				<canvas id="Chart_Track" width="850" height="425"></canvas>
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

<script src="track.js"></script>

</body>
</html>