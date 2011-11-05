
function Map(canvasId, posmarkid, dialogId) {
	this.canvas = document.getElementById(canvasId);
	this.context = this.canvas.getContext("2d");
	this.context.mozImageSmoothingEnabled = false; // contourne un bug de FF qui rend floues les images même à taille naturelle
	this.posmarkdiv = document.getElementById(posmarkid);
	this.initTypesActions();
	this.callbacks = {};
	this.initPalissades();
	this.initEnv();
	this.screenRect = new Rect();
	this.rect = new Rect(); // le rectangle englobant le contenu que l'on veut montrer
	this.originX=0; // coin haut gauche de la case au centre de l'écran
	this.originY=0;
	this.scales = [0.5, 1, 2, 4, 8, 16, 32, 48, 64]; // éliminé : 92
	this.zoom = 64;
	this.z = 0; // la profondeur affichée
	this.couche = null; // la couche actuellement affichée. Doit correspondre à la profondeur this.z
	this.W = 900; // simplement un nombre supérieur à la demi-largeur ou demi-hauteur de la carte mais pas trop
	this.mouseIsDown=false;
	this.pointerX = 2* this.W; // coordonnées du pointeur dans l'univers Braldahim
	this.pointerY = 0;
	this.pointerScreenX = 0; // coordonnées du pointeur dans le référentiel de l'écran
	this.pointerScreenY = 0;
	this.hoverObject = null; // cette notion sera remplacée à terme par une cellule (mais restera null si la cellule ne contient rien d'intéressant)
	this.photoSatellite = new Image();
	this.displayPhotoSatellite = true;
	this.displayRégions = false;
	this.displayFog = true;
	this.displayGrid = false;
	this.displayALot = false; // si true alors on se fiche un peu de la lenteur du dessin, y compris à basse résolution
	this.displayExperimentation = false;
	this.fogImg = null;
	this.actionsParBraldun = {};
	this.fogContext = null;
	this.recomputeCanvasPosition();
	var _this = this;
	var onready = function(){_this.compileLesVues();_this.redraw();};
	this.spritesEnv = new SpriteSet('/images/sprites/sprites-environnements.png', onready);
	this.spritesVueTypes = new SpriteSet('/images/sprites/sprites-vuetypes.png', onready);
	this.photoSatelliteOK = false;
	this.photoSatellite.src = "http://static.braldahim.com/images/sources/harilinn/braldahim_carte4.png";
	this.photoSatellite.onload = function(){
		_this.photoSatelliteRect = new Rect();
		var ps = _this.photoSatellite;
		var ratioSatellite = 1.5;
		var psw = ps.width*ratioSatellite;
		var psh = ps.height*ratioSatellite;
		_this.photoSatelliteRect.x = -0.5*psw -1; // dernier nombre : ajustement manuel
		_this.photoSatelliteRect.y = 0.5*psh - 27; // dernier nombre : ajustement manuel
		_this.photoSatelliteRect.w = psw;
		_this.photoSatelliteRect.h = psh;
		_this.photoSatelliteScreenRect = new Rect();		
		_this.photoSatelliteOK = true;
		_this.redraw();
	};

	this.canvas.addEventListener("mousedown", function(e) {_this.mouseDown(e)}, false);
	this.canvas.addEventListener("mouseup", function(e) {_this.mouseUp(e)}, false);
	$(this.canvas).mouseleave(function(e) {_this.mouseLeave(e)}); // l'événement mouseleave n'est pas standard (IE only), on passe par jquery qui l'émule dans les autres browsers
	this.canvas.addEventListener("mousemove", function(e) {_this.mouseMove(e)}, false);
	this.canvas.addEventListener("DOMMouseScroll", function(e) {e.preventDefault(), _this.mouseWheel(e)}, false); // firefox
	this.canvas.onmousewheel = function(e) {e.preventDefault(), _this.mouseWheel(e)}; // chrome
	$(window).resize(function(){
		_this.recomputeCanvasPosition();
		_this.redraw();
	});
	currentMap = this;
}

