<?php

class RechercheController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			die();
		}
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}

	function hobbitAction() {
		
		$this->_request->get("valeur");
		
		$tabHobbits = null;
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findHobbitsParNom('%'.$this->_request->get("valeur").'%');
		$this->view->champ = $this->_request->get("champ");
		
		
		foreach ($hobbitRowset as $h) {
			$tabHobbits[] = array(
				"id_hobbit" => $h["id_hobbit"],
				"nom" => $h["nom_hobbit"],
			);
		}
		$this->view->pattern = $this->_request->get("valeur");
		$this->view->tabHobbits = $tabHobbits;
		$this->render();
	}
}
