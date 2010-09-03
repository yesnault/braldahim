function findSelectedRadioButton(groupname) {
	var radioButtons = $('myForm').elements[groupname];
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
	
	revealModal('modalPage');
	
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
			} else {
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
	}
	
	if ($('dateAuth')) {
		valeurs = valeurs + sep + "dateAuth=" + $('dateAuth').value;
	} else {
		valeurs = valeurs + sep + "dateAuth=-1" ;
	}
	var pars = valeurs;
	// $("box_action").innerHTML = "";
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
		}
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
						}
					}
				} else {
					for (x = 0; x < sibl.childNodes.length; x++) {
						if (i == 1) m_type = node.childNodes.item(1).childNodes.item(0).data;
						if (i == 3) m_type_valeur = node.childNodes.item(3).childNodes.item(0).data;
						if (i == 5) m_data = node.childNodes.item(5).childNodes.item(0).data;
						if (i == 5) {

							// alert('Fin entrie \n m_type='+m_type+' \n
							// m_type_valeur='+m_type_valeur);
							if (m_type_valeur == "box_action") {
								display_action = true;
							} else if (m_type_valeur == "erreur" && m_data != "") {
								display_erreur = true; // affichage de la boite
														// d'erreur
							}

							if (m_type == "display") {
								_display_(m_type_valeur, m_data);
							}
						}
					}
				}
			}
		}
	}

	// Box erreur
	if (display_erreur) {
		$("erreur").style.display = "block";
	} else {
		if ($("erreur")) {
			$("erreur").style.display = "none";
		}
	}
	
	// Box erreur catch
	if (display_erreur_catch) {
		$("erreur_catch").style.display = "block";
	} else {
		if ($("erreur_catch")) {
			$("erreur_catch").style.display = "none";
		}
	}

	hideModal('modalPage');
	
	if (redirection) {
		document.location.href = redirection_url;
	}

	return;
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
		// erreur si aucune popup n'a ete ouverte depuis l'arrivee sur
		// l'interface
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

function sortGridOnServerRelate(ind, gridObj, direct) {
    return sortGridOnServer(ind, gridObj, direct, url, mygrid);
}

function getGridQStringRelate() {
	return '/bourg/relatexml?anneeselect='+$('anneeRelate').value+'&typeselect='+$('typeRelate').value;
}

function goToRelate() {
	document.location.href = '/bourg/?anneeselect='+$('anneeRelate').value+'&typeselect='+$('typeRelate').value + "&uid="+(new Date()).valueOf();;
}

function sortGridOnServerRecherche(ind, gridObj, direct) {
	return sortGridOnServer(ind, gridObj, direct, url, mygridRecherche);
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
		_get_('/hotel/load?caction=do_hotel_voir&'+id+'='+valeur);
	}
}
