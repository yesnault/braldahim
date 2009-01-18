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
							display_erreur = true; // affichage de la boite d'erreur
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

							//alert('Fin entrie \n m_type='+m_type+' \n m_type_valeur='+m_type_valeur);
							if (m_type_valeur == "box_action") {
								display_action = true;
							} else if (m_type_valeur == "erreur" && m_data != "") {
								display_erreur = true; // affichage de la boite d'erreur
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