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
		$this->prepareMessages($this->view->config->messagerie->message->type->reception);
		$this->render();
	}

	function archivesAction() {
		$this->prepareMessages($this->view->config->messagerie->message->type->archive);
		$this->render();
	}

	function corbeilleAction() {
		$this->prepareMessages($this->view->config->messagerie->message->type->corbeille);
		$this->render();
	}

	function envoyesAction() {
		$this->prepareMessages($this->view->config->messagerie->message->type->envoye);
		$this->render();
	}

	function brouillonsAction() {
		$this->prepareMessages($this->view->config->messagerie->message->type->brouillon);
		$this->render();
	}

	function DoActionAction() {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");

		try {
			$messagerie = Bral_Messagerie_Factory::getAction($this->_request, $this->view);
			$xml_entry->set_valeur($messagerie->getNomInterne());
			$xml_entry->set_data($messagerie->render());
			$this->xml_response->add_entry($xml_entry);
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$this->xml_response->add_entry($xml_entry);
		}
		$this->xml_response->render();
	}

	private function prepareMessages($type) {
		$messageTable = new Message();
		$hobbitTable = new Hobbit();
		$this->_page = 1;
		$this->_nbMax = $this->view->config->messagerie->messages->nb_affiche;

		$messages = $messageTable->findByIdHobbit($this->view->user->id_hobbit, $type, $this->_page, $this->_nbMax);
		$tabMessages = null;
		foreach($messages as $m) {
			$idDestinatairesTab = split(',', $m["destinataires_message"]);
			$hobbits = $hobbitTable->findByIdList($idDestinatairesTab);
			$destinataires = "";
			foreach($hobbits as $h) {
				if ($destinataires == "") {
					$destinataires = $h["nom_hobbit"];
				} else {
					$destinataires = $destinataires.", ".$h["nom_hobbit"];
				}
			}
			$tabMessages[] = array(
			'id_message' => $m["id_message"],
			'titre' => $m["titre_message"],
			'date_envoi' => $m["date_envoi_message"],
			'destinataires' => $destinataires,
			);
		}
		$this->view->messages = $tabMessages;
	}
}
