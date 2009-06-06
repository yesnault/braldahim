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

	if (box == 'racine') { // si l'on fait appel a boxes, on appelle la vue ensuite
		_get_('/interface/load/?box=box_vue');
	}
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
	
	fermeturePopup();
	
	if ($("loaded_" + box).value != "1") {
		$("loaded_" + box).value = 1;
		_get_('/interface/load/?box='+ box);
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

// n'autorise que des chiffres.
// exemple d'utilisation : <input type="text" onkeypress="chiffres(event)">
function chiffres(event, negatif) {
	// Compatibilité IE / Firefox
	if (!event && window.event) {
		event = window.event;
	}

	// IE 
	if (event.keyCode == 37 || event.keyCode == 39 || // fleches deplacement
			event.keyCode == 46 || event.keyCode == 8) { // backspace ou delete
		return;
	} else if (event.keyCode < 48 || event.keyCode > 57) {
		event.returnValue = false;
		event.cancelBubble = true;
	}

	// DOM
	if (event.which == 46 || event.which == 8) { // backspace ou delete
		return;
	} else if (negatif != null && event.which == 45) { // signe -
		return;
	} else if (event.which < 48 || event.which > 57) {
		event.preventDefault();
		event.stopPropagation();
	}
}

function maccordion_fermer(el) {
	var eldown = el.parentNode.id + '-body';
	if ($(eldown).style.display != "none") {
		new Effect.SlideUp(eldown, { duration :0.1 });
		el.style.backgroundImage='url("/public/images/collapsed.gif")';
	}
}

function maccordion_ouvrir(el) {
	var eldown = el.parentNode.id + '-body';
	new Effect.SlideDown(eldown, { duration :0.1 });
	el.style.backgroundImage='url("/public/images/expanded.gif")';
}

function maccordion(el) {
	var eldown = el.parentNode.id + '-body';
	if ($(eldown)) {
		if ($(eldown).style.display == "none") {
			maccordion_ouvrir(el);
		} else {
			maccordion_fermer(el);
		}
	}
}

function limiteTailleTextarea(textarea, max, iddesc) {
	if (textarea.value.length >= max) {
		textarea.value = textarea.value.substring(0, max);
	}
	var reste = max - textarea.value.length;
	var affichage_reste = reste + ' caract&egrave;res restants';
	$(iddesc).innerHTML = affichage_reste;
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
function ouvrirWin(url, titre) {
	window.open(url, titre, "directories=no,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes,width=815,height=600");
}

function messagerie(nbMessageNonLu) {
	if ($('message_nb_label')) {
		$('message_nb').style.display = "block";
		$('message_nb_img').style.display = "block";
		$('img_message_nouveau').style.display = "none";
		$('img_message_ancien').style.display = "none";
		
		if (nbMessageNonLu == 1) {
			$('message_nb_label').innerHTML = " 1 nouveau message&nbsp;";
			$('img_message_nouveau').style.display = "block";
		} else if (nbMessageNonLu > 1) {
			$('message_nb_label').innerHTML = nbMessageNonLu + " nouveaux messages&nbsp;";
			$('img_message_nouveau').style.display = "block";
		} else { // 0
			$('message_nb_label').innerHTML = " Pas de nouveau message&nbsp;";
			$('img_message_ancien').style.display = "block";
		}
	}
}

function loadBox(nomSysteme) {
	_get_('/interface/load/?box=' + nomSysteme);
}

function revealModal(divID) {
    window.onscroll = function () { $(divID).style.top = document.body.scrollTop; };
    $(divID).style.display = "block";
    $(divID).style.top = document.body.scrollTop;
    document.documentElement.scrollTop = 0;
}

function hideModal(divID) {
    $(divID).style.display = "none";
}

function ecrireMessage(idHobbit) {
	fermeturePopup();
	if ($("loaded_box_messagerie").value != "1") {
		// pour eviter de recharger box_messagerie lors du my_switch en dessous
		// si l'onglet n'a jamais été vu.
		$("loaded_box_messagerie").value = "1"; 
	}
	_get_("/messagerie/askaction?caction=do_messagerie_message&valeur_1=nouveau&valeur_2=" + idHobbit);
	my_switch("box_messagerie","boite_c");
}

function fermeturePopup() {
	try {
		return cClick(); // fermeture popup
	} catch (e) {
		// erreur si aucune popup n'a ete ouverte depuis l'arrivee sur l'interface
	}
}

function encodePlus(chaine) {
	var reg=new RegExp("(\\+)", "g");
	return chaine.replace(reg,"[plus]");
}

function chaineCheckbox(liste) {
	val = liste.split(',');
	retour = "";
	for (i = 0; i < val.length; i++) {
		if ($($(val[i]).checked)) {
			retour = retour + $(val[i]).value + ",";
		}
	}
	if (retour != "") {
		retour = retour.substring(0,retour.lastIndexOf(','));
	}
	return retour;
}

function checkboxCocher(liste, valeur, acacher, aafficher) {
	val = liste.split(',');
	retour = "";
	
	acacher.style.display="none";
	aafficher.style.display="block";
	
	for (i = 0; i < val.length; i++) {
		$($(val[i]).checked = valeur);
	}
}

function copierTooltip() {
	javascript:switch2div('contenuTooltip','contenuTooltipCopie');
	$('contenuTooltipCopieText').value = $('contenuTooltipCopieText').value.replace(/<br>/g, '\n');
}


function textCount(field,counterfield,max) {
	if (field.value.length > max) // if too long...trim it!
		field.value = field.value.substring(0, max);
	else
		counterfield.value = max - field.value.length;
}
function wiglwogl(uddeElement) { 
	uddeForm = uddeElement.form; 
	uddeElement = uddeForm.elements[uddeElement.name]; 
	if (uddeElement.length) { 
		bChecked = uddeElement[0].checked; 
		for(i = 1; i < uddeElement.length; i++) {
			uddeElement[i].checked = bChecked; 
		}
	}
} 

/********************************************************************/
/************************* Transbahuter ********************/
/********************************************************************/

function controlePoids(){
	var poids=0;
	if ($('valeur_2').value != -1 ){
		poidsRestant = $('poids_' + $('valeur_2').value).value;
		if (poidsRestant != -1){
		 	for (i=4; i<=$('nb_valeurs').value; i++) {
				if ( $('valeur_' + i).type == 'select-multiple' ){
					for (j=0; j<$('valeur_' + i).options.length; j++){
						if ($('valeur_' + i).options[j].selected == true) {
							if ( i==12 ){
								poids = parseFloat(poids) + parseFloat($('valeur_' + i + '_poids_' + $('valeur_' + i).options[j].value).value);
							}
							else{
								poids = parseFloat(poids) + parseFloat($('valeur_' + i + '_poids').value);
							}
						}
					}
				}
				else {
					poids = parseFloat(poids) + $('valeur_' + i).value * $('valeur_' + i + '_poids').value;
				}
			}
			if (poids > poidsRestant){
				poidsDep = Math.round((poids - poidsRestant)*100)/100;
				alert ('Pas assez de place dans la source d\'arrivée !\nVous dépassez de ' + poidsDep + ' kg');
				return false;
			}
			else{
				return true;
			}
		}
		else{
			return true;
		}
	}
	else{
		return true;
	}
}

function controlePanneau (i) {
	if ( $('valeur_' + i).type == 'select-multiple' ){
		for (j=0; j<$('valeur_' + i).options.length; j++){
			if ($('valeur_' + i).options[j].value != -1) {
				$('valeur_' + i).options[j].selected = false;
				cacher = false;
			}
		}
	}
	else {
		$('valeur_'+i).value = 0;
	}		
	alert ("Votre charrette ne possède pas de panneau amovible, vous ne pouvez transbahuter qu\'un seul type d\'élément ! \n Seul le premier élément sélectionné a été pris en compte.");
}

function controleQte(){
	 v=false;
	 for (i=4;i<=$('nb_valeurs').value;i++){
	 	if ($('valeur_'+i).value > 0 && $('valeur_panneau').value != true && v==true) {
			controlePanneau (i);
	 	}
	 	if ($('valeur_'+i).value > 0) {
			v=true;
		}
	 };
	 cacher = true;
	 poidsOk = controlePoids();

	 if (v==true && $('valeur_1').value != -1 && $('valeur_2').value != -1 && poidsOk == true){
		cacher = false;
	 }
	 if ( $('valeur_2').value == 4 && $('valeur_3').value == -1){
		cacher = true;
	 }
	 $('bouton_deposer').disabled=cacher;
}

function selectAll(){
	cacher = true;
	v = false;
	for (i=4; i<=$('nb_valeurs').value; i++) {
		if ($('valeur_panneau').value != true && v==true) {
			controlePanneau (i);
			break;
	 	}
		if ( $('valeur_' + i).type == 'select-multiple' ){
			for (j=0; j<$('valeur_' + i).options.length; j++){
				if ($('valeur_' + i).options[j].value != -1) {
					$('valeur_' + i).options[j].selected = true;
					cacher = false;
					v = true;
				}
			}
		}
		else {
			$('valeur_' + i).value = $('valeur_' + i + '_max').value;
			if (cacher == true && $('valeur_' + i + '_max').value > 0) {
				cacher = false;
				v = true;
			}
		}
	}
	poidsOk = controlePoids();
	if ( $('valeur_1').value == -1 || $('valeur_2').value == -1 || poidsOk==false){
		cacher = true;
	}
	/*Coffre*/
	if ( $('valeur_2').value == 4 && $('valeur_3').value == -1){
		cacher = true;
	}
	$('bouton_deposer').disabled=cacher;
}

function charrette() {
	if ($('valeur_2').value >= 5){
		$('valeur_3').value = $('id_charrette_' + $('valeur_2').value).value;
	}
}

function activerRechercheCoffreHobbit(id) {
	if ($('recherche_' + id + '_actif').value == 0) {
		new Ajax.Autocompleter('recherche_' + id, 'recherche_' + id + '_update', '/Recherche/hobbit/champ/' + id, { paramName :"valeur", indicator :'indicateur_recherche_' + id, minChars :2,
		afterUpdateElement :getCoffreHobbitId, parameters : { champ :'value' } });
		$('recherche_' + id + '_actif').value = 1;
	}
}

function getCoffreHobbitId(text, li) {
	if (controleSession(li) == true) {
		$('valeur_3').value = li.getAttribute('id_hobbit');
		controleQte("");
	}
}
/********************************************************************/
/************************* RECHERCHE ********************/
/********************************************************************/

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
		makeJsListeAvecSupprimer(li.getAttribute('champ'), li.getAttribute('valeur'), li.getAttribute('id_hobbit'), li.getAttribute('id_hobbit'));
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
		texte = '<label class="alabel" onclick="javascript:ouvrirWin(\'/voir/hobbit/?hobbit='+idHobbit+'\');">' + texte + '(' + idHobbit + ')</label> ';
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

/********************************************************************/
/************************* UDDEIM TOOLS  *************************/
/********************************************************************/
function uddeidswap(id) {
	bb = document.getElementById(id);
	if (bb.style.visibility == 'visible') {
		bb.style.visibility = 'hidden';
	} else {
		bb.style.visibility = 'visible';
	}
}

function uddeIMaddToSelection( frmName, srcListName, tgtListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	var tgtList = eval( 'form.' + tgtListName );
	
	var destinationIds = eval( 'document.' + frmName + '.listids' );

	var srcLen = srcList.length;
	var tgtLen = tgtList.length;
	var tgt = "x";

	var idjoin = new Array();
	
	//build array of target items
	for ( var i=tgtLen-1; i > -1; i-- ) {
		tgt += "," + tgtList.options[i].value + ","
	}

	//Pull selected resources and add them to list	
	for ( var i=0; i < srcLen; i++ ) {
		if ( srcList.options[i].selected && tgt.indexOf( "," + srcList.options[i].value + "," ) == -1 ) {
			if ( srcList.options[i].value == 0 || ( tgtLen != 0 && tgtList.options[0].value == 0 ) ) {
				for ( var j = tgtLen-1; j > -1; j-- ) {
					tgtList.options[j] = null;						
				}
			} 
			opt = new Option( srcList.options[i].text, srcList.options[i].value );
			tgtList.options[tgtList.length] = opt;			
		}
	}
	for ( var i=0; i < tgtList.length; i++ ) {
		idjoin[i] = tgtList.options[i].value;						
	}
	destinationIds.value = idjoin.join( ',' );
}

function uddeIMremoveFromSelection( frmName, srcListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	
	var destinationIds = eval( 'document.' + frmName + '.listids' );
	var idjoin = new Array();

	var srcLen = srcList.length;

	for ( var i=srcLen-1; i > -1; i-- ) {
		if ( srcList.options[i].selected ) {
			srcList.options[i] = null;
			break;
		}
	}
	
	for ( var i=0; i < srcList.length; i++ ) {
		idjoin[i] = srcList.options[i].value;						
	}
	destinationIds.value = idjoin.join( ',' );
}

function userlistdblclick( sel, frmName, srcListName, tgtListName ) {
	uddeIMaddToSelection( frmName, srcListName, tgtListName );
}
function selectionlistdblclick( sel, frmName, srcListName ) {
	uddeIMremoveFromSelection( frmName, srcListName );
}


/********************************************************************/
/*************************** BB Display ***************************/
/********************************************************************/
//bbCode control by
//subBlue design
//www.subBlue.com
//Changed by/for uddeIM
//Changed by/for braldahim

//Startup variables
var imageTag = false;
var theSelection = false;

//Check for Browser & Platform for PC & IE specific bits
//More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
             && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
             && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);

//Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[color=#ff4040]','[/color]','[color=#40ff40]','[/color]','[color=#4040ff]','[/color]','[size=1]','[/size]','[size=2]','[/size]','[size=3]','[/size]','[size=4]','[/size]','[size=5]','[/size]','[ul]','[/ul]','[ol]','[/ol]','[img]','[/img]','[url]','[/url]','[li]','[/li]');
imageTag = false;

//Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null)) {
			return i;
		}
	}
	return thearray.length;
}

