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
class Bral_Scripts_Evenements extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_DYNAMIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 1;
	}

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Evenements - calculScriptImpl - enter -");

		$retour = null;
		$this->calculEvenements($retour);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Evenements - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculEvenements(&$retour) {

		$retour1 = 'idHobbit;idEvenement;type;date;details;'.PHP_EOL;
		$retour2 = '';

		Zend_Loader::loadClass("Evenement");
		$evenementTable = new Evenement();
		$evenements = $evenementTable->findByIdHobbit($this->hobbit->id_hobbit, 1, 100, -1);

		foreach ($evenements as $p) {
			$retour2 .= $this->hobbit->id_hobbit.';'.$p["id_evenement"].';'.$p["nom_type_evenement"].';'.$p["date_evenement"].';'.str_replace(';','',$p["details_evenement"]).';'.PHP_EOL;
		}

		$retour .= $retour1;
		$retour .= $retour2;

	}
}