/**
 * Contenu de map.html.
 */
var map = null

function fetchMap(callback) {
	var httpRequest = new XMLHttpRequest();
	httpRequest.onreadystatechange = function() {
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

function initBraldop() {
	map = new Map("map_canvas", "posmark");
	fetchMap(function(msg) {
		map.setData(msg);
		map.redraw();
		setTimeout(function() {
			map.redraw();
		}, 1000); // contournement de bug pas compris
	});

	$('#layer_satellite').attr('checked', map.displayPhotoSatellite).change(function() {
		map.displayPhotoSatellite = this.checked;
		map.redraw();
	});
	$('#layer_nomsLieux').attr('checked', map.displayTownPlaceNames).change(function() {
		map.displayTownPlaceNames = this.checked;
		map.redraw();
	});
	$('#layer_régions').attr('checked', map.displayRégions).change(function() {
		map.displayRégions = this.checked;
		map.redraw();
	});
}
