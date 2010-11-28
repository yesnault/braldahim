<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class DebrayeController extends Zend_Controller_Action {
	function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		if ($this->view->config->general->actif != 1) {
			Zend_Auth::getInstance()->clearIdentity();
			$this->render();
		} else {
			$this->_redirect('/');
		}
	}
}