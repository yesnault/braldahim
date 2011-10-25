
// quelques constantes, que je déplacerai peut-être
var B_TOP = 1;
var B_RIGHT = 1<<1;
var B_BOTTOM = 1<<2;
var B_LEFT = 1<<3;
var B_PORTAIL = 1<<4;


// initialise le système de palissades
Map.prototype.initPalissades = function() {
    var baseTilesUrl = "http://static.braldahim.com/images/";
    (this.imgTroncPalissade = new Image()).src = baseTilesUrl + 'vue/tronc-palissade.png';
    (this.imgCadenasPalissade = new Image()).src = baseTilesUrl + 'vue/cadenas.png';

	this.imagesPalissades = [];
}

// construit une image, dont la taille peut varier, destinée à être centrée
// L'image construite est adaptée à la résolution 64 et doit donc être scalée
//  pour tout autre résolution.
Map.prototype.getImagePalissade = function(key) {
	var img = this.imagesPalissades[key];
	if (img) return img;
	if (!this.imgTroncPalissade.width) return null;
	var marge = 38; // marge pour le dépassement en dehors de la case
	img = document.createElement('canvas');
	img.width = 64+2*marge;
	img.height = 64+2*marge;
	var c = img.getContext('2d');
	var cx = img.width/2;
	var cy = img.width/2;
	var r = 0.75;
	var lt = this.imgTroncPalissade.width*r; // largeur tronc
	var ht = this.imgTroncPalissade.height*r;
	switch(key) {
		
		case B_LEFT|B_BOTTOM:
		case B_LEFT|B_BOTTOM|B_PORTAIL:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = Math.PI*1.5;
		for (var i=0; i<=nbt; i++) {
			angle += Math.PI*0.5/nbt;
			var bx = marge+(Math.cos(angle))*32;
			var by = marge+(1+Math.sin(angle)*0.5)*64;
			c.drawImage(this.imgTroncPalissade, bx-lt*0.5, by+lt/2-ht, lt, ht);
		}
		break;
						
		case B_TOP|B_RIGHT:
		case B_TOP|B_RIGHT|B_PORTAIL:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = Math.PI;
		for (var i=0; i<=nbt; i++) {
			var bx = marge+(1+Math.cos(angle)*0.5)*64;
			var by = marge+Math.sin(angle)*32;
			c.drawImage(this.imgTroncPalissade, bx-lt/2, by-ht+lt/2, lt, ht);
			angle -= Math.PI*0.5/nbt;
		}
		break;

		case B_TOP|B_LEFT:
		case B_TOP|B_LEFT|B_PORTAIL:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = 0;
		for (var i=0; i<=nbt; i++) {
			var bx = marge+Math.cos(angle)*32;
			var by = marge+Math.sin(angle)*32;
			c.drawImage(this.imgTroncPalissade, bx-lt/2, by-ht+lt/2, lt, ht);
			angle += Math.PI*0.5/nbt;
		}
		break;

		case B_RIGHT|B_BOTTOM:
		case B_RIGHT|B_BOTTOM|B_PORTAIL:
		var nbt = Math.ceil((64*Math.PI)/(4*lt));
		var angle = Math.PI*1.5;
		for (var i=0; i<=nbt; i++) {
			var bx = marge+(1+Math.cos(angle)*0.5)*64;
			var by = marge+(1+Math.sin(angle)*0.5)*64;
			c.drawImage(this.imgTroncPalissade, bx-lt/2, by-ht+lt/2, lt, ht);
			angle -= Math.PI*0.5/nbt;
		}
		break;

		case 0: // case de palissade isolée
		c.drawImage(this.imgTroncPalissade, cx-lt/2, cy+lt/2-ht, lt, ht);

		default:
		// on va dessiner des demi-segments vers le centre
		if (key&B_TOP) {
			var nbt = Math.ceil(64/lt);
			var bx = cx-lt*0.5;
			var by = marge;
			var lta = 64/nbt;
			for (var i=0; i<=nbt/2; i++) {
				c.drawImage(this.imgTroncPalissade, bx, by+lt/2-ht, lt, ht);
				by += lta;
			}
		}
		if (key&B_LEFT) {
			var nbt = Math.ceil(64/lt);
			var by = cy-ht+lt/2;
			var bx = marge;
			var lta = 64/nbt;
			for (var i=0; i<=nbt/2; i++) {
				c.drawImage(this.imgTroncPalissade, bx-lt*0.5, by, lt, ht);
				bx += lta;
			}
		}
		if (key&B_RIGHT) {
			var nbt = Math.ceil(64/lt);
			var by = cy-ht+lt/2;
			var bx = cx;
			var lta = 64/nbt;
			for (var i=0; i<=nbt/2; i++) {
				c.drawImage(this.imgTroncPalissade, bx-lt*0.5, by, lt, ht);
				bx += lta;
			}
		}
		if (key&B_BOTTOM) {
			var nbt = Math.ceil(64/lt);
			var bx = cx-lt*0.5;
			var by = cy;
			var lta = 64/nbt;
			for (var i=0; i<=nbt/2; i++) {
				c.drawImage(this.imgTroncPalissade, bx, by+lt/2-ht, lt, ht);
				by += lta;
			}
		}

	}
	
	this.imagesPalissades[key] = img;
	return img;
}

Map.prototype.drawPalissade = function(screenRect, palissade, hover) {
	var c = this.context;
	var cx = screenRect.x+0.5*screenRect.w;
	var cy = screenRect.y+0.5*screenRect.h;
	var key = palissade.sides;
	if (palissade.Portail) key |= B_PORTAIL;
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
