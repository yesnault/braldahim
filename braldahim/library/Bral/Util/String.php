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
class Bral_Util_String {

	private function __construct() {
	}
	
	public static function firstToUpper($m) {
		return mb_strtoupper($m{0}) . mb_substr($m, 1);
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
	
	public static function isChaineValide($chaine) {
		$valid = true;
		for ($i = 0; $i< mb_strlen($chaine); $i++) {
			if (Bral_Util_String::isCaractereValid(mb_substr($chaine, $i, 1)) == false) {
				$valid = false;
				break;
			}
		}
		return $valid;
	}
	
	public static function isCaractereValid($c) {
		if (in_array($c, self::getTabCaractereValid())) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function isCaractereValidStrict($c) {
		if (in_array($c, self::getTabCaractereValidStrict())) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function stripNonValideStrict($chaine) {
		$retour = "";
		for ($i = 0; $i< mb_strlen($chaine); $i++) {
			if (Bral_Util_String::isCaractereValidStrict(mb_substr($chaine, $i, 1)) == true) {
				$retour .= mb_substr($chaine, $i, 1);
			}
		}
		return $retour;
	}
	
	public static function getTabCaractereValid() {
		return array(
					'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',  'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
			        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',  'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                    '\'', '.', ',', 
                    'ä', 'â', 'à', 'Ä', 'Â', 'À',
                    'é', 'è', 'ê', 'É', 'È', 'Ê',
                    'î', 'ï', 'ì', 'Î', 'Ï', 'Ì',
                    'ö', 'ô', 'ò', 'Ö', 'Ô', 'Ò',
                    'û', 'ü', 'ù', 'Û', 'Ü', 'Ù',
                    'ç', 'Ç', 'æ', 'Æ', '°', '-',
                    'ñ', 'Ñ', 'ã', 'Ã',
                    ' ', 
				);
	}
	
	public static function getTabCaractereValidStrict() {
		return array(
					'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
			        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
					'1', '2', '3', '4', '5', '6', '7', '8', '9', '0'
				);
	}
}