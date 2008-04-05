<?php

class IndexController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}

	function indexAction() {
		$this->render();
	}

	function preDispatch() {
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity() || !isset($this->view->user) || !isset($this->view->user->email_hobbit)) {
			$this->_redirect('auth/login');
		}
	}
}