//Replacement for arrayname.push(value) not implemented in IE until version 5.5
//Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

//Replacement for arrayname.pop() not implemented in IE until version 5.5
//Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}


function emoticon(text) {
	var txtarea = $('myForm').pmessage;
	text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

function bbfontstyle(bbopen, bbclose) {
var txtarea = document.postform.message;

if ((clientVer >= 4) && is_ie && is_win) {
   theSelection = document.selection.createRange().text;
   if (!theSelection) {
      txtarea.value += bbopen + bbclose;
      txtarea.focus();
      return;
   }
   document.selection.createRange().text = bbopen + theSelection + bbclose;
   txtarea.focus();
   return;
}
else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
{
   mozWrap(txtarea, bbopen, bbclose);
   return;
}
else
{
   txtarea.value += bbopen + bbclose;
   txtarea.focus();
}
storeCaret(txtarea);
}


function bbstyle(bbnumber, field) {
	var txtarea = field;

	txtarea.focus();
	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			txtarea.value += bbtags[butnumber + 1];
			buttext = eval('$("myForm").addbbcode' + butnumber + '.src');
			eval('$("myForm").addbbcode' + butnumber + '.src ="' + buttext.substr(0,(buttext.length - 10)) + '.gif"');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection) {
			var sluss;
			var theGuy = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
			// Add tags around selection
			document.selection.createRange().text = theGuy;
			sluss = sel.text.length;
			sel.Text = theGuy;
			if (theGuy.length > 0) {
				sel.moveStart('character', -theGuy.length + sluss);
			}	
			txtarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
		return;
	}
	
	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				// txtarea.value += bbtags[butnumber + 1];
				pasteAtCursor(txtarea, bbtags[butnumber+1]);
				buttext = eval('$("myForm").addbbcode' + butnumber + '.src');
				eval('$("myForm").addbbcode' + butnumber + '.src ="' + buttext.substr(0,(buttext.length - 10)) + '.gif"');
				imageTag = false;
			}
			txtarea.focus();
			return;
	} else { // Open tags
	
		if (imageTag && (bbnumber != 24)) {		// Close image tag before adding another
			// txtarea.value += bbtags[25];
			pasteAtCursor(txtarea, bbtags[25]);
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
			var jubla=$('myForm').addbbcode24.src;
			var juble=jubla.substr(0, (jubla.length - 10));
			var jubli=juble+".gif";
			$('myForm').addbbcode24.src=jubli;
				// Return button back to normal state
			imageTag = false;
		}
		
		// Open tag
		// txtarea.value += bbtags[bbnumber];
		pasteAtCursor(txtarea, bbtags[bbnumber]);
		if ((bbnumber == 24) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(bbcode,bbnumber+1);
		// eval('$('myForm').addbbcode'+bbnumber+'.value += "*"');
		var imgsrcori=eval('$("myForm").addbbcode'+bbnumber+'.src');
		var imgsrcnew=imgsrcori.substr(0, (imgsrcori.length - 4));
		imgsrcnew += "_close.gif";
		eval('$("myForm").addbbcode'+bbnumber+'.src = "'+imgsrcnew+'"');	
		txtarea.focus();
		return;
	}
	storeCaret(txtarea);
}

