<?php

//===============================================================================================================

function Session_Stats_Update($session_id) {

	global $app;

	// $sql = 'SELECT COUNT(DISTINCT(driver)) AS driver_count
	// 		FROM times
	// 		WHERE session='.$session_id;
	// $res = $app->database->query($sql);
	// $row = $res->fetch_object();

	$sql = 'UPDATE sessions SET
				driver_count=(SELECT COUNT(DISTINCT(driver)) AS driver_count
								FROM times
								WHERE session='.$session_id.')
			WHERE id='.$session_id;
	$app->database->query($sql);
}

//===============================================================================================================

function Driver_Ranking_Update($session_id) {

	global $app;

	// update best time rank
	$sql = "SELECT driver, MIN(time) AS best_time
			FROM times
			WHERE session=".$session_id."
			GROUP BY driver
			ORDER BY best_time ASC";
	$res = $app->database->query($sql);
	$rank = 1;
	while(($row = $res->fetch_object()) !== null) {

		$sql = "UPDATE driver_stats_session SET best_time_rank=".$rank."
				WHERE session=".$session_id." AND driver=".$row->driver;
		$app->database->query($sql);
		$rank++;
	}

	// update average time rank
	$sql = "SELECT driver, AVG(time) AS average_time
			FROM times
			WHERE session=".$session_id."
			GROUP BY driver
			ORDER BY average_time ASC";
	$res = $app->database->query($sql);
	$rank = 1;
	while(($row = $res->fetch_object()) !== null) {

		$sql = "UPDATE driver_stats_session SET average_time_rank=".$rank."
				WHERE session=".$session_id." AND driver=".$row->driver;
		$app->database->query($sql);
		$rank++;
	}
}

//===============================================================================================================

function Driver_Stats_Session_Update($driver_id, $session_id)
{
	global $app;

	// create AND/or update driver_stats_session
	$sql = "SELECT driver FROM driver_stats_session WHERE session=".$session_id." AND driver=".$driver_id;
	$res = $app->database->query($sql);
	if($res->num_rows === 0) {
		$sql = 'INSERT INTO driver_stats_session (`session`, `driver`) VALUES ('.$session_id.', '.$driver_id.')';
		$app->database->query($sql);
	}

	$sql = 'SELECT SUM(time) AS total_time, COUNT(time) AS lap_count, AVG(time) AS average_time, MIN(time) AS best_time
			FROM times
			WHERE driver='.$driver_id.' AND session='.$session_id;
	$res = $app->database->query($sql);
	$stats = $res->fetch_object();

	$sql = 'UPDATE driver_stats_session SET
				total_time = '.$stats->total_time.',
				lap_count = '.$stats->lap_count.',
				average_time = '.$stats->average_time.',
				best_time = '.$stats->best_time.'
			WHERE driver='.$driver_id.' AND session='.$session_id;
	$app->database->query($sql);
}

//===============================================================================================================

function Driver_Stats_Track_Update($driver_id, $track_id, $kart_id) {

	global $app;

	// create AND/or update driver_stats_track
	$sql = 'SELECT driver FROM driver_stats_track WHERE driver='.$driver_id.' AND track='.$track_id.' AND kart='.$kart_id;
	$res = $app->database->query($sql);
	if($res->num_rows === 0) {
		$sql = 'INSERT INTO driver_stats_track (`driver`, `track`, `kart`) VALUES ('.$driver_id.', '.$track_id.', "'.$kart_id.'")';
		$app->database->query($sql);
	}

	$sql = 'SELECT sum(driver_stats_session.total_time) AS rtime, count(driver_stats_session.driver) AS rsessions, min(average_time) AS best_average_time, min(driver_stats_session.best_time) AS besttime
			FROM driver_stats_session
				JOIN sessions ON driver_stats_session.session=sessions.id
			WHERE driver='.$driver_id.' AND track='.$track_id.' AND kart='.$kart_id;
	$res = $app->database->query($sql);
	$stats = $res->fetch_object();

	$sql = 'UPDATE driver_stats_track SET
				total_time='.$stats->rtime.',
				session_count='.$stats->rsessions.',
				best_average_time='.$stats->best_average_time.',
				best_time='.$stats->besttime.'
			WHERE driver='.$driver_id.' AND track='.$track_id.' AND kart='.$kart_id;
	$app->database->query($sql);
}

