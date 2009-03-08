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
class Bral_Util_Soule {

	public static function calculFinMatch(&$hobbit) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - enter -");
		$retourFinMatch = false;

		Zend_Loader::loadClass("SouleMatch");
		$souleMatchTable = new SouleMatch();
		$matchsRowset = $souleMatchTable->findByIdHobbitBallon($hobbit->id_hobbit);
		if ($matchsRowset != null && count($matchsRowset) == 1) {
			$match = $matchsRowset[0];
			if (($hobbit->soule_camp_hobbit == "a" && $hobbit->y_hobbit == $match["y_min_soule_terrain"])
			|| ($hobbit->soule_camp_hobbit == "b" && $hobbit->y_hobbit == $match["y_max_soule_terrain"])) {
				$this->calculFinMatchGains();
				$this->calculFinMatchDb($match);
				$retourFinMatch = false;
				$hobbit->est_soule_hobbit = "non";
				$hobbit->soule_camp_hobbit = null;
			}
		} else {
			Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - Le joueur (".$hobbit->id_hobbit.") n'a pas le ballon");
		}
			
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - exit (".$retour.") -");
		return $retourFinMatch;
	}

	private static function calculFinMatchGains() {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchGains - enter -");

		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchGains - exit -");
	}

	private static function calculFinMatchDb($match) {
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchDb - enter - matchId(".$match["id_soule_match"].")");
		$souleMatchTable = new SouleMatch();
		$data = array(
			"date_fin_soule_match" => date("Y-m-d H:i:s"),
			"id_fk_joueur_ballon_soule_match" => null,
			"x_ballon_soule_match" => null,
			"y_ballon_soule_match" => null,
		);
		$where = "id_soule_match = ".(int)$match["id_soule_match"];
		$souleMatchTable->update($data, $where);
		Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchDb - exit -");
	}

}
