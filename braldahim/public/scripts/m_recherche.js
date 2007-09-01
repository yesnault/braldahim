
function activerRechercheHobbit(id) {
		if ($('recherche_'+id+'_actif').value==0) {
			new Ajax.Autocompleter ('recherche_'+id,
	                        'recherche_'+id+'_update',
	                        '/Recherche/hobbit/champ/'+id,
	                        {paramName: "valeur", indicator: 'indicateur_recherche_'+id, minChars: 2, afterUpdateElement: getSelectionId, parameters:{champ: 'value'}});
	        $('recherche_'+id+'_actif').value = 1;
		}
}

function getSelectionId(text, li) {
	if (li.getAttribute('champ') == null) { // aucun ou trop de résultats
		return;
	}
	if ($(li.getAttribute('champ')).value == '') {
		$(li.getAttribute('champ')).value = li.getAttribute('id_hobbit') + ',';
	} else {
		$(li.getAttribute('champ')).value = $(li.getAttribute('champ')).value + li.getAttribute('id_hobbit')+ ',';
	}
	
	var contenu = window.document.createElement('span');
	contenu.name = 'm_'+li.getAttribute('champ')+'_'+li.getAttribute('id_hobbit');
	var texte = li.getAttribute('valeur') + ' <img src="/public/images/supprimer.gif" onClick="javascript:supprimerElement(\''+'aff_'+li.getAttribute('champ')+'\',\''+contenu.name+'\', \''+li.getAttribute('champ')+'\', '+li.getAttribute('id_hobbit')+')" />';
	contenu.id = contenu.name;
	contenu.innerHTML = texte;
	$('aff_'+li.getAttribute('champ')).appendChild(contenu);
	$('recherche_'+li.getAttribute('champ')).value='';
}

function supprimerElement(id_conteneur, id_contenu, id_champ, valeur) {
	$(id_conteneur).removeChild($(id_contenu));
	var tab_valeur = $(id_champ).value.split(',');
	var nouvelle_valeur = '';
	
	for (i=0; i<tab_valeur.length ; i++) {
		if (tab_valeur[i] != valeur) {
			if (tab_valeur[i] != "") {
				nouvelle_valeur = nouvelle_valeur + tab_valeur[i] + ',';
			}
		}
	}
    $(id_champ).value = nouvelle_valeur;
}

function activer_wysiwyg(id) {
	tinyMCE.execCommand('mceAddControl', false, id);
}

function desactiver_wysiwyg(id) {
	tinyMCE.execCommand('mceRemoveControl', false, id);
}

function ajouterAuContenu(idsource, iddestination) {
	if ($(iddestination).value == "") {
		$(iddestination).value = $(idsource).value;
	} else {
		$(iddestination).value = $(iddestination).value + ', ' + $(idsource).value;
	}
}