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
			Zend_Loader::loadClass('Bral_Util_JoomlaUser');
		
			$this->preparePage();
			$this->prepareMessages();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/messagerie.phtml");
	}
	
	private function prepareMessages() {
		$this->view->inscriptionSiteOk = true;
		if (Bral_Util_JoomlaUser::isJoomlaUser(&$this->view->user) === false) {
			$this->view->inscriptionSiteOk = false;
			return;
		}
		
		$josUddeimTable = new JosUddeim();
		
		if ($this->_filtre == $this->view->config->messagerie->message->type->envoye) {
			$messages = $josUddeimTable->findByFromId($this->view->user->id_fk_jos_users_hobbit, $this->_page, $this->_nbMax);
		} else if ($this->_filtre == $this->view->config->messagerie->message->type->supprime) {
			$messages = $josUddeimTable->findByToId($this->view->user->id_fk_jos_users_hobbit, $this->_page, $this->_nbMax, true);
		} else { // reception
			$messages = $josUddeimTable->findByToId($this->view->user->id_fk_jos_users_hobbit, $this->_page, $this->_nbMax);
		}
		
		$idsHobbit = "";
		$tabHobbits = null;
		$tabMessages = null;
		
		if ($messages != null) {
			foreach ($messages as $m) {
				if ($this->_filtre == $this->view->config->messagerie->message->type->envoye) {
					$fieldId = "toid";
				} else {
					$fieldId = "fromid";
				}
				$idsHobbit[] = $m[$fieldId];
			}
			
			if ($idsHobbit != null) {
				$hobbitTable = new Hobbit();
				$hobbits = $hobbitTable->findByIdFkJosUsersList($idsHobbit);
				if ($hobbits != null) {
					foreach($hobbits as $h) {
						$tabHobbits[$h["id_fk_jos_users_hobbit"]] = $h;
					}
				}
			}
			
			foreach ($messages as $m) {
				$expediteur = "";
				if ($tabHobbits != null) {
					if (array_key_exists($m[$fieldId], $tabHobbits)) {
						$expediteur = $tabHobbits[$m[$fieldId]]["prenom_hobbit"] . " ". $tabHobbits[$m[$fieldId]]["nom_hobbit"]. " (".$tabHobbits[$m[$fieldId]]["id_hobbit"].")";
					} else {
						$expediteur = " Erreur ".$m[$fieldId];
					}
				}
				if ($expediteur == "") {
					$expediteur = " Erreur inconnue";
				}
				
				$tabMessages[] = array(
					"id_message" => $m["id"],
					"titre" => $m["message"],
					"date" => $m["datum"],
					'expediteur_destinataire' => $expediteur
				);
			}
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
	
	private function preparePage() {
		$this->_page = 1;
		
		if ($this->_request->get("valeur_4") != "") {
			$this->_filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else {
			$this->_filtre = $this->view->config->messagerie->message->type->reception;
		}
			
		if (($this->_request->get("box") == "box_messagerie") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
		} else if (($this->_request->get("box") == "box_messagerie") && ($this->_request->get("valeur_1") == "s")) {
			$this->_page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
		} else {
			$this->_page = 1;
		}

		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->messagerie->messages->nb_affiche;
	}
}