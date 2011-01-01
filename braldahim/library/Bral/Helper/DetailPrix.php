<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_DetailPrix {

	public static function afficherPrix($e) {
		$retour = "<span>";
		$firstOu = true;
		$ou =  "  <br /> ou ";

		if ($e["prix_1_lot"] != null && $e["prix_1_lot"] >= 0 && $e["unite_1_lot"] > 0) {
			$retour .= $e["prix_1_lot"]. " ";
			$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_lot"], false, $e["prix_1_lot"]);
			$firstOu = false;
		}
			
		if ($e["prix_2_lot"] != null && $e["prix_2_lot"] >= 0 && $e["unite_2_lot"] > 0) {
			if (!$firstOu) {
				$retour .= $ou;
			}
			$retour .= $e["prix_2_lot"]. " ";
			$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_lot"], false, $e["prix_2_lot"]);
			$firstOu = false;
		}
		 
		if ($e["prix_3_lot"] != null && $e["prix_3_lot"] >= 0 && $e["unite_3_lot"] > 0) {
			if (!$firstOu) {
				$retour .= $ou;
			}
			$retour .= $e["prix_3_lot"]. " ";
			$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_lot"], false, $e["prix_3_lot"]);
			$firstOu = false;
		}
			
		if (count($e["prix_minerais"]) > 0) {
			foreach($e["prix_minerais"] as $m) {
				if (!$firstOu) {
					$retour .= $ou;
				}
				$retour .= $m["prix_lot_prix_minerai"]. " ";
				$retour .= htmlspecialchars($m["nom_type_minerai"]);
				$firstOu = false;
			}
		}
			
		if (count($e["prix_parties_plantes"]) > 0) {
			foreach($e["prix_parties_plantes"] as $p) {
				if (!$firstOu) {
					$retour .= $ou;
				}
				$retour .= $p["prix_lot_prix_partieplante"]. " ";
				$s = "";
				if ($p["prix_lot_prix_partieplante"] > 1) {
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
}