//===============================================================================================================

function Driver_Stats_Header_Update($driver_id) {

	global $app;

	// create AND/or update driver_stats_header
	$sql = 'SELECT driver FROM driver_stats_header WHERE driver='.$driver_id;
	$res = $app->database->query($sql);
	if($res->num_rows === 0) {
		$sql = 'insert into driver_stats_header (`driver`, `latest_session`, `first_session`) values ('.$driver_id.', "1970-01-01 00:00:00", "1970-01-01 00:00:00")';
		$app->database->query($sql);
	}

	$sql = 'SELECT tracks.id as track_id, sum(session_count) as num_sessions
			FROM driver_stats_track
				JOIN tracks on driver_stats_track.track=tracks.id
			WHERE driver='.$driver_id.'
			group by track
			ORDER BY num_sessions desc
			LIMIT 1';
	$res = $app->database->query($sql);
	$fav = $res->fetch_object();

	$sql = 'UPDATE driver_stats_header SET
				total_time=(SELECT sum(total_time) FROM driver_stats_track WHERE driver='.$driver_id.'),
				favourite_track='.$fav->track_id.',
				favourite_sessions='.$fav->num_sessions.',
				latest_session=(SELECT date FROM driver_stats_session JOIN sessions ON driver_stats_session.session=sessions.id WHERE driver='.$driver_id.' ORDER BY date DESC LIMIT 1),
				first_session=(SELECT date FROM driver_stats_session JOIN sessions ON driver_stats_session.session=sessions.id WHERE driver='.$driver_id.' ORDER BY date ASC LIMIT 1)
	 		WHERE driver='.$driver_id;
	$app->database->query($sql);
}

//===============================================================================================================

function Track_Driver_Ranking_Update($track_id, $kart_id) {

	global $app;

	// update best time rank
	$sql = "SELECT dss.driver, MIN(dss.best_time) AS best_time2
			FROM driver_stats_session AS dss
				JOIN sessions ON sessions.id=session
			WHERE track=".$track_id." AND kart=".$kart_id."
			GROUP BY dss.driver
			ORDER BY best_time2 ASC";
	$res = $app->database->query($sql);
	$rank = 1;
	while(($row = $res->fetch_object()) !== null) {

		$sql = "UPDATE driver_stats_track SET best_time_rank=".$rank."
				WHERE track=".$track_id." AND kart=".$kart_id." AND driver=".$row->driver;
		$app->database->query($sql);
		$rank++;
	}

	// update average time rank
	$sql = "SELECT dss.driver, MIN(dss.average_time) AS average_time2
			FROM driver_stats_session AS dss
				JOIN sessions ON sessions.id=session
			WHERE track=".$track_id." AND kart=".$kart_id."
			GROUP BY dss.driver
			ORDER BY average_time2 ASC";
	$res = $app->database->query($sql);
	$rank = 1;
	while(($row = $res->fetch_object()) !== null) {

		$sql = "UPDATE driver_stats_track SET average_time_rank=".$rank."
				WHERE track=".$track_id." AND kart=".$kart_id." AND driver=".$row->driver;
		$app->database->query($sql);
		$rank++;
	}
}

//===============================================================================================================

