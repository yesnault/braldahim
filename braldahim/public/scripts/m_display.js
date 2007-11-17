
function _display_(box,data) {
	_display_box(box, data);
}

function _display_box(box, data) {
	if (document.getElementById(box)) {
		document.getElementById(box).innerHTML = data;
	} //else {
		//alert('Erreur m_display : box:'+box+' inconnue');
	//}
}

// Switch pour les onglets sur les box
function my_switch(box,conteneur) {
	val = $('switch_'+conteneur).value.split(',');
	for (i=0; i<val.length; i++) {
		$(val[i]).style.display="none";
		$("onglet_"+val[i]).className="onglet inactif";
	}
	$(box).style.display="block";
	$("onglet_"+box).className="onglet actif";
}

// Switch pour afficher un div et en cacher un autre
function switch2div(div1, div2) {
	if ($(div1).style.display=="none") {
		$(div1).style.display="block";
		$(div2).style.display="none";
	} else {
		$(div1).style.display="none";
		$(div2).style.display="block";
	}
}

// n'autorise que des chiffres.
// exemple d'utilisation : <input type="text" onkeypress="chiffres(event)">
function chiffres(event, negatif) {
	// Compatibilité IE / Firefox
	if(!event&&window.event) {
		event=window.event;
	}
	
	// IE 
	if (event.keyCode == 37 || event.keyCode == 39 || // fleches deplacement
		event.keyCode == 46 || event.keyCode == 8) { // backspace ou delete
		return;
	} else if(event.keyCode < 48 || event.keyCode > 57) {
		event.returnValue = false;
		event.cancelBubble = true;
	}
	
	// DOM
	if (event.which == 46 || event.which == 8) { // backspace ou delete
		return;
	} else if (negatif != null && event.which == 45) { // signe -
		return;
	} else if(event.which < 48 || event.which > 57) {
		event.preventDefault();
		event.stopPropagation();
	}
}

function activer_wysiwyg(id) {
	alert('Activation');
	//tinyMCE.execCommand('mceAddControl', false, id);
}

function desactiver_wysiwyg(id) {
	tinyMCE.execCommand('mceRemoveControl', false, id);
}

function accordion(el, ancien, visible) {
	if ($(visible)) {
		if ($(visible) == el) {
		    return;
		}
	}
	var eldown = el.parentNode.id+'-body';
	var elup = null;
	if ($(visible)) {
		var elup = $(visible).parentNode.id+'-body';
	}
	if ($(elup) && $(eldown)) {
		new Effect.Parallel(
		[
		    new Effect.SlideUp(elup),
		    new Effect.SlideDown(eldown)
		], {
		    duration: 0.1
		});
	} else {
		if ($(elup)) {
			new Effect.SlideUp(elup, {duration: 0.1});
		}
		if ($(eldown)) {
			new Effect.SlideDown(eldown, {duration: 0.1});
		}
	}
	el.style.backgroundImage = "url(/public/images/expanded.gif);";
	
	if ($($(ancien).value+""+'-head')) {
		$($(ancien).value+""+'-head').style.backgroundImage = "url(/public/images/collapsed.gif);";
	}
	if ($(visible)) {
		$(visible).style.backgroundImage = "url(/public/images/collapsed.gif);";
		$(visible).id = '';
	}
	$(ancien).value = el.parentNode.id;
	el.id = visible;
}
