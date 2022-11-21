<?php
require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();

if($app->user === null || $app->user->email !== ADM_EMAIL) {
	http_response_code(404);
	exit();
}


define("MAX_DRIVERS", 12);
define("MAX_LAPS", 20);

if(isset($_REQUEST['a']) === true && $_REQUEST['a'] == "validate") {
	// validate date
	$is_valid_date = false;
	$yy = $_REQUEST['years'];
	$mm = $_REQUEST['months'];
	$dd = $_REQUEST['days'];
	if(checkdate($mm, $dd, $yy) == true) {
		$is_valid_date = true;
	}

	// validate time
	$is_valid_time = false;
	if(is_numeric($_REQUEST["hours"]) == true &&
		intval($_REQUEST["hours"]) >= 0 && intval($_REQUEST["hours"]) < 24 &&
		is_numeric($_REQUEST['minutes']) == true &&
		intval($_REQUEST["minutes"]) >= 0 && intval($_REQUEST["minutes"]) < 60
		) {
		$is_valid_time = true;
	}

	// validate track
	$is_valid_track = false;
	$track_id = 0;
	$sql = "select id from tracks where id=".$_REQUEST['track-id'];
	$res = $app->database->query($sql);
	if(($track = $res->fetch_object()) !== null) {
		$is_valid_track = true;
		$track_id = $track->id;
	}

	// validate kart
	$is_valid_kart = false;
	$sql = "select id from karts where id=".$_REQUEST['kart'];
	$res = $app->database->query($sql);
	if(($kart = $res->fetch_object()) !== null) {
		$is_valid_kart = true;
	}

	// validate drivers
	$is_valid_drivers = false;
	$drivers = intval($_REQUEST['drivers']);
	if($drivers > 0 && $drivers <= MAX_DRIVERS) {
		$is_valid_drivers = true;
	}

	// validate laps
	$is_valid_laps = false;
	$laps = intval($_REQUEST['laps']);
	if($laps > 0 && $laps <= MAX_LAPS) {
		$is_valid_laps = true;
	}

	// if all valid
	if($is_valid_date && $is_valid_time && $is_valid_track && $is_valid_kart && $is_valid_drivers && $is_valid_laps)	{
		header("Location: adm_input_times.php?d=".$_REQUEST['years']."-".$_REQUEST['months']."-".$_REQUEST['days']." ".$_REQUEST['hours'].":".$_REQUEST['minutes'].":00&t=".$track->id."&k=".$_REQUEST["kart"]."&dc=".$drivers."&lc=".$laps);
		exit();
	}
}

$template = htm_get_template();
echo $app->htm_update_header($template);

// deal with input errors
if(isset($_REQUEST['a']) === true && $_REQUEST['a'] == "validate") {
	if($is_valid_date != true) {
		$mes = "La date n'est pas valide.";
		echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
	}
	if($is_valid_time != true) {
		$mes = "L'heure n'est pas valide.";
		echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
	}
	if($is_valid_track != true) {
		$mes = "Le circuit n'est pas valide.";
		echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
	}
	if($is_valid_kart != true) {
		$mes = "Le type de kart n'est pas valide.";
		echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
	}
	if($is_valid_drivers != true) {
		$mes = "Le nombre de pilotes n'est pas valide.";
		echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
	}
	if($is_valid_laps != true) {
		$mes = "Le nombre de tours n'est pas valide.";
		echo Html_Get_Message(MESSAGE_TYPE_ERROR, $mes);
	}
}

?>

