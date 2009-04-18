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

	if (box == 'racine') { // si l'on fait appel a boxes, on appelle la vue ensuite
		_get_('/interface/load/?box=box_vue');
	}
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
	
	fermeturePopup();
	
	if ($("loaded_" + box).value != "1") {
		$("loaded_" + box).value = 1;
		_get_('/interface/load/?box='+ box);
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

// n'autorise que des chiffres.
// exemple d'utilisation : <input type="text" onkeypress="chiffres(event)">
function chiffres(event, negatif) {
	// Compatibilité IE / Firefox
	if (!event && window.event) {
		event = window.event;
	}

	// IE 
	if (event.keyCode == 37 || event.keyCode == 39 || // fleches deplacement
			event.keyCode == 46 || event.keyCode == 8) { // backspace ou delete
		return;
	} else if (event.keyCode < 48 || event.keyCode > 57) {
		event.returnValue = false;
		event.cancelBubble = true;
	}

	// DOM
	if (event.which == 46 || event.which == 8) { // backspace ou delete
		return;
	} else if (negatif != null && event.which == 45) { // signe -
		return;
	} else if (event.which < 48 || event.which > 57) {
		event.preventDefault();
		event.stopPropagation();
	}
}

function maccordion_fermer(el) {
	var eldown = el.parentNode.id + '-body';
	if ($(eldown).style.display != "none") {
		new Effect.SlideUp(eldown, { duration :0.1 });
		el.style.backgroundImage='url("/public/images/collapsed.gif")';
	}
}

function maccordion_ouvrir(el) {
	var eldown = el.parentNode.id + '-body';
	new Effect.SlideDown(eldown, { duration :0.1 });
	el.style.backgroundImage='url("/public/images/expanded.gif")';
}

function maccordion(el) {
	var eldown = el.parentNode.id + '-body';
	if ($(eldown)) {
		if ($(eldown).style.display == "none") {
			maccordion_ouvrir(el);
		} else {
			maccordion_fermer(el);
		}
	}
}

function limiteTailleTextarea(textarea, max, iddesc) {
	if (textarea.value.length >= max) {
		textarea.value = textarea.value.substring(0, max);
	}
	var reste = max - textarea.value.length;
	var affichage_reste = reste + ' caract&egrave;res restants';
	$(iddesc).innerHTML = affichage_reste;
}

function ouvrirProfilH(idHobbit) {
	ouvrirWin('/voir/hobbit/?hobbit=' + idHobbit + '&direct=profil', 'Profil Hobbit n°' + idHobbit);
}
function ouvrirEvenementsH(idHobbit) {
	ouvrirWin('/voir/hobbit/?hobbit=' + idHobbit + '&direct=evenements', 'Evenements Hobbit n°' + idHobbit);
}
function ouvrirFamille(idHobbit) {
	ouvrirWin('/voir/hobbit/?hobbit=' + idHobbit + '&direct=famille', 'Famille Hobbit n°' + idHobbit);
}
function ouvrirCommunaute(idCommunaute) {
	ouvrirWin('/voir/communaute/?communaute=' + idCommunaute, 'Communauté n°' + idCommunaute);
}
function ouvrirMonstre(idMonstre) {
	ouvrirWin('/voir/monstre/?monstre=' + idMonstre, 'Monstre n°' + idMonstre);
}
function ouvrirProfilH(idMonstre) {
	ouvrirWin('/voir/monstre/?monstre=' + idMonstre + '&direct=profil', 'Profil Monstre n°' + idMonstre);
}
function ouvrirEvenementsH(idMonstre) {
	ouvrirWin('/voir/monstre/?monstre=' + idMonstre + '&direct=evenements', 'Evenements Monstre n°' + idMonstre);
}
function ouvrirWin(url, titre) {
	window.open(url, titre, "directories=no,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes,width=815,height=600");
}

function messagerie(nbMessageNonLu) {
	if ($('message_nb_label')) {
		$('message_nb').style.display = "block";
		$('message_nb_img').style.display = "block";
		$('img_message_nouveau').style.display = "none";
		$('img_message_ancien').style.display = "none";
		
		if (nbMessageNonLu == 1) {
			$('message_nb_label').innerHTML = " 1 nouveau message&nbsp;";
			$('img_message_nouveau').style.display = "block";
		} else if (nbMessageNonLu > 1) {
			$('message_nb_label').innerHTML = nbMessageNonLu + " nouveaux messages&nbsp;";
			$('img_message_nouveau').style.display = "block";
		} else { // 0
			$('message_nb_label').innerHTML = " Pas de nouveau message&nbsp;";
			$('img_message_ancien').style.display = "block";
		}
	}
}

function loadBox(nomSysteme) {
	_get_('/interface/load/?box=' + nomSysteme);
}

function revealModal(divID) {
    window.onscroll = function () { $(divID).style.top = document.body.scrollTop; };
    $(divID).style.display = "block";
    $(divID).style.top = document.body.scrollTop;
    document.documentElement.scrollTop = 0;
}

function hideModal(divID) {
    $(divID).style.display = "none";
}

function ecrireMessage(idHobbit) {
	fermeturePopup();
	if ($("loaded_box_messagerie").value != "1") {
		// pour eviter de recharger box_messagerie lors du my_switch en dessous
		// si l'onglet n'a jamais été vu.
		$("loaded_box_messagerie").value = "1"; 
	}
	_get_("/messagerie/askaction?caction=do_messagerie_message&valeur_1=nouveau&valeur_2=" + idHobbit);
	my_switch("box_messagerie","boite_c");
}

function fermeturePopup() {
	try {
		return cClick(); // fermeture popup
	} catch (e) {
		// erreur si aucune popup n'a ete ouverte depuis l'arrivee sur l'interface
	}
}

function encodePlus(chaine) {
	var reg=new RegExp("(\\+)", "g");
	return chaine.replace(reg,"[plus]");
}

function chaineCheckbox(liste) {
	val = liste.split(',');
	retour = "";
	for (i = 0; i < val.length; i++) {
		if ($($(val[i]).checked)) {
			retour = retour + $(val[i]).value + ",";
		}
	}
	if (retour != "") {
		retour = retour.substring(0,retour.lastIndexOf(','));
	}
	return retour;
}

function checkboxCocher(liste, valeur, acacher, aafficher) {
	val = liste.split(',');
	retour = "";
	
	acacher.style.display="none";
	aafficher.style.display="block";
	
	for (i = 0; i < val.length; i++) {
		$($(val[i]).checked = valeur);
	}
}

function copierTooltip() {
	javascript:switch2div('contenuTooltip','contenuTooltipCopie');
	$('contenuTooltipCopieText').value = $('contenuTooltipCopieText').value.replace(/<br>/g, '\n');
}