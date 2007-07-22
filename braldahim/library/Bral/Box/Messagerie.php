<?php

class Bral_Box_Messagerie {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass('Message');
		Zend_Loader::loadClass('TypeMessage');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;

		$this->preparePage();
	}

	function getTitreOnglet() {
		return "Messagerie";
	}

	function getNomInterne() {
		return "box_messagerie";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$suivantOk = false;
		$precedentOk = false;
		$tabMessages = null;
		$tabTypeMessages = null;
		$messageTable = new Message();
		$hobbitTable = new Hobbit();
		$messages = $messageTable->findByIdHobbit($this->view->user->id_hobbit, $this->_filtre, $this->_page, $this->_nbMax);

		foreach ($messages as $m) {
			$idDestinatairesTab = split(',', $m["destinataires_message"]);
			$hobbits = $hobbitTable->findByIdList($idDestinatairesTab);
			$destinataires = "";
			foreach($hobbits as $h) {
				if (in_array($h["id_hobbit"],$idDestinatairesTab)) {
					if ($destinataires == "") {
						$destinataires = $h["nom_hobbit"] . " (".$h["id_hobbit"].")";
					} else {
						$destinataires = $destinataires.", ".$h["nom_hobbit"] . " (".$h["id_hobbit"].")";
					}
				}
			}

			$tabMessages[] = array(
			"titre" => $m["titre_message"],
			"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s',$m["date_envoi_message"]),
			"destinataires" => $destinataires,
			);
		}

		$typeMessageTable = new TypeMessage();
		$typeMesssages = $typeMessageTable->fetchall();

		foreach ($typeMesssages as $t) {
			$tabTypeMessages[] = array(
			"id_type_message" => $t->id_type_message,
			"nom" => $t->nom_type_message
			);
		}

		if ($this->_page == 1) {
			$precedentOk = false;
		} else {
			$precedentOk = true;
		}

		if (count($tabMessages) == 0) {
			$suivantOk = false;
		} else {
			$suivantOk = true;
		}

		$this->view->precedentOk = $precedentOk;
		$this->view->suivantOk = $suivantOk;
		$this->view->messages = $tabMessages;
		$this->view->typeMessages = $tabTypeMessages;
		$this->view->nbMessages = count($this->view->messages);

		$this->view->nom_interne = $this->getNomInterne();
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		return $this->view->render("interface/messagerie.phtml");
	}

	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "box_messagerie") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "box_messagerie") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page = $this->getValeurVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "box_messagerie") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page = $this->getValeurVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = $this->getValeurVerif($this->_request->get("valeur_4"));
		} else {
			$this->_page = 1;
			$this->_filtre = $this->view->config->messagerie->message->type->reception;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->messagerie->messages->nb_affiche;
	}

	private function getValeurVerif($val) {
		if (((int)$val.""!=$val."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$val);
		} else {
			return (int)$val;
		}
	}
}