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
	}

	function indexAction() {
		$this->_redirect('/messagerie/reception');
	}

	function nouveauAction() {
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass("Bral_Validate_StringLength");
			Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
			Zend_Loader::loadClass('Zend_Filter_StripTags');

			$filter = new Zend_Filter_StripTags();

			$this->view->destinataires = trim($filter->filter(trim($this->_request->getPost('destinataires'))));
			$this->view->copies = trim($filter->filter(trim($this->_request->getPost('copies'))));
			$this->view->titre = trim($filter->filter(trim($this->_request->getPost('titre'))));
			$this->view->contenu = trim($filter->filter(trim($this->_request->getPost('contenu'))));

			$validateurDestinataires = new Bral_Validate_Messagerie_Destinataires(true);
			$validateurCopies = new Bral_Validate_Messagerie_Destinataires(false);
			$validateurTitre = new Bral_Validate_StringLength(1, 80);
			$validateurContenu = new Bral_Validate_StringLength(1, 80);

			$validDestinataires = $validateurDestinataires->isValid($this->view->destinataires);
			$validCopies = $validateurCopies->isValid($this->view->copies);
			$validTitre = $validateurTitre->isValid($this->view->titre);
			$validContenu = $validateurContenu->isValid($this->view->contenu);

			if (($validTitre) && ($validDestinataires) && ($validCopies) && ($validContenu)) {
				$messageTable = new Message();
				$data = array(
				'titre_message' => $this->view->titre,
				'id_fk_hobbit_message' => $this->view->user->id_hobbit,
				'id_fk_type_message' => $this->view->config->messagerie->message->type->envoye,
				'date_envoi_message' => date("Y-m-d H:i:s"),
				'date_lecture_message' => null,
				'destinataires_message' => $this->view->destinataires,
				'copies_message' =>  $this->view->copies,
				'est_lu_message' => 'oui',
				'titre_message' => $this->view->titre,
				'contenu_message' => $this->view->contenu,
				);
				$messageTable->insert($data);
				$idDestinatairesTab = split(',', $this->view->destinataires);
				$idEnvoye = array();
				foreach ($idDestinatairesTab as $id) {
					if (!in_array((int)$id, $idEnvoye)) {
						$data["id_fk_hobbit_message"] = (int)$id;
						$data["est_lu_message"] = 'non';
						$data["id_fk_type_message"] = $this->view->config->messagerie->message->type->reception;
						$messageTable->insert($data);
						$idEnvoye[] = (int)$id;
					}
				}
				$this->view->message = "Votre message est envoy&eacute;";
				echo $this->view->render("messagerie/index.phtml");
				return;
			} else {
				if (!$validDestinataires) {
					foreach ($validateurDestinataires->getMessages() as $message) {
						$destinatairesErreur[] = $message;
					}
					$this->view->destinatairesErreur = $destinatairesErreur;
				}

				if (!$validCopies) {
					foreach ($validateurCopies->getMessages() as $message) {
						$copiesErreur[] = $message;
					}
					$this->view->copiesErreur = $copiesErreur;
				}

				if (!$validTitre) {
					$this->view->titreErreur = "Le titre doit comporter entre 1 et 80 caract&egrave;s !";
				}
			}
		}

		$this->render();
	}

	function receptionAction() {
		$this->render();
	}

	function archivesAction() {
		$this->render();
	}

	function corbeilleAction() {
		$this->render();
	}

	function envoyesAction() {
		$this->render();
	}

	function brouillonsAction() {
		$this->render();
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
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$xml_response->add_entry($xml_entry);
		}
		$xml_response->render();
	}
}
