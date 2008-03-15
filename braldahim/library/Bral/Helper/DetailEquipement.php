<?php
class Bral_Helper_DetailEquipement {
    public static function afficher($e) {
        
    	$titre = htmlentities($e["nom"])." ".htmlentities($e["suffixe"])." n&deg;".$e["id_equipement"]. " - D&eacute;tails";
    
    	$text = htmlentities($e["nom"])." ".htmlentities($e["suffixe"])." n&deg;".$e["id_equipement"]."<br><br>";
    	$text .= "Qualit&eacute; : ".htmlentities($e["qualite"])."<br>";
     	$text .= "Niveau : ".$e["niveau"]."<br>";
     	$text .= "Caract&eacute;ristiques :<br>";
    	$text .= self::display("Armure", $e["armure"]);
    	$text .= self::display("Force", $e["force"] );
    	$text .= self::display("Agilit&eacute;", $e["agilite"]);
    	$text .= self::display("Vigueur", $e["vigueur"]);
    	$text .= self::display("Sagesse", $e["sagesse"]);
    	$text .= self::display("Vue", $e["vue"]);
    	$text .= self::display("BM Attaque", $e["bm_attaque"]);
    	$text .= self::display("BM D&eacute;gat", $e["bm_degat"]);
    	$text .= self::display("BM Defense", $e["bm_defense"]);
     	
     	$text .= "<br>Nombre d\'emplacement runique : ".$e["nb_runes"]."<br>";
     	if (count($e["runes"]) > 1) $s='s'; else $s="";
     	 
     	 
     	$text .= count($e["runes"]) ." rune$s sertie$s "."<br>";
     	if (count($e["runes"]) > 0) {
	    	 foreach($e["runes"] as $r) {
	     	 	$text .= "<img src=\'/public/images/runes/".$r["image_type_rune"]."\'  class=\'rune\' title=\'".$r["nom_type_rune"]."\' n&deg;".$r["id_rune_equipement_rune"]." alt=\'".$r["nom_type_rune"]."\' n&deg;".$r["id_rune_equipement_rune"]."  />";
	     	 }
	     	 if ($e["suffixe"] != null && $e["suffixe"] != "") {
	     	 	$text .= "<br>Mot runique associ&eacute; &agrave; ces runes : ".htmlentities($e["suffixe"]);
	     	 } else {
	     	 	$text .= "<br>Aucun mot runique associ&eacute; &agrave; ces runes";
	     	 }
    	}
    	$text .= "<br>";
    	
        return "<span onmouseover=\"return overlib('".$text."',CAPTION,'".$titre."');\"".
        " onclick=\"return overlib('".$text."', STICKY, CAPTION, '".$titre."', CLOSECLICK, EXCLUSIVE);\"".
        " onmouseout=\"return nd();\">".htmlentities($e["nom"]).", n&deg;".$e["id_equipement"]."</span>";
    }
    
    private static function display($display, $valeur) {
    	if ($valeur != null && $valeur != 0) {
    		$plus = "";
    		if ($valeur > 0) {
    			$plus = "+";
    		}
    		return $display ." : $plus".$valeur . "<br>";
    	} else {
    		return null;
    	}
    }
}
?>