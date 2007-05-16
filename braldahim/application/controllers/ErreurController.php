<?php

class ErreurController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->view->controleur = $this->_request->controller;
		
		// Suppression de la session en cours
		Zend_Auth::getInstance()->clearIdentity(); 
	}
	
	function gardiennageAction() {
		$this->render();
	}
}

