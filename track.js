window.onload = init;

let chart = null;
let chart_ctx = null;
let chart_history = null;
let chart_history_ctx = null;

let track_id = 0;
let kart_id = 0;

//=============================================================================================================================

function init() {

	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
	var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
	  return new bootstrap.Tooltip(tooltipTriggerEl)
	});


	chart_history_ctx = document.getElementById("Chart_Best_Time_History").getContext("2d");

	const default_data = document.getElementById('Track_Kart_Default');
	track_id = default_data.dataset.track;
	kart_id = default_data.dataset.kart;
	kart_name = default_data.dataset.kart_name;

	Chart_Track_Best_Time_History({ "track": track_id, "kart": kart_id, "kart_name": kart_name })
		.then(c => { chart_history = c; });

	const follow_button = document.getElementById('Follow_Button');
	if(follow_button !== null) {
		follow_button.onclick = follow;
	}

	const buttons = document.getElementsByClassName("history_button");
	for(button of buttons) {
		button.onclick = History_Button_On_Click;
	}

	const chart_modal = document.getElementById('Chart_Modal');
	chart_modal.addEventListener('show.bs.modal', modal_show);
	chart_modal.addEventListener('hidden.bs.modal', modal_hide);

	chart_ctx = document.getElementById("Chart_Track").getContext("2d");
}

//=============================================================================================================================

function History_Button_On_Click(evt) {

	const target = evt.currentTarget;
	if(chart_history !== null) {
		chart_history.destroy();
	}

	Chart_Track_Best_Time_History({ "track": track_id, "kart": target.dataset.kart, "kart_name": target.dataset.kart_name })
		.then(c => { chart_history = c; });
}

//=============================================================================================================================


function follow(evt) {

	const target = evt.currentTarget;
	const params = target.dataset.action;
	const action = params.split("-")[0];
	const driver = target.dataset.driver;
	const follow = target.dataset.follow;

	let which = params.split("-")[1];
	if(which === "driver")
		which = 'df';
	else
		which = 'tf';

	return fetch(`api.php?action=${action}&d=${driver}&${which}=${follow}`)
		.then(Fetch_Handle_Errors)
		.then(function (response) { return response.json(); })
		.then(function (data) {

			document.getElementById("Follow_Button").classList.toggle('following');

			if(action === 'follow') {

				let node = '';
				if(which === 'df') {
					target.dataset.action = 'unfollow-driver';
					node = `<a id="d${follow}" class="nav-link" href="driver.php?id=${follow}">@${data.name}</a>`;
				}
				else {
					target.dataset.action = 'unfollow-track';
					node = `<a id="t${follow}" class="nav-link" href="track.php?id=${follow}">@${data.name}</a>`;
				}

				document.getElementById("user_follow_elems").innerHTML += node;
			}
			else {
				if(which === 'df') {
					target.dataset.action = 'follow-driver';
					document.getElementById(`d${follow}`).remove();
				}
				else {
					target.dataset.action = 'follow-track';
					document.getElementById(`t${follow}`).remove();
				}
			}
		});
}

//=============================================================================================================================

function modal_show(evt) {

	const button = evt.relatedTarget;
	const params = JSON.parse(button.dataset.params);

	if(button.dataset.type === "tbtr") {
		// track kart best time ranking
		document.querySelector(".modal-title").innerHTML = `Classement Meilleurs Temps - ${button.dataset.track_name} (${button.dataset.kart_name})`;
		Chart_Track_Best_Time_Ranking(params)
			.then(c => { chart = c; });
	}
	else if(button.dataset.type === "tatr") {
		// track kart best time ranking
		document.querySelector(".modal-title").innerHTML = `Classement Temps Moyens - ${button.dataset.track_name} (${button.dataset.kart_name})`;
		Chart_Track_Average_Time_Ranking(params)
			.then(c => { chart = c; });
	}
	else if(button.dataset.type === "ts") {
		// drivers session
		document.querySelector(".modal-title").innerHTML = `Session ${params.date} - ${button.dataset.track_name} (${button.dataset.kart_name})`;
		Chart_Track_Session(params)
			.then(c => { chart = c; });
	}
	else if(button.dataset.type === "sbtr") {
		// session best time ranking
		document.querySelector(".modal-title").innerHTML = `Classement Meilleurs Temps ${params.date} - ${button.dataset.track_name} (${button.dataset.kart_name})`;
		Chart_Session_Best_Time_Ranking(params)
			.then(c => { chart = c; });
	}
	else if(button.dataset.type === "satr") {
		// session average time ranking
		document.querySelector(".modal-title").innerHTML = `Classement Temps Moyens ${params.date} - ${button.dataset.track_name} (${button.dataset.kart_name})`;
		Chart_Session_Average_Time_Ranking(params)
			.then(c => { chart = c; });
	}
}

