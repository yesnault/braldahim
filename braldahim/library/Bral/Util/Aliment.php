<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Aliment {
	public static function getNomType($typeRecette) {

		switch($typeRecette) {
			case "simple":
				return "Simple";
				break;
			case "double":
				return "Double";
				break;
			case "double_ameliore":
				return "Double Ameliorée";
				break;
			case "triple":
				return "Triple";
				break;
			case "quadruple":
				return "Quadruple";
				break;
			case "quintuple":
				return "Quintuple";
				break;
			default:
				return "Bière";
		}
	}

}
