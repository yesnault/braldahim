<?php

class Bral_Util_Faim {
	
	private function __construct() {
	}
	
	public static function calculBalanceFaim(&$hobbit) {
		/*
		 * [0] : -N/2
		 * [1;10] : -N/3
		 * [11;30] : -N/4
	 	 * [31;79] : 0
		 * [80;94] : +N/4
		 * [95;100] : +N/2
		 */
		if ($hobbit->balance_faim_hobbit >= 95) {
			$div = 2;
			$coef = 1;
		} elseif ($hobbit->balance_faim_hobbit >= 80) {
			$div = 4;
			$coef = 1;
		} elseif ($hobbit->balance_faim_hobbit >= 31) {
			$div = 1;
			$coef = 1;
		} elseif ($hobbit->balance_faim_hobbit >= 11) {
			$div = 4;
			$coef = -1;
		} elseif ($hobbit->balance_faim_hobbit >= 1) {
			$div = 3;
			$coef = -1;
		} elseif ($hobbit->balance_faim_hobbit < 1) {
			$div = 2;
			$coef = -1;
		}
		
		$hobbit->force_bbdf_hobbit = $coef * ($hobbit->force_base_hobbit / $div);
		$hobbit->agilite_bbdf_hobbit = $coef * ($hobbit->agilite_base_hobbit / $div);
		$hobbit->vigueur_bbdf_hobbit = $coef * ($hobbit->vigueur_base_hobbit / $div);
		$hobbit->sagesse_bbdf_hobbit = $coef * ($hobbit->sagesse_base_hobbit / $div);
	}
}