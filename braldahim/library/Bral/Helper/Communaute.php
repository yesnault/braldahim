<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Communaute {

	const COEF_TAILLE = 2;
	const COEF_TAILLE_MOBILE = 1.5;

	public static function afficheBarreConstruction($total, $enCours) {
		
		$largeur = (($enCours * 100) / $total);

		if ($largeur > 100) {
			$largeur = 100;
		}

		if (Zend_Registry::get("estMobile")) {
			$largeur = $largeur * self::COEF_TAILLE_MOBILE;
		} else {
			$largeur = $largeur * self::COEF_TAILLE;
		}

		$retour = "<div class='barre_poids'><div class='barre_img img_barre_poids' style='width:".$largeur."px'>";
		$retour .= "</div></div>";

		return $retour;
	}
}
