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
class Bral_Util_Specialisation {


	public static function calculSpecialisationBraldun(&$braldun) {
		self::calculForce($braldun);
		self::calculAgilite($braldun);
		self::calculVigueur($braldun);
		self::calculSagesse($braldun);
	}

	private static function calculForce(&$braldun) {
		if ($braldun->force_base_braldun >= 13) {
			$braldun->force_bm_braldun = $braldun->force_bm_braldun + 2;
		}
		if ($braldun->force_base_braldun >= 16) {
			$braldun->force_bm_braldun = $braldun->force_bm_braldun + 3;
			$braldun->bm_degat_braldun = $braldun->bm_degat_braldun + 3;
		}

		if ($braldun->force_base_braldun >= 20) {
			$braldun->bm_degat_braldun = $braldun->bm_degat_braldun + 10;
		}
	}

	private static function calculAgilite(&$braldun) {
		if ($braldun->agilite_base_braldun >= 13) {
			$braldun->agilite_bm_braldun = $braldun->agilite_bm_braldun + 2;
		}
		if ($braldun->agilite_base_braldun >= 16) {
			$braldun->agilite_bm_braldun = $braldun->agilite_bm_braldun + 3;
			$braldun->bm_defense_braldun = $braldun->bm_defense_braldun + 3;
		}

		if ($braldun->agilite_base_braldun >= 20) {
			$braldun->bm_defense_braldun = $braldun->bm_defense_braldun + 10;
		}
	}

	private static function calculVigueur(&$braldun) {
		if ($braldun->vigueur_base_braldun >= 13) {
			$braldun->vigueur_bm_braldun = $braldun->vigueur_bm_braldun + 2;
		}
		if ($braldun->vigueur_base_braldun >= 16) {
			$braldun->vigueur_bm_braldun = $braldun->vigueur_bm_braldun + 3;
			$braldun->regeneration_bm_braldun = $braldun->regeneration_bm_braldun + 3;
		}

		if ($braldun->vigueur_base_braldun >= 20) {
			$braldun->regeneration_bm_braldun = $braldun->regeneration_bm_braldun + 10;
		}
	}

	private static function calculSagesse(&$braldun) {
		if ($braldun->sagesse_base_braldun >= 13) {
			$braldun->sagesse_bm_braldun = $braldun->sagesse_bm_braldun + 2;
		}
		if ($braldun->sagesse_base_braldun >= 16) {
			$braldun->sagesse_bm_braldun = $braldun->sagesse_bm_braldun + 3;
			$braldun->duree_bm_tour_braldun = $braldun->duree_bm_tour_braldun - 20;
		}

		if ($braldun->sagesse_base_braldun >= 20) {
			$braldun->duree_bm_tour_braldun = $braldun->duree_bm_tour_braldun - 100;
		}
	}

}
