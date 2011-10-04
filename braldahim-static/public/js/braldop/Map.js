
function Map(canvasId, posmarkid, dialogId) {
	this.canvas = document.getElementById(canvasId);
	this.context = this.canvas.getContext("2d");
	this.context.mozImageSmoothingEnabled = false; // contourne un bug de FF qui rend floues les images même à taille naturelle
	this.posmarkdiv = document.getElementById(posmarkid);
	this.callbacks = {};
	this.initTiles();
	this.screenRect = new Rect();
	this.rect = new Rect(); // le rectangle englobant le contenu que l'on veut montrer
	this.originX=0; // coin haut gauche de la grotte au centre de l'écran
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
	this.$dialog = $('#'+dialogId);
	this.dialopIsOpen = false;
	this.fogImg = null;
	this.fogContext = null;
	this.recomputeCanvasPosition();
	var _this = this;
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

	// Gestion de la molette, au dessus ou non, de la carte
	this.mouseOnMap = false;
	$('#'+canvasId).mouseover(function() {
		_this.mouseOnMap = true;
		document.body.style.overflow = "hidden";
	}).mouseout(function() {
		_this.mouseOnMap = false;
		document.body.style.overflow = "";
	});

	this.canvas.addEventListener("mousedown", function(e) {_this.mouseDown(e)}, false);
	this.canvas.addEventListener("mouseup", function(e) {_this.mouseUp(e)}, false);
	this.canvas.addEventListener("mouseleave", function(e) {_this.mouseLeave(e)}, false);
	this.canvas.addEventListener("mousemove", function(e) {_this.mouseMove(e)}, false);
	if (window.addEventListener) window.addEventListener("DOMMouseScroll", function(e) {_this.mouseWheel(e)}, false); // firefox
	window.onmousewheel = function(e) {_this.mouseWheel(e)}; // chrome
	$(window).resize(function(){
		_this.recomputeCanvasPosition();
		_this.redraw();
	});
}

Map.prototype.updatePosDiv = function() {
	this.posmarkdiv.innerHTML='Zoom='+this.zoom+' &nbsp; X='+this.pointerX+' &nbsp; Y='+this.pointerY+' &nbsp; Z='+this.z;
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
	this.updatePosDiv();
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
	var index = ((x+this.W)%(2*this.W))+2*this.W*(y+this.W);
	//console.log("("+x+","+y+") -> "+index);
	var cell = couche.matrix[index];
	if (!cell) {
		cell = {};
		couche.matrix[index] = cell;
	}
	return cell;
}
// renvoie une cellule (en la créant si nécessaire, ne pas utiliser cette méthode en simple lecture)
Map.prototype.getCell = function(couche, x, y) {
	var index = ((x+this.W)%(2*this.W))+2*this.W*(y+this.W);
	return couche.matrix[index];
}

Map.prototype.recomputeCanvasPosition = function() {
	var pos = $(this.canvas).offset();
	this.canvas_position_x = pos.left;
	this.canvas_position_y = pos.top;
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
	console.log("carte reçue");
	this.z = 0; // on va basculer forcément sur la couche zéro
	this.couche = null; 
	for (var ic=0; ic<this.mapData.Couches.length; ic++) {
		var couche = this.mapData.Couches[ic];
		if (couche.Z==0) this.couche = couche;
		couche.matrix = {};//new Array(); // todo benchmarker pour comparer les effets en ram et cpu de la version map et de la version table
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
				o.détails = "Propriétaire : "+o.NomCompletBraldun;
				this.getCellCreate(couche, o.X, o.Y).champ=o;
			}
		}
		if (couche.Echoppes) {
			for (var i=couche.Echoppes.length; i-->0;) {
				var o = couche.Echoppes[i];
				o.détails = o.Métier+" : "+o.NomCompletBraldun;
				this.getCellCreate(couche, o.X, o.Y).échoppe=o;
			}
		}
	}
	if (!this.couche) {
		console.log('Pas de couche zéro !');
		return;
	}
	//  les lieux de ville (pour l'instant ?) n'ont pas de profondeur explicite mais ne concerne que la surface. On les met dans la couche zéro
	if (this.mapData.LieuxVilles) {
		for (var i=this.mapData.LieuxVilles.length; i-->0;) {
			var o = this.mapData.LieuxVilles[i];
			this.getCellCreate(this.couche, o.X, o.Y).lieu=o;
		}
	}
	console.log("carte compilée");
}

