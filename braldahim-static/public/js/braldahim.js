function findSelectedRadioButton(groupname) {
	var radioButtons = $('#myForm').elements[groupname];
	for ( var i = 0; i < radioButtons.length; i++) {
		if (radioButtons[i].checked) {
			return radioButtons[i];
		}
	}
	return null;
}

function _get_(url, encode) {
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
	if ($('#nb_valeurs') && (action == "do")) {
		// Recuperation du nombre de valeur que l'action a besoin
		nb_valeurs = $('#nb_valeurs').val();
		for (i = 1; i <= nb_valeurs; i++) {
			var nom = '#valeur_' + i;
			var elem = $(nom);
			if (elem.type == "radio") {
				radioButton = findSelectedRadioButton(nom);
				if (radioButton != null) {
					valeurs = valeurs + sep + "valeur_" + i + "=" + findSelectedRadioButton(nom).val();
				} else {
					valeurs = valeurs + sep + "valeur_" + i + "=" + elem.val();
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
		$("box_action").innerHTML = "";
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

function textCount(field,counterfield,max) {
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
	if ($("loaded_box_messagerie").value != "1") {
		$("loaded_box_messagerie").value = "1"; 
	}
	_get_("/messagerie/askaction?caction=do_messagerie_message&valeur_1=nouveau&valeur_4=" + idListe);
	my_switch("box_messagerie","boite_c");
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
	
	if (boutonClose == "none") {
		$('#BB_close').hide();
	} else {
		$('#BB_close').show();
	}
	
	$('#BB_overlay').show();
	$('#BB_titre').innerHTML = titre;
	$('#BB_windowwrapper').show();
	$('#'+element).show();
}

function fermeBralBox() {
	$('#BB_overlay').hide();
	$('#BB_windowwrapper').hide();
	$("#erreur").hide();
	$("#erreur_catch").hide();
	$("#box_informations").hide();
	$("#box_action").hide();
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
	$(id).className='tip';
	(Position.offsetParent($(id))).style.zIndex='auto';
	$(id + 'clos').style.display='none';
	$(id + 'dep').style.display='none';
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
				if ($('#valeur_' + i).type == 'select-multiple') {
					for (j=0; j< $('#valeur_' + i).options.length; j++) {
						if ($('#valeur_' + i).options[j].selected == true) {
							if ( i==19 || i==20 || i==23 || i==25 ) {
								poids = parseFloat(poids) + parseFloat($('#valeur_' + i + '_poids_' + $('#valeur_' + i).options[j].val()).val());
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
	if ($('#valeur_' + i).type == 'select-multiple' ) {
		for (j=0; j< $('#valeur_' + i).options.length; j++) {
			if ($('#valeur_' + i).options[j].val() != -1) {
				$('#valeur_' + i).options[j].selected = false;
				cacher = false;
			}
		}
	} else {
		$('#valeur_'+i).val(0);
	}		
	alert ("Cette charrette ne possède pas de panneau amovible, vous ne pouvez transbahuter qu\'un seul type d\'élément ! \n Seul le premier élément sélectionné a été pris en compte.");
}

function controleQte() {
	 v=false;
	 ctrlEchoppe = false;
	 for (i=11;i<=$('#nb_valeurs').val();i++) {
	 	if ($('#valeur_'+i).value > 0 && $('#valeur_panneau').val() != true && v==true) {
			controlePanneau (i);
	 	}
	 	if (controleEchoppe(i) == false ) {
	 		ctrlEchoppe = true;
	 	}
	 	else if ($('#valeur_'+i).val() > 0 ) {
			v=true;
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
	 $('bouton_deposer').attr('disabled', cacher);
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
			if ($('#valeur_' + i).type == 'select-multiple' ) {
				for (j=0; j< $('#valeur_' + i).options.length; j++) {
					if ($('#valeur_' + i).options[j].val() != -1) {
						$('#valeur_' + i).options[j].selected = true;
						cacher = false;
						v = true;
					}
				}
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
			if ($('#valeur_' + i).type == 'select-multiple' ) {
				for (j=0; j< $('#valeur_' + i).options.length; j++) {
					$('#valeur_' + i).options[j].selected = false;
				}
			} else {
				$('#valeur_' + i).val(0);
			}
			return false;
		}
	}
	return true;
}

function afficheTransbahuterRechercheBraldun() {
	if ($('#valeur_2').val() == 4 || $('#valeur_2').val() == 8 || $('#valeur_2').val() == 12) { // constante definie dans Transbahuter.php
		$('#div_braldun').show()
	} else {
		$('#div_braldun').hide()
		$('#valeur_3').val(-1);
	}
	
	if ($('#valeur_2').val() == 8) { // constante definie dans Transbahuter.php
		$('#texte_transbahuter_braldun').html('Vous pouvez réserver cette vente à un unique Braldûn:');
	} else if ($('#valeur_2').val() == 12) { // constante definie dans Transbahuter.php
		$('#texte_transbahuter_braldun').html('Vous pouvez réserver ce lot à un unique Braldûn:');
	} else if ($('#valeur_2').val() == 4) {
		$('#texte_transbahuter_braldun').html('Entrez le Braldûn destinataire:');
	}
}

function afficheTransbahuterVente() {
	if ($('#valeur_2').val() == 8 || $('#valeur_2').val() == 9 || $('#valeur_2').val() == 12) { // constantes definies dans Transbahuter.php
		$('#div_vente_transbahuter').show();
	} else {
		$('div_vente_transbahuter').hide();
	}
}

function controlePrixVenteBoutonDeposer() {
	if ($('#valeur_2').val() == 8 || $('#valeur_2').val() == 9 || $('#valeur_2').val() == 12) { // constantes definies dans Transbahuter.php
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
		$('#recherche_' + id + '_actif').value = 1;
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
		$('recherche_' + id + '_actif').val(1);
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
		$('recherche_' + id + '_actif').val(1);
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

/** *********** *//** *********** */
/** *** splashScreen **** */
/** *********** *//** *********** */


// A self-executing anonymous function,
// standard technique for developing jQuery plugins.

(function($){

	$.fn.splashScreen = function(settings){

		// Providing default options:

		settings = $.extend({
			textLayers		: [],
			textShowTime	: 1500,
			textTopOffset	: 00
		},settings);

		var promoIMG = this;

		// Creating the splashScreen div.
		// The rest of the styling is in splashscreen.css

		var splashScreen = $('<div>',{
			id	: 'splashScreen',
			css:{
				backgroundImage		: promoIMG.css('backgroundImage'),
				backgroundPosition	: 'center '+promoIMG.offset().top+'px',
				height				: $(document).height()
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

$(document).ready(function(){

	// Calling our splashScreen plugin and
	// passing an array with images to be shown
	if ($('#promoIMG').exists()) {
		$('#promoIMG').splashScreen({
			textLayers : [
				'/layout/comte.png',
			]
		});
	}
	
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