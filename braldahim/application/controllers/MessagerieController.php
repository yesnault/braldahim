<?php

class MessagerieController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->view->controleur = $this->_request->controller;

		Zend_Loader::loadClass('Message');
		Zend_Loader::loadClass('Bral_Messagerie_Factory');

		if ($this->_request->getParam('message') != null && ((int)$this->_request->getParam("message").""==$this->_request->getParam("message")."")) {
			$this->view->id_message = $this->_request->getParam('message');
		}
	}

	function indexAction() {
		$this->_redirect('/messagerie/reception');
	}

	/*	function nouveauAction() {
		if ($this->_request->getParam('hobbit') != null && ((int)$this->_request->getParam("hobbit").""==$this->_request->getParam("hobbit")."")) {
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(true);
		$this->view->destinataires = $this->_request->getParam('hobbit');
		$validDestinataires = $validateurDestinataires->isValid($this->view->destinataires);
		if (!$validDestinataires) {
		foreach ($validateurDestinataires->getMessages() as $message) {
		$destinatairesErreur[] = $message;
		}
		$this->view->destinatairesErreur = $destinatairesErreur;
		}
		}
		$this->render();
		}*/

	function AskActionAction() {
		$this->DoActionAction();
	}

	function DoActionAction() {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_response = new Bral_Xml_Response();
		try {
			$messagerie = Bral_Messagerie_Factory::getAction($this->_request, $this->view);
			$xml_entry->set_valeur($messagerie->getNomInterne());
			$xml_entry->set_data($messagerie->render());
			$xml_response->add_entry($xml_entry);
			if ($messagerie->getActiverWysiwyg() == true) {
				$xml_entry = new Bral_Xml_Entry();
				$xml_entry->set_type("action");
				$xml_entry->set_valeur("activer_wysiwyg");
				$xml_entry->set_data("valeur_10");
				$xml_response->add_entry($xml_entry);
			}
			if ($messagerie->refreshMessages() === true) {
				$this->view->affichageInterne = true;
				$xml_entry = new Bral_Xml_Entry();
				$xml_entry->set_type("display");
				$xml_entry->set_valeur("box_messagerie");
				$box = Bral_Box_Factory::getMessagerie($this->_request, $this->view, true);
				$xml_entry->set_data($box->render());
				$xml_response->add_entry($xml_entry);
			}
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$xml_response->add_entry($xml_entry);
		}
		$xml_response->render();
	}

}