// dessine le brouillard de guerre
Map.prototype.drawFog = function() {
	var alwaysUseNewFog = true; // je teste...
	if (this.mapData.Vues.length>1 || alwaysUseNewFog) {
		//> Cette méthode gère correctement tous les cas, en particulier celui de l'intersection de plusieurs vues,
		//  mais elle est lente sur Firefox.
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
					//var hole = holes[i];
					var hole = new Rect();
					hole.x = rz*(this.originX+vue.XMin);
					hole.y = rz*(this.originY-vue.YMin+1);
					hole.w = rz*(this.originX+vue.XMax+1) - hole.x;
					hole.h = - (rz*(this.originY-vue.YMax) - hole.y);
					hole.y -= hole.h;
					if (!Rect_intersect(hole, this.screenRect)) {
						continue;
					}
					if (!hole) continue;
					c.clearRect(hole.x, hole.y, hole.w, hole.h);
				}
			}
		}
		this.context.drawImage(this.fogImg, 0, 0, this.screenRect.w, this.screenRect.h);
	} else {
		//> On utilise une méthode différente s'il n'y a qu'une seule vue car la méthode compatible
		//  avec plusieurs vues est lente sur Firefox.
		var c = this.context;
		c.beginPath();
		c.moveTo(0, 0);
		c.lineTo(this.screenRect.w, 0);
		c.lineTo(this.screenRect.w, this.screenRect.h);
		c.lineTo(0, this.screenRect.h);
		c.closePath();
		var radius = this.zoom/6;
		var vue = this.mapData.Vues[0];
		if (vue.active && vue.Z==this.z) {
			hasVue = true;
			var hole = new Rect();
			hole.x = this.zoom*(this.originX+vue.XMin);
			hole.y = this.zoom*(this.originY-vue.YMin+1);
			hole.w = this.zoom*(this.originX+vue.XMax+1) - hole.x;
			hole.h = - (this.zoom*(this.originY-vue.YMax) - hole.y);
			hole.y -= hole.h;
			if (Rect_intersect(hole, this.screenRect)) {
				c.moveTo(hole.x, hole.y+radius);
				c.arcTo(hole.x, hole.y+hole.h, hole.x+radius, hole.y+hole.h, radius);
				c.arcTo(hole.x+hole.w, hole.y+hole.h, hole.x+hole.w, hole.y+radius, radius);
				c.arcTo(hole.x+hole.w, hole.y, hole.x+radius, hole.y, radius);
				c.arcTo(hole.x, hole.y, hole.x, hole.y+radius, radius);
			}
		}
		c.fillStyle = "rgba(100, 100, 100, 0.5)";
		c.fill();
	}
}

