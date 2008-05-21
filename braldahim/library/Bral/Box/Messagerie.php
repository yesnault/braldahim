<?php

class Bral_Box_Messagerie extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Messagerie";
	}

	function getNomInterne() {
		return "box_messagerie";
	}

	function getChargementInBoxes() {
		return false;
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			Zend_Loader::loadClass('JosUddeim');
			Zend_Loader::loadClass('Bral_Util_ConvertDate');
		
			$this->preparePage();
			$this->prepareMessages();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/messagerie.phtml");
	}
	
	private function prepareMessages() {
		$this->view->inscriptionSiteOk = true;
		if ($this->view->user->id_fk_jos_users_hobbit == null) {
			$this->view->inscriptionSiteOk = false;
			return;
		}
		
		$josUddeimTable = new JosUddeim();
		$messages = $josUddeimTable->findByToId($this->view->user->id_fk_jos_users_hobbit, $this->_page, $this->_nbMax);
		
		$idsHobbit = "";
		$tabHobbits = null;
		
		foreach ($messages as $m) {
			$idsHobbit[] = $m["fromid"];
		}
		
		if ($idsHobbit != null) {
			$hobbitTable = new Hobbit();
			$hobbits = $hobbitTable->findByIdFkJosUsersList($idsHobbit);
			foreach($hobbits as $h) {
				$tabHobbits[$h["id_fk_jos_users_hobbit"]] = $h;
			}
		}
		
		foreach ($messages as $m) {
			$expediteur = "";
			if ($tabHobbits != null) {
				if (array_key_exists($m["fromid"], $tabHobbits)) {
					$expediteur = $tabHobbits[$m["fromid"]]["prenom_hobbit"] . " ". $tabHobbits[$m["fromid"]]["nom_hobbit"];
				} else {
					$expediteur = " Erreur ".$m["fromid"];
				}
			}
			if ($expediteur == "") {
				$expediteur = " Erreur inconnue";
			}
			
			$tabMessages[] = array(
				"id_message" => $m["id"],
				"titre" => $m["message"],
				"date" => $m["datum"],
				'expediteur' => $expediteur
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
		$this->view->nbMessages = count($this->view->messages);

		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
	}
	
	
/*	private function prepareMessages() {
		$suivantOk = false;
		$precedentOk = false;
		$tabMessages = null;
		$tabTypeMessages = null;
		$messageTable = new Message();
		$hobbitTable = new Hobbit();
		$messages = $messageTable->findByIdHobbit($this->view->user->id_hobbit, $this->_filtre, $this->_page, $this->_nbMax);

		foreach ($messages as $m) {
			$idDestinatairesTab = split(',', $m["destinataires_message"]);
			$idExpediteurTab = split(',', $m["expediteur_message"]);
			$idTab = array_merge($idDestinatairesTab, $idExpediteurTab);
			$hobbits = $hobbitTable->findByIdList($idTab);
			
			$destinataires = "";
			$expediteur = "";
			foreach($hobbits as $h) {
				if ($destinataires == "") {
					$destinataires = $h["prenom_hobbit"]. " ".$h["nom_hobbit"] . " (".$h["id_hobbit"].")";
				} else {
					$destinataires = $destinataires.", ".$h["prenom_hobbit"]. " ".$h["nom_hobbit"]. " (".$h["id_hobbit"].")";
				}

				if (in_array($h["id_hobbit"],$idExpediteurTab)) {
					$expediteur = $h["prenom_hobbit"]. " ".$h["nom_hobbit"] . " (".$h["id_hobbit"].")";
				}
			}

			$tabMessages[] = array(
				"id_message" => $m["id_message"],
				"titre" => $m["titre_message"],
				"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s',$m["date_envoi_message"]),
				"destinataires" => $destinataires,
				'expediteur' => $expediteur,
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

		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
	}
*/
	private function preparePage() {
		$this->_page = 1;
		if (($this->_request->get("caction") == "box_messagerie") && ($this->_request->get("valeur_1") == "f")) {
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
		} else if (($this->_request->get("caction") == "box_messagerie") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "box_messagerie") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else if (($this->_request->get("caction") == "do_messagerie_message") && ($this->_request->get("valeur_1") != "")  && ($this->_request->get("valeur_1") != -1)) {
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_1"));
			if ($this->_request->get("valeur_3") != "" && $this->_request->get("valeur_3") != -1) {
				$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3"));
			}
		} else {
			$this->_page = 1;
			$this->_filtre = $this->view->config->messagerie->message->type->reception;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->messagerie->messages->nb_affiche;
	}
}