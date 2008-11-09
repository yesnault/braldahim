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
}