/**
 * Contenu de map.html.
 */
var map = null;


function fetchMap(callback) {
	var httpRequest = new XMLHttpRequest();
	httpRequest.onreadystatechange = function () {
		if (httpRequest.readyState === 4) {
			if (httpRequest.status === 200) {
				var msg = eval('(' + httpRequest.responseText + ')');
				console.log("reçu : ");
				console.log(msg);
				if (callback) callback(msg);
			}
		}
	};
	httpRequest.open('GET', '/interface/cartejson?time=' + (new Date().getTime()) + "&dateAuth=" + $('#dateAuth').val());
	httpRequest.send();
}

function initBraldopCallback(map) {
	map.setCallback("Marcher", function (a) {
		actionMarcher(a);
	});
	map.setCallback("Lieu", function (a) {
		actionLieu(a);
	});
	map.setCallback("Transbahuter", function (a) {
		actionTransbahuter(a);
	});
	map.setCallback("AdminLieu", function (a) {
		actionAdminLieu(a);
	});
	map.setCallback("AdminEau", function (a) {
		actionAdminEau(a);
	});
	map.setCallback("AdminRoute", function (a) {
		actionAdminRoute
	});
	map.setCallback("AdminPalissade", function (a) {
		actionAdminPalissade(a);
	});
}

function actionMarcher(action) {
	_get_('/competences/doaction?caction=do_competence_marcher&valeur_1=' + action.Offset);
}

function actionLieu(action) {
	_get_('/interface/load/?box=box_lieu');
}

function actionTransbahuter(action) {
	_get_('/competences/doaction?caction=ask_competence_transbahuter&valeur_1=1');
}

function actionAdminLieu(action) {
	_get_('/administrationajax/doaction?caction=ask_administrationajax_insererlieu&xyz_lieu=' + action.X + "h" + action.Y + "h" + action.Z);
}

function actionAdminEau(action) {
	_get_('/administrationajax/doaction?caction=ask_administrationajax_inserereau&xyz_eau=' + action.X + "h" + action.Y + "h" + action.Z);
}

function actionAdminRoute(action) {
	_get_('/administrationajax/doaction?caction=ask_administrationajax_insererroute&xyz_route=' + action.X + "h" + action.Y + "h" + action.Z);
}

function actionAdminPalissade(action) {
	_get_('/administrationajax/doaction?caction=ask_administrationajax_insererpalissade&xyz_palissade=' + action.X + "h" + action.Y + "h" + action.Z);
}

function initBraldopFetch() {
	fetchMap(function (msg) {
		map.setData(msg);
		//> on batit le menu de choix de la profondeur
		var html = ''
		if (msg.Couches) {
			html += 'Profondeur : <select id=select_profondeur>';
			for (var i = 0; i < msg.Couches.length; i++) {
				var z = msg.Couches[i].Z;
				html += '<option value=' + z + '>' + z + '</option>';
			}
			html += '</select>';
		}

		$("#positionX").val(msg.Position.X);
		$("#positionY").val(msg.Position.Y);
		$("#positionZ").val(msg.Position.Z);

		$('#choix_profondeur').html(html);

		html = "";
		if (msg.Vues) {
			for (i in msg.Vues) {
				var v = msg.Vues[i];
				v.active = true; // on active par défaut les vues
			}
		}
		$('#view_table tbody').html(html);
		map.setCallback('profondeur', function (z) {
			$('#select_profondeur').val(z);
		});

		map.compileLesVues(); // en raison de leur activation

		centrerVue();
		map.redraw();
		setTimeout(function () {
			map.redraw();
			centrerVue();
		}, 1000); // contournement de bug pas compris
	});
}

function initBraldop() {
	if (localStorage['grid'] == '') {
		localStorage['grid'] = "true";
	}
	map = new Map("map_canvas", "posmark");
	map.displayFog = false;
	initBraldopFetch();

	$('#layer_satellite').attr('checked', map.displayPhotoSatellite).change(function () {
		map.displayPhotoSatellite = this.checked;
		map.redraw();
	});
	$('#layer_régions').attr('checked', map.displayRégions).change(function () {
		map.displayRégions = this.checked;
		map.redraw();
	});
	$('#layer_fog').attr('checked', map.displayFog).change(function () {
		map.displayFog = this.checked;
		map.redraw();
	});
	$('#layer_grid').attr('checked', map.displayGrid).change(function () {
		map.displayGrid = this.checked;
		map.redraw();
	});

	$('#select_profondeur').live('change', function () {
		map.changeProfondeur(parseInt($(this).val()));
		map.redraw();
	});

	$('#goto').click(function () {
		centrerVue();
	});
	$('#icon_grid').click(function () {
		map.displayGrid = !map.displayGrid;
		localStorage['grid'] = '' + map.displayGrid;
		map.redraw();
	});

	$(window).resize(function () {
		centrerVue();
	});

	setTimeout(function () {
		for (i in map.mapData.Vues) {
			var v = map.mapData.Vues[i];
			v.active = true; //this.checked;
			centrerVue();
		}
		map.redraw();
	}, 1000);

	initBraldopCallback(map);
	initBlabla();
}

function centrerVue() {
	if (map.zoom < 32) map.zoom = 32;
	if ($("#administrationvue_x").length && $("#administrationvue_y").length && $("#administrationvue_z").length) {
		map.goto(parseInt($("#administrationvue_x").val()), parseInt($("#administrationvue_y").val()), parseInt($("#administrationvue_z").val()));
	} else {
		map.goto(parseInt($("#positionX").val()), parseInt($("#positionY").val()), parseInt($("#positionZ").val()));
	}
}