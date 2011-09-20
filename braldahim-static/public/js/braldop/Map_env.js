
Map.prototype.initTiles = function() {
	var baseTilesUrl = "http://static.braldahim.com/images/vue/";
	var _this = this;

	this.placeImg = []; // tableau des images des lieux en fonction de leur type entier
	(this.placeImg[1] = new Image()).src = baseTilesUrl + "batiments/mairie.png";
	(this.placeImg[2] = new Image()).src = baseTilesUrl + "batiments/centreformation.png";
	(this.placeImg[3] = new Image()).src = baseTilesUrl + "batiments/gare.png";
	(this.placeImg[4] = new Image()).src = baseTilesUrl + "batiments/hopital.png";
	(this.placeImg[5] = new Image()).src = baseTilesUrl + "batiments/bibliotheque.png";
	(this.placeImg[6] = new Image()).src = baseTilesUrl + "batiments/academie.png";
	(this.placeImg[7] = new Image()).src = baseTilesUrl + "batiments/banque.png";
	(this.placeImg[8] = new Image()).src = baseTilesUrl + "batiments/joaillier.png";
	(this.placeImg[9] = new Image()).src = baseTilesUrl + "batiments/auberge.png";
	(this.placeImg[10] = new Image()).src = baseTilesUrl + "batiments/bbois.png";
	(this.placeImg[11] = new Image()).src = baseTilesUrl + "batiments/bpartieplantes.png";
	(this.placeImg[12] = new Image()).src = baseTilesUrl + "batiments/bminerais.png";
	(this.placeImg[13] = new Image()).src = baseTilesUrl + "batiments/tabatiere.png";
	(this.placeImg[14] = new Image()).src = baseTilesUrl + "batiments/notaire.png";
	(this.placeImg[15] = new Image()).src = baseTilesUrl + "batiments/quete.png";
	(this.placeImg[16] = new Image()).src = baseTilesUrl + "batiments/echangeurrune.png";
	(this.placeImg[17] = new Image()).src = baseTilesUrl + "batiments/assembleur.png";
	(this.placeImg[18] = new Image()).src = baseTilesUrl + "batiments/bpeaux.png";
	(this.placeImg[19] = new Image()).src = baseTilesUrl + "batiments/hotel.png";
	(this.placeImg[20] = new Image()).src = baseTilesUrl + "batiments/postedegarde.png";
	(this.placeImg[21] = new Image()).src = baseTilesUrl + "batiments/entreegrotte.png";
	(this.placeImg[22] = new Image()).src = baseTilesUrl + "batiments/escaliers.png";
	(this.placeImg[23] = new Image()).src = baseTilesUrl + "batiments/lieumythique.png";
	(this.placeImg[24] = new Image()).src = baseTilesUrl + "batiments/ruine.png";
	(this.placeImg[25] = new Image()).src = baseTilesUrl + "batiments/tribunal.png";
	(this.placeImg[26] = new Image()).src = baseTilesUrl + "batiments/contrat.png";
	(this.placeImg[27] = new Image()).src = baseTilesUrl + "batiments/maisonpnj.png";
	(this.placeImg[28] = new Image()).src = baseTilesUrl + "batiments/mine.png";
	(this.placeImg[29] = new Image()).src = baseTilesUrl + "batiments/puit.png";
	(this.placeImg[30] = new Image()).src = baseTilesUrl + "batiments/hall.png";
	(this.placeImg[31] = new Image()).src = baseTilesUrl + "batiments/grenier.png";
	(this.placeImg[32] = new Image()).src = baseTilesUrl + "batiments/temple.png";
	(this.placeImg[33] = new Image()).src = baseTilesUrl + "batiments/marche.png";
	(this.placeImg[34] = new Image()).src = baseTilesUrl + "batiments/infirmerie.png";
	(this.placeImg[35] = new Image()).src = baseTilesUrl + "batiments/baraquement.png";
	(this.placeImg[36] = new Image()).src = baseTilesUrl + "batiments/tribune.png";
	(this.placeImg[37] = new Image()).src = baseTilesUrl + "batiments/atelier.png";
	(this.placeImg[38] = new Image()).src = baseTilesUrl + "batiments/haltegare.png";

	this.echoppeImg = []; // tableau des images des échoppes en fonction de leur métier
	(this.echoppeImg["apothicaire"] = new Image()).src = baseTilesUrl + "echoppes/apothicaire.png";
	(this.echoppeImg["cuisinier"] = new Image()).src = baseTilesUrl + "echoppes/cuisinier.png";
	(this.echoppeImg["forgeron"] = new Image()).src = baseTilesUrl + "echoppes/forgeron.png";
	(this.echoppeImg["menuisier"] = new Image()).src = baseTilesUrl + "echoppes/menuisier.png";
	(this.echoppeImg["tanneur"] = new Image()).src = baseTilesUrl + "echoppes/tanneur.png";
	
	(this.champImg = new Image()).src = baseTilesUrl + "champ.png";

	this.envTiles = {}; // map
	var environnements = new Array(
		"caverne-crevasse", "caverne-gr-crevasse",
		"caverne-crevasse", "caverne-gr", "caverne", "gazon-gr", "gazon", "marais-gr", "marais",
		"mine-gr", "mine", "montagne-gr", "montagne", "plaine", "plaine-gr", "tunnel-gr", "tunnel", "route", "pave"
	);
	for (env in environnements) {
		(this.envTiles[environnements[env]] = new Image()).src = baseTilesUrl + "environnement/" + environnements[env] + ".png";
	}
	
	(this.img_braldun_feminin = new Image()).src = baseTilesUrl + "braldun_feminin.png";
	(this.img_braldun_masculin = new Image()).src = baseTilesUrl + "braldun_masculin.png";
	(this.img_bralduns_feminin = new Image()).src = baseTilesUrl + "bralduns_feminin.png";
	(this.img_bralduns_masculin = new Image()).src = baseTilesUrl + "bralduns_masculin.png";
	(this.img_bralduns_masculin_feminin = new Image()).src = baseTilesUrl + "bralduns_masculin_feminin.png";
	
	for (tile in this.envTiles) {
		tile.onload = function() { 	_this.redraw(); }; // on dirait que ça ne marche pas
	}
}

