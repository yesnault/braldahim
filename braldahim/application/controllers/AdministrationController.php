<?php

class AdministrationController extends Zend_Controller_Action {

	function init() {
		/** TODO a completer */

		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}
}