Map.prototype.updatePosDiv = function() {
	//~ var html = 'Zoom='+this.zoom+' &nbsp; X='+this.pointerX+' &nbsp; Y='+this.pointerY+' &nbsp; Z='+this.z;
	var html = 'X='+this.pointerX+' &nbsp; Y='+this.pointerY+' &nbsp; Z='+this.z;
	var cell = this.getCell(this.couche, this.pointerX, this.pointerY);
	if (cell) {
		var env = this.environnements[cell.fond];
		if (env) html += ' ' + env.nom + ', ' + env.description;
		else console.log('env inconnu : ' + cell.fond); // notons qu'on a des undefined quand il n'y a pas de terrain sous des palissades par exemple
	}
	this.posmarkdiv.innerHTML=html;
}

Map.prototype.changeProfondeur = function(z) {
	var newCouche = null;
	for (var ic=0; ic<this.mapData.Couches.length; ic++) {
		var couche = this.mapData.Couches[ic];
		if (couche.Z==z) newCouche = couche;
	}
	if (!newCouche) {
		console.log('couche de profondeur '+z+' introuvable !');
	} else {
		this.couche = newCouche;
		this.z = z;
	}
	this.matriceVues = this.matricesVuesParZ[this.z];
	this.updatePosDiv();
}

// calcule l'index 2D de la cellule (clef de hash pouvant être utilisée dans des map ou bien dans un tableau (de dimension (2W)²)
Map.prototype.getIndex = function(x, y) {
	return ((x+this.W)%(this.W*2))+(this.W*2)*(y+this.W);
}

// centre l'écran sur la case de coordonnées (x, y, z)
Map.prototype.goto = function(x, y, z) {
	if (this.callbacks['profondeur']) {
		this.callbacks['profondeur'](z);
	}
	this.originX = (this.screenRect.w/2)/this.zoom - x;
	this.originY = y+(this.screenRect.h/2)/this.zoom;
	this.changeProfondeur(z);
	this.redraw();
}

// renvoie une cellule (en la créant si nécessaire, ne pas utiliser cette méthode en simple lecture)
Map.prototype.getCellCreate = function(couche, x, y) {
	var index = this.getIndex(x, y);
	var cell = couche.matrix[index];
	if (!cell) {
		cell = {};
		couche.matrix[index] = cell;
	}
	return cell;
}
// renvoie une cellule ou null s'il n'y en a pas en (x,y) pour cette couche
Map.prototype.getCell = function(couche, x, y) {
	return couche.matrix[this.getIndex(x, y)];
}

Map.prototype.recomputeCanvasPosition = function() {
	var pos = $(this.canvas).offset();
	this.screenRect = new Rect();
	this.screenRect.x = 0;
	this.screenRect.y = 0;
	this.screenRect.w = this.canvas.clientWidth;
	this.screenRect.h = this.canvas.clientHeight;
	this.canvas.width = this.screenRect.w;
	this.canvas.height = this.screenRect.h;
	this.originX = (this.screenRect.w/2)/this.zoom;
	this.originY = (this.screenRect.h/2)/this.zoom;
	this.fogImg = null;
	this.fogContext = null;
}

