<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Util_Controle {

	private function __construct(){}

	/* Verifie si $val est un entier en 
	 * remontant une exception si non.
	 * @return $val
	 */
	public static function getValeurIntVerif($val) {
		if (((int)$val."" != $val."")) {
			throw new Zend_Exception("Bral_Util_Controle Valeur invalide : val=".$val);
		} else {
			return (int)$val;
		}
	}
}