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
class Bral_Helper_DetailRune {

	public static function afficherTooltip($p) {
		return Bral_Helper_Tooltip::render(self::prepareDetail($p, true));
	}

	public static function afficherTexte($p) {
		return stripslashes(self::prepareDetail($p, false));
	}

	private static function prepareDetail($e, $afficheLienHistorique) {
		if ($e["est_identifiee"] == "non") {
			$text = "Rune non identifiée n° ".$e["id_rune"]."<br />";
		} else {
			$text = "Rune ".$e["type"]." n° ".$e["id_rune"]."<br />";
		}

		if ($afficheLienHistorique) {
			$text .= "<label class=\'alabel\' onclick=ouvHistoR(".$e["id_rune"].")>Voir l\'historique</label><br>";
		}
		if ($e["est_identifiee"] == "oui") {
			$text .= "<br>".$e["effet_type_rune"];
		}

		return $text;
	}
}
