/*
contient des fonctions graphiques utilitaires

*/

/**
 * Trace une ligne horizontale épaisse d'un pixel écran (contourne un problème des canvas).
 * 
 * @param c : le contexte 2D du canvas
 * @param x1, x2, y : coordonnées écran
 */
function drawThinHorizontalLine(c, x1, x2, y) {
	c.lineWidth = 1;
	var adaptedY = Math.floor(y)+0.5;
	c.beginPath();
	//if (x1<0||x1>c.canvas.clientWidth||y<0||y>c.canvas.clientHeight) return;
	// je ne sais pas pourquoi, la ligne suivante plante sur FF 3.6 et 4.0...
	c.moveTo(x1, adaptedY);
	c.lineTo(x2, adaptedY);
	c.stroke();
}

/**
 * Trace une ligne verticale épaisse d'un pixel écran (contourne un problème des canvas).
 * 
 * @param c : le contexte 2D du canvas
 * @param x1, x2, y : coordonnées écran
 */
function drawThinVerticalLine(c, x, y1, y2) {
	c.lineWidth = 1;
	var adaptedX = Math.floor(x)+0.5;
	c.beginPath();
	c.moveTo(adaptedX, y1);
	c.lineTo(adaptedX, y2);
	c.stroke();
}


/**
 * renvoie un point, situé à une distance "radius" du centre "center" et 
 *  obtenu par rotation d'angle "angle" du point à droite sur l'horizontale.
 * Attention : on utilise la norme Américaine : les angles sont comptés dans le
 *  sens horaire.
 */ 
function addArcToCenter(center, angle, radius) {
    return new Point(
		center.x + Math.cos(angle)*radius,
		center.y + Math.sin(angle)*radius
    );
}


/**
 * calcule l'angle, en radians, sur le cercle trigonométrique, entre l'horizontale
 * et un point de la périphérie.
 * Le cercle est défini par son centre c, et par le point a sur sa périphérie.
 * Le résultat renvoyé est dans [0, 2Pi], et l'angle est orienté à l'Américaine
 *  (sens horaire).
 */
function computeAngle(c, a) {
	var rx = a.x-c.x;
	var ry = a.y-c.y;
	var r = Math.sqrt(rx*rx+ry*ry);
	var sinus = ry/r;
	var angle;
	if (rx>0) {
		if (ry>0) angle = Math.asin(sinus);
		else angle = Math.PI*2+Math.asin(sinus);
	} else {
		angle = Math.PI-Math.asin(sinus);
	}
	return angle;
}
