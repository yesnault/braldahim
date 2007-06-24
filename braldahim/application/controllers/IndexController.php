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
        if (!$auth->hasIdentity()) { 
            $this->_redirect('auth/login');
        } 
    } 
}

