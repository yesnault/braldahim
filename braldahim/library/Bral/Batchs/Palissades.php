<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Batchs_Palissades {
	
	function __construct() {
	}
	
	public static function calculPalissade() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Palissades - calculPalissade - enter -");
		Zend_Loader::loadClass('Palissade'); 
		
		$palissadeTable = new Palissade();
		$where = $palissadeTable->getAdapter()->quoteInto('date_fin_palissade <= ?', date("Y-m-d H:i:s"));
		$palissadeTable->delete($where);
		unset($palissadeTable);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Palissades - calculPalissade - exit -");
	}
}