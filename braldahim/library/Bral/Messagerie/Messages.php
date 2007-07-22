<?php

class Bral_Messagerie_Messages {

	function __construct($request, $view, $action) {
		Zend_Loader::loadClass("Message");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;

		$this->prepareMessages();
	}

	public function getNomInterne() {
		return "messages";
	}

	public function render() {
		switch($this->action) {
			case "ask":
			case "do":
				return $this->view->render("messagerie/messages.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	private function prepareMessages() {
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		}

		switch($this->request->get("valeur_1")) {
			case $this->view->config->messagerie->message->type->reception:
			case $this->view->config->messagerie->message->type->envoye:
			case $this->view->config->messagerie->message->type->brouillon:
			case $this->view->config->messagerie->message->type->supprime:
			case $this->view->config->messagerie->message->type->archive:
				$type = (int)$this->request->get("valeur_1");
				break;
			default:
				throw new Zend_Exception(get_class($this)." Valeur inconnue : val=".$this->request->get("valeur_1"));
				break;
		}

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
					$destinataires = $h["nom_hobbit"] . " (".$h["id_hobbit"].")";
				} else {
					$destinataires = $destinataires.", ".$h["nom_hobbit"] . " (".$h["id_hobbit"].")";
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