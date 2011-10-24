
Map.prototype.initTiles = function() {
	this.typesBatiments = []; // fait le lien entre le type numérique du batiment et le type chaine
	this.typesBatiments[1] = "mairie";
	this.typesBatiments[2] = "centreformation";
	this.typesBatiments[3] = "gare";
	this.typesBatiments[4] = "hopital";
	this.typesBatiments[5] = "bibliotheque";
	this.typesBatiments[6] = "academie";
	this.typesBatiments[7] = "banque";
	this.typesBatiments[8] = "joaillier";
	this.typesBatiments[9] = "auberge";
	this.typesBatiments[10] = "bbois";
	this.typesBatiments[11] = "bpartieplantes";
	this.typesBatiments[12] = "bminerais";
	this.typesBatiments[13] = "tabatiere";
	this.typesBatiments[14] = "notaire";
	this.typesBatiments[15] = "quete";
	this.typesBatiments[16] = "echangeurrune";
	this.typesBatiments[17] = "assembleur";
	this.typesBatiments[18] = "bpeaux";
	this.typesBatiments[19] = "hotel";
	this.typesBatiments[20] = "postedegarde";
	this.typesBatiments[21] = "entreegrotte";
	this.typesBatiments[22] = "escaliers";
	this.typesBatiments[23] = "lieumythique";
	this.typesBatiments[24] = "ruine";
	this.typesBatiments[25] = "tribunal";
	this.typesBatiments[26] = "contrat";
	this.typesBatiments[27] = "maisonpnj";
	this.typesBatiments[28] = "mine";
	this.typesBatiments[29] = "puits";
	this.typesBatiments[30] = "hall";
	this.typesBatiments[31] = "grenier";
	this.typesBatiments[32] = "temple";
	this.typesBatiments[33] = "marche";
	this.typesBatiments[34] = "infirmerie";
	this.typesBatiments[35] = "baraquement";
	this.typesBatiments[36] = "tribune";
	this.typesBatiments[37] = "atelier";
	this.typesBatiments[38] = "haltegare";
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
		img.outline = outlinedImg;
	}
	return img.outline;
}

// dessine une case d'environnement
Map.prototype.drawFond = function(screenRect, fond) {
	var envTile = this.spritesEnv.get('env-'+fond);
	if (envTile) {
		screenRect.drawImage(this.context, envTile);
	} else {
		//~ console.log('fond introuvable : ' + fond);
		//~ screenRect.fill(this.context, "red");
	}
}

// dessine un lieu de ville, une échoppe ou un champ
Map.prototype.drawLieu = function(screenRect, lieu, img, hover) {
	var c = this.context;
	var cx = screenRect.x+0.75*screenRect.w;
	var cy = screenRect.y+0.25*screenRect.h;
	var imgw;
	if (this.zoom!=64) imgw=this.zoom*0.5;
	if (img) {
		if (hover) {
			drawCenteredImage(c, this.getOutlineImg(img), cx, cy, imgw?imgw+4:null, null);
			this.bubbleText.push(lieu.Nom);
			if (lieu.détails) this.bubbleText.push("  "+lieu.détails);
		}
		drawCenteredImage(c, img, cx, cy, imgw);
	} else {
		console.log("pas d'image pour " + lieu.Nom);
	}
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
