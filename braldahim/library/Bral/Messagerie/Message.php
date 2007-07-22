<?php

class Bral_Messagerie_Message {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass("Message");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;

		$this->prepareMessage();
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

	private function prepareMessage() {
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Message invalide : val=".$this->request->get("valeur_1"));
		}

		$messageTable = new Message();
		$hobbitTable = new Hobbit();

		$message = $messageTable->findByIdHobbitAndIdMessage($this->view->user->id_hobbit, (int)$this->request->get("valeur_1"));
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