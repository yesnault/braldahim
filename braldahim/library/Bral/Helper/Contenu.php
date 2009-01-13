<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Helper_Contenu {
	
	public static function affichePlante($tab) {
		$retour = "";
		if ($tab["possible"] == false) {
	 		$retour .= "-";
		} else {
			if (array_key_exists("quantite", $tab)) {
				if (array_key_exists("poids", $tab)) {
					$retour .= "<span style='cursor:pointer' title='Poids unitaire : ".($tab["poids"]/$tab["quantite"])." Kg, Poids total : ".$tab["poids"]." Kg'>";
				}
	 			$retour .= $tab["quantite"]. " ";
	 			$p = "";
	 			if (array_key_exists("estPreparee", $tab)) {
		 			if ($tab["estPreparee"] == true) {
		 				$p = "_p";
		 			}
		 			$retour .= "<img src='/public/styles/braldahim_defaut/images/type_partieplante/type_partieplante_".$tab["id_type_partieplante"].$p.".png' alt=\"image\"/>";
	 			}
	 			if (array_key_exists("poids", $tab)) {
	 				$retour .= "</span>";
	 			}
			} else {
				$retour .= Bral_Helper_ChampBoutique::afficheChampPlante($tab);
			}
	 	}
		return $retour;
	}
	
	public static function afficheMinerai($tab) {
		$retour = "";
		if (array_key_exists("quantite", $tab)) {
			if (array_key_exists("poids", $tab)) {
				$retour .= "<span style='cursor:pointer' title='Poids unitaire : ".($tab["poids"]/$tab["quantite"])." Kg, Poids total : ".$tab["poids"]." Kg'>";
			}
 			$retour .= $tab["quantite"];
			$p = "";
			if (array_key_exists("estLingot", $tab)) {
				if ($tab["estLingot"] == true) {
					$p = "_p"; 
				}
			}
			$retour .= "<img src='/public/styles/braldahim_defaut/images/type_minerai/type_minerai_".$tab["id_type_minerai"]."$p.png' alt=\"".htmlspecialchars($tab["type"])."\"/>";
 			if (array_key_exists("poids", $tab)) {
 				$retour .= "</span>";
 			}
		} else {
			$retour .= Bral_Helper_ChampBoutique::afficheChampMinerai($tab);
		}
		return $retour;
	}
	
	public static function afficheMunition($tab) {
		$retour = "";
		if (array_key_exists("quantite", $tab)) {
			if (array_key_exists("poids", $tab)) {
				$retour .= "<span style='cursor:pointer' title='Poids unitaire : ".($tab["poids"]/$tab["quantite"])." Kg, Poids total : ".$tab["poids"]." Kg'>";
			}
 			$retour .= $tab["quantite"];
 			if (array_key_exists("poids", $tab)) {
 				$retour .= "</span>";
 			}
		}
		return $retour;
	}
	
	public static function afficheTabac($tab) {
		$retour = "";
		if (array_key_exists("quantite", $tab)) {
 			$retour .= $tab["quantite"];
		} else {
			$retour .= Bral_Helper_ChampBoutique::afficheChampTabac($tab);
		}
		return $retour;
	}
}
