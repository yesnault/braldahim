// parcoure les vues affichées pour trouver les bralduns visibles sur la case x,y.
Map.prototype.getBralduns = function(x, y) {
	var cell = this.getCellVue(x, y);
	if (cell) {
		return cell.bralduns;
	}
	return [];
}


// renvoie la cellule de la vue ou null (hors vue ou vide)
// Attention : cette méthode ne vérifie pas que x et y sont dans la portée de la vue : le faire avant
Map.prototype.getCellVue = function(x, y) {
	return this.matriceVues[this.getIndex(x, y)];
}

Map.prototype.cleanCellVue = function(x, y) {
	delete this.matriceVues[this.getIndex(x, y)];
}

// renvoie une cellule (en la créant si nécessaire, ne pas utiliser cette méthode en simple lecture)
Map.prototype.getCellVueCreate = function(x, y) {
	var index = this.getIndex(x, y);
	var cell = this.matriceVues[index];
	if (!cell) {
		cell = {};
		cell.bralduns = [];
		cell.cadavres = [];
		cell.objets = [];
		cell.monstres = [];
        cell.actions = [];
		cell.nbBraldunsFémininsNonKO=0; 
		cell.nbBraldunsMasculinsNonKO=0;
		cell.zones = [[], [], [], []]; // 4 zones : haut-gauche, centre, bas-gauche et bas-droit (haut-droit n'est pas géré dans la vue et correspond au lieu)
		this.matriceVues[index] = cell;
	}
	return cell;
}

// dessine de 1 à 5 icônes sur ou autour d'un point de l'écran
Map.prototype.drawIcons = function(c, sx, sy, icons, hover) {
	var x=[]; var y=[];
	switch (icons.length) {
	case 1:
		x[0]=sx; y[0]=sy;
		break;
	case 2:
		var d1 = 0.07*this.zoom;
		var d2 = 0.17*this.zoom;
		x[0]=sx+d1; y[0]=sy-d2;
		x[1]=sx-d1; y[1]=sy+d2;
		break;
	case 3:
		var d = 0.17*this.zoom;
		x[0]=sx;   y[0]=sy-d;
		x[1]=sx-d; y[1]=sy+d;
		x[2]=sx+d; y[2]=sy+d;
		break;
	case 4:
	default: // si le cas se présente, il faut implémenter le cas 5
		var d = 0.17*this.zoom;
		x[0]=sx-d; y[0]=sy-d;
		x[1]=sx+d; y[1]=sy-d;
		x[2]=sx-d; y[2]=sy+d;
		x[3]=sx+d; y[3]=sy+d;
	}
	var imgh;
	if (this.zoom!=64) imgh=this.zoom*0.35;
	if (hover) {
		for (var i=x.length; i-->0;) {
			if (icons[i]) {
				drawCenteredImage(c, this.getOutlineImg(icons[i]), x[i], y[i], null, imgh?imgh+4:null);
			} else {
				//~ console.log('image nulle');
			}
		}
	}
	for (var i=0; i<x.length; i++) {
		if (icons[i]) {
			drawCenteredImage(c, icons[i], x[i], y[i], null, imgh?imgh+4:null);
		} else {
			//~ console.log('image nulle');
		}
	}
}

Map.prototype.getObjectImgKey = function(o) {
	switch (o.Type) {

		case "aliment":
		case "graine":
		case "materiel":
		case "minerai":
		case "munition":
		case "potion":
		case "tabac":
			return o.Type+'_'+o.IdType;	

		case "équipement":
			return 'equipement_'+o.IdType;	
		case "ingrédient":
			return 'ingredient_'+o.IdType;
		case "lingot":
			return 'minerai_'+o.IdType+'_p';	
		case "plante":
			return 'partieplante_'+o.IdType;	

		case "castar":
			return 'castars';
		case "rune":
			return 'runes';

		default:
			return o.Type;
	}
}

