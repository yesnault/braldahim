<?php

class BatchsController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		Bral_Util_Securite::controlBatchsOrAdmin($this->_request);
	}

	function palissadesAction() {
		Zend_Loader::loadClass('Bral_Batchs_Palissades'); 
		Bral_Batchs_Palissades::calculPalissade();
		$this->render();
	}
	
}

