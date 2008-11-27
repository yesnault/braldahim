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
	
	// 1 action par batch
	
	function boutiqueboisAction() {
		$this->view->retour = Bral_Batchs_Factory::calculBatch("BoutiqueBois");
		echo $this->view->render("batchs/resultat.phtml");
	}
	
	function boutiquemineraiAction() {
		$this->view->retour = Bral_Batchs_Factory::calculBatch("BoutiqueMinerai");
		echo $this->view->render("batchs/resultat.phtml");
	}
	
	function boutiqueplanteAction() {
		$this->view->retour = Bral_Batchs_Factory::calculBatch("BoutiquePlante");
		echo $this->view->render("batchs/resultat.phtml");
	}

	function palissadesAction() {
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Palissades");
		echo $this->view->render("batchs/resultat.phtml");
	}
	
	function purgeAction() {
		$this->view->retour = Bral_Batchs_Factory::calculBatch("Purge");
		echo $this->view->render("batchs/resultat.phtml");
	}
}

