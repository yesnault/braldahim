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

