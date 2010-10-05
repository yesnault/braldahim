<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Faim {

	private function __construct() {
	}

	public static function calculBalanceFaim(&$braldun, $delta = null) {
		/*
		 * [0] : -N/2
		 * [1;10] : -N/3
		 * [11;30] : -N/4
		 * [31;79] : 0
		 * [80;94] : +N/4
		 * [95;100] : +N/2
		 */
		if ($braldun->balance_faim_braldun >= 95) {
			$div = 2;
			$coef = 1;
		} elseif ($braldun->balance_faim_braldun >= 80) {
			$div = 4;
			$coef = 1;
		} elseif ($braldun->balance_faim_braldun >= 31) {
			$div = 1;
			$coef = 0;
		} elseif ($braldun->balance_faim_braldun >= 11) {
			$div = 4;
			$coef = -1;
		} elseif ($braldun->balance_faim_braldun >= 1) {
			$div = 3;
			$coef = -1;
		} elseif ($braldun->balance_faim_braldun < 1) {
			$div = 2;
			$coef = -1;
		}

		$balanceFaimPvPerdus = null;
		
		if ($delta != null) {
			if ($braldun->niveau_braldun >=5 && -$delta > $braldun->balance_faim_braldun) {
				$pvAvant = $braldun->pv_restant_braldun;
				$braldun->pv_restant_braldun = $braldun->pv_restant_braldun + $delta;
				if ($braldun->pv_restant_braldun <= 0) {
					$braldun->pv_restant_braldun = 1;
				}
				$balanceFaimPvPerdus = $pvAvant - $braldun->pv_restant_braldun;
			}
			$braldun->balance_faim_braldun = $braldun->balance_faim_braldun + $delta;
			if ($braldun->balance_faim_braldun > 100) {
				$braldun->balance_faim_braldun = 100;
			}
		}

		$braldun->force_bbdf_braldun = $coef * round($braldun->niveau_braldun / $div);
		$braldun->agilite_bbdf_braldun = $coef * round($braldun->niveau_braldun / $div);
		$braldun->vigueur_bbdf_braldun = $coef * round($braldun->niveau_braldun / $div);
		$braldun->sagesse_bbdf_braldun = $coef * round($braldun->niveau_braldun / $div);
		
		return $balanceFaimPvPerdus;
	}
}