// l'objet passé, reçu en json, devient le fournisseur des données de carte et de vue.
// Les champs dont le nom commence par une minuscule sont définis localement et
//  ceux dont le nom commence par une majuscule proviennent du serveur (cette norme
//  est valable sur toute la hiérarchie des objets de mapData).
// Les données sont copiées dans une structure qui donne un accès par les coordonnées des cases.
Map.prototype.setData = function(mapData) {
	this.mapData = mapData;
	this.matricesVuesParZ = {};
	this.matricesVuesParZ[0]={};
	this.z = 0; // on va basculer forcément sur la couche zéro
	this.couche = null; 
	for (var ic=0; ic<this.mapData.Couches.length; ic++) {
		var couche = this.mapData.Couches[ic];
		//if (couche.Z==0)
			this.couche = couche;
		couche.matrix = {};//new Array(); // todo benchmarker pour comparer les effets en ram et cpu de la version map et de la version table
		couche.fond = new Image();
		//couche.fond.src = "couche"+couche.Z+".png";
		if (couche.Cases) {
			for (var i=couche.Cases.length; i-->0;) {
				var o = couche.Cases[i];
				this.getCellCreate(couche, o.X, o.Y).fond = o.Fond;
			}
		}
		if (couche.Champs) {
			for (var i=couche.Champs.length; i-->0;) {
				var o = couche.Champs[i];
				o.Nom = "Champ";
				this.getCellCreate(couche, o.X, o.Y).champ=o;
			}
		}
		if (couche.Echoppes) {
			for (var i=couche.Echoppes.length; i-->0;) {
				var o = couche.Echoppes[i];
				this.getCellCreate(couche, o.X, o.Y).échoppe=o;
			}
		}
		if (couche.Lieux) {
			for (var i=couche.Lieux.length; i-->0;) {
				var o = couche.Lieux[i];
				this.getCellCreate(couche, o.X, o.Y).lieu=o;
			}
		}
		if (couche.Palissades) {
			for (var i=couche.Palissades.length; i-->0;) {
				var o = couche.Palissades[i];
				o.sides = 0;
				this.getCellCreate(couche, o.X, o.Y).palissade=o; 
			}
			// deuxième passe : on indique sur chaque case de palissade ses voisins
			for (var i=couche.Palissades.length; i-->0;) {
				var p = couche.Palissades[i];
				var c;
				var nb=0;
				if ((c=this.getCell(couche, p.X+1, p.Y))&&(c.palissade)) {p.sides |= B_RIGHT; nb++;}
				if ((c=this.getCell(couche, p.X-1, p.Y))&&(c.palissade)) {p.sides |= B_LEFT; nb++;}
				if ((c=this.getCell(couche, p.X, p.Y+1))&&(c.palissade)) {p.sides |= B_TOP; nb++;}
				if ((c=this.getCell(couche, p.X, p.Y-1))&&(c.palissade)) {p.sides |= B_BOTTOM; nb++;}
				if (nb==1) { // on va essayer de deviner, le cas échéant, comment ça se prolonge dans le brouillard
					if ((p.sides&B_LEFT)&&(!this.getCell(couche, p.X+1, p.Y))) p.sides|=B_RIGHT;
					else if ((p.sides&B_TOP)&&(!this.getCell(couche, p.X, p.Y-1))) p.sides|=B_BOTTOM;
					else if ((p.sides&B_RIGHT)&&(!this.getCell(couche, p.X-1, p.Y))) p.sides|=B_LEFT;
					else if ((p.sides&B_BOTTOM)&&(!this.getCell(couche, p.X, p.Y+1))) p.sides|=B_TOP;
				}
			}
		}
	}
	if (!this.couche) {
		console.log('Pas de couche zéro !');
		return;
	}
	if (!this.mapData.Vues) this.mapData.Vues=[];
	this.mapData.Vues.sort(function(a, b) {
		return a.Time-b.Time;
	});
	//  les lieux de ville (pour l'instant ?) n'ont pas de profondeur explicite mais ne concernent que la surface. On les met dans la couche zéro
	if (this.mapData.LieuxVilles) {
		for (var i=this.mapData.LieuxVilles.length; i-->0;) {
			var o = this.mapData.LieuxVilles[i];
			this.getCellCreate(this.couche, o.X, o.Y).lieu=o;
		}
	}
	if (mapData.Actions) {
		for (var ia=mapData.Actions.length; ia-->0;) {
			var a = mapData.Actions[ia];
			a.key = this.actions.length; // on donne à l'action une clef pour la retrouver plus facilement
			this.actions.push(a);
			// on ajoute les actions à la vue (trouvée par l'acteur)
			var vue;
			if (this.mapData.Vues) {
				for (var i=this.mapData.Vues.length; i-->0;) {
					if (this.mapData.Vues[i].Voyeur==a.Acteur) {
						vue = this.mapData.Vues[i];
						break;
					}
				}
			}
			if (!vue) {
				console.log('Vue non trouvée pour action');
				continue;
			}
			if (!vue.actions) vue.actions = []; // les actions seront attachées à leur case d'effet éventuelle dans compileLesVues
			vue.actions.push(a);
		}
	}
	this.compileLesVues();
	this.matriceVues = this.matricesVuesParZ[0];
}

