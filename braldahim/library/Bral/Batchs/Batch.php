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
abstract class Bral_Batchs_Batch {
	
	const ETAT_EN_COURS = 'EN_COURS';
	const ETAT_OK = 'OK';
	const ETAT_KO = 'KO';
	
	public function __construct($nomSysteme) {
		Zend_Loader::loadClass('Batch'); 
		$this->nomSysteme = $nomSysteme;
		$this->config = Zend_Registry::get('config');
	}
	
	abstract function calculBatchImpl();
	
	public function calculBatch() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Batch - calculBatch - enter -");
		
		$batchTable = new Batch();
	 	$idBatch = $this->preCalcul($batchTable);
	 	$message = null;
	 	try {
			$message = $this->calculBatchImpl();
	 	} catch (Zend_Exception $e) {
	 		$this->postCalcul($batchTable, $idBatch, self::ETAT_KO, $e->getMessage());
	 		Bral_Util_Log::batchs()->err("Bral_Batchs_Batch - calculBatch - Erreur -");
	 		return false;
	 	}
	 	
		$this->postCalcul($batchTable, $idBatch, self::ETAT_OK, $message);
		
		return true;
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Batch - calculBatch - enter -");
	}
	
	private function preCalcul($batchTable) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Batch - preCalcul - enter -");
		$data = array(
			'type_batch' => $this->nomSysteme,
			'date_debut_batch' => date("Y-m-d H:i:s"),
			'etat_batch' => self::ETAT_EN_COURS,
		);
		
		$idBatch = $batchTable->insert($data);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Batch - preCalcul (id:".$idBatch.") - exit -");
		return $idBatch;
	}
	
	private function postCalcul($batchTable, $idBatch, $etat, $message = null) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Batch - postCalcul - enter -");
		
		$data = array(
			'date_fin_batch' => date("Y-m-d H:i:s"),
			'etat_batch' => $etat,
			'message_batch' => $message,
		);
		$where = 'id_batch='.$idBatch;
		$batchTable->update($data, $where);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Batch - postCalcul - exit -");
	}
	
}