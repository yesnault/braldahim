
function activerRechercheHobbit(id_active, id_recherche, id_update, id_indicateur) {
		if ($(id_active).value==0) {
			new Ajax.Autocompleter (id_recherche,
	                        id_update,
	                        '/Recherche/hobbit',
	                        {paramName: "valeur", indicator: id_indicateur, minChars: 2, updateElement: getSelectionId});
	        $(id_active).value = 1;
		}
}

function getSelectionId(text, li) {
	alert (text.id);
	alert (text.name);
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