// redessine la page. Peut-être appelée de n'importe quel contexte, y compris depuis une méthode de dessin (pour par exemple faire une animation)
Map.prototype.redraw = function() {
	if (this.drawInProgress) {
		this.redrawStacked = true;
		return;
	}
	this.redrawStacked = false;
	try {
		this.drawInProgress = true;
		this.context.fillStyle="#343";
		this.context.fillRect(0, 0, this.screenRect.w, this.screenRect.h);
		this.bubbleText = [];
		if (this.mapData) {
			if (this.displayPhotoSatellite && this.photoSatelliteOK) {
				this.naturalRectToScreenRect(this.photoSatelliteRect, this.photoSatelliteScreenRect);
				this.photoSatelliteScreenRect.drawImage(this.context, this.photoSatellite);
			}
			var xMin = Math.floor(-this.originX);
			var xMax = Math.ceil(this.screenRect.w/this.zoom-this.originX);
			var yMin = -Math.floor(this.screenRect.h/this.zoom-this.originY);
			var yMax = Math.ceil(this.originY);

			if (this.zoom>1) {
				var screenRect = new Rect();
				screenRect.w = this.zoom;
				screenRect.h = this.zoom;
				for (var x=xMin; x<=xMax; x++) {
					for (var y=yMin; y<=yMax; y++) {
						var cell = this.getCell(this.couche, x, y);
						if (cell) {
							screenRect.x = this.zoom*(this.originX+x);
							screenRect.y = this.zoom*(this.originY-y);
							var hover = this.zoom>20 && this.pointerX==x && this.pointerY==y;
							if (cell.fond) this.drawFond(screenRect, cell.fond);
							if (cell.champ) this.drawLieu(screenRect, cell.champ, this.champImg, hover);
							else if (cell.échoppe) this.drawLieu(screenRect, cell.échoppe, this.echoppeImg[cell.échoppe.Métier], hover);
							else if (cell.lieu) this.drawLieu(screenRect, cell.lieu, this.placeImg[cell.lieu.IdTypeLieu], hover);
						}
					}
				}
			}
			if (this.mapData.Vues) {
				for (var i=this.mapData.Vues.length; i-->0;) {
					var vue = this.mapData.Vues[i];
					if (vue.active && vue.Z==this.z) this.drawVue(vue, xMin, xMax, yMin, yMax);
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
			if (this.bubbleText.length>0 && !this.dialopIsOpen) {
				this.bubbleText.splice(0, 0, this.pointerX+','+this.pointerY);
				this.drawBubble();
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
	if (!this.mouseOnMap) return;
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
	if (this.dialopIsOpen) {
		this.$dialog.hide();
		this.dialopIsOpen = false;
		return;
	}
	var mouseX = e.offsetX; // Chrome
	var mouseY = e.offsetY; // Chrome
	if (!mouseX) {
		mouseX = e.layerX; // FF
		mouseY = e.layerY; // FF
	}
	if (Math.abs(mouseX-this.dragStartPageX)<5 && Math.abs(mouseY-this.dragStartPageY)<5 && this.hoverObject) {
		this.openCellDialog(this.pointerX, this.pointerY);
	}
	this.redraw();
}

Map.prototype.mouseLeave = function(e) {
	this.mouseIsDown = false;
	this.hoverObject = null;
	this.redraw();
}

// renvoie un "objet" à la position (x,y) dans le référetiel Braldahim :
// - une cellule s'il y en a une non vide (le fond ne comptant pas)
// - une cellule de vue s'il y en a une non vide, en ne cherchant que dans les vues affichées
// - null s'il n'y a rien d'intéressant sur la case
Map.prototype.objectOn = function(x,y) {
	if (this.zoom<10) return null;
	var cell = this.getCell(this.couche, this.pointerX, this.pointerY);
	if (cell && (cell.champ||cell.échoppe||cell.lieu)) return cell;
	if (this.mapData.Vues) {
		for (var i=this.mapData.Vues.length; i-->0;) {
			var vue = this.mapData.Vues[i];
			if (vue.active && vue.Z==this.z) {
				var cell = getCellVue(vue, x, y);
				if (cell) {
					return cell;
				}
			}
		}
	}
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
	this.updatePosDiv();
	if (this.mouseIsDown) {
		var dx = (mouseX-this.dragStartPageX)/this.zoom;
		var dy = (mouseY-this.dragStartPageY)/this.zoom;
		this.originX = this.dragStartOriginX + dx;
		this.originY = this.dragStartOriginY + dy;
		this.redraw();		
	} else {
		var newHoverObject = this.objectOn(this.pointerX, this.pointerY);
		if (newHoverObject!=this.hoverObject) {
			this.hoverObject = newHoverObject;
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


// permet de spécifier un callback 
// - 'profondeur' : appelé en cas de changement de profondeur. L'argument de la méthode sera la profondeur z
Map.prototype.setCallback = function(key, f) {
	this.callbacks[key] = f;
}
