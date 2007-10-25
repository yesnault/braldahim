<?php

class IndexController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}

	function indexAction() {
		$this->view->title = "Braldahim";
		$this->render();
	}

	function preDispatch() {
		$auth = Zend_Auth::getInstance();
		print_r($auth);
		if (!$auth->hasIdentity() || !isset($this->view->user) || !isset($this->view->user->nom_hobbit)) {
			$this->_redirect('auth/login');
		}
	}
}

