
function activerRechercheHobbit() {
		new Ajax.Autocompleter ('recherche',
	                        'recherche_update',
	                        '/Recherche/hobbit',
	                        {});
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
		$(iddestination).value = $(iddestination).value + ';' + $(idsource).value;
	}
	
}