// dessine le brouillard de guerre
Map.prototype.drawFog = function() {
	var r = 0.07; // on utilise une image plus petite pour le brouillard, pour améliorer les perfs et rendre flou
	var rw = this.canvas.width*r;
	var rh = this.canvas.height*r;
	var rz = this.zoom*r;
	if (!this.fogContext) {
		this.fogImg = document.createElement('canvas');
		this.fogImg.width = rw;
		this.fogImg.height = rh;
		this.fogContext = this.fogImg.getContext('2d');
	}
	var c = this.fogContext;
	c.globalCompositeOperation = 'source-over';
	c.clearRect(0, 0, rw, rh);
	c.fillStyle = "rgba(0, 0, 0, 0.5)";
	c.fillRect(0, 0, rw, rh);
	if (this.mapData.Vues) {
		for (var i=this.mapData.Vues.length; i-->0;) {
			var vue = this.mapData.Vues[i];
			if (vue.active && vue.Z==this.z) {
				var hole = new Rect();
				hole.x = rz*(this.originX+vue.XMin);
				hole.y = rz*(this.originY-vue.YMin+1);
				hole.w = rz*(this.originX+vue.XMax+1) - hole.x;
				hole.h = - (rz*(this.originY-vue.YMax) - hole.y);
				hole.y -= hole.h;
				if (!Rect_intersect(hole, this.screenRect)) {
					continue;
				}
				c.clearRect(hole.x, hole.y, hole.w, hole.h);
			}
		}
	}
	this.context.drawImage(this.fogImg, 0, 0, this.screenRect.w, this.screenRect.h);
}

// dessine la grille
Map.prototype.drawGrid = function() {
	var c = this.context;
	c.strokeStyle = "rgba(100, 100, 100, 0.4)";
	var sy = this.zoom*(this.originY-this.yMax);
	for (;sy<=this.screenRect.h; sy+=this.zoom) {
		drawThinHorizontalLine(c, 0, this.screenRect.w, sy);
	}
	var sx = (this.originX+this.xMin)*this.zoom;
	for (;sx<=this.screenRect.w; sx+=this.zoom) {
		drawThinVerticalLine(c, sx, 0, this.screenRect.w);
	}
}

