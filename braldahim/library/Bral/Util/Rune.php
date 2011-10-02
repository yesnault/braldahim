<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Rune
{

	const HISTORIQUE_CREATION_ID = 1;
	const HISTORIQUE_SERTIR_ID = 2;
	const HISTORIQUE_ACHETER_ID = 3;
	const HISTORIQUE_VENDRE_ID = 4;
	const HISTORIQUE_TRANSBAHUTER_ID = 5;
	const HISTORIQUE_IDENTIFIER_ID = 6;

	public static function dropRune($x, $y, $z, $niveauTue, $niveauBraldun, $idTypeGroupeMonstre, $effetMotD, $idMonstre, $idButin)
	{

		// on ne prend pas le config initialise ici,
		// methode pouvant etre appelée en static de l'exterieur de la classe
		$conf = Zend_Registry::get('config');
		if ($idTypeGroupeMonstre == $conf->game->groupe_monstre->type->gibier) {
			// pas de drop de castar pour les gibiers
			return false;
		}

		Zend_Loader::loadClass("ElementRune");
		Zend_Loader::loadClass("TypeRune");

		//Si 10+2*(Niv tué - Niveau attaquant)+Niveau tué <= 0 alors pas de drop de rune
		if ((10 + 2 * ($niveauTue - $niveauBraldun) + $niveauTue) <= 0) {
			Bral_Util_Log::tech()->debug(" - dropRune - pas de drop de rune : niveauTue=" . $niveauTue . " niveauBraldun=" . $niveauBraldun);
			return false;
		}

		$tirage = Bral_Util_De::get_1d100();

		Bral_Util_Log::tech()->debug(" - dropRune - tirage=" . $tirage . " niveauTue=" . $niveauTue . " effetMotD=" . $effetMotD);

		if ($tirage >= 1 && $tirage <= 1 + ($niveauTue / 4) + $effetMotD) {
			$niveauRune = 'a'; // très rare
		} else if ($tirage >= 2 && $tirage <= 10 + ($niveauTue / 4) + $effetMotD) {
			$niveauRune = 'b'; // rare
		} else if ($tirage >= 11 && $tirage <= 30 - ($niveauTue / 4) + $effetMotD) {
			$niveauRune = 'c'; // peu commune
		} else { //if ($tirage >= 31 && $tirage <= 100 - ($niveau/4) + $effetMotD) {
			$niveauRune = 'd'; // commune
		}

		Bral_Util_Log::tech()->debug(" - dropRune - niveau retenu=" . $niveauRune);

		$typeRuneTable = new TypeRune();
		$typeRuneRowset = $typeRuneTable->findByNiveau($niveauRune);

		if (!isset($typeRuneRowset) || count($typeRuneRowset) == 0) {
			return false; // rien à faire, doit jamais arriver
		}

		$nbType = count($typeRuneRowset);
		$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType - 1);

		$typeRune = $typeRuneRowset[$numeroRune];

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

		Zend_Loader::loadClass("IdsRune");
		$idsRuneTable = new IdsRune();
		$idRune = $idsRuneTable->prepareNext();

		Zend_Loader::loadClass("Rune");
		$runeTable = new Rune();
		$dataRune = array(
			"id_rune" => $idRune,
			"id_fk_type_rune" => $typeRune["id_type_rune"],
			"est_identifiee_rune" => "non",
		);
		$runeTable->insert($dataRune);

		$elementRuneTable = new ElementRune();
		$data = array(
			"id_rune_element_rune" => $idRune,
			"x_element_rune" => $x,
			"y_element_rune" => $y,
			"z_element_rune" => $z,
			"date_fin_element_rune" => $dateFin,
			"id_fk_butin_element_rune" => $idButin,
		);

		$elementRuneTable = new ElementRune();
		$elementRuneTable->insert($data);

		Zend_Loader::loadClass("StatsRunes");
		$statsRunes = new StatsRunes();
		$moisEnCours = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataRunes["id_fk_type_rune_stats_runes"] = $typeRune["id_type_rune"];
		$dataRunes["mois_stats_runes"] = date("Y-m-d", $moisEnCours);
		$dataRunes["nb_rune_stats_runes"] = 1;
		$statsRunes->insertOrUpdate($dataRunes);

		$details = "[m" . $idMonstre . "] a laissé tomber la rune n°" . $idRune;
		self::insertHistorique(self::HISTORIQUE_CREATION_ID, $idRune, $details);

		return $idRune;
	}

	public static function insertHistorique($idTypeHistoriqueRune, $idRune, $details)
	{
		Zend_Loader::loadClass("Bral_Util_Lien");
		$detailsTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($details);

		Zend_Loader::loadClass('HistoriqueRune');
		$historiqueRuneTable = new HistoriqueRune();

		$data = array(
			'date_historique_rune' => date("Y-m-d H:i:s"),
			'id_fk_type_historique_rune' => $idTypeHistoriqueRune,
			'id_fk_historique_rune' => $idRune,
			'details_historique_rune' => $detailsTransforme,
		);
		$historiqueRuneTable->insert($data);
	}
}
