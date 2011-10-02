<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_DetailPrix
{

	public static function afficherPrix($e)
	{
		$retour = "<span>";
		$firstOu = true;
		$ou = "  <br /> ou ";

		if ($e["prix_1_lot"] != null && $e["prix_1_lot"] >= 0 && $e["unite_1_lot"] > 0) {
			$retour .= $e["prix_1_lot"] . " ";
			$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_lot"], false, $e["prix_1_lot"]);
			$firstOu = false;
		}

		$retour .= "</span>";

		return $retour;
	}
}
