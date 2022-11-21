<?php
require_once ('./includes/config.php');
require_once ('./includes/database.php');
require_once ('./includes/app.php');

$app = new App();

if($app->user === null || $app->user->email !== ADM_EMAIL) {
	http_response_code(404);
	exit();
}

$template = htm_get_template();
echo $app->htm_update_header($template);


$sql = 'select name from tracks where id='.$_REQUEST["t"];
$res = $app->database->query($sql);
$track = $res->fetch_object();

$sql = 'select name from karts where id='.$_REQUEST["k"];
$res = $app->database->query($sql);
$kart = $res->fetch_object();
?>

<h1 class="text-center mt-4 mb-4">Session: <?php echo $track->name.', '.$kart->name.', '.$_REQUEST["d"]; ?></h1>
<br>
<br>
<form role="form" action="adm_import.php" method="post">
	<input type="hidden" name="action" value="validate">
	<input type="hidden" name="d" value="<?php echo $_REQUEST["d"]; ?>">
	<input type="hidden" name="t" value="<?php echo $_REQUEST["t"]; ?>">
	<input type="hidden" name="k" value="<?php echo $_REQUEST["k"]; ?>">
	<input type="hidden" name="dc" value="<?php echo $_REQUEST["dc"]; ?>">
	<input type="hidden" name="lc" value="<?php echo $_REQUEST["lc"]; ?>">
<?php

for($driver_index=1; $driver_index<=$_REQUEST["dc"]; $driver_index++) {
	echo '<input type="hidden" name="d'.$driver_index.'-id" id="d'.$driver_index.'-id" />' . PHP_EOL;
}

// divs
// echo '<div class="row">';
// $col_size = floor(12 / ($_REQUEST["dc"] + 1));
// $col_first = 12 - $col_size * ($_REQUEST["dc"] - 1);
// for($driver_index=0; $driver_index<$_REQUEST["dc"]; $driver_index++) {

// 	if($driver_index === 0) {
// 		echo '<div class="col-md-'.$col_first.'">';
// 	}
// 	else {
// 		echo '<div class="col-md-'.$col_size.'">';
// 	}

// 	for($lap_index=0; $lap_index<=$_REQUEST["lc"]; $lap_index++) {

// 		if($lap_index !== 0) {

// 			if($driver_index === 0) {
// 				echo '<div class="form-group form-inline has-feedback">';
// 				echo '<label class="control-label col-md-'.$col_size.'">Lap '.$lap_index.'</label>';
// 			}
// 			else {
// 				echo '<div class="form-group has-feedback">';
// 			}

// 			echo '<input class="form-control lap" type="text" name="d'.$driver_index.'-l'.$lap_index.'" /><span class="glyphicon form-control-feedback"></span>';
// 			echo '</div>';
// 		}
// 		else {
// 			// driver name
// 			if($driver_index === 0) {
// 				echo '<div class="form-group form-inline">';
// 				echo '<label class="control-label">Lap 0</label>';
// 			}
// 			else {
// 				echo '<div class="form-group">';
// 			}
// 			echo '<input class="form-control" type="text" name="d'.$driver_index.'" value="D'.$driver_index.'" />
// 				</div>';
// 		}
// 	}
// 	echo '</div>';
// }
// echo '</div>';

echo '<table class="table">';
for($lap_index=0; $lap_index<=$_REQUEST["lc"]; $lap_index++) {

	echo '<tr>';
	for($driver_index=0; $driver_index<=$_REQUEST["dc"]; $driver_index++) {

		if($lap_index === 0) {
			if($driver_index === 0) {
				// corner
				echo '<td><label class="control-label">Pilotes</label></td>';
			}
			else {
				// driver input
				$tab_index = ($driver_index - 1) * ($_REQUEST["lc"] + 1) + 1;
				echo '<td><input autocomplete="off" class="form-control text-center driver-autocomplete" type="text" id="d'.$driver_index.'" tabindex="'.$tab_index.'" autocomplete="off"/></td>';
			}
		}
		else {
			if($driver_index === 0) {
				// lap label
				echo '<td><label class="control-label">Lap '.$lap_index.'</label></td>';
			}
			else {
				// lap input
				$tab_index = ($driver_index - 1) * ($_REQUEST["lc"] + 1) + $lap_index + 1;
				echo '<td><input class="form-control text-center lap" type="text" name="d'.$driver_index.'-l'.$lap_index.'" tabindex="'.$tab_index.'" /><span class="glyphicon form-control-feedback"></span></td>';
			}
		}
	}
	echo '</tr>';
}
echo '</table>'

?>
		<input type="submit" value="Importer" class="btn btn-primary" />
	</form>
