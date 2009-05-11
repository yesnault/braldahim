<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Helper_DetailMateriel {
	
 	public static function afficherPrix($e) {
    	$retour = "<span>";
    	$firstOu = true;
		$ou =  "  <br /> ou ";
    	
    	if ($e["prix_1_vente_echoppe_materiel"] >= 0 && $e["unite_1_vente_echoppe_materiel"] > 0) {
	    	$retour .= $e["prix_1_vente_echoppe_materiel"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_materiel"], false, $e["prix_1_vente_echoppe_materiel"]);
	    	$firstOu = false; 
    	}
    	
    	if ($e["prix_2_vente_echoppe_materiel"] >= 0 && $e["unite_2_vente_echoppe_materiel"] > 0) {
    		if (!$firstOu) { 
    			$retour .= $ou;
    		}
    		
	    	$retour .= $e["prix_2_vente_echoppe_materiel"]. " ";
	    	$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_materiel"], false, $e["prix_2_vente_echoppe_materiel"]);
	    	$firstOu = false; 
    	}
    	
    	if ($e["prix_3_vente_echoppe_materiel"] >= 0 && $e["unite_3_vente_echoppe_materiel"] > 0) {
    	    if (!$firstOu) { 
    			$retour .= $ou;
    		}
	    	$retour .= $e["prix_3_vente_echoppe_materiel"]. " ";
    		$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_materiel"], false, $e["prix_3_vente_echoppe_materiel"]);
    		$firstOu = false; 
    	}
    	
 	    if (count($e["prix_minerais"]) > 0) {
 	    	foreach($e["prix_minerais"] as $m) {
 	    		if (!$firstOu) { 
    				$retour .= $ou;
    			}
		    	$retour .= $m["prix_echoppe_materiel_minerai"]. " ";
	    		$retour .= htmlspecialchars($m["nom_type_minerai"]);
	    		$firstOu = false; 
 	    	}
    	}
    	
    	if (count($e["prix_parties_plantes"]) > 0) {
    	 	foreach($e["prix_parties_plantes"] as $p) {
    	 	  	if (!$firstOu) { 
    				$retour .= $ou;
    			}
		    	$retour .= $p["prix_echoppe_materiel_partieplante"]. " ";
		    	$s = "";
		    	if ($p["prix_echoppe_materiel_partieplante"] > 1) {
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
    
    public static function afficher($e) {
    	//return "<span ".self::afficherJs($e).">".htmlspecialchars($e["nom"]).", n&deg;".$e["id_materiel"]."</span>";
    	$retour = "<span ".self::afficherJs($e).">";
    	$retour .= "<img src='/public/styles/braldahim_defaut/images/type_materiel/type_materiel_".$e["id_type_materiel"].".png' alt=\"".htmlspecialchars($e["nom"])."\"/>";
		$retour .= "</span>";
    	return $retour;
    }
    
    public static function afficherJs($e) {
    	$text = htmlspecialchars($e["nom"])." <br /><br />";
     	$text .= "Num&eacute;ro du mat&eacute;riel :".$e["id_materiel"]."<br />";
     	$text .= "Caract&eacute;ristiques : TODO<br />";
    #	$text .= self::display("Armure", $e["armure"]);
    	
    	$text .= "<br />";
    	return Bral_Helper_Tooltip::jsTip($text);
    }
    
    private static function display($display, $valeur, $unite = "") {
    	if ($valeur != null && $valeur != 0) {
    		$plus = "";
    		if ($valeur > 0) {
    			$plus = "+";
    		}
    		return $display ." : $plus".$valeur . $unite."<br />";
    	} else {
    		return null;
    	}
    }
    
    /**
     * Affiche les recettes des materiels
     */
    public static function afficheRecette($typeMateriel) {
    	$retour = "";
		if (isset($typeMateriel)) {
			$retour .= "<div id='caracs_materiel'>";
			$retour .= "<table align='center'>";
			$retour .= "<th>Capacite</th>";
			$retour .= "<th>Durabilite</th>";
			$retour .= "<th>Usure</th>";
			$retour .= "<th>Poids</th>";
			$retour .= "<tr>";
			$retour .= "<td>".$typeMateriel["capacite"]." </td>";
			$retour .= "<td>".$typeMateriel["durabilite"]." </td>";
			$retour .= "<td>".$typeMateriel["usure"]." </td>";
			$retour .= "<td>".$typeMateriel["poids"]." </td>";
			$retour .= "</tr>";
			$retour .= "</table>";
			$retour .= "</div>";
		}
	
	return $retour;
    }
}
