<?php
class Bral_Helper_DetailPotion {

 	public static function afficherPrix($e) {
    	$retour = "<span>";
    	$firstOu = true;
		$ou =  "  <br /> ou ";
		
    	if ($e["prix_1_vente_echoppe_potion"] > 0) {
	    	$retour .= $e["prix_1_vente_echoppe_potion"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_potion"]);
	    	$firstOu = false; 
    	}
    	
    	if ($e["prix_2_vente_echoppe_potion"] > 0) {
    		if (!$firstOu) { 
    			$retour .= $ou;
    		}
	    	$retour .= $e["prix_2_vente_echoppe_potion"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_potion"]);
	    	$firstOu = false; 
    	}	
	    
    	if ($e["prix_3_vente_echoppe_potion"] > 0) {
    		if (!$firstOu) { 
    			$retour .= $ou;
    		}
	    	$retour .= $e["prix_3_vente_echoppe_potion"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_potion"]);
	    	$firstOu = false; 
    	}
    	
    	if (count($e["prix_minerais"]) > 0) {
 	    	foreach($e["prix_minerais"] as $m) {
 	    		if (!$firstOu) { 
    				$retour .= $ou;
    			}
		    	$retour .= $m["prix_echoppe_potion_minerai"]. " ";
	    		$retour .= htmlspecialchars($m["nom_type_minerai"]);
	    		$firstOu = false; 
 	    	}
    	}
    	
    	if (count($e["prix_parties_plantes"]) > 0) {
    	 	foreach($e["prix_parties_plantes"] as $p) {
    	 	  	if (!$firstOu) { 
    				$retour .= $ou;
    			}
		    	$retour .= $p["prix_echoppe_potion_partieplante"]. " ";
		    	$s = "";
		    	if ($p["prix_echoppe_potion_partieplante"] > 1) {
		    		$s = "s";
		    	}
	    		$retour .= htmlspecialchars($p["nom_type_partieplante"]). "$s ";
	    		$retour .= htmlspecialchars($p["prefix_type_plante"]);
	    		$retour .= htmlspecialchars($p["nom_type_plante"]);
	    		$firstOu = false; 
 	    	}
    	}
    	
    	
    	$retour .= "</span>";
    	
    	return $retour;
    }
    
    public static function afficher($p) {
    	return "<span ".self::afficherJs($p).">".htmlspecialchars($p["nom"]).", n&deg;".$p["id_potion"]."</span>";
    }
    
    public static function afficherJs($p) {
    	$titre = htmlspecialchars($p["nom"])." de qualit&eacute; ".htmlspecialchars($p["qualite"])." - D&eacute;tails";
    	
   		$text = "Num&eacute;ro de la potion :".$p["id_potion"]."<br />";
    	$text .= "Niveau : ".$p["niveau"]."<br />";
     	$text .= "Caract&eacute;ristique : <br /> Cette potion apporte un ".$p["bm_type"];
     	$text .= " sur la caract&eacute;ristique ".$p["caracteristique"];
    	$text .= "<br />";
    	
    	return Bral_Helper_Tooltip::jsTip($text, $titre);
    }
}
