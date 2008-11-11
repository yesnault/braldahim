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
	 			$retour .= $tab["quantite"];
			} else {
				$retour .= Bral_Helper_ChampBoutique::afficheChampPlante($tab);
			}
	 	}
		return $retour;
	}
	
	public static function afficheMinerai($tab) {
		$retour = "";
		if (array_key_exists("quantite", $tab)) {
 			$retour .= $tab["quantite"];
		} else {
			$retour .= Bral_Helper_ChampBoutique::afficheChampMinerai($tab);
		}
		return $retour;
	}
}
