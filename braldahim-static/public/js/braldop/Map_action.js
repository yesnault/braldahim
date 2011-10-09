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
	this.typesActions[0] = {nom:'Marcher', iconeCase:icon('vue/pas')};
	this.actions = []; // un tableau de toutes les actions
}

// Methode publique
// champs :
//  - idBraldun : id du braldun pouvant réaliser les actions. On doit avoir une vue pour ce Braldun
//  - actions  : une liste d'actions
//  - callback : la méthode à appeler en cas de demande de réalisation de l'action. 
//               cette méthode sera appelée avec pour paramètres idBraldun et l'action.
//               elle peut être nulle (cas par exemple d'une interface tactique ne faisant
//               que lister les actions possibles
// L'implémentation ne permet pas pour l'instant de supprimer des actions
Map.prototype.setActions = function(idBraldun, actions, callback) {
	// on cherche la vue
	var vue;
	if (this.mapData.Vues) {
		for (var i=this.mapData.Vues.length; i-->0;) {
			if (this.mapData.Vues[i].Voyeur==idBraldun) {
				vue = this.mapData.Vues[i];
				break;
			}
		}
	}
	if (!vue) {
		console.log('Vue non trouvée pour les actions passées (idBraldun='+idBraldun+')');
		return;
	}
	vue.actions = actions;
	this.callbackActions = callback;
	//todo : filtre pour virer les actions sans type connu ?
	for (var i=0; i<actions.length; i++) {
		var a = actions[i];
		a.acteur = idBraldun;
		a.key = this.actions.length; // on donne à l'action une clef pour la retrouver plus facilement
		this.actions.push(a);
		var img = this.typesActions[actions[i].Type].iconeCase;
		if (img) {
			var cell = getCellVueCreate(vue, a.X, a.Y);
			cell.action = a; // une action max par case pour l'instant
			cell.zones[1].push(this.typesActions[a.Type].iconeCase);
		}
	}
}

function mapDoAction(key) {
	var action = currentMap.actions[key];
	if (currentMap.dialopIsOpen) {
		currentMap.$dialog.hide();
		currentMap.dialopIsOpen = false;
	}
	if (currentMap.callbackActions) {
		currentMap.callbackActions(action.acteur, action);
	} else {
		console.log("aucun callback d'action défini");
	}
}
