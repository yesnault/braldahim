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
class Bral_Batchs_Donjons extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Donjon - calculBatchImpl - enter -");

		Zend_Loader::loadClass("Donjon");
		$donjonTable = new Donjon();
		$donjons = $donjonTable->findAll();

		Zend_Loader::loadClass("Bral_Util_Donjon");

		if ($donjons != null) {
			foreach($donjons as $d) {
				Bral_Util_Donjon::controleInscriptionEquipe($d, $this->view);
				Bral_Util_Donjon::controleFin($d, $this->view);
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Donjon - calculBatchImpl - exit -");
		return "";
	}
}