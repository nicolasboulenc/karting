<?php

require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$driver_id = req_assert('driver');

$app = new App();

// check if user authentified
if($app->user === null) {
	http_response_code(404);
	exit();
}

$template = htm_get_template();
echo $app->htm_update_header($template);

// get driver2 data
$sql = 'SELECT id, pseudo FROM drivers WHERE id='.$driver_id;
$res = $app->database->query($sql);
$driver2 = $res->fetch_object();

// get driver1 stats header
$sql = 'SELECT *, tracks.name AS track_name
		FROM driver_stats_header
			JOIN tracks ON driver_stats_header.favourite_track=tracks.id
		WHERE driver='.$app->user->id;
$res = $app->database->query($sql);
$driver1_stats_header = $res->fetch_object();

// get driver2 stats header
$sql = 'SELECT *, tracks.name AS track_name
		FROM driver_stats_header
			JOIN tracks ON driver_stats_header.favourite_track=tracks.id
		WHERE driver='.$driver2->id;
$res = $app->database->query($sql);
$driver2_stats_header = $res->fetch_object();

?>

<table class="table table-responsive">
<thead>
	<tr>
		<th class="col-4">&nbsp;</th>
		<th class="col-4 text-center"><h1><?php echo $app->user->pseudo; ?></h1></th>
		<th class="col-4 text-center"><h1><?php echo $driver2->pseudo; ?></h1></th>
	</tr>
</thead>
<tbody>
<tr>
	<td class="col-4">Temps Total en Course:</td>
	<td class="col-4 text-center"><?php echo fmt_hours($driver1_stats_header->total_time);?></td>
	<td class="col-4 text-center"><?php echo fmt_hours($driver2_stats_header->total_time);?></td>
</tr>
<tr>
	<td class="col-4">Circuit le Plus Pratique:</td>
	<?php
		echo '<td class="col-4 text-center"><a href="track.php?id='.$driver1_stats_header->favourite_track.'">'.$driver1_stats_header->track_name.'</a> ('.$driver1_stats_header->favourite_sessions.' sessions)</td>';
		echo '<td class="col-4 text-center"><a href="track.php?id='.$driver2_stats_header->favourite_track.'">'.$driver2_stats_header->track_name.'</a> ('.$driver2_stats_header->favourite_sessions.' sessions)</td>';
	?>
</tr>
<tr>
	<td class="col-4">Derniere Session en Date:</td>
	<td class="col-4 text-center"><?php echo fmt_date_long($driver1_stats_header->latest_session);?></td>
	<td class="col-4 text-center"><?php echo fmt_date_long($driver2_stats_header->latest_session);?></td>
</tr>
<tr>
	<td class="col-4">Premiere Session en Date:</td>
	<td class="col-4 text-center"><?php echo fmt_date_long($driver1_stats_header->first_session);?></td>
	<td class="col-4 text-center"><?php echo fmt_date_long($driver2_stats_header->first_session);?></td>
</tr>
</tbody>
</table>

<table class="table table-responsive">
<thead>
	<tr>
		<th class="col-4" style="vertical-align: bottom"><h2>Statistiques par Circuit</h2></th>
		<th class="col-2 text-center">Meilleur Temps</th>
		<th class="col-2 text-center">Meilleure Moyenne</th>
		<th class="col-2 text-center">Meilleur Temps</th>
		<th class="col-2 text-center">Meilleure Moyenne</th>
	</tr>
</thead>
<tbody>

<?php
// get track stats for driver1 and driver2
$sql = 'SELECT driver, track AS track_id, tracks.name AS track_name, karts.name AS kart_name, best_average_time, best_time
		FROM driver_stats_track
			JOIN tracks ON track=tracks.id
			JOIN karts ON kart=karts.id
		WHERE driver='.$app->user->id.' OR driver='.$driver2->id.'
		ORDER BY tracks.name ASC, karts.name ASC';
$res = $app->database->query($sql);

$driver1_track_stats = array();
$driver2_track_stats = array();
$track_kart_combos = array();

while(($stats = $res->fetch_object()) != null) {
	if($stats->driver == $app->user->id) {
		$driver1_track_stats[$stats->track_name.$stats->kart_name] = $stats;
	}
	else {
		$driver2_track_stats[$stats->track_name.$stats->kart_name] = $stats;
	}
	$track_kart_combos[$stats->track_name.$stats->kart_name] = (object)array('track_id'=>$stats->track_id, 'track_name'=>$stats->track_name, 'kart_name'=>$stats->kart_name);
}

ksort($track_kart_combos);
$n = count($track_kart_combos);
$empty_stats = (object)array('best_average_time'=>0,'best_time'=>0);