// cette méthode est imparfaite : elle ne crée pas réellement un contour
Map.prototype.getOutlineImg = function(img) {
	if (!img.outline) {
		var outlinedImg = document.createElement('canvas');
		var ow = img.width+4;
		var oh = img.height+4;
		outlinedImg.width = ow;
		outlinedImg.height = oh;
		oc = outlinedImg.getContext('2d');
		oc.drawImage(img, 0, 0, ow, oh);
		oc.globalCompositeOperation="source-in";
		oc.fillStyle="Gold";//"DarkGoldenRod";
		oc.fillRect(0, 0, ow, oh);
		//oc.drawImage(img, 2, 2); TODO ça serait pas mal d'avoir une seule image (cad que l'outline contienne l'original) mais je maitrise pas bien le rendu
		img.outline = outlinedImg;
	}
	return img.outline;
}

// dessine une case d'environnement
Map.prototype.drawCell = function(cell) {
	var screenRect = new Rect();
	screenRect.x = this.zoom*(this.originX+cell.X);
	screenRect.y = this.zoom*(this.originY-cell.Y);
	screenRect.w = this.zoom;
	screenRect.h = this.zoom;
	if (!Rect_intersect(screenRect, this.screenRect)) {
		return;
	}
	var envTile = this.envTiles[cell.Fond];
	if (envTile) {
		screenRect.drawImage(this.context, envTile);
	} else {
		screenRect.fill(this.context, "red");
	}
}

// dessine un champ
Map.prototype.drawChamp = function(e) {
	var screenRect = new Rect();
	screenRect.w = this.zoom/2;
	screenRect.h = screenRect.w*(29/32); // ajustement manuel parce que je suis fatigué des dimensions de l'image du champ
	screenRect.x = this.zoom*(this.originX+e.X)+screenRect.w;
	screenRect.y = this.zoom*(this.originY-e.Y);
	if (!Rect_intersect(screenRect, this.screenRect)) {
		return;
	}
	var c = this.context;
	c.save();
	if (e.X==this.pointerX && e.Y==this.pointerY) {
		c.shadowOffsetX = 0;
		c.shadowOffsetY = 0;
		c.shadowBlur = 5;
		c.shadowColor = "black";
		var d = 3;
		c.drawImage(this.getOutlineImg(this.champImg), screenRect.x-d, screenRect.y-d, screenRect.w+2*d, screenRect.h+2*d);
		c.shadowBlur = 0;
		this.bubbleText.push("Champ du Braldûn " + e.IdBraldun);
	}
	screenRect.drawImage(c, this.champImg);
	c.restore();
}

// dessine une échoppe
Map.prototype.drawEchoppe = function(e) {
	var screenRect = new Rect();
	screenRect.w = this.zoom/2;
	screenRect.h = screenRect.w;
	screenRect.x = this.zoom*(this.originX+e.X)+screenRect.w;
	screenRect.y = this.zoom*(this.originY-e.Y);
	if (!Rect_intersect(screenRect, this.screenRect)) {
		return;
	}
	var c = this.context;
	c.save();
	var img = this.echoppeImg[e.Métier];
	if (img) {
		if (e.X==this.pointerX && e.Y==this.pointerY) {
			c.shadowOffsetX = 0;
			c.shadowOffsetY = 0;
			c.shadowBlur = 5;
			c.shadowColor = "black";
			var d = 3;
			c.drawImage(this.getOutlineImg(img), screenRect.x-d, screenRect.y-d, screenRect.w+2*d, screenRect.h+2*d);
			c.shadowBlur = 0;
			this.bubbleText.push(e.Nom);
			this.bubbleText.push('('+e.Métier+')');
		}
		screenRect.drawImage(c, img);
	} else {
		console.log("pas d'image pour " + e.Métier);
	}	c.restore();
}

