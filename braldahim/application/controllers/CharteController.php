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
class CharteController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/'); 
		}
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}
		
		$this->view->controleur = $this->_request->controller;
	}
	
	function indexAction() {
 		$this->render();
	}
	
	function accepterAction() {
		if ($this->_request->isPost()) {
			$hobbitTable = new Hobbit();
			$data = array("est_charte_validee_hobbit" => "oui");
			$hobbitTable->update($data, $where);
			$this->_redirect('/interface');
		} else {
			$this->_redirect('/auth/logout');
		}
	}
}