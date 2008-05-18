<?php


class Bral_Util_String {

	private function __construct() {
	}
	
	public static function firstToUpper($m) {
		return strtoupper($m{0}) . substr($m, 1);
	}
	
	/*
	 * Retourne un caractère en majuscule, y compris la majuscule 
	 * des caractères accentués.
	 */
	public static function toUpper($c) {
		$c = strtoupper($c);
		$tab = array(
			'ä' => 'Ä',
			'â' => 'Â',
			'à' => 'À',
			'é' => 'É',
			'è' => 'È',
			'ê' => 'Ê',  
			'î' => 'Î',
			'ï' => 'Ï',
			'ì' => 'Ì',
			'ö' => 'Ö',
			'ô' => 'Ô',
			'ò' => 'Ò',
			'û' => 'Û', 
			'ü' => 'Ü',
			'ù' => 'Ù', 
			'ç' => 'Ç', 
			'ñ' => 'Ñ', 
			'ã' => 'Ã',
		);
		
		if (array_key_exists($c, $tab)) {
			return $tab[$c];
		} else {
			return $c;
		}
	}
}