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

		//> on compile les objets de la vue sous forme matricielle, essentiellement pour
		//   avoir les nombres de bralduns de chaque sexe dans chaque cellule
		if (!vue.matrix) {
			vue.matrix = [];
			for (ib in vue.Bralduns) {
				var b = vue.Bralduns[ib];
				var index = ((b.X-vue.XMin)%W)+(W*(b.Y-vue.YMin));
				var cell = vue.matrix[index];
				if (!cell) {
					cell = {};
					cell.bralduns = [];
					cell.nbBraldunsFéminins=0;
					cell.nbBraldunsMasculins=0;
					vue.matrix[index] = cell;
				}
				cell.bralduns.push(b);
				if (b.Sexe=='f') cell.nbBraldunsFéminins++;
				else cell.nbBraldunsMasculins++;
			}
		}
		
		//> on dessine les bralduns en vue
		var naturalSize = this.zoom==64;
		var imgh;
		if (this.zoom!=64) imgh=this.zoom*0.38;
		for (var x=vue.XMin; x<=vue.XMax; x++) {
			for (var y=vue.YMin; y<=vue.YMax; y++) {
				var index = ((x-vue.XMin)%W)+(W*(y-vue.YMin));
				var cell = vue.matrix[index];
				if (cell) {
					var selected = (this.pointerX==x && this.pointerY==y);
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
