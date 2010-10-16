<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Butins extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Butins - calculBatchImpl - enter -");
		Zend_Loader::loadClass('Butin'); 
		
		$butinTable = new Butin();
		
		$date = date("Y-m-d H:i:s");
		$add_day = -2; // 2 jours au sol
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		
		$where = "date_butin <= '".$dateFin."'";
		$nb = $butinTable->delete($where);
		unset($butinTable);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Butins - nb:".$nb." - where:".$where);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Butins - calculBatchImpl - exit -");
		return "nb delete:".$nb. " date:".$dateFin;
	}
}