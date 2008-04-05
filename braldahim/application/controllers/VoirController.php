<?php

class VoirController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		
		$this->view->config = Zend_Registry::get('config');
		$this->view->controleur = $this->_request->controller;
		
		Zend_Loader::loadClass('Bral_Voir_Factory');
	}

	function indexAction() {
		$this->_redirect('auth/login');
	}

	function communauteAction() {
		$voir = Bral_Voir_Factory::getCommunaute($this->_request, $this->view);
		echo $voir->render();
	}
	
	function hobbitAction() {
		$this->render();
	}
	
	function doactionAction() {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_response = new Bral_Xml_Response();
		try {
			$voir = Bral_Voir_Factory::getAction($this->_request, $this->view);
			$xml_entry->set_valeur($voir->getNomInterne());
			$xml_entry->set_data($voir->render());
			$xml_response->add_entry($xml_entry);
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$xml_response->add_entry($xml_entry);
		}
		$xml_response->render();
	}
}

