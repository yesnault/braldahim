Map.prototype.openDialog = function(startingRectInCanvas, title, content) {
	this.dialopIsOpen = true;
	var $canvas = $(this.canvas);
	var $document = $(document);
	var docWidth = $(document).width();
	var docHeight = $(document).height();
	var width = $canvas.width();
	if (width>400) width=400;
	var wx = $canvas.offset().left+this.pointerScreenX;
	var wy = $canvas.offset().top+this.pointerScreenY;
	if (wx<docWidth/2) {
		this.$dialog.css('left', (wx+40)+'px');
		this.$dialog.css('right', (docWidth-wx-width)+'px');
	} else {
		this.$dialog.css('right', (docWidth-wx+40)+'px');
		this.$dialog.css('left', (wx-width)+'px');
	}
	if (wy<docHeight/2) {
		this.$dialog.css('top', (wy-20)+'px');
		this.$dialog.css('bottom', '');
	} else {
		this.$dialog.css('top', '');
		this.$dialog.css('bottom', (docHeight-wy+20)+'px');
	}
	
	var html = [];
	var h=0;
	html[h++] = '<span class=dialog_title>';
	html[h++] = title;
	html[h++] = '</span><hr>';
	html[h++] = '<div id=dialog_content>';
	html[h++] = content;
	html[h++] = '<hr><small>Cliquez pour fermer ce menu</small></div>';
	this.$dialog.html(html.join(''));
	this.$dialog.show();
}

Map.prototype.openCellDialog = function(x, y) {
	console.log("openCellDialog");
	var cell = this.getCell(x, y);
	var screenRect = new Rect();
	screenRect.w = this.zoom;
	screenRect.h = this.zoom;
	screenRect.x = this.zoom*(this.originX+x);
	screenRect.y = this.zoom*(this.originY-y);
	var html = [];
	var h=0;
	var empty = false;
	if (cell.champ) {
		html[h++] = '<table><tr><td><img src="'+this.champImg.src+'"></td><td> Champ de '+cell.champ.NomCompletBraldun+'</td></tr></table>';
	} else if (cell.échoppe) {
		html[h++] = '<table><tr><td><img src="'+this.echoppeImg[cell.échoppe.Métier].src+'"></td><td> '+cell.échoppe.Nom+'<br>'+cell.échoppe.détails+'</td></tr></table>';
	} else if (cell.lieu) {
		html[h++] = '<table><tr><td><img src="'+this.placeImg[cell.lieu.IdTypeLieu].src+'"></td><td> '+cell.lieu.Nom+'</td></tr></table>';
	} else {
		empty = true;
	}
	var bralduns = this.getBralduns(x, y);
	if (bralduns.length>0) {
		empty = false;
		html[h++] = "<b>Braldûns :</b>";
		html[h++] = '<table>';
		for (var ib=0; ib<bralduns.length; ib++) {
			var b = bralduns[ib];
			var img = b.Sexe=='f' ? this.img_braldun_feminin : this.img_braldun_masculin;
			html[h++] = '<tr><td>';
			html[h++] = '<img src="'+img.src+'">';
			html[h++] = '</td><td><a target=winprofil href="http://jeu.braldahim.com/voir/braldun/?braldun='+b.Id+'&direct=profil">'+b.Prénom+' '+b.Nom+'</a></td><td>niv. '+b.Niveau;
			html[h++] = '</td></tr>';
		}		
		html[h++] = '</table>';
	}
	if (empty) html[h++] = "<i>Il n'y a rien ici</i>";
	this.openDialog(screenRect, x+","+y, html.join(''));
}
