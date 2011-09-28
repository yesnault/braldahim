
Map.prototype.initTiles = function() {
	var baseTilesUrl = "http://static.braldahim.com/images/";
	var _this = this;

	this.placeImg = []; // tableau des images des lieux en fonction de leur type entier
	(this.placeImg[1] = new Image()).src = baseTilesUrl + "vue/batiments/mairie.png";
	(this.placeImg[2] = new Image()).src = baseTilesUrl + "vue/batiments/centreformation.png";
	(this.placeImg[3] = new Image()).src = baseTilesUrl + "vue/batiments/gare.png";
	(this.placeImg[4] = new Image()).src = baseTilesUrl + "vue/batiments/hopital.png";
	(this.placeImg[5] = new Image()).src = baseTilesUrl + "vue/batiments/bibliotheque.png";
	(this.placeImg[6] = new Image()).src = baseTilesUrl + "vue/batiments/academie.png";
	(this.placeImg[7] = new Image()).src = baseTilesUrl + "vue/batiments/banque.png";
	(this.placeImg[8] = new Image()).src = baseTilesUrl + "vue/batiments/joaillier.png";
	(this.placeImg[9] = new Image()).src = baseTilesUrl + "vue/batiments/auberge.png";
	(this.placeImg[10] = new Image()).src = baseTilesUrl + "vue/batiments/bbois.png";
	(this.placeImg[11] = new Image()).src = baseTilesUrl + "vue/batiments/bpartieplantes.png";
	(this.placeImg[12] = new Image()).src = baseTilesUrl + "vue/batiments/bminerais.png";
	(this.placeImg[13] = new Image()).src = baseTilesUrl + "vue/batiments/tabatiere.png";
	(this.placeImg[14] = new Image()).src = baseTilesUrl + "vue/batiments/notaire.png";
	(this.placeImg[15] = new Image()).src = baseTilesUrl + "vue/batiments/quete.png";
	(this.placeImg[16] = new Image()).src = baseTilesUrl + "vue/batiments/echangeurrune.png";
	(this.placeImg[17] = new Image()).src = baseTilesUrl + "vue/batiments/assembleur.png";
	(this.placeImg[18] = new Image()).src = baseTilesUrl + "vue/batiments/bpeaux.png";
	(this.placeImg[19] = new Image()).src = baseTilesUrl + "vue/batiments/hotel.png";
	(this.placeImg[20] = new Image()).src = baseTilesUrl + "vue/batiments/postedegarde.png";
	(this.placeImg[21] = new Image()).src = baseTilesUrl + "vue/batiments/entreegrotte.png";
	(this.placeImg[22] = new Image()).src = baseTilesUrl + "vue/batiments/escaliers.png";
	(this.placeImg[23] = new Image()).src = baseTilesUrl + "vue/batiments/lieumythique.png";
	(this.placeImg[24] = new Image()).src = baseTilesUrl + "vue/batiments/ruine.png";
	(this.placeImg[25] = new Image()).src = baseTilesUrl + "vue/batiments/tribunal.png";
	(this.placeImg[26] = new Image()).src = baseTilesUrl + "vue/batiments/contrat.png";
	(this.placeImg[27] = new Image()).src = baseTilesUrl + "vue/batiments/maisonpnj.png";
	(this.placeImg[28] = new Image()).src = baseTilesUrl + "vue/batiments/mine.png";
	(this.placeImg[29] = new Image()).src = baseTilesUrl + "vue/batiments/puits.png";
	(this.placeImg[30] = new Image()).src = baseTilesUrl + "vue/batiments/hall.png";
	(this.placeImg[31] = new Image()).src = baseTilesUrl + "vue/batiments/grenier.png";
	(this.placeImg[32] = new Image()).src = baseTilesUrl + "vue/batiments/temple.png";
	(this.placeImg[33] = new Image()).src = baseTilesUrl + "vue/batiments/marche.png";
	(this.placeImg[34] = new Image()).src = baseTilesUrl + "vue/batiments/infirmerie.png";
	(this.placeImg[35] = new Image()).src = baseTilesUrl + "vue/batiments/baraquement.png";
	(this.placeImg[36] = new Image()).src = baseTilesUrl + "vue/batiments/tribune.png";
	(this.placeImg[37] = new Image()).src = baseTilesUrl + "vue/batiments/atelier.png";
	(this.placeImg[38] = new Image()).src = baseTilesUrl + "vue/batiments/haltegare.png";

	this.echoppeImg = []; // tableau des images des échoppes en fonction de leur métier
	(this.echoppeImg["apothicaire"] = new Image()).src = baseTilesUrl + "vue/echoppes/apothicaire.png";
	(this.echoppeImg["cuisinier"] = new Image()).src = baseTilesUrl + "vue/echoppes/cuisinier.png";
	(this.echoppeImg["forgeron"] = new Image()).src = baseTilesUrl + "vue/echoppes/forgeron.png";
	(this.echoppeImg["menuisier"] = new Image()).src = baseTilesUrl + "vue/echoppes/menuisier.png";
	(this.echoppeImg["tanneur"] = new Image()).src = baseTilesUrl + "vue/echoppes/tanneur.png";
	
	(this.champImg = new Image()).src = baseTilesUrl + "vue/champ.png";

	this.envTiles = {}; // map
	var environnements = new Array(
		"caverne-crevasse", "caverne-gr-crevasse",
		"caverne-crevasse", "caverne-gr", "caverne", "gazon-gr", "gazon", "marais-gr", "marais",
		"mine-gr", "mine", "montagne-gr", "montagne", "plaine", "plaine-gr", "tunnel-gr", "tunnel", "route", "pave",
		"palissade", "portail", "lac", "mer", "peuprofonde", "profonde", //  eaux
		"erables", "erables-gr", "chenes", "chenes-gr", "peupliers", "peupliers-gr", "hetres", "hetres-gr" // bosquets
	);
	for (env in environnements) {
		(this.envTiles[environnements[env]] = new Image()).src = baseTilesUrl + "vue/environnement/" + environnements[env] + ".png";
	}
	
	(this.img_braldun_feminin = new Image()).src = baseTilesUrl + "vue/braldun_feminin.png";
	(this.img_braldun_masculin = new Image()).src = baseTilesUrl + "vue/braldun_masculin.png";
	(this.img_bralduns_feminin = new Image()).src = baseTilesUrl + "vue/bralduns_feminin.png";
	(this.img_bralduns_masculin = new Image()).src = baseTilesUrl + "vue/bralduns_masculin.png";
	(this.img_bralduns_masculin_feminin = new Image()).src = baseTilesUrl + "vue/bralduns_masculin_feminin.png";
	(this.img_braldun_ko = new Image()).src = baseTilesUrl + "vue/braldunKo.png";
	
	this.imgObjets = {};
	(this.imgObjets['castar'] = new Image()).src = baseTilesUrl + "vue/castars.png";
	(this.imgObjets['charrette'] = new Image()).src = baseTilesUrl + "cockpit/charrette.png";
	(this.imgObjets['cuir'] = new Image()).src = baseTilesUrl + "elements/cuir.png";
	(this.imgObjets['fourrure'] = new Image()).src = baseTilesUrl + "elements/fourrure.png";
	(this.imgObjets['peau'] = new Image()).src = baseTilesUrl + "elements/peau.png";
	(this.imgObjets['planche'] = new Image()).src = baseTilesUrl + "elements/planche.png";
	(this.imgObjets['rondin'] = new Image()).src = baseTilesUrl + "elements/rondin.png";
	(this.imgObjets['rune'] = new Image()).src = baseTilesUrl + "vue//runes.png"; // rien pour le singulier ?
	
	var numTypeMonstres =[1, 4, 5, 6, 7, 8, 9, 10, 11, 13, 14, 15, 16, 17, 21, 23, 24, 25, 26, 27, 28, 37, 38];
	this.imgMonstres = [];
	for (var i in numTypeMonstres) {
		var num = numTypeMonstres[i];
		var o = {};
		(o.a=new Image()).src = baseTilesUrl + 'type_monstre/'+num+'a.png'; // un seul
		(o.b=new Image()).src = baseTilesUrl + 'type_monstre/'+num+'b.png'; // plusieurs
		this.imgMonstres[num]=o;
	}
	(this.imgMultiMonstres=new Image()).src = baseTilesUrl + 'vue/monstres.png';
	(this.imgMonstreInconnu=new Image()).src = baseTilesUrl + 'vue/monstre.png';
	
	(this.imgCadavre=new Image()).src = baseTilesUrl + 'vue/cadavre.png';
	
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
		img.outline = outlinedImg;
	}
	return img.outline;
}

// dessine une case d'environnement
Map.prototype.drawFond = function(screenRect, fond) {
	var envTile = this.envTiles[fond];
	if (envTile) {
		screenRect.drawImage(this.context, envTile);
	} else {
		screenRect.fill(this.context, "red");
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
