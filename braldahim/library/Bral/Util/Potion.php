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
class Bral_Util_Potion {

	public static function getNomType($typePotion) {
		switch($typePotion) {
			case "potion":
				return "Potion";
				break;
			case "vernis_reparateur":
				return "Vernis réparateur";
				break;
			case "vernis_enchanteur":
				return "Vernis enchanteur";
				break;
			default:
				throw new Zend_Exception("Bral_Util_Potion::getNomType typePotion invalide id:".$typePotion);
				break;
		}
	}
}
