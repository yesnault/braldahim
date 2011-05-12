function findSelectedRadioButton(groupname) {
	var radioButtons = $('#myForm').elements[groupname];
	for ( var i = 0; i < radioButtons.length; i++) {
		if (radioButtons[i].checked) {
			return radioButtons[i];
		}
	}
	return null;
}

function _get_specifique_(url, valeurs) {
	var sep = '&';
	if ($('#dateAuth')) {
		valeurs = valeurs + sep + "dateAuth=" + $('#dateAuth').val();
	} else {
		valeurs = valeurs + sep + "dateAuth=-1" ;
	}
	var pars = valeurs;
	$.ajax({
		   type: "POST",
		   url: url,
		   processData: false,
		   data: valeurs,
		   success: showResponse
		 });
}

function _get_(url, nomAction, encode) {
	var valeurs = "";
	var nb_valeurs = 0;
	var action = "";
	
	revealModal('#modalPage');
	
	if (url.length > 34) {
		if (url.substring(0, 9) == "/palmares") { // /palmares/doaction?caction=ask/do
			if ((url.substring(10, 12) == "do") && (url.substring(27, 29) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 10) == "/brasserie") { // /brasserie/doaction?caction=ask/do
			if ((url.substring(11, 13) == "do") && (url.substring(28, 30) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 6) == "/hotel") { // /hotel/doaction?caction=ask/do
			if ((url.substring(7, 9) == "do") && (url.substring(24, 28) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 12) == "/competences") { // /competences/doaction?caction=ask/do
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
		} else if (url.substring(0, 4) == "/lot") { // /lot/doaction?caction=ask/do
			if ((url.substring(5, 7) == "do") && (url.substring(22, 24) == "do")) {
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
	
	var suffixe = '';
	if (nomAction) {
		suffixe = '-' + nomAction;
	}
	
	if ($('#nb_valeurs' + suffixe) && (action == "do")) {
		// Recuperation du nombre de valeur que l'action a besoin
		nb_valeurs = $('#nb_valeurs' + suffixe).val();
		for (i = 1; i <= nb_valeurs; i++) {
			var nom = '#valeur_' + i + suffixe;
			var elem = $(nom);
			if (elem.get(0).tagName == "RADIO") {
				radioButton = findSelectedRadioButton(nom);
				if (radioButton != null) {
					valeurs = valeurs + sep + "valeur_" + i + "=" + findSelectedRadioButton(nom).val();
				} else {
					valeurs = valeurs + sep + "valeur_" + i + "=" + elem.val();
				}
			} else if (elem[0].type == "select-multiple") {
				for (j = 0; j<=elem[0].options.length-1; j++) {
					if (elem[0].options[j].selected) {
						valeurs = valeurs + sep + "valeur_" + i + "[]=" + elem[0].options[j].value;
					}
				}
			} else {
				if (encode) {
					valeurs = valeurs + sep + "valeur_" + i + "=" + encodeURIComponent(elem.val());
				} else {
					valeurs = valeurs + sep + "valeur_" + i + "=" + elem.val();
				}
			}
			sep = "&";
		}
		$("#box_action").innerHTML = "Chargement...";
	}
	
	if ($('#dateAuth')) {
		valeurs = valeurs + sep + "dateAuth=" + $('#dateAuth').val();
	} else {
		valeurs = valeurs + sep + "dateAuth=-1" ;
	}
	
	 $.ajax({
		   type: "POST",
		   url: url,
		   processData: false,
		   data: valeurs,
		   success: showResponse
		 });
}

function showResponse(reponse) {
	var xmldoc = reponse;
	var textdoc = reponse.responseText;
	var display_action = false;
	var display_informations = false;
	var display_erreur = false;
	var display_erreur_catch = false;
	var activer_wysiwyg = false;

	var xmlHeader = '<?xml version="1.0" encoding="utf-8" ?>';

	// if ((xmldoc == null) || (textdoc.substring(0, 39) != xmlHeader)) {
	if ((xmldoc == null)) {
		if (textdoc == "") {
			return;
		}
	} else if (xmldoc == "logout") {
		if ($('#valeur_message').val() != '') {
			alert("Votre session a expiré, veuillez vous reconnecter. " +
					"\n\n Attention, vous avez un message en cours de rédaction. Pour le récupérer : " +
					"\n 1 : selectionnez tout (Ctrl+A)" +
					"\n 2 : copiez dans votre presse papier (Ctrl+C)" +
					"\n 3 : appuyez sur la touche Entrée pour fermer cette fenêtre" +
					"\n 4 : coller le texte quelque part (bloc note par ex.) (Ctrl+V)" +
					"\n Le message :\n\n"
					+ $('#valeur_message').val());
		} else {
			alert("Votre session a expiré, veuillez vous reconnecter.");
		}
		document.location.href = "/";
	/*
	 * } else if (xmldoc != "clear") { _display_("erreur_catch", textdoc);
	 * display_erreur_catch = true;
	 */
	} else if (xmldoc == "clear") {
		$("#box_action").innerHTML = "";
	} else {
		estInternetExplorer = false;
		if (navigator.appName == "Microsoft Internet Explorer") {
			estInternetExplorer = false;
		} else {
			estInternetExplorer = true;
		}

		var root = xmldoc.getElementsByTagName('root').item(0);
		for ( var iNode = 0; iNode < root.childNodes.length; iNode++) {
			var node = root.childNodes.item(iNode);

			for (i = 0; i < node.childNodes.length; i++) {
				var sibl = node.childNodes.item(i);
				if (estInternetExplorer == false) {
					if (i == 0) m_type = sibl.text
					if (i == 1) m_type_valeur = sibl.text
					if (i == 2) m_data = sibl.text

					if (i == 2) {
						if (m_type_valeur == "box_action") {
							display_action = true;
						} else if (m_type_valeur == "erreur" && m_data != "")  {
							display_erreur = true; // affichage de la boite
													// d'erreur
						}
						
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

							if (m_type_valeur == "box_action") {
								display_action = true;
							} else if (m_type_valeur == "box_informations" && m_data != "") {
								display_informations = true; // affichage de
																// la boite
																// d'informations
							} else if (m_type_valeur == "erreur" && m_data != "") {
								display_erreur = true; // affichage de la boite
														// d'erreur
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
									// alert('Fin entrie \n m_type='+m_type+' \n
									// m_type_valeur='+m_type_valeur + '
									// m_data='+m_data);
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
		if ($("#erreur")) {
			$("#erreur").hide();
		}
	}
	
	// Box erreur catch
	if (display_erreur_catch) {
		ouvreBralBox("erreur_catch");
	} else {
		if ($("#erreur_catch")) {
			$("#erreur_catch").hide();
		}
	}

	hideModal('#modalPage');
	
	return;
}

function textCount(field, counterfield, max) {
	if (field.val().length > max) {
		field.val(field.val().substring(0, max));
	} else {
		counterfield.val(max - field.val().length);
	}
}

function ecrireMessage(idBraldun) {
	chargeBoxMessagerie();
	_get_("/messagerie/askaction?caction=do_messagerie_message&valeur_1=nouveau&valeur_2=" + idBraldun);
	my_switch("box_messagerie","boite_c");
}

function ecrireMessageListeContact(idListe) {
	if ($("#loaded_box_messagerie").val() != "1") {
		$("#loaded_box_messagerie").val("1"); 
	}
	_get_("/messagerie/askaction?caction=do_messagerie_message&valeur_1=nouveau&valeur_4=" + idListe);
	my_switch("box_messagerie","boite_c");
}

function checkboxCocher(liste, valeur, acacher, aafficher) {
	val = liste.split(',');
	retour = "";
	
	acacher.style.display="none";
	aafficher.show();
	
	for (i = 0; i < val.length; i++) {
		$($('#'+val[i]).attr('checked', valeur));
	}
}

function encodePlus(chaine) {
	var reg = new RegExp("(\\+)", "g");
	return chaine.replace(reg,"[plus]");
}

function _display_(box, data) {
	if (box == "erreur_catch") {
		$("#erreur_catch_contenu").innerHTML = data;
	} else {
		_display_box(box, data);
	}
}

function _display_box(box, data) {
	if ($('#'+box)) {
		$('#'+box).html(data);
	}
	
	if (box == 'racine') { // si l'on fait appel a boxes, on appelle la vue
		// ensuite
		_get_('/interface/load/?box=box_vue');
	}
}

function revealModal(divID) {
    // window.onscroll = function () { $(divID).style.top =
	// document.body.scrollTop; };
    $(divID).show();
}

function hideModal(divID) {
    $(divID).hide();
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
	
	$( "#"+element ).dialog({
		modal: true,
		minWidth : 600,
		closeText : "Fermer",
		 show: 'slide'
	});
}

function fermeBralBox() {
	$("#box_action").dialog("close");
	$("#box_informations").dialog("close");
	$("#erreur").dialog("close");
	$("#erreur_catch").dialog("close");
}

// Switch pour les onglets sur les box
function my_switch(box, conteneur, controleur) {
	val = $('#switch_' + conteneur).val().split(',');
	
	var dejaAffiche = false;
	
	if ($('#'+box) && $("#"+box).css('display') == "block") {
		dejaAffiche = true;
		$('#'+box).hide();
		return;
	}
	
	$('#'+box).show();
	$("#onglet_" + box).className = "onglet actif";
	
	
	for (i = 0; i < val.length; i++) {
		if ($('#'+val[i]) && val[i] != box) {
			$('#'+val[i]).hide();
		}
		$("#onglet_" + val[i]).className = "onglet inactif";
	}
	
	if ($("#loaded_" + box).val() != "1") {
		$("#loaded_" + box).val(1);
		_get_('/'+controleur+'/load/?box='+ box);
	}
}

// Switch pour afficher un div et en cacher un autre
function switch2div(div1, div2) {
	if ($("#"+div1).css('display') == "none") {
		$("#"+div1).show();
		$("#"+div2).hide();
	} else {
		$("#"+div1).hide();
		$("#"+div2).show();
	}
}

function limiteTailleTextarea(textarea, max, iddesc) {
	if (textarea.value.length >= max) {
		textarea.value = textarea.value.substring(0, max);
	}
	var reste = max - textarea.value.length;
	var affichage_reste = reste + ' caract&egrave;res restants';
	$('#' + iddesc).html(affichage_reste);
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
	if (url.substring(0, 6) == '/voir/') {
		url = "http://jeu.braldahim.com" + url;
	}
	window.open(url, titre, "directories=no,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes,width=800,height=600");
}


function box_warning(data) {
	$('#box_warning').show();
	$('#box_warning').innerHTML = data;
}

function messagerie(nbMessageNonLu) {
	if ($('#message_nb_label')) {
		$('#message_nb').show();
		$('#message_nb_img').show();
		$('#img_message_nouveau').hide();
		$('#img_message_ancien').hide();
		
		if (nbMessageNonLu == 1) {
			$('#message_nb_label').innerHTML = " 1 nouveau message&nbsp;";
			$('#img_message_nouveau').show();
		} else if (nbMessageNonLu > 1) {
			$('#message_nb_label').innerHTML = nbMessageNonLu + " nouveaux messages&nbsp;";
			$('#img_message_nouveau').show();
		} else { // 0
			$('#message_nb_label').innerHTML = " Pas de nouveau message&nbsp;";
			$('#img_message_ancien').show();
		}
	}
}

function loadBox(nomSysteme) {
	_get_('/interface/load/?box=' + nomSysteme);
}

function sortGridOnServerRelate(ind, gridObj, direct) {
    return sortGridOnServer(ind, gridObj, direct, getGridQStringRelate(), mygrid);
}

function getGridQStringRelate() {
	return '/bourg/relatexml?anneeselect='+$('#anneeRelate').value+'&typeselect='+$('#typeRelate').val();
}

function goToRelate() {
	document.location.href = '/bourg/?anneeselect='+$('#anneeRelate').value+'&typeselect='+$('#typeRelate').value + "&uid="+(new Date()).valueOf();
}

function sortGridOnServerRecherche(ind, gridObj, direct) {
	return sortGridOnServer(ind, gridObj, direct, getGridQStringRecherche(), mygridRecherche);
}

function getGridQStringRecherche() {
	return "/bourg/relatexml?typeselect=recherche";
}

function sortGridOnServer(ind, gridObj, direct, url, grid) {
	grid.clearAll();
    murl = url + (url.indexOf("?") >=0 ? "&" : "?") + "orderby=" + ind + "&direct=" + direct + "&uid="+(new Date()).valueOf();
    grid.loadXML(murl);
    grid.setSortImgState(true, ind, direct);
    return false;
}

function selectionnerLot(idLot) {
	var texteSelectionne = "Sélectionné, cliquer pour désélectionner";
	var texteSelectionner = "Sélectionner";
	
	var idChamp = '#tabLotsSelectionnes';
	var idChampTexte = '#selectionLotsTexte';
	var tabValeur = $(idChamp).val().split(',');
	var nouvelleValeur = '';
	element = "#selectionnerLot" + idLot;
	
	if ($(element).html() == texteSelectionne) {
		$(element).html(texteSelectionner);
		
		for (i = 0; i < tabValeur.length; i++) {
			if (tabValeur[i] != idLot && tabValeur[i] != "") {
				if (nouvelleValeur == "") {
					nouvelleValeur = tabValeur[i];
				} else {
					nouvelleValeur = nouvelleValeur + ',' + tabValeur[i];
				}
			}
		}
		
	} else {
		$(element).html(texteSelectionne);
		if ($(idChamp).val() != "") {
			nouvelleValeur = $(idChamp).val() + ',' + idLot;
		} else {
			nouvelleValeur = idLot;
		}
	}
	
	$(idChamp).val(nouvelleValeur);
	
	var s='';
	if (nouvelleValeur.indexOf(',') > 0) s = 's';
	if (nouvelleValeur == "") {
		nouvelleValeur = "Lot" + s + " sélectionné" + s + " : aucun";
	} else {
		nouvelleValeur = "Lot" + s + " sélectionné" + s + " : " + nouvelleValeur;
	}
	$(idChampTexte).html(nouvelleValeur);
}

function braltipFixer(id) {
	$(id).className='tipf';
	(Position.offsetParent($(id))).style.zIndex=25;
	$(id + 'clos').style.display='inline';
	$(id + 'dep').style.display='inline';
	$(id + 'fix').style.display='none';
	
	new Draggable(id, { handle: id + 'dep' });
}

function braltipDeFixer(id) {
	$('#'+id).className='tip';
	(Position.offsetParent($('#'+id))).style.zIndex='auto';
	$('#'+id + 'clos').hide();
	$('#'+id + 'dep').hide();
	$('#'+id + 'fix').attr('display','inline');
	
	$('#'+id).style.left='0px';
	$('#'+id).style.right='0px';
	$('#'+id).style.top='-3px'
	destroyDraggable(id);
}

function destroyDraggable(id) {
    for(var i=0, size=Draggables.drags.length; i<size; i++) {
      if(Draggables.drags[i].element.id == id) {
        Draggables.drags[i].destroy();
      }
    }
}


function maccordion_fermer(el) {
	var eldown = el.parents().attr("id") + '-body';
	$("#"+eldown).hide("fast");
}

function maccordion_ouvrir(el) {
	var eldown = el.parents().attr("id") + '-body';
	$("#"+eldown).show("fast");
}

function maccordion(el) {
	var eldown = el.parentNode.id + '-body';
	$("#"+eldown).toggle("fast");
}


/** ***************************************************************** */
/** *********************** Transbahuter ******************* */
/** ***************************************************************** */

function controlePoids() {
	var poids=0;
	if ($('#valeur_2').val() != -1 ) {
		poidsRestant = $('#poids_' + $('#valeur_2').val()).val();
		if (poidsRestant != -1) {
		 	for (i=11; i<=$('#nb_valeurs').val(); i++) {
				if ($('#valeur_' + i)[0].type  == 'select-multiple') {
					for (j=0; j< $('#valeur_' + i)[0].options.length; j++) {
						if ($('#valeur_' + i)[0].options[j].selected == true) {
							if ( i==19 || i==20 || i==23 || i==25 ) {
								poids = parseFloat(poids) + parseFloat($('#valeur_' + i + '_poids_' + $('#valeur_' + i)[0].options[j].value).val());
							} else {
								poids = parseFloat(poids) + parseFloat($('#valeur_' + i + '_poids').val());
							}
						}
					}
				} else {
					poids = parseFloat(poids) + $('#valeur_' + i).val() * $('#valeur_' + i + '_poids').val();
				}
			}
			if (poids > poidsRestant) {
				poidsDep = Math.round((poids - poidsRestant) * 100) / 100;
				alert ('Pas assez de place dans la source d\'arrivée !\nVous dépassez de ' + poidsDep + ' kg');
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	} else {
		return true;
	}
}

function controlePanneau (i) {
	if ($('#valeur_' + i)[0].type  == 'select-multiple' ) {
		for (j=0; j< $('#valeur_' + i)[0].options.length; j++) {
			$('#valeur_' + i + ' option').attr("selected","false");
			cacher = false;
		}
	} else {
		$('#valeur_'+i).val(0);
	}		
	alert ("Cette charrette ne possède pas de panneau amovible, vous ne pouvez transbahuter qu\'un seul type d\'élément ! \n Seul le premier élément sélectionné a été pris en compte.");
}

function controleQte() {
	 v = false;
	 ctrlEchoppe = false;
	 for (i=11;i<=$('#nb_valeurs').val();i++) {
	 	if ($('#valeur_'+i)[0].value > 0 && $('#valeur_panneau').val() != true && v==true) {
			controlePanneau (i);
	 	}
	 	if (controleEchoppe(i) == false ) {
	 		ctrlEchoppe = true;
	 	}
	 	else if ($('#valeur_'+i)[0].value > 0 ) {
			v = true;
		}
	 }
	 
	 cacher = true;
	 if (ctrlEchoppe == true ) {
		 alert ("Dans une échoppe, vous ne pouvez transbahuter que des matières premières !");
	 }
	 poidsOk = controlePoids();
	 if (v==true && $('#valeur_1').val() != -1 && $('#valeur_2').val() != -1 && poidsOk == true) {
		cacher = false;
	 }
	 if ($('#valeur_2').val() == 4 && $('#valeur_3').val() == -1) {
		cacher = true;
	 }
	 $('#bouton_deposer').attr('disabled', cacher);
}

function selectAll(valmin, valmax) {
	cacher = true;
	v = false;
	ctrlEchoppe = false;
	for (i=valmin; i<=valmax; i++) {
		if ($('#valeur_panneau').val() != true && v==true) {
			controlePanneau (i);
			break;
	 	}
		if ($('#valeur_' + i + '_echoppe').val() == 'oui' || $('#valeur_2').val() != 5) {
			if ($('#valeur_' + i)[0].type == 'select-multiple' ) {
				$('#valeur_' + i + ' option').attr("selected","selected");
				cacher = false;
				v = true;
			} else {
				$('#valeur_' + i).val($('#valeur_' + i + '_max').val());
				if (cacher == true && $('#valeur_' + i + '_max').val() > 0) {
					cacher = false;
					v = true;
				}
			}
		} else {
			ctrlEchoppe = true;
		}
	}
	if (ctrlEchoppe == true ) {
		alert ("Dans une échoppe, vous ne pouvez transbahuter que des matières premières !");
	}
	poidsOk = controlePoids();
	if ($('#valeur_1').val() == -1 || $('#valeur_2').val() == -1 || poidsOk == false) {
		cacher = true;
	}
	/* Coffre */
	if ($('#valeur_2').val() == 4 && $('#valeur_3').val() == -1) {
		cacher = true;
	}
	$('#bouton_deposer').attr('disabled', cacher);
}

function charrette() {
	if ($('#valeur_2').val() >= 13) {
		$('#valeur_3').val($('#id_charrette_' + $('#valeur_2').val()).val());
	}
}

function controleEchoppe(i) {
	if ($('#valeur_2').val() == 5) {
		if ( ($('#valeur_' + i + '_echoppe').val() == 'non') && $('#valeur_' + i).val() > 0) {
			if ($('#valeur_' + i)[0].type  == 'select-multiple' ) {
				$('#valeur_' + i + ' option').attr("selected","false");
			} else {
				$('#valeur_' + i).val(0);
			}
			return false;
		}
	}
	return true;
}

function afficheTransbahuterRechercheBraldun() {
	// constante definie dans Transbahuter.php
	if ($('#valeur_2').val() == 4 || $('#valeur_2').val() == 8 || $('#valeur_2').val() == 12) { 
		$('#div_braldun').show()
	} else {
		$('#div_braldun').hide()
		$('#valeur_3').val(-1);
	}
	
	// constante definie dans Transbahuter.php
	if ($('#valeur_2').val() == 8) { 
		$('#texte_transbahuter_braldun').html('Vous pouvez réserver cette vente à un unique Braldûn:');
		// constante definie dans Transbahuter.php
	} else if ($('#valeur_2').val() == 12) { 
		$('#texte_transbahuter_braldun').html('Vous pouvez réserver ce lot à un unique Braldûn:');
	} else if ($('#valeur_2').val() == 4) {
		$('#texte_transbahuter_braldun').html('Entrez le Braldûn destinataire:');
	}
}

function afficheTransbahuterVente() {
	// constantes definies dans Transbahuter.php
	if ($('#valeur_2').val() == 8 || $('#valeur_2').val() == 9 || $('#valeur_2').val() == 12) { 
		$('#div_vente_transbahuter').show();
	} else {
		$('#div_vente_transbahuter').hide();
	}
}

function controlePrixVenteBoutonDeposer() {
	// constantes definies dans Transbahuter.php
	if ($('#valeur_2').val() == 8 || $('#valeur_2').val() == 9 || $('#valeur_2').val() == 12) { 
		if ($('#valeur_4').value >= 0 && $('#valeur_4').val() != '' && $('#valeur_5').val() !=-1 ) {
			return true;
		} else {
			alert('Il faut rentrer un prix valide');
			return false;
		}
	}
	return true;
}

function activerRechercheUniqueBraldun(id, avecBraldun, avecPnj) {
	if ($('#recherche_' + id + '_actif').val() == 0) {
		new Ajax.Autocompleter('#recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id + '/avecBraldunEnCours/' + avecBraldun + '/avecPnj/' + avecPnj, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getUniqueBraldunId, parameters : { champ :'value' } });
		$('#recherche_' + id + '_actif').val(1);
	}
}

function getUniqueBraldunId(text, li) {
	if (controleSession(li) == true) {
		$(li.getAttribute('champ')).val(li.getAttribute('id_braldun'));
	}
}

function activerRechercheTransbahuterBraldun(id) {
	if ($('#recherche_' + id + '_actif').val() == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getTransbahuterBraldunId, parameters : { champ :'value' } });
		$('#recherche_' + id + '_actif').val(1);
	}
}

function getTransbahuterBraldunId(text, li) {
	if (controleSession(li) == true) {
		$('#valeur_3').val(li.getAttribute('id_braldun'));
		controleQte("");
	}
}

function activerRechercheBraldunIdentificationRune(id) {
	if ($('#recherche_' + id + '_actif').val() == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/braldun/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getBraldunIdentificationRune, parameters : { champ :'value' } });
		$('#recherche_' + id + '_actif').val(1);
	}
}

function getBraldunIdentificationRune(text, li) {
	if (controleSession(li) == true) {
		$('#valeur_2').val(li.getAttribute('id_braldun'));
		if ($("#valeur_1").val() == -1) {
			$("#bouton_demanderidentificationrune").attr('disabled', true);
		} else {
			$("#bouton_demanderidentificationrune").attr('disabled', false);
		}
	}
}



/** ***************************************************************** */
/** *********************** RECHERCHE ******************* */
/** ***************************************************************** */

function activerRechercheBraldun(id) {
	$( "#recherche_" + id).autocomplete({
		source: "/Recherche/braldun/",
		minLength: 2,
		select: function( event, ui ) {
			if (ui.item && ui.item.id > 0) {
				makeJsListeAvecSupprimer(id, ui.item.value, ui.item.id, ui.item.id);
			}
		}
	});
}

function activerRechercheBourlingueur(id, idTypeDistinction) {
	$( "#recherche_" + id).autocomplete({
		source: "/Recherche/braldun/type/"+ idTypeDistinction,
		minLength: 2,
		select: function( event, ui ) {
			if (ui.item && ui.item.id > 0) {
				makeJsListeAvecSupprimer(id, ui.item.value, ui.item.id, ui.item.id);
			}
		}
	});
}

function activerRechercheAdminBraldun(id) {
	$( "#recherche_" + id).autocomplete({
		source: "/Recherche/braldun/",
		minLength: 2,
		select: function( event, ui ) {
			if (ui.item && ui.item.id > 0) {
				$('#id_braldun').val(ui.item.id);
			}
		}
	});
}

function activerRechercheVoirBraldun(id) {
	
	$( "#recherche_" + id).autocomplete({
		source: "/Recherche/braldun/",
		minLength: 2,
		select: function( event, ui ) {
			if (ui.item && ui.item.id > 0) {
				document.location.href = "/voir/braldun/?braldun=" + ui.item.id;
				$('#recherche_' + id).val('Chargement en cours...');
			}
		}
	});
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

function makeJsListeAvecSupprimer(champ, valeur, idJos, idBraldun) {
	if ($("#"+champ).val() == '') {
		$("#"+champ).val(idJos);
	} else {
		var reg = new RegExp("[,]+", "g");
		var tableau = $("#"+champ).val().split(reg);
		var trouve = false;
		for (var i=0; i<tableau.length; i++) {
			 if (tableau[i] == idJos) {
				 trouve = true;
			 }
		}
		if (trouve == false) {
			$("#"+champ).val($("#"+champ).val() + ',' + idJos);
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
	texte = texte + ' <img src="'+$('#urlStatique').val() + '/images/divers/supprimer.gif" onClick="javascript:supprimerElement(\'' + 'aff_' + champ + '\'';
	texte = texte + ',\'' + contenu.name + '\', \'' + champ + '\', ' + idJos + ')" />';
	
	if ($('#cpt_' + champ)) {
		$('#cpt_' + champ).val(parseInt($('#cpt_' + champ).val() *1) + parseInt(1));
	}
	if ($('#onChange_' + champ)) {
		eval($('#onChange_' + champ).val());
	}
	
	contenu.id = contenu.name;
	contenu.innerHTML = texte;
	$('#aff_' + champ).append(contenu);
}

function supprimerElement(idConteneur, idContenu, idChamp, valeur) {
	$("#"+idContenu).remove();
	var tabValeur = $("#"+idChamp).val().split(',');
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
	$("#"+idChamp).val(nouvelleValeur);
	if ($('#cpt_' + idChamp)) {
		$('#cpt_' + idChamp).val(parseInt($('#cpt_' + idChamp).val()) - parseInt(1));
	}
	if ($('#onChange_' + idChamp)) {
		eval($('#onChange_' + idChamp).val());
	}
}

function ajouterAuContenu(idsource, iddestination) {
	if ($("#"+iddestination).val() == "") {
		$("#"+iddestination).val($(idsource).val());
	} else {
		$("#"+iddestination).val($(iddestination).val() + ', ' + $(idsource).val());
	}
}


/** ***************************************************************** */
/** ************************* BB Display ************************** */
/** ***************************************************************** */
// bbCode control by
// subBlue design
// www.subBlue.com
// Changed by/for uddeIM
// Changed by/for braldahim

// Startup variables
var imageTag = false;
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from:
// http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
             && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
             && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);

// Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[color=#ff4040]','[/color]','[color=#40ff40]','[/color]','[color=#4040ff]','[/color]','[size=1]','[/size]','[size=2]','[/size]','[size=3]','[/size]','[size=4]','[/size]','[size=5]','[/size]','[ul]','[/ul]','[ol]','[/ol]','[img]','[/img]','[url]','[/url]','[li]','[/li]','[left]','[/left]','[center]','[/center]','[right]','[/right]','[justify]','[/justify]');
imageTag = false;

// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null)) {
			return i;
		}
	}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
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
			buttext = eval('$("#addbbcode' + butnumber + '").attr("src")');
			eval('$("#addbbcode' + butnumber + '").attr("src", "' + buttext.substr(0, (buttext.length - 10)) + '.gif")');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text; // Get text
																// selection
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
	} else if (txtarea[0].selectionEnd && (txtarea[0].selectionEnd - txtarea[0].selectionStart > 0)) {
		mozWrap(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
		return;
	}
	
	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber + 1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked &
							// default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				// txtarea.value += bbtags[butnumber + 1];
				pasteAtCursor(txtarea, bbtags[butnumber+1]);
				buttext = eval('$("#addbbcode' + butnumber + '").attr("src")');
				eval('$("#addbbcode' + butnumber + '").attr("src", "' + buttext.substr(0, (buttext.length - 10)) + '.gif")');
				imageTag = false;
			}
			txtarea.focus();
			return;
	} else { // Open tags
	
		if (imageTag && (bbnumber != 24)) {		// Close image tag before adding
												// another
			// txtarea.value += bbtags[25];
			pasteAtCursor(txtarea, bbtags[25]);
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag
												// from the list
			var jubla = $('#addbbcode24').attr("src");
			var juble = jubla.substr(0, (jubla.length - 10));
			var jubli = juble+".gif";
			$('#addbbcode24').attr("src",jubli);
			// Return button back to normal state
			imageTag = false;
		}
		
		// Open tag
		// txtarea.value += bbtags[bbnumber];
		pasteAtCursor(txtarea, bbtags[bbnumber]);
		// Check to stop additional tags after an unclosed image tag
		if ((bbnumber == 24) && (imageTag == false)) {
			imageTag = 1; 
		}
		arraypush(bbcode,bbnumber+1);
		// eval('$('#myForm').addbbcode'+bbnumber+'.value += "*"');
		var imgsrcori = eval('$("#addbbcode'+bbnumber+'").attr("src")');
		var imgsrcnew = imgsrcori.substr(0, (imgsrcori.length - 4));
		imgsrcnew += "_close.gif";
		eval('$("#addbbcode'+bbnumber+'").attr("src", "' + imgsrcnew + '")');	
		txtarea.focus();
		return;
	}
	storeCaret(txtarea);
}

// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close) {
	var selLength = txtarea[0].textLength;
	var selStart = txtarea[0].selectionStart;
	var selEnd = txtarea[0].selectionEnd;
	if (selEnd == 1 || selEnd == 2) {
		selEnd = selLength;
	}

	var s1 = txtarea.val().substring(0, selStart);
	var s2 = txtarea.val().substring(selStart, selEnd)
	var s3 = txtarea.val().substring(selEnd, selLength);
	txtarea.val(s1 + open + s2 + close + s3);
	
	var anfangs = s1;
	var endes = s1 + open + s2 + close;
	var anfang = anfangs.length;
	var ende= endes.length;

	txtarea[0].selectionStart = anfang;
	txtarea[0].selectionEnd = ende;	
	
	return;
}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) {
		textEl.caretPos = document.selection.createRange().duplicate();
	}
}

// Insert emoticons
function emo(e, field) {
	// $('#myForm').pmessage.value=$('myForm').pmessage.value+$e;
	pasteAtCursor(field, e);
	field.focus();
}

function pasteAtCursor(theGirl, theGuy) {
	/* This function is based upon a function in PHPMyAdmin */
	/* (C) www.phpmyadmin.net. Changed by/for uddeIM */
	/* See http://www.gnu.org/copyleft/gpl.html for license */
	if (document.selection) {
		// IE support
		var sluss;
		theGirl.focus();
		sel = document.selection.createRange();
		sluss = sel.text.length;
		sel.text = theGuy;
		if (theGuy.length > 0) {
			sel.moveStart('character', -theGuy.length + sluss);
		}		
	} else if (theGirl[0].selectionStart || theGirl[0].selectionStart == '0') {
		// MOZILLA/NETSCAPE support
		var startPos = theGirl[0].selectionStart;
		var endPos = theGirl[0].selectionEnd;
		theGirl.val(theGirl.val().substring(0, startPos) + theGuy + theGirl.val().substring(endPos, theGirl.val().length));
		theGirl[0].selectionStart = startPos + theGuy.length;
		theGirl[0].selectionEnd = startPos + theGuy.length;
	} else {
		theGirl.val(theGirl.val() + theGuy);
	}
}


/** *********** *//** *********** */
/** *** CHIFFRES  **** */
/** *********** *//** *********** */
//n'autorise que des chiffres.
//exemple d'utilisation : <input type="text" onkeypress="chiffres(event)">
function chiffres(event, negatif) {
	// Compatibilité IE / Firefox
	if (!event && window.event) {
		event = window.event;
	}

	// IE tab, fleches deplacement
	// backspace ou delete
	if (event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || 
			event.keyCode == 46 || event.keyCode == 8) { 
		return;
	} else if (event.keyCode < 48 || event.keyCode > 57) {
		event.returnValue = false;
		event.cancelBubble = true;
	}

	// DOM backspace ou delete
	if (event.which == 9 || event.which == 46 || event.which == 8) { 
		return;
	} else if (negatif != null && event.which == 45) { // signe -
		return;
	} else if (event.which < 48 || event.which > 57) {
		event.preventDefault();
		event.stopPropagation();
	}
}

/** *********** *//** *********** */
/** *** splashScreen **** */
/** *********** *//** *********** */

(function($){

	$.fn.splashScreen = function(settings) {

	// Providing default options:

	settings = $.extend({
		textLayers : [],
		textShowTime : 1500,
		textTopOffset : 00
	},settings);

	var promoIMG = this;

	// Creating the splashScreen div.
	// The rest of the styling is in splashscreen.css

	var splashScreen = $('<div>',{
		id : 'splashScreen',
		css:{
			backgroundImage : promoIMG.css('backgroundImage'),
			backgroundPosition : 'center '+promoIMG.offset().top+'px',
			height : $(document).height()
		}
	});
	
	$('body').append(splashScreen);
	
	splashScreen.click(function(){
		splashScreen.fadeOut('slow');
	});
	
	// Binding a custom event for changing the current visible text
	// according
	// to the contents of the textLayers array (passed as a parameter)

	splashScreen.bind('changeText',function(e,newID){
	
		// If the image that we want to show is
		// within the boundaries of the array:
	
		if(settings.textLayers[newID]){
			showText(newID);
		}
		else {
			splashScreen.click();
		}
	});

	splashScreen.trigger('changeText',0);

	// Extracting the functionality into a
	// separate function for convenience.

	function showText(id){
		var text = $('<img>',{
		src:settings.textLayers[id],
		css: {
			marginTop : promoIMG.offset().top+settings.textTopOffset
		}
	}).hide();

	text.load(function(){
		// text.fadeIn('slow').delay(settings.textShowTime).fadeOut('slow',function(){
		text.fadeIn('slow');
		splashScreen.delay(settings.textShowTime).click();
		/*
		* .fadeOut('slow',function(){ text.remove();
		* splashScreen.trigger('changeText',[id+1]); });
		*/
	});

	splashScreen.append(text);
	}

	return this;
	}

})(jQuery);


jQuery.fn.exists = function(){return jQuery(this).length>0;}

$(document).ready(function() {

	// Calling our splashScreen plugin and
	// passing an array with images to be shown
	if ($('#promoIMG').exists()) {
		$('#promoIMG').splashScreen({
			textLayers : [
				$('#urlStatique').val() + '/images/layout/comte.png',
			]
		});
		
		$(function() {
		    // Use this example, or...
		    $('a[rel*=lightboxScreenshot]').lightBox(); // Select all links that contains lightbox in the attribute rel
		    $('a[rel*=lightboxCarte]').lightBox(); // Select all links that contains lightbox in the attribute rel
		});
	}
	
	if ($('#main-cycle').exists()) {
		$('#main-cycle').cycle({ 
			fx: 'scrollHorz',
		    speed: $('body.lte8').length ? 0 : 1000,
			timeout: 0,
			timeout: 20000,
			next:   '#c-next', 
			prev:   '#c-prev' ,
		    cleartype: true,
			cleartypeNoBg: true
		});
	}


/*$('#main-cycle').cycle({
	fx: 'scrollLeftCustom',
	easing: 'easeInOutExpo',
	speed: $('body.lte8').length ? 0 : 1000,
	timeout: 0,
	timeout: 8000,
	width: 840,
	next: '#c-next',
	prev: '#c-prev',
	cleartype: true,
	cleartypeNoBg: true
	}); */
	
	if ($('#pageflip').exists()) {
		$("#pageflip").hover(function() { // On hover...
			$("#pageflip img , .msg_block").stop()
				.animate({ // Animate and expand the image and the msg_block
							// (Width + height)
					width: '307px',
					height: '319px'
				}, 500);
			} , function() {
			$("#pageflip img").stop() // On hover out, go back to original
										// size 50x52
				.animate({
					width: '50px',
					height: '52px'
				}, 220);
			$(".msg_block").stop() // On hover out, go back to original size
									// 50x50
				.animate({
					width: '50px',
					height: '50px'
				}, 200); // Note this one retracts a bit faster (to prevent
							// glitching in IE)
		});
	}
});

