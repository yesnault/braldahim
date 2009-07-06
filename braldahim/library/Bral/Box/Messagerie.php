<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
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
		
		$tabMessages = Bral_Util_Messagerie::prepareMessages($this->view->user->id_hobbit, $this->_filtre, $this->_page, $this->_nbMax);
		
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