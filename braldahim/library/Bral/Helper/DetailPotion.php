<?php
class Bral_Helper_DetailPotion {
    public static function afficher($p) {
        
    	$titre = htmlentities($p["nom"])." de qualit&eacute; ".htmlentities($p["qualite"])." - D&eacute;tails";
    
     	$text = "Num&eacute;ro de la potion :".$p["id_potion"]."<br>";
    	$text .= "Niveau : ".$p["niveau"]."<br>";
     	$text .= "Caract&eacute;ristique : <br> Cette potion apporte un ".$p["bm_type"];
     	$text .= " sur la caract&eacute;ristique ".$p["caracteristique"];
    	$text .= "<br>";
    	
    	return "<span ".Bral_Helper_Tip::jsTip($text, $titre).">".htmlentities($p["nom"]).", n&deg;".$p["id_potion"]."</span>";
    }
}
