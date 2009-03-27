<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
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
			$coef = 0;
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

		$hobbit->force_bbdf_hobbit = $coef * floor($hobbit->niveau_hobbit / $div);
		$hobbit->agilite_bbdf_hobbit = $coef * floor($hobbit->niveau_hobbit / $div);
		$hobbit->vigueur_bbdf_hobbit = $coef * floor($hobbit->niveau_hobbit / $div);
		$hobbit->sagesse_bbdf_hobbit = $coef * floor($hobbit->niveau_hobbit / $div);
	}
}