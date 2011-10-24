/*
 * l'objet SpriteSet donne accès aux petites images regroupées dans une plus grosse
 *  suivant la technique dite des "sprites css". On utilise donc les classes css pour
 *  récupérer les coordonnées des sprites dans la grosse image.
 * Le nom du fichier css doit être identique (suffixe excepté) à celui de l'image (gif ou png).
 * Le callback onReady est appelé lorsque l'image globale est chargée.
 */ 

function SpriteSet(src, onReady) {
	this.ready = false;
	this.globalImage = new Image();
	var _this = this;
	this.globalImage.onload = function(){_this.ready=true;onReady()};
	this.globalImage.src = src;
	var path = src.split('/');
	var filename = path[path.length-1];
	this.name = filename.substring(0, filename.lastIndexOf('.'));
	this.sprites = {};
	for (var i=0; i<document.styleSheets.length; i++) {
		var styleSheet = document.styleSheets[i];

		if (StringContains(styleSheet.href, this.name)) { // test théoriquement pas 100% fiable mais ça ira bien
			this.cssRules = styleSheet.rules; // chrome, IE
			if (!this.cssRules) this.cssRules = styleSheet.cssRules; // firefox
			break;
		}
	}
	if (!this.cssRules) {
		console.log('ERREUR : feuille de style '+this.name+'.css introuvable');
		return;
	}
}

// renvoie l'image correspondant à la clef passée.
// Attention : la clef doit être en minuscule.
// Si aucun sprite n'est trouvé, et si alternateKey est définie, alors on renvoie
//  le sprite correspondant à alternateKey.
SpriteSet.prototype.get = function(key, alternateKey) {
	var img = this.sprites[key];
	if (img) return img;
	if (key!=key.toLowerCase('Clef non en minuscule : ' + key));
	if (!this.cssRules) return;
	if (!this.globalImage.width) {
		console.log('image globale non initialisée');
		return;
	}
	var found = false;
	for (var ir=this.cssRules.length; ir-->0;) {
		var rule = this.cssRules[ir];
		var index = rule.selectorText.indexOf('.'+key);
		if (index==-1) continue;
		if (
			index>=0 && (		
				(index+key.length+1==rule.selectorText.length)
				|| (rule.selectorText.charAt(index+key.length+1)==',')
			)
		) {
			found = true;
			var atoi = function(s){
				if (StringEndsWith(s, 'px')) s=s.substring(0, s.length-2);
				return parseInt(s);
			}
			var width = atoi(rule.style.getPropertyValue('width'));
			var height = atoi(rule.style.getPropertyValue('height'));
			var posStr = rule.style.getPropertyValue('background-position').split(' ');
			var x = -atoi(posStr[0]);
			var y = -atoi(posStr[1]);
			img = document.createElement('canvas');
			img.width = width;
			img.height = height;
			var c = img.getContext('2d');
			c.drawImage(this.globalImage, x, y, width, height, 0, 0, width, height);
			var selectors = rule.selectorText.split(',');
			var keys = [];
			for (var is=selectors.length; is-->0;) {
				var selector = selectors[is].trim();
				if (selector.charAt(0)=='.') {
					this.sprites[selector.substring(1)] = img;
				}
			}
			img.cssSelector = key;
			break;
		}
	}
	if (!found) {
		console.log('sprite "'+key+'" not found in '+this.name);
		if (alternateKey) {
			img = this.get(alternateKey);
			if (img) {
				console.log(' -> using "' + alternateKey + '" instead');
				this.sprites[key] = img;
			}
		}
	}
	return img;
}

// renvoie une clef css correspondant à la clef.
// Ceci peut être le alternateKey fourni lors d'un appel précédent à get.
SpriteSet.prototype.css = function(key) {
	var img = this.sprites[key];
	if (img) return img.cssSelector;
}
