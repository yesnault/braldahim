<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Aliment.php 2806 2010-07-14 22:13:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-15 00:13:50 +0200 (jeu., 15 juil. 2010) $
 * $LastChangedRevision: 2806 $
 * $LastChangedBy: yvonnickesnault $
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
