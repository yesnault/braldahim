
// quelques constantes
var B_TOP = 1;
var B_RIGHT = 1<<1;
var B_BOTTOM = 1<<2;
var B_LEFT = 1<<3;
var B_PORTAIL = 1<<4;
var B_INDESTRUCTIBLE = 1<<5;


// initialise le système de palissades
Map.prototype.initPalissades = function() {
	(this.imgTroncPalissade = new Image()).src = $('#urlStatique').val() + '/images/vue/tronc-palissade.png';
	(this.imgTroncPalissadeIndestructible = new Image()).src = $('#urlStatique').val() + '/images/vue/tronc-palissade-indestructible.png';
	(this.imgCadenasPalissade = new Image()).src = $('#urlStatique').val() + '/images/vue/cadenas.png';
	this.imagesPalissades = [];
}

// construit une image, dont la taille peut varier, destinée à être centrée
// L'image construite est adaptée à la résolution 64 et doit donc être scalée
//  pour tout autre résolution.
Map.prototype.getImagePalissade = function(key) {
	var img = this.imagesPalissades[key];
	if (img) return img;
	var imgTronc = (key&B_INDESTRUCTIBLE) ? this.imgTroncPalissadeIndestructible : this.imgTroncPalissade;
	if (!imgTronc.width) return null;
	var marge = 41; // marge pour le dépassement en dehors de la case
	img = document.createElement('canvas');
	img.width = 64+2*marge;
	img.height = 64+2*marge;
	
	var c = img.getContext('2d');
	var cx = img.width/2;
	var cy = img.width/2;
	var r = 0.75;
	var li = imgTronc.width*r; // largeur image
	var lt = li; // largeur tronc
	var ht = imgTronc.height*r; // hauteur image
	switch(key&~(B_PORTAIL|B_INDESTRUCTIBLE)) {
		
		case B_LEFT|B_BOTTOM:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = Math.PI*1.5;
		for (var i=0; i<=nbt; i++) {
			angle += Math.PI*0.5/nbt;
			var bx = marge+(Math.cos(angle))*32;
			var by = marge+(1+Math.sin(angle)*0.5)*64;
			c.drawImage(imgTronc, bx-li*0.5, by+lt/2-ht, li, ht);
		}
		break;
						
		case B_TOP|B_RIGHT:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = Math.PI;
		for (var i=0; i<=nbt; i++) {
			var bx = marge+(1+Math.cos(angle)*0.5)*64;
			var by = marge+Math.sin(angle)*32;
			c.drawImage(imgTronc, bx-li/2, by-ht+lt/2, li, ht);
			angle -= Math.PI*0.5/nbt;
		}
		break;

		case B_TOP|B_LEFT:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = 0;
		for (var i=0; i<=nbt; i++) {
			var bx = marge+Math.cos(angle)*32;
			var by = marge+Math.sin(angle)*32;
			c.drawImage(imgTronc, bx-li/2, by-ht+lt/2, li, ht);
			angle += Math.PI*0.5/nbt;
		}
		break;

		case B_RIGHT|B_BOTTOM:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = Math.PI*1.5;
		for (var i=0; i<=nbt; i++) {
			var bx = marge+(1+Math.cos(angle)*0.5)*64;
			var by = marge+(1+Math.sin(angle)*0.5)*64;
			c.drawImage(imgTronc, bx-li/2, by-ht+lt/2, li, ht);
			angle -= Math.PI*0.5/nbt;
		}
		break;

		case 0: // case de palissade isolée
		c.drawImage(imgTronc, cx-li/2, cy+lt/2-ht, li, ht);
		break;

		default:
		// on va dessiner des demi-segments vers le centre
		if (key&B_TOP) {
			var nbt = Math.ceil(64/lt);
			var bx = cx-li*0.5;
			var by = marge;
			var lta = 64/nbt;
			for (var i=0; i<=nbt/2; i++) {
				c.drawImage(imgTronc, bx, by+lt/2-ht, li, ht);
				by += lta;
			}
		}
		if (key&B_LEFT) {
			var nbt = Math.ceil(32/lt);
			var by = cy-ht+li*0.5;
			var bx = marge;
			var lta = 32/nbt;
			for (var i=0; i<=nbt; i++) {
				c.drawImage(imgTronc, bx-li*0.5, by, li, ht);
				bx += lta;
			}
		}
		if (key&B_RIGHT) {
			var nbt = Math.ceil(32/lt);
			var by = cy-ht+li*0.5;
			var bx = cx;
			var lta = 32/nbt;
			for (var i=0; i<=nbt; i++) {
				c.drawImage(imgTronc, bx-li*0.5, by, li, ht);
				bx += lta;
			}
		}
		if (key&B_BOTTOM) {
			var nbt = Math.ceil(64/lt);
			var bx = cx-li*0.5;
			var by = cy;
			var lta = 64/nbt;
			for (var i=0; i<=nbt/2; i++) {
				c.drawImage(imgTronc, bx, by+lt/2-ht, li, ht);
				by += lta;
			}
		}
	}
	
	this.imagesPalissades[key] = img;
	return img;
}

Map.prototype.drawPalissade = function(screenRect, palissade) {
	var c = this.context;
	var cx = screenRect.x+0.5*screenRect.w;
	var cy = screenRect.y+0.5*screenRect.h;
	var key = palissade.sides;
	if (palissade.Portail) key |= B_PORTAIL;
	if (!palissade.Destructible) key |= B_INDESTRUCTIBLE;
	var img = this.getImagePalissade(key);
	if (img) {
		if (this.zoom==64) {
			drawCenteredImage(c, img, cx, cy);
		} else {
			drawCenteredImage(c, img, cx, cy, img.width*this.zoom/64);
		}
		if (palissade.Portail) {
			if (this.zoom==64) {
				drawCenteredImage(c, this.imgCadenasPalissade, cx, cy);
			} else {
				drawCenteredImage(c, this.imgCadenasPalissade, cx, cy, this.imgCadenasPalissade.width*this.zoom/64);
			}
		}
	} else {
		console.log("pas d'image de palissade");
	}
}
