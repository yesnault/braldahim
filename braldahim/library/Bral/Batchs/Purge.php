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
class Bral_Batchs_Purge extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - calculBatchImpl - enter -");
		$retour = null;
		
		$retour .= $this->purgeBatch();
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - calculBatchImpl - exit -");
		return $retour;
	}
	
	private function purgeBatch() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeBatch - enter -");
		
		Zend_Loader::loadClass('Batch'); 
		
		$batchTable = new Batch();
		
		$date = date("Y-m-d H:i:s");
		$add_day = - $this->config->batchs->purge->table->batch->nbjours->ok;
		
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		$where = $batchTable->getAdapter()->quoteInto('date_debut_batch <= ?',  $dateFin);
		$nb = $batchTable->delete($where. " and etat_batch like 'OK'");
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - Ok - nb:".$nb." - where:".$where);
		
		$add_day = - $this->config->batchs->purge->table->batch->nbjours->tous;
		
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		$where = $batchTable->getAdapter()->quoteInto('date_debut_batch <= ?',  $dateFin);
		$nb = $batchTable->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - tous - nb:".$nb." - where:".$where);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Purge - purgeBatch - exit -");
		return "nb delete:".$nb. " date:".$dateFin;
	}
	
}