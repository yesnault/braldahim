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
			Zend_Loader::loadClass('Message');
			Zend_Loader::loadClass('Bral_Util_ConvertDate');

			$this->preparePage();
			$this->prepareMessages();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/messagerie.phtml");
	}

	private function prepareMessages() {
		$paginator = null;
		$tabMessages = Bral_Util_Messagerie::prepareMessages($this->view->user->id_hobbit, $paginator, $this->view->filtre, $this->view->page, $this->_nbMax);

		$this->view->paginator = $paginator;
		$this->view->messages = $tabMessages;

		Zend_Loader::loadClass("Zend_View_Helper_PaginationControl");
		Zend_Paginator::setDefaultScrollingStyle('All');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('/interface/messagerie/pagination.phtml');
		$this->view->paginator->setView($this->view);
	}

	private function preparePage() {
		$this->view->page = 1;

		if ($this->_request->get("valeur_4") != "") {
			$this->view->filtre =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
		} else {
			$this->view->filtre = $this->view->config->messagerie->message->type->reception;
		}
			
		if (($this->_request->get("box") == "box_messagerie") && ($this->_request->get("valeur_1") == "page")) { // si le joueur a clique sur une icone
			$this->view->page =  Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3"));
		} else {
			$this->view->page = 1;
		}

		if ($this->view->page < 1) {
			$this->view->page = 1;
		}
		$this->_nbMax = $this->view->config->messagerie->messages->nb_affiche;

	}
}