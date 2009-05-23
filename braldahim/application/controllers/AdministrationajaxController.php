<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class AdministrationajaxController  extends Bral_Controller_Action {
	public function doactionAction() {
		Bral_Util_Securite::controlAdmin();
		$this->doBralAction("Bral_Administrationajax_Factory");
	}	
}
	
/*
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
	
	function deplaceAction() {
		Zend_Loader::loadClass('Lieu');
		
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
		
		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
		$idLieu = $filter->filter($this->_request->get('id_lieu'));
		
		$lieuTable = new Lieu();
		
		$data = array(
			"x_lieu" => $xLieu,
			"y_lieu" => $yLieu,
		);
		$where = "id_lieu=".$idLieu;
		$lieuTable->update($data, $where);
		
		$this->render();
	}
}
*/
