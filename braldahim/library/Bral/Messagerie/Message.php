<?php

class Bral_Messagerie_Message {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass("Message");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;

		$this->view->message = null;
		$this->view->information = null;
		$this->refreshMessages = false;
		$this->prepareAction();
		$this->view->envoiMessage = false;
	}

	public function getNomInterne() {
		return "messagerie_message";
	}

	function render() {
		switch($this->request->get("valeur_3")) {
			case "envoi" :
			case "nouveau" :
				return $this->view->render("messagerie/nouveau.phtml");
				break;
			case "repondre" :
			case "repondretous" :
			case "archiver" :
			case "supprimer" :
			case "annuler" :
			case "message" :
				return $this->view->render("messagerie/message.phtml");
				break;
			default :
				throw new Zend_Exception(get_class($this)."::render invalide :".$this->request->get("valeur_3"));
		}
	}

	public function refreshMessages() {
		return $this->refreshMessages;
	}

	private function prepareAction() {
		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Message invalide : val=".$this->request->get("valeur_2"));
		}

		switch($this->request->get("valeur_3")) {
			case "envoi" :
				$this->envoiMessage();
				break;
			case "nouveau" :
				$this->prepareNouveau();
				break;
			case "repondre" :
				$this->prepareRepondre();
				break;
			case "repondretous" :
				$this->prepareRepondreTous();
				break;
			case "archiver" :
				$this->prepareArchiver();
				break;
			case "supprimer" :
				$this->prepareSupprimer();
				break;
			case "message" :
				$this->prepareMessage();
				break;
			default :
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->request->get("valeur_3"));
		}
	}

	private function prepareNouveau() {
		// TODO
	}

	private function prepareRepondre() {
		// TODO
	}

	private function prepareRepondreTous() {
		// TODO
	}

	private function prepareArchiver() {
		$messageTable = new Message();
		$message = $messageTable->findByIdHobbitAndIdMessage($this->view->user->id_hobbit, (int)$this->request->get("valeur_2"));
		if ($message != null) {
			$data = array('id_fk_type_message' => $this->view->config->messagerie->message->type->archive);
			$where = "id_message=".(int)$this->request->get("valeur_2");
			$messageTable->update($data, $where);
			$this->view->information = "Le message est archiv&eacute;";
			$this->refreshMessages = true;
		} else {
			throw new Zend_Exception(get_class($this)."::archiver Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
	}

	private function prepareSupprimer() {
		$messageTable = new Message();
		$message = $messageTable->findByIdHobbitAndIdMessage($this->view->user->id_hobbit, (int)$this->request->get("valeur_2"));
		if ($message != null) {
			$data = array('id_fk_type_message' => $this->view->config->messagerie->message->type->supprime);
			$where = "id_message=".(int)$this->request->get("valeur_2");
			$messageTable->update($data, $where);
			$this->view->information = "Le message est supprim&eacute;";
			$this->refreshMessages = true;
		} else {
			throw new Zend_Exception(get_class($this)."::supprimer Message invalide : idhobbit=".$this->view->user->id_hobbit." val=".$this->request->get("valeur_2"));
		}
	}

	private function prepareMessage() {
		$messageTable = new Message();
		$hobbitTable = new Hobbit();

		$message = $messageTable->findByIdHobbitAndIdMessage($this->view->user->id_hobbit, (int)$this->request->get("valeur_2"));
		$tabMessage = null;
		foreach($message as $m) {
			$idDestinatairesTab = split(',', $m["destinataires_message"]);
			$idCopiesTab = split(',', $m["copies_message"]);
			$idTab = array_merge($idDestinatairesTab, $idCopiesTab);
			$hobbits = $hobbitTable->findByIdList($idTab);
			$destinataires = "";
			$copies = "";
			foreach($hobbits as $h) {
				if (in_array($h["id_hobbit"],$idDestinatairesTab)) {
					if ($destinataires == "") {
						$destinataires = $h["nom_hobbit"] . " (".$h["id_hobbit"].")";
					} else {
						$destinataires = $destinataires.", ".$h["nom_hobbit"] . " (".$h["id_hobbit"].")";
					}
				}
				if (in_array($h["id_hobbit"],$idCopiesTab)) {
					if ($copies == "") {
						$copies = $h["nom_hobbit"] . " (".$h["id_hobbit"].")";
					} else {
						$copies = $copies.", ".$h["nom_hobbit"] . " (".$h["id_hobbit"].")";
					}
				}

			}
			if ($m["date_lecture_message"] == null) {
				$data = array('date_lecture_message' => date("Y-m-d H:i:s"));
				$where = "id_message=".$m["id_message"];
				$messageTable->update($data, $where);
			}
			$tabMessage = array(
			'id_message' => $m["id_message"],
			'titre' => $m["titre_message"],
			'date_envoi' => $m["date_envoi_message"],
			'destinataires' => $destinataires,
			'copies' => $copies,
			'contenu' => $m["contenu_message"],
			);
		}
		$this->view->message = $tabMessage;
	}

	private function envoiMessage() {
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Bral_Validate_Messagerie_Destinataires");
		Zend_Loader::loadClass('Zend_Filter_StripTags');

		$filter = new Zend_Filter_StripTags();

		$this->view->destinataires = trim($filter->filter(trim($this->request->get('valeur_4'))));
		$this->view->copies = trim($filter->filter(trim($this->request->get('valeur_5'))));
		$this->view->titre = trim($filter->filter(trim($this->request->get('valeur_6'))));
		$this->view->contenu = trim($filter->filter(trim($this->request->get('valeur_7'))));

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
			'titre_message' => $this->view->titre,
			'contenu_message' => $this->view->contenu,
			);
			$messageTable->insert($data);
			$idDestinatairesTab = split(',', $this->view->destinataires);
			$idEnvoye = array();
			foreach ($idDestinatairesTab as $id) {
				if (!in_array((int)$id, $idEnvoye)) {
					$data["id_fk_hobbit_message"] = (int)$id;
					$data["id_fk_type_message"] = $this->view->config->messagerie->message->type->reception;
					$messageTable->insert($data);
					$idEnvoye[] = (int)$id;
				}
			}
			$this->view->envoiMessage = true;
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
				$this->view->titreErreur = "Le titre doit comporter entre 1 et 80 caractères !";
			}
		}
	}
}