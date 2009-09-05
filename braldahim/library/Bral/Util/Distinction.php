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

	function __construct() {
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