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
class AdministrationController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}
	
	function indexAction() {
		$this->view->md5_value = "";
		if ($this->_request->get("md5_source") != "") {
			$this->view->md5_source = $this->_request->get("md5_source");
			$this->view->md5_value = md5($this->_request->get("md5_source"));
		}
		$this->render();
	}
	
	function md5Action() {
		$this->render();
	}
}