function Track_Stats_Kart_Update($track_id, $kart_id) {

	global $app;

	// create AND/or update track_stats_kart
	$sql = 'SELECT track FROM track_stats_kart WHERE track='.$track_id.' AND kart='.$kart_id;
	$res = $app->database->query($sql);
	if($res->num_rows === 0) {
		// insert stats record
		$sql = 'INSERT INTO track_stats_kart (track, kart) VALUES ('.$track_id.', '.$kart_id.')';
		$app->database->query($sql);
	}

	$sql = 'SELECT SUM(time) as total_time FROM times JOIN sessions ON times.session=sessions.id WHERE sessions.track='.$track_id.' AND sessions.kart='.$kart_id;
	$res = $app->database->query($sql);
	$total_time = $res->fetch_object();

	$sql = 'SELECT driver, MIN(best_average_time) AS time
			FROM driver_stats_track
			WHERE track='.$track_id.' AND kart='.$kart_id.'
			GROUP BY driver
			ORDER BY time ASC
			LIMIT 1';
	$res = $app->database->query($sql);
	$best_average_stats = $res->fetch_object();

	$sql = 'SELECT driver, MIN(best_time) AS time
			FROM driver_stats_track
			WHERE track='.$track_id.' AND kart='.$kart_id.'
			GROUP BY driver
			ORDER BY time ASC
			LIMIT 1';
	$res = $app->database->query($sql);
	$best_time_stats = $res->fetch_object();

	$sql = 'UPDATE track_stats_kart SET
				total_time = '.$total_time->total_time.',
				session_count = (SELECT COUNT(id) FROM sessions WHERE track='.$track_id.' AND kart='.$kart_id.'),
				driver_count = (SELECT COUNT(DISTINCT(driver)) FROM times JOIN sessions ON sessions.id=times.session WHERE track='.$track_id.' AND kart='.$kart_id.'),
				best_average_time = '.$best_average_stats->time.',
				best_average_driver = '.$best_average_stats->driver.',
				best_time = '.$best_time_stats->time.',
				best_time_driver = '.$best_time_stats->driver.'
			WHERE track='.$track_id.' AND kart='.$kart_id;
	$app->database->query($sql);
}

//===============================================================================================================

function Track_Stats_Header_Update($track_id) {

	global $app;

	// create AND/or update track_stats_header
	$sql = 'SELECT track FROM track_stats_header WHERE track='.$track_id;
	$res = $app->database->query($sql);
	if($res->num_rows === 0) {
		// insert stats record
		$sql = 'INSERT INTO track_stats_header (`track`, `latest_session`, `first_session`) VALUES ('.$track_id.', "1970-01-01 00:00:00", "1970-01-01 00:00:00")';
		$app->database->query($sql);
	}

	$sql = 'SELECT SUM(total_time) AS racetime, SUM(session_count) AS session_count
			FROM track_stats_kart
			WHERE track='.$track_id;
	$res = $app->database->query($sql);
	$race_stats = $res->fetch_object();

	$sql = 'SELECT date FROM sessions WHERE track='.$track_id.' ORDER BY date desc LIMIT 1';
	$res = $app->database->query($sql);
	$latest_session = (object)array("date"=>null);
	if($res->num_rows > 0) {
		$latest_session = $res->fetch_object();
	}

	$sql = 'SELECT date FROM sessions WHERE track='.$track_id.' ORDER BY date ASC LIMIT 1';
	$res = $app->database->query($sql);
	$first_session = (object)array("date"=>null);
	if($res->num_rows > 0) {
		$first_session = $res->fetch_object();
	}

	// for tracks with no sessions/times
	if($race_stats->racetime === null) $race_stats->racetime = 0;
	if($race_stats->session_count === null) $race_stats->session_count = 0;
	if($latest_session->date === null) $latest_session->date = "1970-01-01 00:00:00";
	if($first_session->date === null) $first_session->date = "1970-01-01 00:00:00";

	$sql = 'UPDATE track_stats_header SET
				total_time='.$race_stats->racetime.',
				session_count='.$race_stats->session_count.',
				latest_session="'.$latest_session->date.'",
				first_session="'.$first_session->date.'"
			WHERE track='.$track_id;
	$app->database->query($sql);
}

?>