<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Util_Distinction {

	const ID_TYPE_BOURLINGUEUR_CENTRE = 1;
	const ID_TYPE_BOURLINGUEUR_SUD_OUEST = 2;
	const ID_TYPE_BOURLINGUEUR_SUD_EST = 3;
	const ID_TYPE_BOURLINGUEUR_NORD = 4;
	const ID_TYPE_BOURLINGUEUR_EST = 5;
	
	const ID_TYPE_TEAM = 6;
	const ID_TYPE_QUETE_RP = 7;
	const ID_TYPE_SOULE = 8;
	const ID_TYPE_BETA_TESTEUR = 9;
	
	function __construct() {
	}
	
	public static function ajouterDistinction($idHobbit, $idTypeDistinction, $texte, $url = null) {
		Zend_Loader::loadClass("HobbitsDistinction");
		$hobbitsDistinctionTable = new HobbitsDistinction();
		
		$data = array(
			'id_fk_hobbit_hdistinction' => $idHobbit,
			'id_fk_type_distinction_hdistinction' => $idTypeDistinction,
			'texte_hdistinction' => $texte,
			'url_hdistinction' => $url,
			'date_hdistinction' => date("Y-m-d"),
		);
		
		$hobbitsDistinctionTable->insert($data);
				
	}

	public static function prepareDistinctions($idHobbit) {
		Zend_Loader::loadClass("HobbitsDistinction");
		$hobbitsDistinctionTable = new HobbitsDistinction();
		$hobbitsDistinctionRowset = $hobbitsDistinctionTable->findDistinctionsByHobbitId($idHobbit);
		unset($hobbitsDistinctionTable);
		$tabDistinctions = null;
		$possedeDistinction = false;

		foreach($hobbitsDistinctionRowset as $t) {
			$possedeDistinction = true;

			$tabDistinctions[] = array(
				"nom_systeme" => $t["nom_systeme_type_distinction"],
				"nom_type" => $t["nom_type_distinction"],
				"nom" => $t["texte_hdistinction"],
				"date_hdistinction" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $t["date_hdistinction"]),
				"url_hdistinction" => $t["url_hdistinction"],
			);

		}
		unset($hobbitsDistinctionRowset);

		$retour["tabDistinctions"] = $tabDistinctions;
		$retour["possedeDistinction"] = $possedeDistinction;
		return $retour;
	}
}