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
class Bral_Util_Metier {
	
	const METIER_MINEUR_ID = 1;
	const METIER_CHASSEUR_ID = 2;
	const METIER_BUCHERON_ID = 3;
	const METIER_HERBORISTE_ID = 4;
	const METIER_FORGERON_ID = 5;
	const METIER_APOTHICAIRE_ID = 6;
	const METIER_MENUISIER_ID = 7;
	const METIER_CUISINIER_ID = 8;
	const METIER_TANNEUR_ID = 9;
	const METIER_GUERRIER_ID = 10;
	const METIER_TERRASSIER_ID = 11;

	function __construct() {
	}

	public static function prepareMetier($idHobbit, $sexeHobbit) {
		Zend_Loader::loadClass("HobbitsMetiers");
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($idHobbit);
		unset($hobbitsMetiersTable);
		$tabMetiers = null;
		$tabMetierCourant = null;
		$possedeMetier = false;

		foreach($hobbitsMetierRowset as $m) {
			$possedeMetier = true;
				
			if ($sexeHobbit == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}
				
			$t = array("id_metier" => $m["id_metier"],
				"nom" => $nom_metier,
				"nom_systeme" => $m["nom_systeme_metier"],
				"est_actif" => $m["est_actif_hmetier"],
				"date_apprentissage" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $m["date_apprentissage_hmetier"]),
				"description" => $m["description_metier"],
			);
				
			if ($m["est_actif_hmetier"] == "non") {
				$tabMetiers[] = $t;
			}

			if ($m["est_actif_hmetier"] == "oui") {
				$tabMetierCourant = $t;
			}
		}
		unset($hobbitsMetierRowset);

		$retour["tabMetierCourant"] = $tabMetierCourant;
		$retour["tabMetiers"] = $tabMetiers;
		$retour["possedeMetier"] = $possedeMetier;
		return $retour;
	}

	public static function getIdMetierCourant($hobbit) {
		Zend_Loader::loadClass("HobbitsMetiers");
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetierCourantByHobbitId($hobbit->id_hobbit);

		if (count($hobbitsMetiersTable) > 1) {
			throw new Zend_Exception("Bral_Util_Metier::getIdMetierCourant metier courant invalide:".$hobbit->id_hobbit);
		}

		if (count($hobbitsMetiersTable) == 1) {
			$idMetier = $hobbitsMetierRowset[0]["id_metier"];
		} else {
			$idMetier = null;
		}
		return $idMetier;
	}
}