// redessine la page. Peut-être appelée de n'importe quel contexte, y compris depuis une méthode de dessin (pour par exemple faire une animation)
Map.prototype.redraw = function() {
	if (!(this.spritesVueTypes.ready&&this.spritesEnv.ready)) {
		return;
	}
	if (this.drawInProgress) {
		this.redrawStacked = true;
		return;
	}
	this.redrawStacked = false;
	try {
		this.drawInProgress = true;
		this.context.fillStyle="#343";
		this.context.fillRect(0, 0, this.screenRect.w, this.screenRect.h);
		if (this.mapData) {
			if (this.displayPhotoSatellite && this.photoSatelliteOK) {
				this.naturalRectToScreenRect(this.photoSatelliteRect, this.photoSatelliteScreenRect);
				this.photoSatelliteScreenRect.drawImage(this.context, this.photoSatellite);
			}
			
			// un carambar au premier qui pourra me réduire le paragraphe qui suit sans diminuer les perfs
			this.xMin = Math.floor(-this.originX);
			this.xMax = Math.ceil(this.screenRect.w/this.zoom-this.originX);
			this.yMin = -Math.floor(this.screenRect.h/this.zoom-this.originY);
			this.yMax = Math.ceil(this.originY);
			if (this.xMin<-800) {
				this.xMin=-800;
				if (this.xMax<-800) this.xMax=-800;
			}
			if (this.xMax>800) {
				this.xMax=800;
				if (this.xMin>800) this.xMin=800;
			}
			if (this.yMin<-500) {
				this.yMin=-500;
				if (this.yMax<-500) this.xMax=-500;
			}
			if (this.yMax>500) {
				this.yMax=500;
				if (this.yMin>500) this.yMin=500;
			}

			if (this.zoom>2) {
				var screenRect = new Rect(); // rectangle d'une cellule en coordonnées canvas
				screenRect.w = this.zoom;
				screenRect.h = this.zoom;
				for (var x=this.xMin; x<=this.xMax; x++) {
					for (var y=this.yMax; y>=this.yMin; y--) { // on balaie en commencant par le haut de l'écran (plus "loin" en perspective)
						var cell = this.getCell(this.couche, x, y);
						if (cell) {
							screenRect.x = this.zoom*(this.originX+x);
							screenRect.y = this.zoom*(this.originY-y);
							var hover = this.zoom>20 && this.pointerX==x && this.pointerY==y;
							if (cell.fond) this.drawFond(screenRect, cell.fond);
							if (cell.champ) this.drawLieu(screenRect, cell.champ, this.spritesVueTypes.get('champ'), hover);
							else if (cell.échoppe) this.drawLieu(screenRect, cell.échoppe, this.spritesVueTypes.get(cell.échoppe.Métier), hover);
							else if (cell.lieu) this.drawLieu(screenRect, cell.lieu, this.spritesVueTypes.get('lieu_' + cell.lieu.IdTypeLieu), hover);
						}
					}
				}
			} else if (this.couche.fond.width) { // si l'image de fond obtenue du serveur est disponible, on l'utilise pour les basses résolutions
				var sw = this.xMax-this.xMin;
				var sh = this.yMax-this.yMin;
				this.context.drawImage(
					this.couche.fond,
					this.xMin+800, 500-this.yMax, sw, sh,
					this.zoom*(this.originX+this.xMin), this.zoom*(this.originY-this.yMax), this.zoom*sw, this.zoom*sh
				);
			}
			if (this.zoom>15 && this.displayGrid) {
				this.drawGrid();
			}
			if (this.zoom>2) { // on dessine les palissades après avoir dessiné la grille pour qu'elle ne les recouvre pas
				var screenRect = new Rect();
				screenRect.w = this.zoom;
				screenRect.h = this.zoom;
				for (var x=this.xMin; x<=this.xMax; x++) {
					for (var y=this.yMax; y>=this.yMin; y--) { // on balaie en commencant par le haut de l'écran (plus "loin" en perspective)
						var cell = this.getCell(this.couche, x, y);
						if (cell && cell.palissade) {
							screenRect.x = this.zoom*(this.originX+x);
							screenRect.y = this.zoom*(this.originY-y);
							this.drawPalissade(screenRect, cell.palissade);
						}
					}
				}
			}
			if (this.mapData.Vues) {
				if (this.zoom>30) {
					this.dessineLesVues();
				}
				if (this.displayFog) {
					this.drawFog();
				}
			}
			if (this.mapData.Villes && this.zoom<=60) {
				for (var i=this.mapData.Villes.length; i-->0;) {
					this.drawTown(this.mapData.Villes[i]);
				}
			}
			if (this.displayRégions && this.mapData.Régions) {
				for (var i=this.mapData.Régions.length; i-->0;) {
					this.drawRégion(this.mapData.Régions[i]);
				}
			}
		}
	} finally {
		this.drawInProgress = false;
	}
	if (this.redrawStacked) {
		setTimeout(this.redraw, 40); 
	}
}
Map.prototype.mouseWheel = function(e) {
	if (this.mouseIsDown) return;
	if (this.dialogIsOpen) {
		this.closeDialog();
	}
	var delta = 0;
	if (!e) e=window.e;
	if (e.wheelDelta) {
		delta = e.wheelDelta / 120;
	} else if (e.detail) {
		delta = -e.detail / 3;
	}
	var oldZoom = this.zoom;
	// recherche du scaleIndex (on va supposer qu'on le trouve)
	var scaleIndex = 0;
	for (var i=0; i<this.scales.length; i++) {
		if (this.zoom==this.scales[i]) {
			scaleIndex = i;
			break;
		}
	}
	if (delta>0) {
		if (scaleIndex<this.scales.length-1) {
			this.zoom = this.scales[++scaleIndex];
		}
	} else if (scaleIndex>0){
		this.zoom = this.scales[--scaleIndex];
	}
	var zr = (1/this.zoom-1/oldZoom);
	this.zoomChangedSinceLastRedraw = true;
	var mouseX = e.offsetX; // Chrome
	var mouseY = e.offsetY; // Chrome
	if (!mouseX) {
		mouseX = e.layerX; // FF
		mouseY = e.layerY; // FF
	}
	this.originX += (mouseX)*zr; 
	this.originY += (mouseY)*zr;
	this.updatePosDiv();
	this.hoverObject = null;
	this.redraw();
}
Map.prototype.mouseDown = function(e) {
	var mouseX = e.offsetX; // Chrome
	var mouseY = e.offsetY; // Chrome
	if (!mouseX) {
		mouseX = e.layerX; // FF
		mouseY = e.layerY; // FF
	}
	this.mouseIsDown = true;
	this.dragStartPageX = mouseX;
	this.dragStartPageY = mouseY;
	this.dragStartOriginX = this.originX;
	this.dragStartOriginY = this.originY;
	this.zoomChangedSinceLastRedraw = true;
	this.redraw();
}
Map.prototype.mouseUp = function(e) {
	this.mouseIsDown = false;
	if (this.dialogIsOpen) {
		if (this.dialogIsFixed) {
			this.closeDialog();
		} else {
			this.fixDialog();
		}
		return;
	}
	var mouseX = e.offsetX; // Chrome
	var mouseY = e.offsetY; // Chrome
	if (!mouseX) {
		mouseX = e.layerX; // FF
		mouseY = e.layerY; // FF
	}
	this.redraw();
}