<h1 class="text-center mt-4 mb-4">Saisie de Session</h1>
<form role="form" action="adm_input_session.php" method="get">
	<input type="hidden" name="a" value="validate" />
	<input type="hidden" name="track-id" id="track-id" value="" />

	<!-- Date -->
	<div class="row mb-3">
		<label for="days" class="col-sm-2 col-form-label">Date</label>
		<div class="col">
			<select name="days" class="form-select">
			<option value="01">1</option>
			<option value="02">2</option>
			<option value="03">3</option>
			<option value="04">4</option>
			<option value="05">5</option>
			<option value="06">6</option>
			<option value="07">7</option>
			<option value="08">8</option>
			<option value="09">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
			<option value="13">13</option>
			<option value="14">14</option>
			<option value="15">15</option>
			<option value="16">16</option>
			<option value="17">17</option>
			<option value="18">18</option>
			<option value="19">19</option>
			<option value="20">20</option>
			<option value="21">21</option>
			<option value="22">22</option>
			<option value="23">23</option>
			<option value="24">24</option>
			<option value="25">25</option>
			<option value="26">26</option>
			<option value="27">27</option>
			<option value="28">28</option>
			<option value="29">29</option>
			<option value="30">30</option>
			<option value="31">31</option>
			</select>
		</div>
		<div class="col">
			<select name="months" class="form-select">
			<option value="01">1</option>
			<option value="02">2</option>
			<option value="03">3</option>
			<option value="04">4</option>
			<option value="05">5</option>
			<option value="06">6</option>
			<option value="07">7</option>
			<option value="08">8</option>
			<option value="09">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
			</select>
		</div>
		<div class="col">
			<select name="years" class="form-select">
			<option value="2000">2000</option>
			<option value="2001">2001</option>
			<option value="2002">2002</option>
			<option value="2003">2003</option>
			<option value="2004">2004</option>
			<option value="2005">2005</option>
			<option value="2006">2006</option>
			<option value="2007">2007</option>
			<option value="2008">2008</option>
			<option value="2009">2009</option>
			<option value="2010">2010</option>
			<option value="2011">2011</option>
			<option value="2012">2012</option>
			<option value="2013">2013</option>
			<option value="2014">2014</option>
			<option value="2015">2015</option>
			<option value="2016">2016</option>
			<option value="2017">2017</option>
			<option value="2018">2018</option>
			<option value="2019">2019</option>
			<option value="2020">2020</option>
			<option value="2021">2021</option>
			<option value="2022">2022</option>
			<option value="2023">2023</option>
			<option value="2024">2024</option>
			<option value="2025">2025</option>
			<option value="2026">2026</option>
			<option value="2027">2027</option>
			<option value="2028">2028</option>
			<option value="2029">2029</option>
			<option value="2030">2030</option>
			</select>
		</div>
	</div>

	<!-- Hours -->
	<div class="row mb-3">
		<label for="hours" class="col-sm-2 col-form-label">Heure</label>
		<div class="col">
			<select name="hours" class="form-select">
			<option value="00">0</option>
			<option value="01">1</option>
			<option value="02">2</option>
			<option value="03">3</option>
			<option value="04">4</option>
			<option value="05">5</option>
			<option value="06">6</option>
			<option value="07">7</option>
			<option value="08">8</option>
			<option value="09">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
			<option value="13">13</option>
			<option value="14">14</option>
			<option value="15">15</option>
			<option value="16">16</option>
			<option value="17">17</option>
			<option value="18">18</option>
			<option value="19">19</option>
			<option value="20">20</option>
			<option value="21">21</option>
			<option value="22">22</option>
			<option value="23">23</option>
		</select></div>

		<!-- Minutes -->
		<div class="col">
			<select name="minutes" class="form-select">
			<option value="00">0</option>
			<option value="01">1</option>
			<option value="02">2</option>
			<option value="03">3</option>
			<option value="04">4</option>
			<option value="05">5</option>
			<option value="06">6</option>
			<option value="07">7</option>
			<option value="08">8</option>
			<option value="09">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
			<option value="13">13</option>
			<option value="14">14</option>
			<option value="15">15</option>
			<option value="16">16</option>
			<option value="17">17</option>
			<option value="18">18</option>
			<option value="19">19</option>
			<option value="20">20</option>
			<option value="21">21</option>
			<option value="22">22</option>
			<option value="23">23</option>
			<option value="24">24</option>
			<option value="25">25</option>
			<option value="26">26</option>
			<option value="27">27</option>
			<option value="28">28</option>
			<option value="29">29</option>
			<option value="30">30</option>
			<option value="31">31</option>
			<option value="32">32</option>
			<option value="33">33</option>
			<option value="34">34</option>
			<option value="35">35</option>
			<option value="36">36</option>
			<option value="37">37</option>
			<option value="38">38</option>
			<option value="39">39</option>
			<option value="40">40</option>
			<option value="41">41</option>
			<option value="42">42</option>
			<option value="43">43</option>
			<option value="44">44</option>
			<option value="45">45</option>
			<option value="46">46</option>
			<option value="47">47</option>
			<option value="48">48</option>
			<option value="49">49</option>
			<option value="50">50</option>
			<option value="51">51</option>
			<option value="52">52</option>
			<option value="53">53</option>
			<option value="54">54</option>
			<option value="55">55</option>
			<option value="56">56</option>
			<option value="57">57</option>
			<option value="58">58</option>
			<option value="59">59</option>
		</select></div>
	</div>

	<!-- Track -->
	<div class="row mb-3">
		<label for="track-name" class="col-sm-2 col-form-label">Circuit</label>
		<div class="col">
			<input autocomplete="off" type="text" class="form-control" name="track-name" id="track-name">
		</div>
	</div>

	<!-- Kart -->
	<div class="row mb-3">
		<label for="track" class="col-sm-2 col-form-label">Kart</label>
		<div class="col"><select name="kart" class="form-select">
<?php
$sql = 'select id, name from karts order by name asc';
$res = $app->database->query($sql);
while(($row = $res->fetch_object()) != NULL) {
echo '<option value="'.$row->id.'">'.$row->name.'</option>';
}
?>
		</select></div>
	</div>

	<!-- Num drivers -->
	<div class="row mb-3">
		<label for="laps" class="col-sm-2 col-form-label">Nombre de Pilotes</label>
		<div class="col"><select name="drivers" class="form-select">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
		</select></div>
	</div>

	<!-- Num laps -->
	<div class="row mb-3">
		<label for="laps" class="col-sm-2 col-form-label">Nombre de Tours</label>
		<div class="col"><select name="laps" class="form-select">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
			<option value="13">13</option>
			<option value="14">14</option>
			<option value="15">15</option>
			<option value="16">16</option>
			<option value="17">17</option>
			<option value="18">18</option>
			<option value="19">19</option>
			<option value="20">20</option>
		</select></div>
	</div>

	<input type="submit" value="Suivant" class="btn btn-outline-primary" />
</form>


<?php
echo $app->htm_update_footer($template);
?>
<script>
const autocomplete_input_session_config = {
	selector: "#track-name",
	placeHolder: "Rechercher un circuit...",
	data: {
		src: async (query) => {
			try {
				// Fetch Data from external Source
				const source = await fetch("api.php?action=autocomplete&param=tracks");
				const data = await source.json();
				return data;
			}
			catch (error) {
				return error;
			}
		},
		keys: ["text"]
	},
	resultItem: {
		highlight: {
			render: true
		}
	}
};
const autocomplete_input_session = new autoComplete(autocomplete_input_session_config);
document.querySelector("#track-name").addEventListener("selection", function (event) {
	// console.log(event);
	event.target.value = event.detail.selection.value.text;
	document.getElementById("track-id").value = event.detail.selection.value.id;
});
</script>
</body>
</html>