foreach($track_kart_combos as $k=>$v) {
	if(array_key_exists($k, $driver1_track_stats) == true) {
		$stats1 = $driver1_track_stats[$k];
	}
	else {
		$stats1 = $empty_stats;
	}

	if(array_key_exists($k, $driver2_track_stats) == true) {
		$stats2 = $driver2_track_stats[$k];
	}
	else {
		$stats2 = $empty_stats;
	}

	$best_time1_gliph = "";
	$best_time2_gliph = "";
	$average_time1_gliph = "";
	$average_time2_gliph = "";

	if($stats1->best_time != 0 && $stats2->best_time != 0 && $stats1->best_average_time != 0 && $stats2->best_average_time != 0) {
		if($stats1->best_time < $stats2->best_time) {
			$best_time1_gliph = "success";
		}
		else {
			$best_time2_gliph = "success";
		}

		if($stats1->best_average_time < $stats2->best_average_time) {
			$average_time1_gliph = "success";
		}
		else {
			$average_time2_gliph = "success";
		}
	}

	echo '<tr>';
	echo '<td class="col-4" style="vertical-align: middle"><a href="track.php?id='.$track_kart_combos[$k]->track_id.'">'.$track_kart_combos[$k]->track_name.'</a> ('.$track_kart_combos[$k]->kart_name.')</td>';
	echo '<td class="col-2 text-center table-'.$best_time1_gliph.'">'.fmt_millisec($stats1->best_time).'</td>';
	echo '<td class="col-2 text-center table-'.$average_time1_gliph.'">'.fmt_millisec($stats1->best_average_time).'</td>';
	echo '<td class="col-2 text-center table-'.$best_time2_gliph.'">'.fmt_millisec($stats2->best_time).'</td>';
	echo '<td class="col-2 text-center table-'.$average_time2_gliph.'">'.fmt_millisec($stats2->best_average_time).'</td>';
	echo '</tr>';
}
?>
</tbody>
</table>

<table class="table table-responsive">
<thead>
	<tr>
		<th class="col-4"><h2>Statistiques par Session</h2></th>
		<th class="col-2 text-center">Meilleur Temps</th>
		<th class="col-2 text-center">Temps Moyen</th>
		<th class="col-2 text-center">Meilleur Temps</th>
		<th class="col-2 text-center">Temps Moyen</th>
	</tr>
</thead>
<tbody>
<?php

// get track stats for driver1 and driver2
$sql = 'SELECT driver, track AS track_id, tracks.name AS track_name, karts.name AS kart_name, date AS session_date,	average_time, driver_stats_session.best_time
		FROM driver_stats_session
			JOIN sessions ON session=sessions.id
			JOIN tracks ON track=tracks.id
			JOIN karts ON kart=karts.id
		WHERE driver='.$app->user->id.' OR driver='.$driver2->id.'
		ORDER BY tracks.name ASC, karts.name ASC, date DESC';
$res = $app->database->query($sql);

$driver1_session_stats = array();
$driver2_session_stats = array();
$track_kart_date_combos = array();
while(($stats = $res->fetch_object()) != null) {
	if($stats->driver == $app->user->id) {
		$driver1_session_stats[$stats->track_name.$stats->kart_name.$stats->session_date] = $stats;
	}
	else {
		$driver2_session_stats[$stats->track_name.$stats->kart_name.$stats->session_date] = $stats;
	}
	$track_kart_date_combos[$stats->track_name.$stats->kart_name.$stats->session_date] = (object)array('track_id'=>$stats->track_id, 'track_name'=>$stats->track_name, 'kart_name'=>$stats->kart_name, 'session_date'=>$stats->session_date);
}

ksort($track_kart_date_combos);
$n = count($track_kart_date_combos);
$current_track_kart = '';

foreach($track_kart_date_combos as $k=>$v) {

	if(array_key_exists($k, $driver1_session_stats) == true && array_key_exists($k, $driver2_session_stats) == true) {

		if($track_kart_date_combos[$k]->track_name.$track_kart_date_combos[$k]->kart_name != $current_track_kart) {
			echo '<tr>';
			echo '<td class="col-12" colspan="5"><a href="track.php?id='.$track_kart_date_combos[$k]->track_id.'">'.$track_kart_date_combos[$k]->track_name.'</a> ('.$track_kart_date_combos[$k]->kart_name.')</td>';
			echo '</tr>';
			$current_track_kart = $track_kart_date_combos[$k]->track_name.$track_kart_date_combos[$k]->kart_name;
		}

		$stats1 = $driver1_session_stats[$k];
		$stats2 = $driver2_session_stats[$k];

		if($stats1->best_time < $stats2->best_time)	{
			$best_time1_gliph = "success";
			$best_time2_gliph = "";
		}
		else {
			$best_time1_gliph = "";
			$best_time2_gliph = "success";
		}

		if($stats1->average_time < $stats2->average_time) {
			$average_time1_gliph = "success";
			$average_time2_gliph = "";
		}
		else {
			$average_time1_gliph = "";
			$average_time2_gliph = "success";
		}

		echo '<tr>';
		echo '<td class="col-4" style="text-indent: 30px">'.fmt_date_med($track_kart_date_combos[$k]->session_date).'</td>';
		echo '<td class="col-2 text-center table-'.$best_time1_gliph.'">'.fmt_millisec($stats1->best_time).'</td>';
		echo '<td class="col-2 text-center table-'.$average_time1_gliph.'">'.fmt_millisec($stats1->average_time).'</td>';
		echo '<td class="col-2 text-center table-'.$best_time2_gliph.'">'.fmt_millisec($stats2->best_time).'</td>';
		echo '<td class="col-2 text-center table-'.$average_time2_gliph.'">'.fmt_millisec($stats2->average_time).'</td>';
		echo '</tr>';
	}
}
?>
</tbody>
</table>

<?php
	echo $app->htm_update_footer($template);
?>

</body>
</html>