Map.prototype.mouseLeave = function(e) {
	this.mouseIsDown = false;
	this.hoverObject = null;
	if (this.dialogIsOpen && !this.dialogIsFixed) this.closeDialog();
	this.redraw();
}

// renvoie un "objet" à la position (x,y) dans le référetiel Braldahim :
// - une cellule s'il y en a une non vide (le fond ne comptant pas)
// - une cellule de vue s'il y en a une non vide, en ne cherchant que dans les vues affichées
// - null s'il n'y a rien d'intéressant sur la case
Map.prototype.objectOn = function(x,y) {
	if (this.zoom<10) return null;
	var cell = this.getCell(this.couche, this.pointerX, this.pointerY);
	if (cell && (cell.champ||cell.échoppe||cell.lieu||cell.palissade)) return cell;
	cell = this.getCellVue(x, y);
	if (cell) return cell;
	return null;
}

Map.prototype.mouseMove = function(e) {
	if (!this.mapData) return;
	var mouseX = e.offsetX; // Chrome
	var mouseY = e.offsetY; // Chrome
	if (!mouseX) {
		mouseX = e.layerX; // FF
		mouseY = e.layerY; // FF
	}
	this.pointerScreenX = mouseX;
	this.pointerScreenY = mouseY;
	this.pointerX = Math.floor(mouseX/this.zoom-this.originX);
	this.pointerY = -Math.floor(mouseY/this.zoom-this.originY);
	if (this.mouseIsDown) {
		if (this.dialogIsOpen) {
			this.closeDialog();
		}
		var dx = (mouseX-this.dragStartPageX)/this.zoom;
		var dy = (mouseY-this.dragStartPageY)/this.zoom;
		this.originX = this.dragStartOriginX + dx;
		this.originY = this.dragStartOriginY + dy;
		this.redraw();		
	} else if (!(this.dialogIsOpen&&this.dialogIsFixed)){
		this.updatePosDiv();
		var newHoverObject = this.objectOn(this.pointerX, this.pointerY);
		if (newHoverObject!=this.hoverObject) {
			this.hoverObject = newHoverObject;
			if (newHoverObject) {
				this.openCellDialog(this.pointerX, this.pointerY, false);
			} else if (this.dialogIsOpen) {
				this.$dialog.hide();
				this.dialogIsOpen = false;
			}
			this.redraw();
		}
	}
}

Map.prototype.naturalToScreen = function(naturalPoint, screenPoint) {
	screenPoint.x = this.zoom*(this.originX+naturalPoint.x+0.5);
	screenPoint.y = this.zoom*(this.originY-naturalPoint.y+0.5);
};

Map.prototype.screenToNatural = function(screenPoint, naturalPoint) {
	naturalPoint.x = screenPoint.x/this.zoom - this.originX;	
	naturalPoint.y = screenPoint.y/this.zoom - this.originY;
};

Map.prototype.screenRectToNaturalRect = function(screenRect, naturalRect) {
	naturalRect.x = screenRect.x/this.zoom - this.originX;	
	naturalRect.y = screenRect.y/this.zoom - this.originY;
	naturalRect.w = screenRect.w/this.zoom;
	naturalRect.h = screenRect.h/this.zoom;
};

Map.prototype.naturalRectToScreenRect = function(naturalRect, screenRect) {
	screenRect.x = this.zoom*(this.originX+naturalRect.x);
	screenRect.y = this.zoom*(this.originY-naturalRect.y);
	screenRect.w = this.zoom*naturalRect.w;
	screenRect.h = this.zoom*naturalRect.h;
};


// permet de spécifier un callback pour une clef
// - 'profondeur' : appelé en cas de changement de profondeur. L'argument de la méthode sera la profondeur z
Map.prototype.setCallback = function(key, f) {
	this.callbacks[key] = f;
}
