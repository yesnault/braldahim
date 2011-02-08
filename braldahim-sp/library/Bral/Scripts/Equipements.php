<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Equipements.php 2806 2010-07-14 22:13:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-15 00:13:50 +0200 (jeu., 15 juil. 2010) $
 * $LastChangedRevision: 2806 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Scripts_Equipements extends Bral_Scripts_Script {

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
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculEquipement();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculEquipement() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculEquipement - enter -");
		$retour = "";
		$this->calculEquipementBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculEquipement - exit -");
		return $retour;
	}

	private function calculEquipementBraldun(&$retour) {
		Zend_Loader::loadClass("BraldunEquipement");

		$equipementTable = new BraldunEquipement();
		$equipementsRowset = $equipementTable->findByIdBraldun($this->braldun->id_braldun);

		if ($equipementsRowset != null) {
			foreach($equipementsRowset as $e) {
				$retour .= "EQUIPEMENT;".$e["id_equipement_hequipement"].PHP_EOL;
			}
		} else {
			$retour .= "AUCUN_EQUIPEMENT";
		}
	}
}