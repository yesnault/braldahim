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
