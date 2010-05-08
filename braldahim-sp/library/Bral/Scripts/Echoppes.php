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
class Bral_Scripts_Echoppes extends Bral_Scripts_Script {

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
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculEchoppes();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculEchoppes() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculEchoppes - enter -");
		$retour = "";
		$this->calculEchoppesBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculEchoppes - exit -");
		return $retour;
	}

	private function calculEchoppesBraldun(&$retour) {
		Zend_Loader::loadClass("Echoppe");

		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdBraldun($this->braldun->id_braldun);

		if ($echoppesRowset != null) {
			foreach($echoppesRowset as $e) {
				$retour .= "ECHOPPE;".$e["id_echoppe"].';'.$e["x_echoppe"].';'.$e["y_echoppe"].';'.$e["z_echoppe"].';'.$e["id_metier"].';'.$e["id_region"].PHP_EOL;
			}
		} else {
			$retour .= "AUCUNE_ECHOPPE";
		}
	}
}