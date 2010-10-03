<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Sondage extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Sondage - calculBatchImpl - enter -");

		Zend_Loader::loadClass("Sondage");

		$retour = null;
		$retour .= $this->calculSondageFin();
		$retour .= $this->calculSondageDebut();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Sondage - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculSondageFin() {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Sondage - calculSondageFin - enter -");
		$retour = "";

		$sondageTable = new Sondage();
		$sondageEnCours = $sondageTable->findATerminer();

		if ($sondageEnCours != null && count($sondageEnCours) > 1) {
			throw new Zend_Exception("Erreur, nb sondage actif > 1 : ".count($sondageEnCours));
		} elseif ($sondageEnCours != null && count($sondageEnCours) == 1) {
			$sondageEnCours = $sondageEnCours[0];
			$data = array('etat_sondage' => 'TERMINE');
			$where = 'id_sondage = '.$sondageEnCours["id_sondage"];
			$sondageTable->update($data, $where);

			// mise à jour des bralduns
			$braldunTable = new Braldun();
			$data = array('est_sondage_valide_braldun' => 'oui');
			$where = "niveau_braldun > 3";
			$braldunTable->update($data, $where);
		} else {
			// pas de sondage, rien à faire
		}

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Sondage - calculSondageFin - exit -");
		return $retour;
	}

	private function calculSondageDebut() {
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Sondage - calculSondageDebut - enter -");
		$retour = "";

		$sondageTable = new Sondage();
		$sondageEnCours = $sondageTable->findADebuter();

		if ($sondageEnCours != null && count($sondageEnCours) > 1) {
			throw new Zend_Exception("Erreur, nb sondage a debuter > 1 : ".count($sondageEnCours));
		} elseif ($sondageEnCours != null && count($sondageEnCours) == 1) {
			$sondageEnCours = $sondageEnCours[0];
			$data = array('etat_sondage' => 'EN_COURS');
			$where = 'id_sondage = '.$sondageEnCours["id_sondage"];
			$sondageTable->update($data, $where);

			// mise à jour des bralduns
			$braldunTable = new Braldun();
			$data = array('est_sondage_valide_braldun' => 'non');
			$where = "niveau_braldun > 3";
			$braldunTable->update($data, $where);
		} else {
			// pas de sondage, rien à faire
		}

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Sondage - calculSondageDebut - exit -");
		return $retour;
	}
}