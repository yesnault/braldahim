/*
 * Champs de l'objet action :
 *  Type : numérique, voir map.typesActions
 *  X
 *  Y
 *  Z : optionnel car implicitement le z du braldun
 *  IdBraldun : id, le cas échéant, de la cible s'il s'agit d'un braldun
 *  IdMonstre :  id, le cas échéant, de la cible s'il s'agit d'un monstre
 *  PA : coût en PA
 * 
 * 
 * Champs du type d'action :
 *  nom
 *  iconeCase : optionnel, à afficher sur la case où l'action est disponible
 *  icone
 */

Map.prototype.initTypesActions = function() {
	var icon = function(s) {
		var img = new Image();
		img.src="http://static.braldahim.com/images/"+s+".png";
		return img;
	}
	this.typesActions = [];
	this.typesActions['Marcher'] = {nom:'Marcher', iconeCase:icon('vue/pas')};
    this.typesActions['Lieu'] = {nom:'Entrer dans le lieu', iconeCase:false};
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
