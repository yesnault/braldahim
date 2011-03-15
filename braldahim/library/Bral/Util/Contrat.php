<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Contrat {

	public static function action($braldunSource, $braldunCible) {
		Zend_Loader::loadClass("Contrat");
		$contratTable = new Contrat();
		$contrats = $contratTable->findEnCoursByIdBraldunSourceAndCible($braldunSource->id_braldun, $braldunCible->id_braldun);

		if (count($contrats) > 1) {
			throw  new Zend_Exception("Bral_Util_Contrat erreur nbContrat : ".count($contrats). " idSource:".$braldunSource->id_braldun." idCible:".$braldunCible);
		}

		if ($contrats != null && count($contrats) == 1) {
			$contrat = $contrats[0];

			$gains = self::prepareGains($braldunSource, $braldunCible, $contrat);

			$data = array(
				'date_fin_contrat' => date("Y-m-d H:i:s"),
				'gain_contrat' => $gains,
				'etat_contrat' => 'terminé',
			);
			$where = "id_contrat = ".$contrat["id_contrat"];
			$contratTable->update($data, $where);

			return true;
		} else {
			return false;
		}
	}

	private static function prepareGains($braldunSource, $braldunCible, $contrat) {

		$retour = "";
		if ($contrat["type_contrat"] == 'gredin') {
			$castars = 10 * $braldunCible->points_redresseur_braldun;
		} else {
			$castars = 10 * $braldunCible->points_gredin_braldun;
		}

		Zend_Loader::loadClass("Coffre");
		$coffreTable = new Coffre();

		$coffre = $coffreTable->findByIdBraldun($braldunSource->id_braldun);
		if ($coffre == null || count($coffre) != 1) {
			throw new Zend_Eception("Erreur  Contrat::prepareGains idb:".$braldunSource->id_braldun);
		}

		$idCoffre = $coffre[0]["id_coffre"];

		$data = array(
			"quantite_castar_coffre" => $castars,
			"id_fk_braldun_coffre" => $braldunSource->id_braldun,
			"id_coffre" => $idCoffre,
		);
		$coffreTable->insertOrUpdate($data);

		//$nbRunes = Bral_Util_De::get_1d3();
		$nbRunes = 5;

		Zend_Loader::loadClass("TypeRune");
		Zend_Loader::loadClass("IdsRune");
		Zend_Loader::loadClass("Rune");
		Zend_Loader::loadClass("CoffreRune");
		
		$idsRuneTable = new IdsRune();
		$runeTable = new Rune();
		$typeRuneTable = new TypeRune();
		$coffreRuneTable = new CoffreRune();

		for($i = 1; $i <= $nbRunes; $i++) {
			$tirage = Bral_Util_De::get_1d100();

			if ($tirage <= 60) {
				$niveauRune = 'c';
			} elseif ($tirage <= 90) {
				$niveauRune = 'b';
			} elseif ($tirage <= 100) {
				$niveauRune = 'a';
			}

			$typeRuneRowset = $typeRuneTable->findByNiveau($niveauRune);

			$nbType = count($typeRuneRowset);
			$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType-1);

			$typeRune = $typeRuneRowset[$numeroRune];

			$idRune = $idsRuneTable->prepareNext();

			$dataRune = array (
				"id_rune" => $idRune,
				"id_fk_type_rune" => $typeRune["id_type_rune"],
				"est_identifiee_rune" => "non",
			);
			$runeTable->insert($dataRune);
				
			$data = array (
				"id_rune_coffre_rune" => $idRune,
				"id_fk_coffre_coffre_rune" => $idCoffre,
			);
			$coffreRuneTable->insert($data);
		}

		$retour .= $castars. " castars et ".$nbRunes. " runes à identifier, le tout placé dans votre coffre.";

		return $retour;

	}
}