// construit l'objet matriceVues qui contient les infos de toutes les vues visibles
Map.prototype.compileLesVues = function() {
	if (!(this.spritesVueTypes.ready&&this.spritesEnv.ready)) {
		//~ console.log('not ready for compilation');
		return;
	}
	this.matricesVuesParZ = {};
	this.matriceVues = {};
	//~ var nn=0, zz=1, tt=2; // pour tests affichage points gredin et points redresseur
    if (!this.mapData) return; // contournement non compris, cela vaut null parfois. Evite une exception en dessus
	for (var iv=0; iv<this.mapData.Vues.length; iv++) {
		var vue = this.mapData.Vues[iv];
		if (!vue.active) continue;
		this.matriceVues = this.matricesVuesParZ[vue.Z];
		if (!this.matriceVues) {
			this.matriceVues = {};
			this.matricesVuesParZ[vue.Z] = this.matriceVues;
		}
		if (iv>0) {
			// on nettoie la zone en vue (les vues ont été triées par date auparavant)
			for (x=vue.XMin; x<=vue.XMax; x++) {
				for (y=vue.YMin; y<=vue.YMax; y++) {
					this.cleanCellVue(x, y);
				}
			}
		}
		for (ib in vue.Bralduns) {
			var b = vue.Bralduns[ib];
			//~ b.PointsGredin = ((nn++)%3)*((zz++)%3)*((tt++)%5)*((tt++)%11);
			//~ b.PointsRedresseur = ((nn++)%7)*((zz++)%7)*((tt++)%3);
			var cell = this.getCellVueCreate(b.X, b.Y)
			cell.bralduns.push(b);
		}
		for (io in vue.Objets) {
			var o = vue.Objets[io];
			var cell = this.getCellVueCreate(o.X, o.Y);
			cell.objets.push(o);
		}
		for (io in vue.Monstres) {
			var o = vue.Monstres[io];
			var cell = this.getCellVueCreate(o.X, o.Y);
			cell.monstres.push(o);
		}
		for (io in vue.Cadavres) {
			var o = vue.Cadavres[io];
			var cell = this.getCellVueCreate(o.X, o.Y);
			cell.cadavres.push(o);
		}
		//> on ajoute les actions aux cellules
		if (vue.actions) {
			for (var i=0; i<vue.actions.length; i++) {
				var a = vue.actions[i];
				var cell = this.getCellVueCreate(a.X, a.Y);
				cell.actions.push(a); // pour la popup, plusieurs actions possibles
                if (this.typesActions[a.Type].isIconeMap) { // affichage de l'icône ou non sur la case
                    cell.zones[1].push(this.typesActions[a.Type].icone);
                }
			}
		}
		//> pour chaque cellule on construit les tableaux d'images par zones
		for (var x=vue.XMin; x<=vue.XMax; x++) {
			for (var y=vue.YMin; y<=vue.YMax; y++) {
				var cell = this.getCellVue(x, y);
				if (cell) {
					var nbBraldunsFémininsNonKO=0; 
					var nbBraldunsMasculinsNonKO=0;
					var nbBraldunsKO=0;
					//-- zone 0 : bralduns
					if (cell.bralduns.length) {
						var hasBraldunsCampA = false;
						var hasBraldunsCampB = false;
						for (var i=0; i<cell.bralduns.length; i++) {
							var b = cell.bralduns[i];
							if (b.KO) {
								nbBraldunsKO++;
							} else {
								if (b.Sexe=='f') nbBraldunsFémininsNonKO++;
								else nbBraldunsMasculinsNonKO++;
								if (b.Camp=='a') hasBraldunsCampA=true;
								else if (b.Camp=='b') hasBraldunsCampB=true;
							}
						}
						if (nbBraldunsFémininsNonKO+nbBraldunsMasculinsNonKO>0) {
							var key = 'braldun';
							if (nbBraldunsFémininsNonKO+nbBraldunsMasculinsNonKO>1) key+='s';
							if (nbBraldunsMasculinsNonKO>0) key += '_masculin';
							if (nbBraldunsFémininsNonKO>0) key += '_feminin';
							if (hasBraldunsCampA && hasBraldunsCampB) key += '-combat';
							else if (hasBraldunsCampA) key += '-a';
							else if (hasBraldunsCampB) key += '-b';
							var img = this.spritesVueTypes.get(key);
							if (img) cell.zones[0].push(img);
							//~ else console.log("pas d'image de braldun pour la clé '" +key+"'");
						}
					}
					//-- zone 0 : monstres
					if (cell.monstres.length) {
						var nbByType = {};
						var nbTypes=0;
						var t;
						for (var i=cell.monstres.length; i-->0;) {
							t = cell.monstres[i].IdType;
							if (nbByType[t]) {
								nbByType[t]++;
							} else {
								nbByType[t] = 1;
								nbTypes++;
							}
						}
						if (nbTypes==1 && cell.monstres.length==2) {
							var img = this.spritesVueTypes.get('monstre_'+t+'a', 'monstre');
							cell.zones[0].push(img);
							cell.zones[0].push(img);
						} else if (nbTypes==1 && cell.monstres.length==3) {
							cell.zones[0].push(this.spritesVueTypes.get('monstre_'+t+'b', 'monstres'));
							cell.zones[0].push(this.spritesVueTypes.get('monstre_'+t+'a', 'monstre'));
						} else if (nbTypes<3) {
							for (t in nbByType) {
								cell.zones[0].push(nbByType[t]==1 ? this.spritesVueTypes.get('monstre_'+t+'a', 'monstre') : this.spritesVueTypes.get('monstre_'+t+'b', 'monstres'));
							}
						} else {
							cell.zones[0].push(this.spritesVueTypes.get('monstres'));
						}
					}
					//-- zone 2 : braldun KO
					if (nbBraldunsKO>0) {
						cell.zones[2].push(this.spritesVueTypes.get('braldunko'));
					}
					//-- zone 2 : cadavre
					if (cell.cadavres.length) {
						cell.zones[2].push(this.spritesVueTypes.get('cadavre'));
					}
					//-- zones 1, 2 et 3 : objets, triés suivant leur type et orientés dans l'une des deux zones
					if (cell.objets.length) {
						for (var i=0; i<cell.objets.length; i++) {
							var o = cell.objets[i];
							var typeDéjàPrésent = false;
							for (var j=0; j<i; j++) {
								if (o.Type==cell.objets[j].Type) {
									typeDéjàPrésent = true;
									break;
								}
							}
							if (typeDéjàPrésent) continue;
							var dest = cell.zones[3];
							if (o.Type=='castar'||o.Type=='rune') dest = cell.zones[2];
							else if (o.Type=="ballon"||o.Type=="buisson") dest = cell.zones[1];
							var img = this.spritesVueTypes.get(this.getObjectImgKey(o));
							if (img) {
								dest.push(img);
							} else {
								console.log("pas d'image pour cet objet :", o);
							}
						}
					}
				}
			}
		}
	}	
}


