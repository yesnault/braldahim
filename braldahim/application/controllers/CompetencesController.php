<?php

class CompetencesController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->xml_response = new Bral_Xml_Response();
	}
	
	function DoActionAction() {
		
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		
		try {
			$competence = Bral_Competences_Factory::getAction($this->_request, $this->view);
			$xml_entry->set_valeur($competence->getNomInterne());
			$xml_entry->set_data($competence->render());
			$xml_response->add_entry($xml_entry);
			$boxToRefresh = $competence->getListBoxRefresh();
			for ($i=0; $i<count($boxToRefresh); $i++) {
				//$this->initView();
				$xml_entry = new Bral_Xml_Entry();
				$xml_entry->set_type("display");
				$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, true);
				$xml_entry->set_valeur($c->getNomInterne());
				$xml_entry->set_data($c->render());
				$xml_response->add_entry($xml_entry);
			}
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$this->xml_response->add_entry($xml_entry);
		}
		$this->xml_response->render();
	}
}