</div>
<br>


<?php
echo $app->htm_update_footer($template);
?>

<script>
// autocomplete
const autocomplete_input_times_config = {
	selector: "",
	placeHolder: "Rechercher un pilote...",
	data: {
		src: async (query) => {
			try {
				// Fetch Data from external Source
				const source = await fetch("api.php?action=autocomplete&param=drivers");
				const data = await source.json();
				return data;
			}
			catch (error) {
				return error;
			}
		},
		keys: ["text"]
	},
	resultItem: { highlight: { render: true } }
};

const elems = document.getElementsByClassName("driver-autocomplete");
const autocompletes = [];
for(elem of elems) {
	autocomplete_input_times_config.selector = `#${elem.id}`;
	autocompletes.push(new autoComplete(autocomplete_input_times_config));
	elem.addEventListener("selection", autocomplete_on_select);
}

function autocomplete_on_select(event) {
	// console.log(event);
	event.target.value = event.detail.selection.value.text;
	document.getElementById(`${event.target.id}-id`).value = event.detail.selection.value.id;
}

// validate times
let template_str = "__:__.__";
// $('input.lap').data("digits", "");
const input_lap_elems = document.getElementsByClassName("lap");
for(let elem of input_lap_elems) {
	elem.dataset.digits = "";
	elem.onfocus = Lap_On_Focus;
	elem.onblur = Lap_On_Blur;
	elem.onkeydown = Lap_On_Keydown;
}

function Lap_On_Focus(evt) {
	const digits = evt.currentTarget.dataset.digits;
	const value = templatize(digits, template_str);
	evt.currentTarget.value = value;
}

function Lap_On_Blur(evt) {
	const value = untemplatize(evt.currentTarget.value);
	evt.currentTarget.value = value;
}

function Lap_On_Keydown(evt) {

	// if tab do nothing
	if(evt.which == 9) return true;
	let digits = evt.currentTarget.dataset.digits;

	// numbers
	if(evt.which > 47 && evt.which < 58 && digits.length < 6) {
		digits += String.fromCharCode(evt.which);
	}
	if(evt.which > 95 && evt.which < 106 && digits.length < 6) {
		digits += (evt.which - 96);
	}

	// backspace and delete
	else if(evt.which == 8 || evt.which == 46) {
		digits = digits.substring(0, digits.length - 1);
	}

	evt.currentTarget.dataset.digits = digits;
	let value_str = templatize(digits, template_str);
	evt.currentTarget.value = value_str;
	validate(evt.currentTarget);

	evt.preventDefault();
}

function templatize(value_str, template_str) {
	let template_idx = template_str.length - 1;
	let value_idx = value_str.length - 1;

	while(template_idx > -1 && value_idx > -1) {
		if(template_str.charAt(template_idx) == "_") {
			template_str = template_str.substring(0, template_idx) + value_str.charAt(value_idx) + template_str.substring(template_idx + 1);
			value_idx--;
		}
		template_idx--;
	}
	return template_str;
}

function untemplatize(value_str) {
	let value_idx = 0;
	let value_count = value_str.length;

	while(value_idx < value_count) {
		if(value_str.charAt(value_idx) == "_") {
			value_str = value_str.substring(0, value_idx) + value_str.substring(value_idx + 1);
			value_count--;
		}
		else if((value_str.charAt(value_idx) == ":" || value_str.charAt(value_idx) == ".") && value_idx == 0) {
			value_str = value_str.substring(0, value_idx) + value_str.substring(value_idx + 1);
			value_count--;
		}
		else {
			value_idx++;
		}
	}
	return value_str;
}

function validate(elem) {
	let value_str = elem.value;

	let value_idx = 0;
	let value_count = value_str.length;

	while(value_idx < value_count) {
		if(value_str.charAt(value_idx) == "_") {
			value_str = value_str.substring(0, value_idx) + "0" + value_str.substring(value_idx + 1);
		}
		value_idx++;
	}

	// check minutes
	let minutes_str = value_str.substring(0, 2)
	let minutes = parseInt(minutes_str);
	if(minutes > 60) {
		elem.nextSibling.classList.remove("glyphicon-ok");
		elem.nextSibling.classList.add("glyphicon-remove");
		return;
	}

	//check seconds
	let seconds_str = value_str.substring(3, 5);
	let seconds = parseInt(seconds_str);
	if(seconds > 60) {
		elem.nextSibling.classList.remove("glyphicon-ok");
		elem.nextSibling.classList.add("glyphicon-remove");
		return;
	}

	elem.nextSibling.classList.remove("glyphicon-remove");
	elem.nextSibling.classList.add("glyphicon-ok");
}
</script>
</body>
</html>