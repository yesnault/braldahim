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

	$("box_chargement").style.display = "block";

	if (url.length > 34) {
		if (url.substring(0, 12) == "/competences") { // /competences/doaction?caction=ask/do
			if ((url.substring(13, 15) == "do") && (url.substring(30, 32) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 9) == "/echoppes") { // /echoppes/doaction?caction=ask/do
			if ((url.substring(10, 12) == "do") && (url.substring(27, 29) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 8) == "/echoppe") { // /echoppe/doaction?caction=ask/do
			if ((url.substring(9, 11) == "do") && (url.substring(26, 28) == "do") || (url.substring(9, 11) == "do") && (url.substring(26, 29) == "ask")) {
				action = "do";
			}
		} else if (url.substring(0, 6) == "/lieux") { // /lieux/doaction?caction=ask/do
			if ((url.substring(7, 9) == "do") && (url.substring(24, 26) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 11) == "/messagerie") { // /messagerie/doaction?caction=ask/do
			if ((url.substring(12, 14) == "do") && (url.substring(29, 31) == "do")) {
				action = "do";
			}
		} else if (url.substring(0, 11) == "/communaute") { // /communaute/doaction?caction=ask/do
			if ((url.substring(12, 14) == "do") && (url.substring(29, 31) == "do")) {
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
	} else if ($('nb_valeurs') && (action == "ask")) {
		alert("Code A Supprimer ? m_action.js ligne 72");
		Modalbox.hide();
	}

	var pars = valeurs + sep + "dateAuth=" + $('dateAuth').value;
	var myAjax = new Ajax.Request(url, { postBody :pars, onComplete :showResponse });
}

function showResponse(originalRequest) {
	var xmldoc = originalRequest.responseXML;
	var textdoc = originalRequest.responseText;
	var display_action = false;
	var display_informations = false;
	var display_erreur = false;
	var activer_wysiwyg = false;

	var xmlHeader = '<?xml version="1.0" encoding="utf-8" ?>';

	if ((xmldoc == null) || (textdoc.substring(0, 39) != xmlHeader)) {
		if (textdoc == "logout") {
			alert("Votre session a expiré, veuillez vous reconnecter.");
			document.location.href = "/";
		} else if (textdoc != "clear") {
			alert('Une erreur est survenue :\n' + textdoc);
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
						if (m_type_valeur == "box_action")
							display_action = true;
						else if (m_type_valeur == "informations" && m_data != "")
							display_informations = true; // affichage de la boite d'informations
						else if (m_type_valeur == "erreur" && m_data != "") display_erreur = true; // affichage de la boite d'erreur

						if (m_type == "display") {
							_display_(m_type_valeur, m_data);
						} else if (m_type == "action") {
							if (m_type_valeur == "goto" && m_data != "") {
								redirection = true;
								redirection_url = m_data;
							} else if (m_type_valeur == "effect.appear" && m_data != "") {
								Effect.Appear(m_data, { duration :2.0 });
							}
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
							} else if (m_type_valeur == "informations" && m_data != "") {
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
								}
							}
						}
					}
				}
			}
		}
	}
	// Box action
	if (display_action) {
		Modalbox.show($("box_action"), { title :'Action', width :450, overlayClose :false });
	}

	// Box informations
	if (display_informations) {
		Modalbox.show($("informations"), { title :'Informations', width :450, overlayClose :false });
	}

	// Box erreur
	if (display_erreur) {
		Modalbox.show($("erreur"), { title :'Une erreur est survenu', width :400, overlayClose :false });
	} else {
		if ($("erreur")) {
			$("erreur").style.display = "none";
		}
	}

	$("box_chargement").style.display = "none";

	if (redirection) {
		document.location.href = redirection_url;
	}

	return;
}
