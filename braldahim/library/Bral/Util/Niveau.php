<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Niveau {

	const NIVEAU_MAX = 40;
	const NB_PI_NIVEAU_MAX = 4100;
	
	private function __construct() {
	}

	/**
	 * Le niveau suivant est calculé à partir d'un certain nombre de px perso
	 * qui doit être >= à :
	 * NiveauSuivantPX = NiveauSuivant x 5
	 */
	public static function calculNiveau(&$braldun, &$changeNiveau = null) {

		if ($braldun->niveau_braldun >= self::NIVEAU_MAX) {
			return false;
		}
		
		if ($changeNiveau == null) {
			$changeNiveau = false;
		}

		$niveauDiv10Precedent = floor($braldun->niveau_braldun / 10);

		$niveauSuivantPx = ($braldun->niveau_braldun + 1) * 5;
		if ($braldun->px_perso_braldun >= $niveauSuivantPx) {
			$braldun->px_perso_braldun = $braldun->px_perso_braldun - $niveauSuivantPx;
			$braldun->niveau_braldun = $braldun->niveau_braldun + 1;
			$braldun->pi_cumul_braldun = $braldun->pi_cumul_braldun + $niveauSuivantPx;
			$braldun->pi_braldun = $braldun->pi_braldun + $niveauSuivantPx;
			$changeNiveau = true;

			$niveauDiv10Actuel = floor($braldun->niveau_braldun / 10);
			if ($niveauDiv10Precedent != $niveauDiv10Actuel) {
				self::calculTitre(&$braldun);
				Zend_Loader::loadClass("Bral_Util_Soule");
				Bral_Util_Soule::calculDesinscription($braldun->id_braldun);
				self::calculAccesNiveau40($braldun);
			}
			
			self::gainCastars($braldun);
		}

		$niveauSuivantPx = ($braldun->niveau_braldun + 1) * 5;
		if ($braldun->px_perso_braldun >= $niveauSuivantPx) {
			self::calculNiveau(&$braldun, &$changeNiveau);
		}
		return $changeNiveau;
	}
	
	private static function calculAccesNiveau40(&$braldun) {
		// Si niveau >= 40
		// pi = 4100 - pi_braldun + pi_academie
		if ($braldun->niveau_braldun >= self::NIVEAU_MAX) {
			$pi = self::NB_PI_NIVEAU_MAX - $braldun->pi_academie_braldun;
			$braldun->pi_braldun = $pi;
		}
	}

	private static function calculTitre(&$braldun) {
		Zend_Loader::loadClass("TypeTitre");
		Zend_Loader::loadClass("BraldunsTitres");
		Zend_Loader::loadClass("Bral_Util_Titre");

		$idTitre = Bral_Util_De::get_1d4();

		$data = array(
			'id_fk_braldun_htitre' => $braldun->id_braldun,
			'id_fk_type_htitre' => $idTitre,
			'niveau_acquis_htitre' => floor($braldun->niveau_braldun / 10) * 10,
			'date_acquis_htitre' => date("Y-m-d"),
		);

		$braldunsTitres = new BraldunsTitres();
		$braldunsTitres->insert($data);

		$typeTitre = new TypeTitre();
		$typeTitreRowset = $typeTitre->findById($idTitre);

		if ($braldun->sexe_braldun == "feminin") {
			$braldun->titre_courant_braldun = $typeTitreRowset->nom_feminin_type_titre;
		} else {
			$braldun->titre_courant_braldun = $typeTitreRowset->nom_masculin_type_titre;
		}

		Bral_Util_Titre::calculNouveauTitre(&$braldun, $typeTitreRowset);
	}

	// jusqu'au niveau 10 inclut, niveau*50 castars sont placés à la banque
	private static function gainCastars($braldun) {

		if ($braldun->niveau_braldun < 11) {
			$nbCastars = $braldun->niveau_braldun * 50;

			Zend_Loader::loadClass("Coffre");
			$coffreTable = new Coffre();
			$data = array(
				"quantite_castar_coffre" => $nbCastars,
				"id_fk_braldun_coffre" => $braldun->id_braldun,
			);
			$coffreTable->insertOrUpdate($data);
		}
	}

	public static function calculNiveauDepuisPI($piCaract) {
		$niveau = 0;
		$pi = 0;
		for ($a=1; $a <= 100; $a++) {
			$pi = $pi + ($a - 1) * $a;
			if ($pi >= $piCaract) {
				$niveau = $a;
				break;
			}
		}
		return $niveau;
	}
}