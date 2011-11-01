Map.prototype.drawBubble =  function() {
	var c = this.context;
	var h = 14;
	var maxWidth = 0;
	c.font = h+"px Verdana";
	for (var i=0; i<this.bubbleText.length; i++) {
		var textWidth = c.measureText(this.bubbleText[i]).width;
		if (textWidth>maxWidth) maxWidth=textWidth;
	}
	var margin = 5;
	var dtp = 20;
	var bubbleRect = new Rect;
	var lh = 1.5*h;
	bubbleRect.h = this.bubbleText.length*h*1.5 + 2*margin;
	bubbleRect.w = maxWidth + 2*margin;
	bubbleRect.x = this.pointerScreenX>this.screenRect.w/2
		? this.pointerScreenX-bubbleRect.w-dtp
		: this.pointerScreenX+dtp;
	bubbleRect.y = this.pointerScreenY>this.screenRect.h/2
		? this.pointerScreenY-bubbleRect.h-dtp
		: this.pointerScreenY+dtp;
	bubbleRect.makePath(c, margin);
	c.fillStyle = "rgba(240, 250, 240, 0.9)";
	c.fill();
	var x = bubbleRect.x + margin;
	var y = bubbleRect.y + margin + h;
	c.fillStyle = "black";
	for (var i=0; i<this.bubbleText.length; i++) {
		c.fillText(this.bubbleText[i], x, y);
		y += lh;
	}	
}
