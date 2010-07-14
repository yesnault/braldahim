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
class Bral_Scripts_Champs extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_STATIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 1;
	}

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Champs - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculChamps();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Champs - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculChamps() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Champs - calculChamps - enter -");
		$retour = "";
		$this->calculChampsBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Champs - calculChamps - exit -");
		return $retour;
	}

	private function calculChampsBraldun(&$retour) {
		Zend_Loader::loadClass("Champ");

		$champsTable = new Champ();
		$champsRowset = $champsTable->findByIdBraldun($this->braldun->id_braldun);

		if ($champsRowset != null) {
			foreach($champsRowset as $e) {
				$retour .= "CHAMP;".$e["id_champ"].';'.$e["x_champ"].';'.$e["y_champ"].';'.$e["z_champ"].';'.$e["id_region"].PHP_EOL;
			}
		} else {
			$retour .= "AUCUN_CHAMP";
		}
	}
}