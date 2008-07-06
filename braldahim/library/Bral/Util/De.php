<?php

class Bral_Util_De {

	public static function get_1d1() {
		return 1;
	}
	
	public static function get_1d2() {
		srand(self::make_seed());
		return rand(1, 2);
	}

	public static function get_1d3() {
		srand(self::make_seed());
		return rand(1, 3);
	}
	
	public static function get_1d5() {
		srand(self::make_seed());
		return rand(1, 5);
	}
	
	public static function get_1d6() {
		srand(self::make_seed());
		return rand(1, 6);
	}
	
	public static function get_1d20() {
		srand(self::make_seed());
		return rand(1, 20);
	}
	
	public static function get_1d100() {
		srand(self::make_seed());
		return rand(1, 100);
	}
	
	public static function get_2d3() {
		$n = self::get_1d3();
		$n = $n + self::get_1d3();
		return $n;
	}
	
	public static function get_3d3() {
		$n = self::get_2d3();
		$n = $n + self::get_1d3();
		return $n;
	}
	
	public static function get_4d3() {
		$n = self::get_3d3();
		$n = $n + self::get_1d3();
		return $n;
	}
	
	public static function getLanceDeSpecifique($n, $a, $b) {
		$retour = 0;
		for ($i = 1; $i <= $n; $i++) {
			$retour = $retour + self::get_de_specifique($a, $b);
		}
		
		return $retour;
	}
	
	public static function get_de_specifique($a, $b) {
		if (!is_int(intval($a))) {
			throw new Exception("De::get_de_specifique : a invalides : ".$a);
		}
		if (!is_int(intval($b))) {
			throw new Exception("De::get_de_specifique : b invalides : ".$b);
		}
		if ($a > $b) {
			throw new Exception("De::get_de_specifique : parametre invalides : a(".$a.") > b(".$b.")");
		}

		srand(self::make_seed());
		return rand($a, $b);
	}
	
	public static function get_de_specifique_hors_liste($a, $b, $liste) {
		$n = self::get_de_specifique($a, $b);

		if (is_array($liste)) {
			if (in_array($n, $liste)) {
				return self::get_de_specifique_hors_liste($a, $b, $liste);
			} else {
				return $n;
			}
		} else if ($liste == null) {
			return $n;
		} else {
			throw new Exception("De::get_de_specifique_hors_liste : liste invalide ");
		}
	}
	
	public static function get_chaine_aleatoire($longueur) {
		srand(self::make_seed());
		// 10 + 26 + 26 = 62
		$tab = array(
			0,1,2,3,4,5,6,7,8,9,
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',  'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',  'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
		);
		
		$mot = "";
		for ($i = 0; $i < $longueur; $i++) {
			$mot .= $tab[rand (0, count($tab) - 1)];
		}
		return $mot;
	}
	
	private function __construct() {}

	private static function make_seed() {
		list ($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}
}