//=============================================================================================================================

function modal_hide(evt) {
	if(chart != null) chart.destroy();
	chart = null;
}
//=============================================================================================================================

const Chart_Track_Best_Time_History = function(params) {

	return fetch(`api.php?action=chart&c=tbth&t=${params.track}&k=${params.kart}`)
		.then(Fetch_Handle_Errors)
		.then(function (response) { return response.json(); })
		.then(function (data) {

			const labels = [];
			const best_time_data = [];

			let session_idx = 0;
			const session_count = data.length;
			while(session_idx < session_count) {
				labels[session_idx] = data[session_idx].date;
				best_time_data[session_idx] = data[session_idx].best_time;
				session_idx++;
			}

			const chart_data = {
			labels: labels,
			datasets: [{
					label: `Meilleur Temps - ${params.kart_name}`,
					borderColor: 'rgb(255, 99, 132)',
					data: best_time_data
				}]
			};

			const options = {
				scales: {
					x: {
						type: 'time',
						display: true
					}
				}
			};

			const config = {
				type: 'line',
				options: options,
				data: chart_data
			};

			return new Chart(chart_history_ctx, config);
		});
}

//=============================================================================================================================

const Chart_Track_Session = function(params) {

	return fetch(`api.php?action=chart&c=ts&s=${params.session}`)
		.then(Fetch_Handle_Errors)
		.then(function (response) { return response.json(); })
		.then(function (data) {


			const colors = ["f94144","f3722c","f8961e","f9844a","f9c74f","90be6d","43aa8b","4d908e","577590","277da1"];


			const chart_data = {
				labels: [],
				datasets: []
			};

			let driver = data[0].pseudo;
			let color_index = 0;
			let color = colors[color_index];
			let dataset = { "label": driver, "borderColor": `#${color}`, "data": [] };

			let index = 0;
			const count = data.length;
			while(index < count) {
				if(driver !== data[index].pseudo) {
					chart_data.datasets.push(dataset);
					color_index = (color_index + 1) % colors.length;
					color = colors[color_index];
					driver = data[index].pseudo;
					dataset = { "label": driver, "borderColor": `#${color}`, "data": [] };
				}
				if(chart_data.labels.indexOf(data[index].lap) === -1) {
					chart_data.labels.push(data[index].lap);
				}
				dataset.data.push(data[index].time);
				index++;
			}
			//push the last dataset
			chart_data.datasets.push(dataset);


			const options = {};

			const config = {
				type: 'line',
				options: options,
				data: chart_data,
			};

			return new Chart(chart_ctx, config);
		});
}

//=============================================================================================================================

const Chart_Track_Best_Time_Ranking = function(params) {

	return fetch(`api.php?action=chart&c=tbtr&t=${params.track}&k=${params.kart}`)
		.then(Fetch_Handle_Errors)
		.then(function (response) { return response.json(); })
		.then(function (data) {

			const labels =  [];
			const best_times =  [];

			let session_idx = 0;
			const session_count = data.length;
			while(session_idx < session_count) {
				labels[session_idx] = data[session_idx].driver_pseudo;
				best_times[session_idx] = data[session_idx].best_time;
				session_idx++;
			}

			const chart_data = {
				labels: labels,
				datasets: [{
					label: 'Meilleur Temps',
					borderColor: 'rgb(205, 69, 102)',
					backgroundColor: 'rgb(255, 99, 132)',
					data: best_times
				}]
			};

			const options = {
				elements: {
					bar: { borderWidth: 2 }
				},
				plugins: {
					legend: { display: false }
				}
			};

			const config = {
				type: 'bar',
				options: options,
				data: chart_data,
			};

			return new Chart(chart_ctx, config);
		});
}

