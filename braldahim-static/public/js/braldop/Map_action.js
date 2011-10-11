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
	// 0 : marcher
	this.typesActions["Marcher"] = {nom:'Marcher', iconeCase:icon('vue/pas')};
	this.actions = []; // un tableau de toutes les actions
}


function mapDoAction(key) {
	var action = currentMap.actions[key];
	if (currentMap.dialopIsOpen) {
		currentMap.$dialog.hide();
		currentMap.dialopIsOpen = false;
	}
    if (currentMap.callbacks[action.Type]) {
    	currentMap.callbacks[action.Type](action);
    } else {
    	console.log("aucun callback d'action défini");
    }
}
