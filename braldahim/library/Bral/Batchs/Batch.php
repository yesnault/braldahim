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
	
	protected $view = null;
	
	public function __construct($nomSysteme, $view = null) {
		Zend_Loader::loadClass('Batch'); 
		$this->nomSysteme = $nomSysteme;
		$this->config = Zend_Registry::get('config');
		$this->view = $view;
	}
	
	abstract function calculBatchImpl();
	
	public function calculBatch($param = null) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Batch - calculBatch - enter -");
		
		$batchTable = new Batch();
	 	$idBatch = $this->preCalcul($batchTable);
	 	$message = null;
	 	try {
			$message = $this->calculBatchImpl($param);
	 	} catch (Zend_Exception $e) {
	 		$this->postCalcul($batchTable, $idBatch, self::ETAT_KO, $e->getMessage());
	 		Bral_Util_Log::batchs()->err("Bral_Batchs_Batch - calculBatch - Erreur -");
	 		
		 	$config = Zend_Registry::get('config');
			if ($config->general->mail->exception->use == '1') {
				Zend_Loader::loadClass("Bral_Util_Mail");
				$mail = Bral_Util_Mail::getNewZendMail();
				
				$mail->setFrom($config->general->mail->exception->from, $config->general->mail->exception->nom);
				$mail->addTo($config->general->mail->exception->from, $config->general->mail->exception->nom);
				$mail->setSubject("[Braldahim-Batch] Exception rencontrée");
				$mail->setBodyText("--------> ".date("Y-m-d H:m:s"). ' IdBatch:'.$idBatch.PHP_EOL.$e->getMessage(). PHP_EOL);
				$mail->send();
			}
			
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