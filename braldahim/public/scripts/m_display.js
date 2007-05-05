
function _display_(box,data) {
        //if (box == "box_authentification")
        //        _display_box_authentification(data);
        //else
                _display_box(box, data);
}

//function _display_box_authentification(data) {
//        b = document.getElementById('racine').innerHTML = "";
//        b = document.getElementById('racine').innerHTML = data;
//}


function _display_box(box, data) {
  document.getElementById(box).innerHTML = data;
}

// Switch pour les onglets sur les box
function my_switch(box,conteneur) {
 val = document.getElementById('switch_'+conteneur).value.split(',');
 for (i=0; i<val.length; i++) {
   document.getElementById("box_"+val[i]).style.display="none";
   document.getElementById("onglet_box_"+val[i]).className="onglet inactif";
 }
 document.getElementById(box).style.display="block";
 document.getElementById("onglet_"+box).className="onglet actif";

}

function swith2div(div1, div2, message) {
	if (document.getElementById(div1).style.display=="none") {
		document.getElementById(div1).style.display="block";
		document.getElementById(div2).style.display="none";
	} else {
		document.getElementById(div1).style.display="none";
		document.getElementById(div2).style.display="block";
	}
}
