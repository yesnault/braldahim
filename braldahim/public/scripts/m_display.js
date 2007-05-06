
function _display_(box,data) {
	_display_box(box, data);
}

function _display_box(box, data) {
	if (document.getElementById(box)) {
		document.getElementById(box).innerHTML = data;
	} else {
		alert('Erreur m_display : box:'+box+' inconnue');
	}
}

// Switch pour les onglets sur les box
function my_switch(box,conteneur) {
	val = document.getElementById('switch_'+conteneur).value.split(',');
	for (i=0; i<val.length; i++) {
		document.getElementById(val[i]).style.display="none";
		document.getElementById("onglet_"+val[i]).className="onglet inactif";
	}
	document.getElementById(box).style.display="block";
	document.getElementById("onglet_"+box).className="onglet actif";
}

// Switch pour afficher un div et en cacher un autre
function switch2div(div1, div2) {
	if (document.getElementById(div1).style.display=="none") {
		document.getElementById(div1).style.display="block";
		document.getElementById(div2).style.display="none";
	} else {
		document.getElementById(div1).style.display="none";
		document.getElementById(div2).style.display="block";
	}
}
