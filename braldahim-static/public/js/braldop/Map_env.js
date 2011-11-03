
Map.prototype.initEnv = function() {
	//> petite base de données des environnements
	this.environnements = {};
	this.environnements['plaine'] = {PA:1, distance:2, vue:6};
	this.environnements['montagne'] = {PA:2, distance:1, vue:5};
	this.environnements['marais'] = {PA:2, distance:1, vue:5};
	this.environnements['gazon'] = {PA:1, distance:1, vue:6};
	this.environnements['chenes'] = {PA:1, distance:1, vue:4};
	this.environnements['erables'] = {PA:1, distance:1, vue:4};
	this.environnements['hetres'] = {PA:1, distance:1, vue:4};
	this.environnements['peupliers'] = {PA:1, distance:1, vue:4};
	this.environnements['lac'] = {PA:0, distance:0, vue:6};
	this.environnements['peuprofonde'] = {PA:3, distance:1, vue:6};
	this.environnements['profonde'] = {PA:0, distance:0, vue:6};
	this.environnements['mer'] = {PA:0, distance:0, vue:6};
	this.environnements['caverne'] = {PA:1, distance:1, vue:2};
	this.environnements['caverne-crevasse'] = {PA:1, distance:1, vue:2};
	this.environnements['tunnel'] = {PA:1, distance:1, vue:2};
	this.environnements['pave'] = {PA:1, distance:3, vue:6};
	this.environnements['route'] = {PA:1, distance:3, vue:6};
	this.environnements['mine'] = {PA:1, distance:1, vue:2};
	
	for (var key in this.environnements) this.environnements[key].nom=key;
	this.environnements['chenes'].nom='chênes';
	this.environnements['erables'].nom='érables';
	this.environnements['hetres'].nom='hêtres';
	this.environnements['pave'].nom='pavés';
	this.environnements['caverne-crevasse'].nom='crevasse';
	
	var balisés = {};
	for (var key in this.environnements) {
		var e = this.environnements[key];
		var kb = key+'-gr';
		if (key=='caverne-crevasse') kb='caverne-gr-crevasse';
		balisés[kb] = {PA:1, distance:3, vue:e.vue, nom:e.nom+' avec balise'};
	}
	for (var key in balisés) this.environnements[key]=balisés[key];
	
	for (var key in this.environnements) {
		var e = this.environnements[key];
		e.description = 'déplacement : ';
		if (e.distance==0) {
			e.description = 'infranchissable';
		} else {
			e.description = 'déplacement : '+e.distance+' case';
			if (e.distance>1) e.description += 's';
			e.description += ' pour '+e.PA+' PA';
		}
		e.description += ', vue : '+e.vue;
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
