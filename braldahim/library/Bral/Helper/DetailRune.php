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
class Bral_Helper_DetailRune {

	public static function afficherJs($p) {
		return Bral_Helper_Tooltip::jsTip(self::prepareDetail($p));
	}

	public static function afficherTexte($p) {
		return stripslashes(self::prepareDetail($p));
	}

	private static function prepareDetail($e) {
		$text = "Num&eacute;ro de la rune: ".$e["id_rune"]."<br />";
		$text .= "<br />Type : ".htmlspecialchars($e["nom_type"])."<br />";
		return $text;
	}
}
