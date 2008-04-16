<?php
class Bral_Helper_DetailPotion {

 	public static function afficherPrix($e) {
    	$retour = "<span>";
		
    	if ($e["prix_1_vente_echoppe_potion"] > 0) {
	    	$retour .= $e["prix_1_vente_echoppe_potion"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_potion"]);
    	}
    	
    	if ($e["prix_2_vente_echoppe_potion"] > 0) {
	    	$retour .= $e["prix_2_vente_echoppe_potion"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_potion"]);
    	}	
	    
    	if ($e["prix_3_vente_echoppe_potion"] > 0) {
	    	$retour .= $e["prix_3_vente_echoppe_potion"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_potion"]);
    	}
    	
    	$retour .= "</span>";
    	
    	return $retour;
    }
    
    public static function afficher($p) {
    	return "<span ".self::afficherJs($p).">".htmlentities($p["nom"]).", n&deg;".$p["id_potion"]."</span>";
    }
    
    public static function afficherJs($p) {
    	$titre = htmlentities($p["nom"])." de qualit&eacute; ".htmlentities($p["qualite"])." - D&eacute;tails";
    	
   		$text = "Num&eacute;ro de la potion :".$p["id_potion"]."<br />";
    	$text .= "Niveau : ".$p["niveau"]."<br />";
     	$text .= "Caract&eacute;ristique : <br /> Cette potion apporte un ".$p["bm_type"];
     	$text .= " sur la caract&eacute;ristique ".$p["caracteristique"];
    	$text .= "<br />";
    	
    	return Bral_Helper_Tip::jsTip($text, $titre);
    }
}
