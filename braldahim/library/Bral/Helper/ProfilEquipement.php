<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_ProfilEquipement {
	
 	public static function afficher($typesEmplacement, $avecDetail) {
 		Zend_Loader::loadClass("Bral_Helper_DetailEquipement");
    	$retour = "";
    	
    	if ($typesEmplacement == null) {
    		return "erreur";
    	}
    	
		$retour .= "<table align='center' border='0' sytle='margin-left: auto; margin-right: auto;'>";
		$retour .= "<tr>";
		$retour .= "<td>";
		$retour .= "<table align='center' border='1'>";
		
		foreach($typesEmplacement as $k => $e) {
			if ($e["position"] == "gauche" && $e["affiche"] == "oui") {
				$retour .= "<tr>";
				$retour .= "<td class='equipement'>";
				$retour .= $e["nom_type_emplacement"]."<br />";
				if (count($e["equipementPorte"]) > 0) {
					foreach($e["equipementPorte"] as $p) {
						$retour .= Bral_Helper_DetailEquipement::afficher($p, $avecDetail);
					}
				} else {
					$retour .= "Libre";
				}
				$retour .= "</td>";
				$retour .= "</tr>";
			}
		}
		$retour .= "</table>";
    	$retour .= "</td>";
    	$retour .= "<td>";
    	$retour .= "<table align='center' border='1'>";
    	$retour .= "<tr>";
		$i = 0;
 		foreach($typesEmplacement as $k => $e) {
 			if ($e["position"] == "droite" && $e["affiche"] == "oui") {
 				if ($k != "mains") { 
	 				$i++;
 				}
	 			if ($k == "deuxmains") {
	 				$i++;
	 			}
    			$retour .= "<td class='equipement'>";
 				$retour .= $e["nom_type_emplacement"]."<br />";
 				if (count($e["equipementPorte"]) > 0) {
					foreach($e["equipementPorte"] as $p) {
 						$retour .= Bral_Helper_DetailEquipement::afficher($p, $avecDetail);
 					}
				}
				$retour .= "</td>";
			 }
 		}
	 	if ($i == 1) {
    		$retour .= "<td class='equipement'>1 main de libre</td>";
	 	} elseif ($i == 0) {
    		$retour .= "<td class='equipement'>Mains libres</td>";
	 	}
    	$retour .= "</tr>";
    	$retour .= "</table>";
    	$retour .= "</td>";
    	$retour .= "</tr>";
    	$retour .= "</table>";
    	return $retour;
    }
}
