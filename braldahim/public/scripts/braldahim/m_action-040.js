function findSelectedRadioButton(groupname) {
	var radioButtons = $('myForm').elements[groupname];
	for ( var i = 0; i < radioButtons.length; i++) {
		if (radioButtons[i].checked) {
			return radioButtons[i];
		}
	}
	return null;
}

function _get_specifique_(url, valeurs) {
	var sep = '&';
	if ($('dateAuth')) {
		valeurs = valeurs + sep + "dateAuth=" + $('dateAuth').value;
	} else {
		valeurs = valeurs + sep + "dateAuth=-1" ;
	}
	var pars = valeurs;
	var myAjax = new Ajax.Request(url, { postBody :pars, onComplete :showResponse });
}

function _get_(url, encode) {
	var valeurs = "";
	var nb_valeurs = 0;
	var action = "";
	
	revealModal();
	
	if (url.length > 34) {
		if (url.substring(0, 12) == "/competences") { // /competences/doaction?caction=ask/do
			if ((url.substring(13, 15) == "do") && (url.substring(30, 32) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 10) == "/charrette") { // /charrette/doaction?caction=ask/do
			if ((url.substring(11, 13) == "do") && (url.substring(28, 30) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 9) == "/boutique" || url.substring(0, 9) == "/echoppes") { // /echoppes/doaction?caction=ask/do
			if ((url.substring(10, 12) == "do") && (url.substring(27, 29) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 7) == "/carnet" || url.substring(0, 7) == "/champs" || url.substring(0, 7) == "/blabla") { // /carnet/doaction?caction=ask/do
			if ((url.substring(8, 10) == "do") && (url.substring(25, 27) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 8) == "/echoppe") { // /echoppe/doaction?caction=ask/do
			if ((url.substring(9, 11) == "do") && (url.substring(26, 28) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 6) == "/lieux" || url.substring(0, 6) == "/hotel" || url.substring(0, 6) == "/soule" || url.substring(0, 6) == "/butin") { // /lieux/doaction?caction=ask/do
			if ((url.substring(7, 9) == "do") && (url.substring(24, 26) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 11) == "/messagerie" || url.substring(0, 11) == "/communaute") { // /messagerie/doaction?caction=ask/do
			if ((url.substring(12, 14) == "do") && (url.substring(29, 31) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 19) == "/administrationajax") { // /administrationajax/doaction?caction=ask/do
			if ((url.substring(20, 22) == "do") && (url.substring(37, 39) == "do")) {
				action = "do";
			}
		}
	}

	var sep = '';
	
	if ($('nb_valeurs') && (action == "do")) {
		// Recuperation du nombre de valeur que l'action a besoin
		nb_valeurs = $('nb_valeurs').value;
		for (i = 1; i <= nb_valeurs; i++) {
			var nom = 'valeur_' + i;
			var elem = $(nom);
			if (elem.type == "radio") {
				radioButton = findSelectedRadioButton(nom);
				if (radioButton != null) {
					valeurs = valeurs + sep + "valeur_" + i + "=" + findSelectedRadioButton(nom).value;
				} else {
					valeurs = valeurs + sep + "valeur_" + i + "=" + elem.value;
				}
			} 
			else if (elem.type == "select-multiple") {
				for (j = 0; j<=elem.options.length-1; j++){
					if (elem.options[j].selected){
						valeurs = valeurs + sep + "valeur_" + i + "[]=" + elem.options[j].value;
					}
				}
			}
			else {
				if (encode) {
					valeurs = valeurs + sep + "valeur_" + i + "=" + encodeURIComponent(elem.value);
				} else {
					valeurs = valeurs + sep + "valeur_" + i + "=" + elem.value;
				}
			}
			sep = "&";
		}
		$("box_action").innerHTML = "Chargement...";
	} else if ($('nb_valeurs') && (action == "ask")) {
		alert("Code A Supprimer ? m_action.js ligne 72");
		fermeBralBox();
	}
	
	if ($('dateAuth')) {
		valeurs = valeurs + sep + "dateAuth=" + $('dateAuth').value;
	} else {
		valeurs = valeurs + sep + "dateAuth=-1" ;
	}
	var pars = valeurs;
	//$("box_action").innerHTML = "";
	var myAjax = new Ajax.Request(url, { postBody :pars, onComplete :showResponse });
}

function showResponse(originalRequest) {
	var xmldoc = originalRequest.responseXML;
	var textdoc = originalRequest.responseText;
	var display_action = false;
	var display_informations = false;
	var display_erreur = false;
	var display_erreur_catch = false;
	var activer_wysiwyg = false;

	var xmlHeader = '<?xml version="1.0" encoding="utf-8" ?>';

	if ((xmldoc == null) || (textdoc.substring(0, 39) != xmlHeader)) {
		if (textdoc == "") {
			return;
		} else if (textdoc.substring(0, 6) == "logout") {
			alert("Votre session a expiré, veuillez vous reconnecter.");
			document.location.href = "/";
		} else if (textdoc.substring(0, 5) != "clear") {
			_display_("erreur_catch", textdoc);
			display_erreur_catch = true;
		} else if (textdoc.substring(0, 5) == "clear") {
			$("box_action").innerHTML = "";
		}
	} else {
		if (Prototype.Browser.IE) {
			estInternetExplorer = true;
		} else {
			estInternetExplorer = false;
		}

		var root = xmldoc.getElementsByTagName('root').item(0);
		for ( var iNode = 0; iNode < root.childNodes.length; iNode++) {
			var node = root.childNodes.item(iNode);

			for (i = 0; i < node.childNodes.length; i++) {
				var sibl = node.childNodes.item(i);
				if (estInternetExplorer) {
					if (i == 0) m_type = sibl.text
					if (i == 1) m_type_valeur = sibl.text
					if (i == 2) m_data = sibl.text

					if (i == 2) {
						if (m_type_valeur == "box_action")
							display_action = true;
						else if (m_type_valeur == "box_informations" && m_data != "")
							display_informations = true; // affichage de la boite d'informations
						else if (m_type_valeur == "erreur" && m_data != "") display_erreur = true; // affichage de la boite d'erreur
						
						//alert('m_type:' + m_type + " m_type_valeur:" + m_type_valeur );
						
						if (m_type == "display") {
							_display_(m_type_valeur, m_data);
						} else if (m_type == "action") {
							if (m_type_valeur == "goto" && m_data != "") {
								redirection = true;
								redirection_url = m_data;
							} else if (m_type_valeur == "effect.appear" && m_data != "") {
								Effect.Appear(m_data, { duration :2.0 });
							} else if (m_type_valeur == "HTMLTableTools" && m_data != "") {
								new HTMLTableTools(m_data);
							} else if (m_type_valeur == "messagerie" && m_data != "") {
								messagerie(m_data);
							} else if (m_type_valeur == "warning") {
								box_warning(m_data);
							}
						} else if (m_type == "load_box") {
							loadBox(m_type_valeur);
						}
					}
				} else {
					for (x = 0; x < sibl.childNodes.length; x++) {
						if (i == 1) m_type = node.childNodes.item(1).childNodes.item(0).data;
						if (i == 3) m_type_valeur = node.childNodes.item(3).childNodes.item(0).data;
						if (i == 5) m_data = node.childNodes.item(5).childNodes.item(0).data;
						if (i == 5) {

							//alert('Fin entrie \n m_type='+m_type+' \n m_type_valeur='+m_type_valeur);
							if (m_type_valeur == "box_action") {
								display_action = true;
							} else if (m_type_valeur == "box_informations" && m_data != "") {
								display_informations = true; // affichage de la boite d'informations
							} else if (m_type_valeur == "erreur" && m_data != "") {
								display_erreur = true; // affichage de la boite d'erreur
							}

							if (m_type == "display") {
								_display_(m_type_valeur, m_data);
							} else if (m_type == "action") {
								if (m_type_valeur == "goto" && m_data != "") {
									redirection = true;
									redirection_url = m_data;
								} else if (m_type_valeur == "effect.disappear" && m_data != "") {
									Effect.Appear(m_data, { duration :4.0, from :1.0, to :0.0 });
								} else if (m_type_valeur == "HTMLTableTools" && m_data != "") {
									//alert('Fin entrie \n m_type='+m_type+' \n m_type_valeur='+m_type_valeur + ' m_data='+m_data);
									new HTMLTableTools(m_data);
								} else if (m_type_valeur == "messagerie" && m_data != "") {
									messagerie(m_data);
								} else if (m_type_valeur == "warning") {
									box_warning(m_data);
								}
							} else if (m_type == "load_box") {
								loadBox(m_type_valeur);
							}
						}
					}
				}
			}
		}
	}
	// Box action
	if (display_action) {
		ouvreBralBox("box_action");
	}

	// Box informations
	if (display_informations) {
		ouvreBralBox("box_informations");
	}

	// Box erreur
	if (display_erreur) {
		ouvreBralBox("erreur");
	} else {
		if ($("erreur")) {
			$("erreur").style.display = "none";
		}
	}
	
	// Box erreur catch
	if (display_erreur_catch) {
		ouvreBralBox("erreur_catch");
	} else {
		if ($("erreur_catch")) {
			$("erreur_catch").style.display = "none";
		}
	}

	hideModal();
	
	if (redirection) {
		document.location.href = redirection_url;
	}

	return;
}

function ouvreBralBox(element) {
	
	if (element == "box_action") {
		titre = "Action";
		boutonClose = "block";
	} else if (element == "box_informations") {
		titre = "Informations";
		boutonClose = "none";
	} else if (element == "erreur") {
		titre = "Une erreur est survenue";
		boutonClose = "none";
	} else if (element == "erreur_catch") {
		titre = "Une erreur est survenue (catch)";
		boutonClose = "none";
	} else {
		titre = "Une erreur est survenue (js)";
		boutonClose = "none";
	}
	
	if(Prototype.Browser.IE) {
		$('BB_overlay').style.backgroundColor="#999999";
	}
	$('BB_close').style.display = boutonClose;
	$('BB_overlay').style.display = "block";
	$('BB_titre').innerHTML = titre;
	$('BB_windowwrapper').style.display = "block";
	$(element).style.display = "block";
}

function fermeBralBox() {
	$('BB_overlay').style.display = "none";
	$('BB_windowwrapper').style.display = "none";
	$("erreur").style.display = "none";
	$("erreur_catch").style.display = "none";
	$("box_informations").style.display = "none";
	$("box_action").style.display = "none";
}

function textCount(field,counterfield,max) {
	if (field.value.length > max) {
		field.value = field.value.substring(0, max);
	} else {
		counterfield.value = max - field.value.length;
	}
}

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
		_get_('/interface/load/?box=box_profil');
		_get_('/interface/load/?box=box_competences_basiques');
		_get_('/interface/load/?box=box_vue');
		_get_('/interface/load/?box=box_blabla');
	}
}

// Switch pour les onglets sur les box
function my_switch(box, conteneur) {
	val = $('switch_' + conteneur).value.split(',');
	for (i = 0; i < val.length; i++) {
		if ($(val[i])) {
			$(val[i]).style.display = "none";
		}
		if ($("onglet_" + val[i]).className.indexOf("premier") > 1) {
			$("onglet_" + val[i]).className = "onglet inactif premier";
		} else {
			$("onglet_" + val[i]).className = "onglet inactif";	
		}
		
	}
	if ($(box)) {
		$(box).style.display = "block";
	}
	if ($("onglet_" + box).className.indexOf("premier") > 1) {
		$("onglet_" + box).className = "onglet actif premier";
	} else {
		$("onglet_" + box).className = "onglet actif";
	}
	
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

function ouvrirProfilH(idBraldun) {
	ouvrirWin('/voir/braldun/?braldun=' + idBraldun + '&direct=profil', 'Profil Braldun n°' + idBraldun);
}
function ouvrirEvenementsH(idBraldun) {
	ouvrirWin('/voir/braldun/?braldun=' + idBraldun + '&direct=evenements', 'Evenements Braldun n°' + idBraldun);
}
function ouvrirFamille(idBraldun) {
	ouvrirWin('/voir/braldun/?braldun=' + idBraldun + '&direct=famille', 'Famille Braldun n°' + idBraldun);
}
function ouvrirCommunaute(idCommunaute) {
	ouvrirWin('/voir/communaute/?communaute=' + idCommunaute, 'Communauté n°' + idCommunaute);
}
function ouvrirMonstre(idMonstre) {
	ouvrirWin('/voir/monstre/?monstre=' + idMonstre, 'Monstre n°' + idMonstre);
}
function ouvrirProfilM(idMonstre) {
	ouvrirWin('/voir/monstre/?monstre=' + idMonstre + '&direct=profil', 'Profil Monstre n°' + idMonstre);
}
function ouvrirEvenementsM(idMonstre) {
	ouvrirWin('/voir/monstre/?monstre=' + idMonstre + '&direct=evenements', 'Evenements Monstre n°' + idMonstre);
}
function ouvEquipement(id) {
	ouvrirWin('/voir/equipement/?equipement=' + id, 'Equipement n°' + id);
}
function ouvProfilE(id) {
	ouvrirWin('/voir/equipement/?equipement=' + id + '&direct=profil', 'Profil Equipement n°' + id);
}
function ouvHistoE(id) {
	ouvrirWin('/voir/equipement/?equipement=' + id + '&direct=historique', 'Historique Equipement n°' + id);
}
function ouvPotion(id) {
	ouvrirWin('/voir/potion/?potion=' + id, 'Potion n°' + id);
}
function ouvProfilP(id) {
	ouvrirWin('/voir/potion/?potion=' + id + '&direct=profil', 'Profil Potion n°' + id);
}
function ouvHistoP(id) {
	ouvrirWin('/voir/potion/?potion=' + id + '&direct=historique', 'Historique Potion n°' + id);
}
function ouvMateriel(id) {
	ouvrirWin('/voir/materiel/?materiel=' + id, 'Matériel n°' + id);
}
function ouvProfilMa(id) {
	ouvrirWin('/voir/materiel/?materiel=' + id + '&direct=profil', 'Profil Matériel n°' + id);
}
function ouvHistoMa(id) {
	ouvrirWin('/voir/materiel/?materiel=' + id + '&direct=historique', 'Historique Matériel n°' + id);
}
function ouvRune(id) {
	ouvrirWin('/voir/rune/?rune=' + id, 'Rune n°' + id);
}
function ouvProfilR(id) {
	ouvrirWin('/voir/rune/?rune=' + id + '&direct=profil', 'Profil Rune n°' + id);
}
function ouvHistoR(id) {
	ouvrirWin('/voir/rune/?rune=' + id + '&direct=historique', 'Historique Rune n°' + id);
}

function ouvrirWin(url, titre) {
	window.open(url, titre, "directories=no,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes,width=815,height=600");
}

function box_warning(data) {
	$('box_warning').style.display = "block";
	$('box_warning').innerHTML = data;
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

function revealModal() {
    window.onscroll = function () { $('modalPage').style.top = document.body.scrollTop; };
    $('modalPage').style.display = "block";
    $('modalPage').style.top = document.body.scrollTop;
    document.documentElement.scrollTop = 0;
}

function hideModal() {
    $('modalPage').style.display = "none";
}

function chargeBoxMessagerie() {
	if ($("loaded_box_messagerie").value != "1") {
		// pour eviter de recharger box_messagerie lors du my_switch en dessous
		// si l'onglet n'a jamais été vu.
		$("loaded_box_messagerie").value = "1"; 
	}
}

function ecrireMessage(idBraldun) {
	chargeBoxMessagerie();
	_get_("/messagerie/askaction?caction=do_messagerie_message&valeur_1=nouveau&valeur_2=" + idBraldun);
	my_switch("box_messagerie","boite_c");
}

function ecrireMessageListeContact(idListe) {
	if ($("loaded_box_messagerie").value != "1") {
		$("loaded_box_messagerie").value = "1"; 
	}
	_get_("/messagerie/askaction?caction=do_messagerie_message&valeur_1=nouveau&valeur_4=" + idListe);
	my_switch("box_messagerie","boite_c");
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

function copierTooltipStatic(idDivInitiale, replaceBrChar) {
	javascript:switch2div('contenuTooltip','contenuTooltipCopie');
	$('contenuTooltipCopieText').value = $(idDivInitiale).innerHTML.replace(/<br>/g, replaceBrChar);
}

function textCount(field,counterfield,max) {
	if (field.value.length > max) // if too long...trim it!
		field.value = field.value.substring(0, max);
	else
		counterfield.value = max - field.value.length;
}
function wiglwogl(uddeElement) { 
	uddeForm = uddeElement.form; 
	uddeElement = uddeForm.elements[uddeElement.name]; 
	if (uddeElement.length) { 
		bChecked = uddeElement[0].checked; 
		for(i = 1; i < uddeElement.length; i++) {
			uddeElement[i].checked = bChecked; 
		}
	}
} 

/********************************************************************/
/************************* Transbahuter ********************/
/********************************************************************/

function controlePoids(){
	var poids=0;
	if ($('valeur_2').value != -1 ){
		poidsRestant = $('poids_' + $('valeur_2').value).value;
		if (poidsRestant != -1){
		 	for (i=4; i<=$('nb_valeurs').value; i++) {
				if ( $('valeur_' + i).type == 'select-multiple' ){
					for (j=0; j<$('valeur_' + i).options.length; j++){
						if ($('valeur_' + i).options[j].selected == true) {
							if ( i==12 || i==16 ){
								poids = parseFloat(poids) + parseFloat($('valeur_' + i + '_poids_' + $('valeur_' + i).options[j].value).value);
							}
							else{
								poids = parseFloat(poids) + parseFloat($('valeur_' + i + '_poids').value);
							}
						}
					}
				}
				else {
					poids = parseFloat(poids) + $('valeur_' + i).value * $('valeur_' + i + '_poids').value;
				}
			}
			if (poids > poidsRestant){
				poidsDep = Math.round((poids - poidsRestant)*100)/100;
				alert ('Pas assez de place dans la source d\'arrivée !\nVous dépassez de ' + poidsDep + ' kg');
				return false;
			}
			else{
				return true;
			}
		}
		else{
			return true;
		}
	}
	else{
		return true;
	}
}

function controlePanneau (i) {
	if ( $('valeur_' + i).type == 'select-multiple' ){
		for (j=0; j<$('valeur_' + i).options.length; j++){
			if ($('valeur_' + i).options[j].value != -1) {
				$('valeur_' + i).options[j].selected = false;
				cacher = false;
			}
		}
	}
	else {
		$('valeur_'+i).value = 0;
	}		
	alert ("Cette charrette ne possède pas de panneau amovible, vous ne pouvez transbahuter qu\'un seul type d\'élément ! \n Seul le premier élément sélectionné a été pris en compte.");
}

function controleQte(){
	 v=false;
	 ctrlEchoppe = false;
	 for (i=4;i<=$('nb_valeurs').value;i++) {
	 	if ($('valeur_'+i).value > 0 && $('valeur_panneau').value != true && v==true) {
			controlePanneau (i);
	 	}
	 	if (controleEchoppe(i) == false ) {
	 		ctrlEchoppe = true;
	 	}
	 	else if ($('valeur_'+i).value > 0 ) {
			v=true;
		}
	 }
	 cacher = true;
	 if (ctrlEchoppe == true ) {
		 alert ("Dans une échoppe, vous ne pouvez transbahuter que des matières premières !");
	 }
	 poidsOk = controlePoids();
	 if (v==true && $('valeur_1').value != -1 && $('valeur_2').value != -1 && poidsOk == true){
		cacher = false;
	 }
	 if ( $('valeur_2').value == 4 && $('valeur_3').value == -1){
		cacher = true;
	 }
	 $('bouton_deposer').disabled=cacher;
}

function selectAll(){
	cacher = true;
	v = false;
	ctrlEchoppe = false;
	for (i=4; i<=$('nb_valeurs').value; i++) {
		if ($('valeur_panneau').value != true && v==true) {
			controlePanneau (i);
			break;
	 	}
		if ( $('valeur_' + i + '_echoppe').value == 'oui' || $('valeur_2').value != 5) {
			if ( $('valeur_' + i).type == 'select-multiple' ){
				for (j=0; j<$('valeur_' + i).options.length; j++){
					if ($('valeur_' + i).options[j].value != -1) {
						$('valeur_' + i).options[j].selected = true;
						cacher = false;
						v = true;
					}
				}
			}
			else {
				$('valeur_' + i).value = $('valeur_' + i + '_max').value;
				if (cacher == true && $('valeur_' + i + '_max').value > 0) {
					cacher = false;
					v = true;
				}
			}
		}
		else {
			ctrlEchoppe = true;
		}
	}
	if (ctrlEchoppe == true ) {
		alert ("Dans une échoppe, vous ne pouvez transbahuter que des matières premières !");
	}
	poidsOk = controlePoids();
	if ( $('valeur_1').value == -1 || $('valeur_2').value == -1 || poidsOk==false){
		cacher = true;
	}
	/*Coffre*/
	if ( $('valeur_2').value == 4 && $('valeur_3').value == -1){
		cacher = true;
	}
	$('bouton_deposer').disabled=cacher;
}

function charrette() {
	if ($('valeur_2').value >= 7){
		$('valeur_3').value = $('id_charrette_' + $('valeur_2').value).value;
	}
}

function controleEchoppe(i) {
	if($('valeur_2').value == 5){
		if ( ($('valeur_' + i + '_echoppe').value == 'non') && $('valeur_' + i).value > 0) {
			if ( $('valeur_' + i).type == 'select-multiple' ) {
				for (j=0; j<$('valeur_' + i).options.length; j++) {
					$('valeur_' + i).options[j].selected = false;
				}
			}
			else {
				$('valeur_' + i).value = 0;
			}
			return false;
		}
	}
	return true;
}

/*function echoppe() {
	if($('valeur_2').value == 5){
		ctrlEchoppe = false;
		for (i=4; i<=$('nb_valeurs').value; i++) {
			if (controleEchoppe(i) == false && ctrlEchoppe == false) {
				ctrlEchoppe = true;
			}
		}
		if (ctrlEchoppe == true) {
			alert ("Dans une échoppe, vous ne pouvez transbahuter que des matières premières !");
		}
	}
}*/

function afficheAutreCoffre(){
	if($('valeur_2').value==4){
		document.getElementById('div_braldun').style.visibility='visible';
		document.getElementById('div_braldun').style.display='block';
	}
	else{
		document.getElementById('div_braldun').style.visibility='hidden';
		document.getElementById('div_braldun').style.display='none';
		$('valeur_3').value=-1;
	}
}

function activerRechercheUniqueBraldun(id, avecBraldun, avecPnj) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id + '/avecBraldunEnCours/' + avecBraldun + '/avecPnj/' + avecPnj, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getUniqueBraldunId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function getUniqueBraldunId(text, li) {
	if (controleSession(li) == true) {
		$(li.getAttribute('champ')).value = li.getAttribute('id_braldun');
	}
}

function activerRechercheCoffreBraldun(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getCoffreBraldunId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function getCoffreBraldunId(text, li) {
	if (controleSession(li) == true) {
		$('valeur_3').value = li.getAttribute('id_braldun');
		controleQte("");
	}
}

function activerRechercheBraldunIdentificationRune(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getBraldunIdentificationRune, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function getBraldunIdentificationRune(text, li) {
	if (controleSession(li) == true) {
		$('valeur_2').value = li.getAttribute('id_braldun');
		if ($("valeur_1").value==-1){
			$("bouton_demanderidentificationrune").disabled=true;
		} else {
			$("bouton_demanderidentificationrune").disabled=false;
		}
	}
}
/********************************************************************/
/************************* RECHERCHE ********************/
/********************************************************************/

function activerRechercheBraldun(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getSelectionId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function activerRechercheBourlingueur(id, idTypeDistinction) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/bourlingueur/champ/' + id + '/type/' + idTypeDistinction , { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getSelectionId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function activerRechercheAdminBraldun(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getAdminBraldunId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function activerRechercheVoirBraldun(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getVoirId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function controleSession(li) {
	if (li.getAttribute('champ') == null) { // aucun ou trop de résultats
		return false;
	} else if (li.getAttribute('champ') == 'logout') {
		alert("Votre session a expiré, veuillez vous reconnecter.");
		document.location.href = "/";
		return false;
	} else {
		return true;
	}
}

function getVoirId(text, li) {
	if (controleSession(li) == true) {
		document.location.href = "/voir/braldun/?braldun=" + li.getAttribute('id_braldun');
		$('recherche_' + li.getAttribute('champ')).value = 'Chargement en cours...';
	}
}

function getAdminBraldunId(text, li) {
	if (controleSession(li) == true) {
		$('id_braldun').value = li.getAttribute('id_braldun');
	}
}

function getSelectionId(text, li) {
	if (controleSession(li) == true) {
		makeJsListeAvecSupprimer(li.getAttribute('champ'), li.getAttribute('valeur'), li.getAttribute('id_braldun'), li.getAttribute('id_braldun'));
		$('recherche_' + li.getAttribute('champ')).value = '';
	}
}

function makeJsListeAvecSupprimer(champ, valeur, idJos, idBraldun) {
	if ($(champ).value == '') {
		$(champ).value = idJos;
	} else {
		var reg=new RegExp("[,]+", "g");
		var tableau=$(champ).value.split(reg);
		var trouve = false;
		for (var i=0; i<tableau.length; i++) {
			 if (tableau[i] == idJos) {
				 trouve = true;
			 }
		}
		if (trouve == false) {
			$(champ).value = $(champ).value + ',' + idJos;
		} else {
			return;
		}
	}	
	var contenu = window.document.createElement('span');
	contenu.name = 'm_' + champ + '_' + idJos;
	
	var texte = valeur;
	if (idBraldun != null) {
		texte = '<label class="alabel" onclick="javascript:ouvrirWin(\'/voir/braldun/?braldun='+idBraldun+'\');">' + texte + '(' + idBraldun + ')</label> ';
	}
	texte = texte + ' <img src="/public/images/supprimer.gif" onClick="javascript:supprimerElement(\'' + 'aff_' + champ + '\'';
	texte = texte + ',\'' + contenu.name + '\', \'' + champ + '\', ' + idJos + ')" />';
	
	if ($('cpt_' + champ)) {
		$('cpt_' + champ).value = parseInt($('cpt_' + champ).value *1) + parseInt(1);
	}
	if ($('onChange_' + champ)) {
		eval($('onChange_' + champ).value);
	}
	
	contenu.id = contenu.name;
	contenu.innerHTML = texte;
	$('aff_' + champ).appendChild(contenu);
}

function supprimerElement(idConteneur, idContenu, idChamp, valeur) {
	$(idConteneur).removeChild($(idContenu));
	var tabValeur = $(idChamp).value.split(',');
	var nouvelleValeur = '';

	for (i = 0; i < tabValeur.length; i++) {
		if (tabValeur[i] != valeur) {
			if (tabValeur[i] != "") {
				if (nouvelleValeur == "") {
					nouvelleValeur = tabValeur[i];
				} else {
					nouvelleValeur = nouvelleValeur + ',' + tabValeur[i];
				}
			}
		}
	}
	$(idChamp).value = nouvelleValeur;
	if ($('cpt_' + idChamp)) {
		$('cpt_' + idChamp).value = parseInt($('cpt_' + idChamp).value) - parseInt(1);
	}
	if ($('onChange_' + idChamp)) {
		eval($('onChange_' + idChamp).value);
	}
}

function ajouterAuContenu(idsource, iddestination) {
	if ($(iddestination).value == "") {
		$(iddestination).value = $(idsource).value;
	} else {
		$(iddestination).value = $(iddestination).value + ', ' + $(idsource).value;
	}
}

/********************************************************************/
/************************* UDDEIM TOOLS  *************************/
/********************************************************************/
function uddeidswap(id) {
	bb = document.getElementById(id);
	if (bb.style.visibility == 'visible') {
		bb.style.visibility = 'hidden';
	} else {
		bb.style.visibility = 'visible';
	}
}

function uddeIMaddToSelection( frmName, srcListName, tgtListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	var tgtList = eval( 'form.' + tgtListName );
	
	var destinationIds = eval( 'document.' + frmName + '.listids' );

	var srcLen = srcList.length;
	var tgtLen = tgtList.length;
	var tgt = "x";

	var idjoin = new Array();
	
	//build array of target items
	for ( var i=tgtLen-1; i > -1; i-- ) {
		tgt += "," + tgtList.options[i].value + ","
	}

	//Pull selected resources and add them to list	
	for ( var i=0; i < srcLen; i++ ) {
		if ( srcList.options[i].selected && tgt.indexOf( "," + srcList.options[i].value + "," ) == -1 ) {
			if ( srcList.options[i].value == 0 || ( tgtLen != 0 && tgtList.options[0].value == 0 ) ) {
				for ( var j = tgtLen-1; j > -1; j-- ) {
					tgtList.options[j] = null;						
				}
			} 
			opt = new Option( srcList.options[i].text, srcList.options[i].value );
			tgtList.options[tgtList.length] = opt;			
		}
	}
	for ( var i=0; i < tgtList.length; i++ ) {
		idjoin[i] = tgtList.options[i].value;						
	}
	destinationIds.value = idjoin.join( ',' );
}

function uddeIMremoveFromSelection( frmName, srcListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	
	var destinationIds = eval( 'document.' + frmName + '.listids' );
	var idjoin = new Array();

	var srcLen = srcList.length;

	for ( var i=srcLen-1; i > -1; i-- ) {
		if ( srcList.options[i].selected ) {
			srcList.options[i] = null;
			break;
		}
	}
	
	for ( var i=0; i < srcList.length; i++ ) {
		idjoin[i] = srcList.options[i].value;						
	}
	destinationIds.value = idjoin.join( ',' );
}

function userlistdblclick( sel, frmName, srcListName, tgtListName ) {
	uddeIMaddToSelection( frmName, srcListName, tgtListName );
}
function selectionlistdblclick( sel, frmName, srcListName ) {
	uddeIMremoveFromSelection( frmName, srcListName );
}


/********************************************************************/
/*************************** BB Display ***************************/
/********************************************************************/
//bbCode control by
//subBlue design
//www.subBlue.com
//Changed by/for uddeIM
//Changed by/for braldahim

//Startup variables
var imageTag = false;
var theSelection = false;

//Check for Browser & Platform for PC & IE specific bits
//More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
             && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
             && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);

//Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[color=#ff4040]','[/color]','[color=#40ff40]','[/color]','[color=#4040ff]','[/color]','[size=1]','[/size]','[size=2]','[/size]','[size=3]','[/size]','[size=4]','[/size]','[size=5]','[/size]','[ul]','[/ul]','[ol]','[/ol]','[img]','[/img]','[url]','[/url]','[li]','[/li]','[left]','[/left]','[center]','[/center]','[right]','[/right]','[justify]','[/justify]');
imageTag = false;

//Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null)) {
			return i;
		}
	}
	return thearray.length;
}

//Replacement for arrayname.push(value) not implemented in IE until version 5.5
//Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

//Replacement for arrayname.pop() not implemented in IE until version 5.5
//Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}


function emoticon(text) {
	var txtarea = $('myForm').pmessage;
	text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

function bbfontstyle(bbopen, bbclose) {
var txtarea = document.postform.message;

if ((clientVer >= 4) && is_ie && is_win) {
   theSelection = document.selection.createRange().text;
   if (!theSelection) {
      txtarea.value += bbopen + bbclose;
      txtarea.focus();
      return;
   }
   document.selection.createRange().text = bbopen + theSelection + bbclose;
   txtarea.focus();
   return;
}
else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
{
   mozWrap(txtarea, bbopen, bbclose);
   return;
}
else
{
   txtarea.value += bbopen + bbclose;
   txtarea.focus();
}
storeCaret(txtarea);
}


function bbstyle(bbnumber, field) {
	var txtarea = field;

	txtarea.focus();
	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			txtarea.value += bbtags[butnumber + 1];
			buttext = eval('$("myForm").addbbcode' + butnumber + '.src');
			eval('$("myForm").addbbcode' + butnumber + '.src ="' + buttext.substr(0,(buttext.length - 10)) + '.gif"');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection) {
			var sluss;
			var theGuy = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
			// Add tags around selection
			document.selection.createRange().text = theGuy;
			sluss = sel.text.length;
			sel.Text = theGuy;
			if (theGuy.length > 0) {
				sel.moveStart('character', -theGuy.length + sluss);
			}	
			txtarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
		return;
	}
	
	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				// txtarea.value += bbtags[butnumber + 1];
				pasteAtCursor(txtarea, bbtags[butnumber+1]);
				buttext = eval('$("myForm").addbbcode' + butnumber + '.src');
				eval('$("myForm").addbbcode' + butnumber + '.src ="' + buttext.substr(0,(buttext.length - 10)) + '.gif"');
				imageTag = false;
			}
			txtarea.focus();
			return;
	} else { // Open tags
	
		if (imageTag && (bbnumber != 24)) {		// Close image tag before adding another
			// txtarea.value += bbtags[25];
			pasteAtCursor(txtarea, bbtags[25]);
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
			var jubla=$('myForm').addbbcode24.src;
			var juble=jubla.substr(0, (jubla.length - 10));
			var jubli=juble+".gif";
			$('myForm').addbbcode24.src=jubli;
				// Return button back to normal state
			imageTag = false;
		}
		
		// Open tag
		// txtarea.value += bbtags[bbnumber];
		pasteAtCursor(txtarea, bbtags[bbnumber]);
		if ((bbnumber == 24) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(bbcode,bbnumber+1);
		// eval('$('myForm').addbbcode'+bbnumber+'.value += "*"');
		var imgsrcori=eval('$("myForm").addbbcode'+bbnumber+'.src');
		var imgsrcnew=imgsrcori.substr(0, (imgsrcori.length - 4));
		imgsrcnew += "_close.gif";
		eval('$("myForm").addbbcode'+bbnumber+'.src = "'+imgsrcnew+'"');	
		txtarea.focus();
		return;
	}
	storeCaret(txtarea);
}

//From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) 
		selEnd = selLength;

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	
	var anfangs = s1;
	var endes = s1+open+s2+close;
	var anfang = anfangs.length;
	var ende= endes.length;
	
		txtarea.selectionStart = anfang;
		txtarea.selectionEnd = ende;	
	
	return;
}

//Insert at Claret position. Code from
//http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}


//Insert emoticons
function emo(e, field) {
// $('myForm').pmessage.value=$('myForm').pmessage.value+$e;
pasteAtCursor(field, e);
field.focus();
}

function pasteAtCursor(theGirl, theGuy) {
/* This function is based upon a function in PHPMyAdmin */
/* (C) www.phpmyadmin.net. Changed by/for uddeIM */
/* See http://www.gnu.org/copyleft/gpl.html for license */
	if (document.selection) {
		//IE support
		var sluss;
		theGirl.focus();
		sel = document.selection.createRange();
		sluss = sel.text.length;
		sel.text = theGuy;
		if (theGuy.length > 0) {
			sel.moveStart('character', -theGuy.length + sluss);
		}		
	} else if (theGirl.selectionStart || theGirl.selectionStart == '0') {
		//MOZILLA/NETSCAPE support
		var startPos = theGirl.selectionStart;
		var endPos = theGirl.selectionEnd;
		theGirl.value = theGirl.value.substring(0, startPos) + theGuy + theGirl.value.substring(endPos, theGirl.value.length);
		theGirl.selectionStart = startPos + theGuy.length;
		theGirl.selectionEnd = startPos + theGuy.length;
	} else {
		theGirl.value += theGuy;
	}
}

function jsMenuHotel(id, valeur) {
	
	if (id != "hotel_menu_recherche_pratique") $("hotel_menu_recherche_pratique").value = -1;
	if (id != "hotel_menu_recherche_equipements") $("hotel_menu_recherche_equipements").value = -1;
	if (id != "hotel_menu_recherche_munitions") $("hotel_menu_recherche_munitions").value = -1;
	if (id != "hotel_menu_recherche_materiels") $("hotel_menu_recherche_materiels").value = -1;
	if (id != "hotel_menu_recherche_matieres_premieres") $("hotel_menu_recherche_matieres_premieres").value = -1;
	if (id != "hotel_menu_recherche_matieres_transformees") $("hotel_menu_recherche_matieres_transformees").value = -1;
	if (id != "hotel_menu_recherche_aliments_ingredients") $("hotel_menu_recherche_aliments_ingredients").value = -1;
	if (id != "hotel_menu_recherche_potions") $("hotel_menu_recherche_potions").value = -1;
	if (id != "hotel_menu_recherche_runes") $("hotel_menu_recherche_runes").value = -1;
	
	if (valeur != -1) {
		_get_('/hotel/doaction?caction=ask_hotel_voir&'+id+'='+valeur);
	}
}

function disabledAllBtnMessagerie() {
	$('msgBtnSupSelect').disabled = true;
	$('msgBtnArcSelect').disabled = true;
	$('msgBtnMarSelect').disabled = true;
	$('msgBtnSupAll').disabled = true;
	$('msgBtnMarAll').disabled = true;
}

function braltipFixer(id) {
	$(id).className='tipf';
	(Position.offsetParent($(id))).style.zIndex=25;
	$(id + 'clos').style.display='inline';
	if ($(id + 'dep')) {
		$(id + 'dep').style.display='inline';
	}
	$(id + 'fix').style.display='none';
	
	new Draggable(id, { handle: id + 'dep' });
}

function braltipDeFixer(id) {
	$(id).className='tip';
	(Position.offsetParent($(id))).style.zIndex='auto';
	$(id + 'clos').style.display='none';
	if ($(id + 'dep')) {
		$(id + 'dep').style.display='none';
	}
	$(id + 'fix').style.display='inline';
	
	$(id).style.left='0px';
	$(id).style.right='0px';
	$(id).style.top='-3px'
	destroyDraggable(id);
}

function destroyDraggable(id) {
    for(var i=0, size=Draggables.drags.length; i<size; i++) {
      if(Draggables.drags[i].element.id == id) {
        Draggables.drags[i].destroy();
      }
    }
}

function braltipDispEnr(id) {
	if ($(id + 'sel').style.display == 'inline') {
		$(id + 'sel').style.display='none';
	} else {
		$(id + 'sel').style.display='inline';
		$(id + 'btnEnr').disabled=false;
	}
}

function braltipEnr(el, id) {
	el.disabled=true;
	_get_specifique_('/carnet/doaction?caction=do_carnet_enregistre', 'mode=ajout&carnet='+$(id+'numNote').value+'&msg='+id+'msg'+'&texte_carnet='+encodeURIComponent($(id+'texte').innerHTML))
}

function braltipMsg(id) {
	chargeBoxMessagerie();
	_get_("/messagerie/doaction?caction=do_messagerie_message&valeur_1=envoi&valeur_3="+encodeURIComponent($(id+'texte').innerHTML));
	my_switch("box_messagerie","boite_c");
}

