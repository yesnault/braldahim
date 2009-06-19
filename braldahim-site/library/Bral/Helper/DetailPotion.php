<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: DetailPotion.php 839 2008-12-26 21:35:54Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-12-26 22:35:54 +0100 (Fri, 26 Dec 2008) $
 * $LastChangedRevision: 839 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Helper_DetailPotion {

 	public static function afficherPrix($e) {
 		Zend_Loader::loadClass("Bral_Helper_DetailPrix");
 		return Bral_Helper_DetailPrix::afficherPrix($e, "_echoppe_potion");
    }
    
    public static function afficher($p) {
    	return "<span ".self::afficherJs($p).">".htmlspecialchars($p["nom"]).", n&deg;".$p["id_potion"]."</span>";
    }
    
    public static function afficherJs($p) {
    	$titre = htmlspecialchars($p["nom"])." de qualit&eacute; ".htmlspecialchars($p["qualite"])." - D&eacute;tails";
    	
   		$text = "Num&eacute;ro de la potion : ".$p["id_potion"]."<br />";
    	$text .= "Niveau : ".$p["niveau"]."<br />";
    	$text .= "Poids : ".Bral_Util_Poids::POIDS_POTION." Kg<br />";
     	$text .= "Caract&eacute;ristique : <br /> Cette potion apporte un ".$p["bm_type"];
     	$text .= " sur la caract&eacute;ristique ".$p["caracteristique"];
    	$text .= "<br />";
    	
    	Zend_Loader::loadClass("Bral_Helper_Tooltip");
    	return Bral_Helper_Tooltip::jsTip($text, $titre);
    }
}