//=============================================================================================================================

const Chart_Track_Average_Time_Ranking = function(params) {

	return fetch(`api.php?action=chart&c=tatr&t=${params.track}&k=${params.kart}`)
		.then(Fetch_Handle_Errors)
		.then(function (response) { return response.json(); })
		.then(function (data) {

			const labels =  [];
			const average_times =  [];

			let session_idx = 0;
			const session_count = data.length;
			while(session_idx < session_count) {
				labels[session_idx] = data[session_idx].driver_pseudo;
				average_times[session_idx] = data[session_idx].best_average_time;
				session_idx++;
			}

			const chart_data = {
				labels: labels,
				datasets: [{
					label: 'Temps Moyen',
					borderColor: 'rgb(34, 132, 205)',
					backgroundColor: 'rgb(54, 162, 235)',
					data: average_times
				}]
			};

			const options = {
				elements: {
					bar: { borderWidth: 2 }
				},
				plugins: {
					legend: { display: false }
				}
			};

			const config = {
				type: 'bar',
				options: options,
				data: chart_data,
			};

			return new Chart(chart_ctx, config);
		});
}

//=============================================================================================================================

const Chart_Session_Best_Time_Ranking = function(params) {

	return fetch(`api.php?action=chart&c=sbtr&s=${params.session}`)
		.then(Fetch_Handle_Errors)
		.then(function (response) { return response.json(); })
		.then(function (data) {

			const labels =  [];
			const best_times =  [];

			let session_idx = 0;
			const session_count = data.length;
			while(session_idx < session_count) {
				labels[session_idx] = data[session_idx].driver_pseudo;
				best_times[session_idx] = data[session_idx].best_time;
				session_idx++;
			}

			const chart_data = {
				labels: labels,
				datasets: [{
					label: 'Meilleur Temps',
					borderColor: 'rgb(205, 69, 102)',
					backgroundColor: 'rgb(255, 99, 132)',
					data: best_times
				}]
			};

			const options = {
				elements: {
					bar: { borderWidth: 2 }
				},
				plugins: {
					legend: { display: false }
				}
			};

			const config = {
				type: 'bar',
				options: options,
				data: chart_data,
			};

			return new Chart(chart_ctx, config);
		});
}

//=============================================================================================================================

const Chart_Session_Average_Time_Ranking = function(params) {

	return fetch(`api.php?action=chart&c=satr&s=${params.session}`)
		.then(Fetch_Handle_Errors)
		.then(function (response) { return response.json(); })
		.then(function (data) {

			const labels =  [];
			const average_times =  [];

			let session_idx = 0;
			const session_count = data.length;
			while(session_idx < session_count) {
				labels[session_idx] = data[session_idx].driver_pseudo;
				average_times[session_idx] = data[session_idx].average_time;
				session_idx++;
			}

			const chart_data = {
				labels: labels,
				datasets: [{
					label: 'Temps Moyen',
					borderColor: 'rgb(34, 132, 205)',
					backgroundColor: 'rgb(54, 162, 235)',
					data: average_times
				}]
			};

			const options = {
				elements: {
					bar: { borderWidth: 2 }
				},
				plugins: {
					legend: { display: false }
				}
			};

			const config = {
				type: 'bar',
				options: options,
				data: chart_data,
			};

			return new Chart(chart_ctx, config);
		});
}


//==================================================================================================================
//   _____                _     _   _                 _ _
//  |  ___|              | |   | | | |               | | |
//  | |____   _____ _ __ | |_  | |_| | __ _ _ __   __| | | ___ _ __ ___
//  |  __\ \ / / _ \ '_ \| __| |  _  |/ _` | '_ \ / _` | |/ _ \ '__/ __|
//  | |___\ V /  __/ | | | |_  | | | | (_| | | | | (_| | |  __/ |  \__ \
//  \____/ \_/ \___|_| |_|\__| \_| |_/\__,_|_| |_|\__,_|_|\___|_|  |___/
//
//
//==================================================================================================================

function Fetch_Handle_Errors(response) {
	if (response.ok !== true) {
		throw Error(response.statusText);
	}
	return response;
}