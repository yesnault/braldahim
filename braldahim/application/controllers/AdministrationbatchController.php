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
class AdministrationbatchController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}
	
	function indexAction() {
		$this->render();
	}
	
	public function batchsAction() {
		Zend_Loader::loadClass("Batch");
		Zend_Loader::loadClass("Bral_Batchs_Batch");
		$batchTable = new Batch();
		
		$this->view->nbEnCours = $batchTable->countAllByEtat(Bral_Batchs_Batch::ETAT_EN_COURS);
		$this->view->nbOk = $batchTable->countAllByEtat(Bral_Batchs_Batch::ETAT_OK);
		$this->view->nbKo = $batchTable->countAllByEtat(Bral_Batchs_Batch::ETAT_KO);
		$this->view->nbTotal = $batchTable->countAll();
		
		$dateDebut = date("Y-m-d 0:0:0");
		$dateFin = date("Y-m-d H:i:s");
		$this->view->dateDebut = Bral_Util_ConvertDate::get_date_add_day_to_date($dateDebut, -3);
		$this->view->dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateFin, 0);
		$this->view->nbOk3jours = $batchTable->countByDate($this->view->dateDebut, $this->view->dateFin, Bral_Batchs_Batch::ETAT_OK);
		$this->view->nbKo3jours = $batchTable->countByDate($this->view->dateDebut, $this->view->dateFin, Bral_Batchs_Batch::ETAT_KO);
		
		$batchsRowset = $batchTable->fetchAll(null, "id_batch desc");
		$this->view->batchs = $batchsRowset->toArray();
		$this->render();
	}
	
}

