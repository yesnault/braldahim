function _display_(box, data) {
	if (box == "erreur_catch") {
		$("erreur_catch_contenu").innerHTML = data;
	} else {
		_display_box(box, data);
	}
}

function _display_box(box, data) {
	if ($(box)) {
		$(box).innerHTML = data;
	}
}

function revealModal(divID) {
    window.onscroll = function () { $(divID).style.top = document.body.scrollTop; };
    $(divID).style.display = "block";
    $(divID).style.top = document.body.scrollTop;
}

function hideModal(divID) {
    $(divID).style.display = "none";
}

// Switch pour les onglets sur les box
function my_switch(box, conteneur) {
	val = $('switch_' + conteneur).value.split(',');
	for (i = 0; i < val.length; i++) {
		if ($(val[i])) {
			$(val[i]).style.display = "none";
		}
		$("onglet_" + val[i]).className = "onglet inactif";
	}
	if ($(box)) {
		$(box).style.display = "block";
	}
	$("onglet_" + box).className = "onglet actif";
	
	try {
		cClick(); // fermeture popup
	} catch (e) {
		// erreur si aucune popup n'a ete ouverte depuis l'arrivee sur l'interface
	}
	
	if ($("loaded_" + box).value != "1") {
		$("loaded_" + box).value = 1;
		_get_('/palmares/load/?box='+ box);
	}
}

// Switch pour afficher un div et en cacher un autre
function switch2div(div1, div2) {
	if ($(div1).style.display == "none") {
		$(div1).style.display = "block";
		$(div2).style.display = "none";
	} else {
		$(div1).style.display = "none";
		$(div2).style.display = "block";
	}
}

function ouvrirWin(url, titre) {
	window.open(url, titre, "directories=no,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes,width=800,height=600");
}
