<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
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
		$this->view->inscriptionSiteOk = true;
		if (Bral_Util_JoomlaUser::isJoomlaUser(&$this->view->user) === false) {
			$this->view->inscriptionSiteOk = false;
		}
		
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
		$josUddeimTable = new JosUddeim();
		
		if ($this->_filtre == $this->view->config->messagerie->message->type->envoye) {
			$messages = $josUddeimTable->findByFromId($this->view->user->id_fk_jos_users_hobbit, $this->_page, $this->_nbMax);
		} else if ($this->_filtre == $this->view->config->messagerie->message->type->supprime) {
			$messages = $josUddeimTable->findByToOrFromIdSupprime($this->view->user->id_fk_jos_users_hobbit, $this->_page, $this->_nbMax);
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
				$idsHobbit[$m["toid"]] = $m["toid"];
				$idsHobbit[$m["fromid"]] = $m["fromid"];
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
				$destinataire = "";
				if ($tabHobbits != null) {
					if (array_key_exists($m["toid"], $tabHobbits)) {
						$destinataire = $tabHobbits[$m["toid"]]["prenom_hobbit"] . " ". $tabHobbits[$m["toid"]]["nom_hobbit"]. " (".$tabHobbits[$m["toid"]]["id_hobbit"].")";
					} else {
						$destinataire = " Erreur ".$m["toid"];
					}
					
					if (array_key_exists($m["fromid"], $tabHobbits)) {
						$expediteur = $tabHobbits[$m["fromid"]]["prenom_hobbit"] . " ". $tabHobbits[$m["fromid"]]["nom_hobbit"]. " (".$tabHobbits[$m["fromid"]]["id_hobbit"].")";
					} else {
						$expediteur = " Erreur ".$m["fromid"];
					}
				}
				if ($expediteur == "") {
					$expediteur = " Erreur inconnue";
				}
				if ($destinataire == "") {
					$destinataire = " Erreur inconnue";
				}
				
				$tabMessages[] = array(
					"id_message" => $m["id"],
					"titre" => $m["message"],
					"date" => $m["datum"],
					"expediteur" => $expediteur,
					"destinataire" => $destinataire,
					"toread" => $m["toread"],
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