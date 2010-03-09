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
class Bral_Batchs_Routes extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Routes - calculBatchImpl - enter -");
		Zend_Loader::loadClass('Route'); 
		
		$routeTable = new Route();
		$dateFin = date("Y-m-d H:i:s");
		$where = "date_fin_route is not null and date_fin_route <= '".$dateFin."'";
		$nb = $routeTable->delete($where);
		unset($routeTable);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Routes - nb:".$nb." - where:".$where);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Routes - purgeBatch - exit -");
		return "nb delete:".$nb. " date:".$dateFin;
	}
}