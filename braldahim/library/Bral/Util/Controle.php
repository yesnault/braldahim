<?php

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