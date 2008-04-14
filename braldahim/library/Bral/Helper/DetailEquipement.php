<?php

class Bral_Helper_DetailEquipement {

    public static function afficher($e) {
    	return "<span ".self::afficherJs($text).">".htmlentities($e["nom"]).", n&deg;".$e["id_equipement"]."</span>";
    }
    
    public static function afficherJs($e) {
    	$text = htmlentities($e["nom"])." ".htmlentities(addslashes($e["suffixe"]))." de qualit&eacute; ".htmlentities($e["qualite"])." <br /><br />";
     	$text .= "Num&eacute;ro de la pi&egrave;ce :".$e["id_equipement"]."<br />";
    	$text .= "Niveau : ".$e["niveau"]."<br />";
     	$text .= "Caract&eacute;ristiques :<br />";
    	$text .= self::display("Armure", $e["armure"]);
    	$text .= self::display("Force", $e["force"] );
    	$text .= self::display("Agilit&eacute;", $e["agilite"]);
    	$text .= self::display("Vigueur", $e["vigueur"]);
    	$text .= self::display("Sagesse", $e["sagesse"]);
    	$text .= self::display("Vue", $e["vue"]);
    	$text .= self::display("BM Attaque", $e["bm_attaque"]);
    	$text .= self::display("BM Defense", $e["bm_defense"]);
     	$text .= self::display("BM D&eacute;g&acirc;ts", $e["bm_degat"]);
    	
     	$text .= "<br />Nombre d\'emplacement runique : ".$e["nb_runes"]."<br />";
     	if (count($e["runes"]) > 1) $s='s'; else $s="";
     	 
     	 
     	$text .= count($e["runes"]) ." rune$s sertie$s "."<br />";
     	if (count($e["runes"]) > 0) {
	    	 foreach($e["runes"] as $r) {
	     	 	$text .= "<img src=\'/public/images/runes/".$r["image_type_rune"]."\'  class=\'rune\' title=\'".$r["nom_type_rune"]." :".htmlentities(addslashes($r["effet_type_rune"]))."\' n&deg;".$r["id_rune_equipement_rune"]." alt=\'".$r["nom_type_rune"]."\' n&deg;".$r["id_rune_equipement_rune"]."  />";
	     	 }
	     	 if ($e["suffixe"] != null && $e["suffixe"] != "") {
	     	 	$text .= "<br />Mot runique associ&eacute; &agrave; ces runes : ".htmlentities(addslashes($e["suffixe"]));
	     	 } else {
	     	 	$text .= "<br />Aucun mot runique n\'est associ&eacute; &agrave; ces runes";
	     	 }
    	}
    	$text .= "<br />";
    	return Bral_Helper_Tip::jsTip($text);
    }
    
    private static function display($display, $valeur) {
    	if ($valeur != null && $valeur != 0) {
    		$plus = "";
    		if ($valeur > 0) {
    			$plus = "+";
    		}
    		return $display ." : $plus".$valeur . "<br />";
    	} else {
    		return null;
    	}
    }
}
