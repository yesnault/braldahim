/*
 * Champs de l'objet action :
 *  Type : numérique, voir map.typesActions
 *  X
 *  Y
 *  Z : optionnel car implicitement le z du braldun
 *  PA : coût en PA
 *
 * Champs du type d'action :
 *  nom
 *  icone: optionnel, à afficher dans la popup où l'action est disponible
 *  iconeCase : affichage de l'icone sur la case ou non
 */

Map.prototype.initTypesActions = function() {
	var icon = function(s) {
		var img = new Image();
		img.src="http://static.braldahim.com/images/"+s+".png";
		return img;
	}
	this.typesActions = [];
	this.typesActions['Marcher'] = {nom:'Marcher', icone:icon('vue/pas'), isIconeMap:true};
    this.typesActions['Lieu'] = {nom:'Entrer dans le lieu', icone:icon('vue/pas'), isIconeMap:true};
    this.typesActions['Transbahuter'] = {nom:'Transbahuter', icone:icon('vue/laban'), isIconeMap:false};
	this.actions = []; // un tableau de toutes les actions
}


function mapDoAction(key) {
	var action = currentMap.actions[key];
	if (currentMap.dialopIsOpen) {
		currentMap.$dialog.hide();
		currentMap.dialopIsOpen = false;
	}
	var callback = currentMap.callbacks[action.Type];
	if (callback) {
		callback(action);
	} else {
		console.log("aucun callback d'action défini");
	}
}
