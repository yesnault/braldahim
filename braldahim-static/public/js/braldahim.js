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
		nb_valeurs = $('#nb_valeurs').value;
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
		$("#box_action").innerHTML = "Chargement...";
	} else if ($('#nb_valeurs') && (action == "ask")) {
		alert("Code A Supprimer ? m_action.js ligne 72");
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
		if ($('valeur_message').value != '') {
			alert("Votre session a expiré, veuillez vous reconnecter. " +
					"\n\n Attention, vous avez un message en cours de rédaction. Pour le récupérer : " +
					"\n 1 : selectionnez tout (Ctrl+A)" +
					"\n 2 : copiez dans votre presse papier (Ctrl+C)" +
					"\n 3 : appuyez sur la touche Entrée pour fermer cette fenêtre" +
					"\n 4 : coller le texte quelque part (bloc note par ex.) (Ctrl+V)" +
					"\n Le message :\n\n"
					+ $('valeur_message').value);
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
	if (field.value.length > max) {
		field.value = field.value.substring(0, max);
	} else {
		counterfield.value = max - field.value.length;
	}
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
	return '/bourg/relatexml?anneeselect='+$('#anneeRelate').value+'&typeselect='+$('#typeRelate').value;
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

function jsMenuHotel(id, valeur) {
	
	if (id != "hotel_menu_recherche_pratique") $("#hotel_menu_recherche_pratique").value = -1;
	if (id != "hotel_menu_recherche_equipements") $("#hotel_menu_recherche_equipements").value = -1;
	if (id != "hotel_menu_recherche_munitions") $("#hotel_menu_recherche_munitions").value = -1;
	if (id != "hotel_menu_recherche_materiels") $("#hotel_menu_recherche_materiels").value = -1;
	if (id != "hotel_menu_recherche_matieres_premieres") $("#hotel_menu_recherche_matieres_premieres").value = -1;
	if (id != "hotel_menu_recherche_matieres_transformees") $("#hotel_menu_recherche_matieres_transformees").value = -1;
	if (id != "hotel_menu_recherche_aliments_ingredients") $("#hotel_menu_recherche_aliments_ingredients").value = -1;
	if (id != "hotel_menu_recherche_potions") $("#hotel_menu_recherche_potions").value = -1;
	if (id != "hotel_menu_recherche_runes") $("#hotel_menu_recherche_runes").value = -1;
	
	if (valeur != -1) {
		_get_('/hotel/load?caction=do_hotel_voir&'+id+'='+valeur);
	}
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