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
class Bral_Helper_DetailPrix {

	public static function afficherPrix($e, $suffixe) {
		$retour = "<span>";
		$firstOu = true;
		$ou =  "  <br /> ou ";

		$suffixe2 = $suffixe;
		if ($suffixe2 == "") {
			$suffixe2 = "vente_prix"; // pour les tables vente_prix_minerai et vente_prix_partieplante
		}

		if ($e["prix_1_vente".$suffixe] >= 0 && $e["unite_1_vente".$suffixe] > 0) {
			$retour .= $e["prix_1_vente".$suffixe]. " ";
			$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_vente".$suffixe], false, $e["prix_1_vente".$suffixe]);
			$firstOu = false;
		}
		 
		if ($e["prix_2_vente".$suffixe] >= 0 && $e["unite_2_vente".$suffixe] > 0) {
			if (!$firstOu) {
				$retour .= $ou;
			}
			$retour .= $e["prix_2_vente".$suffixe]. " ";
			$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_vente".$suffixe], false, $e["prix_2_vente".$suffixe]);
			$firstOu = false;
		}
	  
		if ($e["prix_3_vente".$suffixe] >= 0 && $e["unite_3_vente".$suffixe] > 0) {
			if (!$firstOu) {
				$retour .= $ou;
			}
			$retour .= $e["prix_3_vente".$suffixe]. " ";
			$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_vente".$suffixe], false, $e["prix_3_vente".$suffixe]);
			$firstOu = false;
		}
		 
		if (count($e["prix_minerais"]) > 0) {
			foreach($e["prix_minerais"] as $m) {
				if (!$firstOu) {
					$retour .= $ou;
				}
				$retour .= $m["prix".$suffixe2."_minerai"]. " ";
				$retour .= htmlspecialchars($m["nom_type_minerai"]);
				$firstOu = false;
			}
		}
		 
		if (count($e["prix_parties_plantes"]) > 0) {
			foreach($e["prix_parties_plantes"] as $p) {
				if (!$firstOu) {
					$retour .= $ou;
				}
				$retour .= $p["prix".$suffixe2."_partieplante"]. " ";
				$s = "";
				if ($p["prix".$suffixe2."_partieplante"] > 1) {
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
