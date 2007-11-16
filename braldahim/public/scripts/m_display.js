
function _display_(box,data) {
	_display_box(box, data);
}

function _display_box(box, data) {
	if (document.getElementById(box)) {
		document.getElementById(box).innerHTML = data;
	} //else {
		//alert('Erreur m_display : box:'+box+' inconnue');
	//}
}

// Switch pour les onglets sur les box
function my_switch(box,conteneur) {
	val = document.getElementById('switch_'+conteneur).value.split(',');
	for (i=0; i<val.length; i++) {
		document.getElementById(val[i]).style.display="none";
		document.getElementById("onglet_"+val[i]).className="onglet inactif";
	}
	document.getElementById(box).style.display="block";
	document.getElementById("onglet_"+box).className="onglet actif";
}

// Switch pour afficher un div et en cacher un autre
function switch2div(div1, div2) {
	if (document.getElementById(div1).style.display=="none") {
		document.getElementById(div1).style.display="block";
		document.getElementById(div2).style.display="none";
	} else {
		document.getElementById(div1).style.display="none";
		document.getElementById(div2).style.display="block";
	}
}

// n'autorise que des chiffres.
// exemple d'utilisation : <input type="text" onkeypress="chiffres(event)">
function chiffres(event) {
	// Compatibilité IE / Firefox
	if(!event&&window.event) {
		event=window.event;
	}
	
	// IE 
	if (event.keyCode == 37 || event.keyCode == 39 || // fleches deplacement
		event.keyCode == 46 || event.keyCode == 8) { // backspace ou delete
		return;
	} else if(event.keyCode < 48 || event.keyCode > 57) {
		event.returnValue = false;
		event.cancelBubble = true;
	}
	
	// DOM
	if (event.which == 46 || event.which == 8) { // backspace ou delete
		return;
	} else if(event.which < 48 || event.which > 57) {
		event.preventDefault();
		event.stopPropagation();
	}
}

function activer_wysiwyg(id) {
	alert('Activation');
	//tinyMCE.execCommand('mceAddControl', false, id);
}

function desactiver_wysiwyg(id) {
	tinyMCE.execCommand('mceRemoveControl', false, id);
}

function accordion(el) {
    if ($('visible') == el) {
        return;
    }
    if ($('visible')) {
        var eldown = el.parentNode.id+'-body';
        var elup = $('visible').parentNode.id+'-body';
        
        if ($(elup) && $(eldown)) {
	        new Effect.Parallel(
	        [
	            new Effect.SlideUp(elup),
	            new Effect.SlideDown(eldown)
	        ], {
	            duration: 0.1
	        });
        } else {
	        if ($(elup)) {
	        	new Effect.SlideUp(elup, {duration: 0.1});
	        }
	        if ($(eldown)) {
	        	new Effect.SlideDown(eldown, {duration: 0.1});
	        }
        }
        el.style.backgroundImage = "url(/public/images/expanded.gif);";
        
        if ($($('ancien').value+""+'-head')) {
        	$($('ancien').value+""+'-head').style.backgroundImage = "url(/public/images/collapsed.gif);";
        }
        $('visible').style.backgroundImage = "url(/public/images/collapsed.gif);";
        $('visible').id = '';
        $('ancien').value = el.parentNode.id;
        
    }
    el.id = 'visible';
}
