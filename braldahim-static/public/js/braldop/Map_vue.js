// parcoure les vues affichées pour trouver les bralduns visibles sur la case x,y.
Map.prototype.getBralduns = function(x, y) {
	if (this.mapData.Vues) {
		for (var i=this.mapData.Vues.length; i-->0;) {
			var vue = this.mapData.Vues[i];
			if (vue.active) {
				var cell = getCellVue(vue, x, y);
				if (cell) {
					return cell.bralduns;
				}
			}
		}
	}
	return [];
}

// renvoie la première cellule de vue trouvée (en ne cherchant que parmi les vues affichées)
Map.prototype.getCellVueVisible = function(x, y) {
	if (this.mapData.Vues) {
		for (var i=this.mapData.Vues.length; i-->0;) {
			var vue = this.mapData.Vues[i];
			if (vue.active) {
				var cell = getCellVue(vue, x, y);
				if (cell) {
					return cell;
				}
			}
		}
	}
	return null;
}

// renvoie la cellule de la vue ou null (hors vue ou vide)
function getCellVue(vue, x, y) {
	var W = vue.XMax-vue.XMin;
	var index = ((x-vue.XMin)%W)+(W*(y-vue.YMin));
	return vue.matrix ? vue.matrix[index] : null;
}

// renvoie une cellule (en la créant si nécessaire, ne pas utiliser cette méthode en simple lecture)
function getCellVueCreate(vue, x, y) {
	var W = vue.XMax-vue.XMin;
	var index = ((x-vue.XMin)%W)+(W*(y-vue.YMin));
	var cell = vue.matrix[index];
	if (!cell) {
		cell = {};
		cell.bralduns = [];
		cell.objets = [];
		cell.nbBraldunsFéminins=0;
		cell.nbBraldunsMasculins=0;
		vue.matrix[index] = cell;
	}
	return cell;
}

// dessine la vue d'un Braldun
Map.prototype.drawVue = function(vue) {
	var screenRect = new Rect();
	screenRect.x = this.zoom*(this.originX+vue.XMin);
	screenRect.y = this.zoom*(this.originY-vue.YMin+1);
	screenRect.w = this.zoom*(this.originX+vue.XMax+1) - screenRect.x;
	screenRect.h = - (this.zoom*(this.originY-vue.YMax) - screenRect.y);
	screenRect.y -= screenRect.h;
	if (!Rect_intersect(screenRect, this.screenRect)) {
		return;
	}
	var c = this.context;
	var W = vue.XMax-vue.XMin;

	if (this.zoom>30) {

		//> on compile les objets de la vue sous forme matricielle et on compte les 
		//   bralduns de chaque sexe
		if (!vue.matrix) {
			vue.matrix = [];
			for (ib in vue.Bralduns) {
				var b = vue.Bralduns[ib];
				var cell = getCellVueCreate(vue, b.X, b.Y)
				cell.bralduns.push(b);
				if (b.Sexe=='f') cell.nbBraldunsFéminins++;
				else cell.nbBraldunsMasculins++;
			}
			for (io in vue.Objets) {
				var o = vue.Objets[io];
				var cell = getCellVueCreate(vue, o.X, o.Y);
				cell.objets.push(o);
			}
		}
		
		//> on dessine les trucs en vue
		var naturalSize = this.zoom==64;
		var imgh;
		if (this.zoom!=64) imgh=this.zoom*0.38;
		for (var x=vue.XMin; x<=vue.XMax; x++) {
			for (var y=vue.YMin; y<=vue.YMax; y++) {
				var cell = getCellVue(vue, x, y);
				if (cell) {
					var selected = (this.pointerX==x && this.pointerY==y);
					//> les bralduns
					var imgb = null;
					if (cell.nbBraldunsFéminins>0 && cell.nbBraldunsMasculins>0) {
						imgb = this.img_bralduns_masculin_feminin;					
					} else if (cell.nbBraldunsFéminins>1) {
						imgb = this.img_bralduns_feminin;
					} else if (cell.nbBraldunsMasculins>1) {
						imgb = this.img_bralduns_masculin;
					} else if (cell.nbBraldunsFéminins>0) {
						imgb = this.img_braldun_feminin;
					} else if (cell.nbBraldunsMasculins>0) {
						imgb = this.img_braldun_masculin;
					}
					if (imgb!=null && imgb.width) {
						var cx = this.zoom*(this.originX+x)+this.zoom/4;
						var cy = this.zoom*(this.originY-y)+this.zoom/4;
						if (selected) {
							this.bubbleText.push('Bralduns :');
							for (var ib in cell.bralduns) {
								var b = cell.bralduns[ib];
								this.bubbleText.push('  '+b.Prénom+' '+b.Nom+' (niveau '+b.Niveau+')');
							}
							drawCenteredImage(c, this.getOutlineImg(imgb), cx, cy, null, imgh?imgh+4:null);
						}
						drawCenteredImage(c, imgb, cx, cy, null, imgh);
					}
					//> les objets 
					// TODO dans un premier temps pour tester je n'en dessine qu'un
					if (cell.objets.length>0) {
						if (selected) {
							this.bubbleText.push('Objets :');
							for (var ib in cell.objets) {
								var o = cell.objets[ib];
								if (o.Quantité) this.bubbleText.push('  '+o.Quantité+' '+o.Type+(o.Quantité>1?'s':''));
								else this.bubbleText.push('  '+o.Type);
							}
						}
						var o = cell.objets[0];
						var img = this.imgObjets[o.Type];
						if (img) {
							var cx = this.zoom*(this.originX+x)+this.zoom*0.75;
							var cy = this.zoom*(this.originY-y)+this.zoom*0.75;
							if (selected) {
								drawCenteredImage(c, this.getOutlineImg(img), cx, cy, null, imgh?imgh+4:null);
							}
							drawCenteredImage(c, img, cx, cy, null, imgh);
						} else {
							console.log("pas d'image pour l'objet " + o.Type);
						}
						
					}
				}
			}
		}
		
	}
	
	
	//> on assombrit tout ce qui n'est pas la vue
	// Là pour l'instant je fais comme s'il n'y avait qu'une vue active. Sinon il faut cumuler les trous au lieu
	//  d'accumuler les obscurcissement. Je ferai ça quand il y aura plusieurs vues...
	c.fillStyle = "rgba(100, 100, 100, 0.4)";
	this.screenRect.makeHolePath(c, screenRect, 7);
	c.fill();
	
}