// dessine la vue d'un Braldun (la partie intersectant this.xMin, this.xMax, etc.)
Map.prototype.dessineLesVues = function() {
	var c = this.context;
	for (var x=this.xMin; x<=this.xMax; x++) {
		for (var y=this.yMin; y<=this.yMax; y++) {
			var cell = this.getCellVue(x, y);
			if (cell) {
				var hover = (this.pointerX==x && this.pointerY==y);
				var d = this.zoom*0.25;
				var cx = this.zoom*(this.originX+x);
				var cy = this.zoom*(this.originY-y);
				if (cell.zones[0].length>0) this.drawIcons(c, cx+d, cy+d, cell.zones[0], hover);
				if (cell.zones[2].length>0) this.drawIcons(c, cx+d, cy+3*d, cell.zones[2], hover);
				if (cell.zones[3].length>0) this.drawIcons(c, cx+3*d, cy+3*d, cell.zones[3], hover);
				if (cell.zones[1].length>0) this.drawIcons(c, cx+2*d, cy+2*d, cell.zones[1], hover);
				if (hover) { // remplissage de la bulle du hover
					if (cell.bralduns.length) {
						if (cell.bralduns.length>5) {
							this.bubbleText.push('Plein de Bralduns ('+cell.bralduns.length+')');
						} else {
							this.bubbleText.push('Bralduns :');
							for (var ib=0; ib<cell.bralduns.length; ib++) {
								var b = cell.bralduns[ib];
								var s = '  '+b.Prénom+' '+b.Nom+' (niv.'+b.Niveau+')'
								if (b.KO) s += ' KO';
								if (b.IdCommunauté>0) s += ' ' +this.mapData.Communautés[b.IdCommunauté].Nom;
								this.bubbleText.push(s);
							}
						}
					}
					if (cell.monstres.length) {
						if (cell.monstres.length>5) {
							this.bubbleText.push('Plein de monstres ('+cell.monstres.length+')');
						} else {
							this.bubbleText.push('Monstres :');
							for (var ib=0; ib<cell.monstres.length; ib++) {
								var o = cell.monstres[ib];
								this.bubbleText.push('  '+o.Nom+' '+o.Taille+(o.Gibier?' (gibier)':''));
							}
						}
					}
					if (cell.cadavres.length) {
						if (cell.cadavres.length>3) {
							this.bubbleText.push('Plein de cadavres ('+cell.cadavres.length+')');
						} else {
							this.bubbleText.push('Cadavres :');
							for (var ib=0; ib<cell.cadavres.length; ib++) {
								var o = cell.cadavres[ib];
								this.bubbleText.push('  '+o.Nom+' '+o.Taille);
							}
						}
					}
					if (cell.objets.length) { // les buissons sont considérés "au sol"
						if (cell.objets.length>5) {
							this.bubbleText.push("Plein d'objets au sol");
						} else {
							this.bubbleText.push('Au sol :');
							for (var ib=0; ib<cell.objets.length; ib++) {
								var o = cell.objets[ib];
								this.bubbleText.push('  '+o.Label);
							}
						}
					}
				}
			}
		}
	}
}
