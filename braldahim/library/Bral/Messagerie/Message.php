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

	}

	public function getNomInterne() {
		return "message";
	}

	function render() {
		switch($this->action) {
			case "ask":
			case "do":
				return $this->view->render("messagerie/message.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
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
			default :
				$this->prepareMessage();
		}
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
}