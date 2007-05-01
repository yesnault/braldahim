function findSelectedRadioButton( groupname )
{
        var radioButtons = document.myForm.elements[groupname];
        for( var i = 0; i < radioButtons.length; i++ )
        {
                if( radioButtons[i].checked )
                        return radioButtons[i];
        }
        return null;
}

function _get_(url){
  var valeurs = "";
  var nb_valeurs = 0;
  
  if ($('nb_valeurs') && (p[0] == "d" && p[1] == "o")) {
      // Recuperation du nombre de valeur que l'action a besoin
      nb_valeurs = $('nb_valeurs').value;
      for (i = 1; i<=nb_valeurs ; i++) {
            var nom = 'valeur_'+i;
            var elem = $(nom);
            if (elem.type == "radio") {
                    valeurs = valeurs + "&valeur_" +i+ "=" +findSelectedRadioButton(nom).value;
                    //valeurs = valeurs + "&valeur_" +i+ "=" +$F(nom).value;
            } else {
                    valeurs = valeurs + "&valeur_" +i+ "=" +elem.value;
            }
      }
  }
  
  var pars = valeurs;
  //alert("url="+url);
  var myAjax = new Ajax.Request( url, {method: 'get', parameters: pars, onComplete: showResponse} );
}

function showResponse(originalRequest) {
    var xmldoc = originalRequest.responseXML;
    if (xmldoc == null) {
    	alert('Erreur survenue:'+xmldoc);
    }
    	
    var root = xmldoc.getElementsByTagName('root').item(0);
    var display_action = false;
    var display_informations = false;

    for (var iNode = 0; iNode < root.childNodes.length; iNode++) {
      var node = root.childNodes.item(iNode);

      for (i = 0; i < node.childNodes.length; i++) {
           var sibl = node.childNodes.item(i);
           for (x = 0; x < sibl.childNodes.length; x++) {
            if (i == 1)
              m_type = node.childNodes.item(1).childNodes.item(0).data;
            if (i == 3)
              m_type_valeur = node.childNodes.item(3).childNodes.item(0).data;
            if (i == 5)
              m_data = node.childNodes.item(5).childNodes.item(0).data;
            if (i == 5) {
            
			  //alert('Fin entrie');
              if (m_type_valeur == "box_action")
                display_action = true;
              else if (m_type_valeur == "informations" && m_data !="")
                display_informations = true; // affichage de la boite d'informations

              if (m_type == "display")
                _display_(m_type_valeur, m_data);
            }
           }
      }
    }

    // Box action
    if (display_action) {
      $("box_action").style.display = "block";
    } else {
      if ($("box_action"))
           $("box_action").style.display = "none";
    }


    // Box informations
    if (display_informations) {
      $("informations").style.display = "block";
    } else {
      if ($("informations"))
       $("informations").style.display = "none";
    }

    return ;
}