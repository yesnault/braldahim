<?php

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