//From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) 
		selEnd = selLength;

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	
	var anfangs = s1;
	var endes = s1+open+s2+close;
	var anfang = anfangs.length;
	var ende= endes.length;
	
		txtarea.selectionStart = anfang;
		txtarea.selectionEnd = ende;	
	
	return;
}

//Insert at Claret position. Code from
//http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}


//Insert emoticons
function emo(e, field) {
// $('myForm').pmessage.value=$('myForm').pmessage.value+$e;
pasteAtCursor(field, e);
field.focus();
}

function pasteAtCursor(theGirl, theGuy) {
/* This function is based upon a function in PHPMyAdmin */
/* (C) www.phpmyadmin.net. Changed by/for uddeIM */
/* See http://www.gnu.org/copyleft/gpl.html for license */
	if (document.selection) {
		//IE support
		var sluss;
		theGirl.focus();
		sel = document.selection.createRange();
		sluss = sel.text.length;
		sel.text = theGuy;
		if (theGuy.length > 0) {
			sel.moveStart('character', -theGuy.length + sluss);
		}		
	} else if (theGirl.selectionStart || theGirl.selectionStart == '0') {
		//MOZILLA/NETSCAPE support
		var startPos = theGirl.selectionStart;
		var endPos = theGirl.selectionEnd;
		theGirl.value = theGirl.value.substring(0, startPos) + theGuy + theGirl.value.substring(endPos, theGirl.value.length);
		theGirl.selectionStart = startPos + theGuy.length;
		theGirl.selectionEnd = startPos + theGuy.length;
	} else {
		theGirl.value += theGuy;
	}
}

function jsMenuHotel(id, valeur) {
	
	if (id != "hotel_menu_recherche_pratique") $("hotel_menu_recherche_pratique").value = -1;
	if (id != "hotel_menu_recherche_equipements") $("hotel_menu_recherche_equipements").value = -1;
	if (id != "hotel_menu_recherche_materiels") $("hotel_menu_recherche_materiels").value = -1;
	if (id != "hotel_menu_recherche_matieres_premieres") $("hotel_menu_recherche_matieres_premieres").value = -1;
	if (id != "hotel_menu_recherche_matieres_transformees") $("hotel_menu_recherche_matieres_transformees").value = -1;
	if (id != "hotel_menu_recherche_aliments") $("hotel_menu_recherche_aliments").value = -1;
	if (id != "hotel_menu_recherche_potions") $("hotel_menu_recherche_potions").value = -1;
	if (id != "hotel_menu_recherche_runes") $("hotel_menu_recherche_runes").value = -1;
	
	_get_('/hotel/doaction?caction=ask_hotel_voir&'+id+'='+valeur);
}