// dessine un lieu de ville
Map.prototype.drawTownPlace = function(lieu) {
	var screenRect = new Rect();
	screenRect.w = this.zoom/2;
	screenRect.h = screenRect.w;
	screenRect.x = this.zoom*(this.originX+lieu.X)+screenRect.w;
	screenRect.y = this.zoom*(this.originY-lieu.Y);
	if (!Rect_intersect(screenRect, this.screenRect)) {
		return;
	}
	var c = this.context;
	c.save();
	var img = this.placeImg[lieu.IdTypeLieu];
	if (img) {
		if (lieu.X==this.pointerX && lieu.Y==this.pointerY) {
			c.shadowOffsetX = 0;
			c.shadowOffsetY = 0;
			c.shadowBlur = 5;
			c.shadowColor = "black";
			var d = 3;
			c.drawImage(this.getOutlineImg(this.placeImg[lieu.IdTypeLieu]), screenRect.x-d, screenRect.y-d, screenRect.w+2*d, screenRect.h+2*d);
			c.shadowBlur = 0;
			this.bubbleText.push(lieu.Nom);
		}
		screenRect.drawImage(c, img);
	} else {
		console.log("pas d'image pour " + lieu.Nom);
	}
	if (this.displayTownPlaceNames && this.zoom>60) {
		c.fillStyle = "black";
		var lh = 12;
		c.font = "bold "+lh+"px Verdana";
		c.shadowOffsetX = 0;
		c.shadowOffsetY = 0;
		c.shadowBlur = 4;
		c.shadowColor = "white";
		var textWidth = c.measureText(lieu.Nom).width;
		var x=screenRect.x+(screenRect.w-textWidth)/2;
		var y=screenRect.y+(screenRect.h)/2;
		if (lieu.X%2) y+=lh+7;
		else y+=4;
		c.fillText(lieu.Nom, x, y);
	}
	c.restore();
}

// dessine un nom de ville
Map.prototype.drawTown = function(ville) {
	var c = this.context;
	var screenRect = new Rect();
	screenRect.x = this.zoom*(this.originX+ville.XMin);
	screenRect.y = this.zoom*(this.originY-ville.YMin);
	screenRect.w = this.zoom*(this.originX+ville.XMax) - screenRect.x;
	screenRect.h = - (this.zoom*(this.originY-ville.YMax) - screenRect.y);
	screenRect.y -= screenRect.h;
	if (!Rect_intersect(screenRect, this.screenRect)) {
		return;
	}
	c.fillStyle = "white";
	var lh = ville.EstCapitale ? 18 : 14;
	c.font = "bold "+lh+"px Verdana";
	c.save();
	c.shadowOffsetX = 0;
	c.shadowOffsetY = 0;
	c.shadowBlur = 5;
	c.shadowColor = "black";
	var textWidth = c.measureText(ville.Nom).width;
	var x=screenRect.x+(screenRect.w-textWidth)/2;
	var y=screenRect.y+(screenRect.h)/2;
	c.fillText(ville.Nom, x, y);
	c.restore();
}

// dessine une région
Map.prototype.drawRégion = function(r) {
	var c = this.context;
	var screenRect = new Rect();
	screenRect.x = this.zoom*(this.originX+r.XMin);
	screenRect.y = this.zoom*(this.originY-r.YMin);
	screenRect.w = this.zoom*(this.originX+r.XMax) - screenRect.x;
	screenRect.h = - (this.zoom*(this.originY-r.YMax) - screenRect.y);
	screenRect.y -= screenRect.h;
	if (!Rect_intersect(screenRect, this.screenRect)) {
		return;
	}
	c.save();
	var color = r.EstPvp ? "red" : "#99F";
	c.strokeStyle = color;
	screenRect.drawThin(this.context);
	c.fillStyle = color;
	var lh = 20;
	c.font = "bold "+lh+"px Verdana";
	c.shadowOffsetX = 0;
	c.shadowOffsetY = 0;
	c.shadowBlur = 5;
	c.shadowColor = "black";
	var textWidth = c.measureText(r.Nom).width;
	var x=screenRect.x+(screenRect.w-textWidth)/2;
	var y=screenRect.y+(screenRect.h)/2;
	c.fillText(r.Nom, x, y);
	c.restore();
}
