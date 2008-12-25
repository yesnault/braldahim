
function activerRechercheHobbit(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/hobbit/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getSelectionId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function activerRechercheAdminHobbit(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/hobbit/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getAdminHobbitId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function activerRechercheVoirHobbit(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/hobbit/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
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
		document.location.href = "/voir/hobbit/?hobbit=" + li.getAttribute('id_hobbit');
		$('recherche_' + li.getAttribute('champ')).value = 'Chargement en cours...';
	}
}

function getAdminHobbitId(text, li) {
	if (controleSession(li) == true) {
		$('id_hobbit').value = li.getAttribute('id_hobbit');
	}
}

function getSelectionId(text, li) {
	if (controleSession(li) == true) {
		makeJsListeAvecSupprimer(li.getAttribute('champ'), li.getAttribute('valeur'), li.getAttribute('id_fk_jos_users_hobbit'), li.getAttribute('id_hobbit'));
		$('recherche_' + li.getAttribute('champ')).value = '';
	}
}

function makeJsListeAvecSupprimer(champ, valeur, idJos, idHobbit) {
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
	if (idHobbit != null) {
		texte = texte + ' (' + idHobbit + ') ';
	}
	texte = texte + ' <img src="/public/images/supprimer.gif" onClick="javascript:supprimerElement(\'' + 'aff_' + champ + '\'';
	texte = texte + ',\'' + contenu.name + '\', \'' + champ + '\', ' + idJos + ')" />';
	
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
}

function ajouterAuContenu(idsource, iddestination) {
	if ($(iddestination).value == "") {
		$(iddestination).value = $(idsource).value;
	} else {
		$(iddestination).value = $(iddestination).value + ', ' + $(idsource).value;
	}
}

