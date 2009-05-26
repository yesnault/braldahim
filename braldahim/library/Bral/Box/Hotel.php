<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Box_Hotel extends Bral_Box_Box {

	function getTitreOnglet() {
		return "H&ocirc;tel";
	}

	function getNomInterne() {
		return "box_lieu";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/hotel.phtml");
	}

	private function data() {
		Zend_Loader::loadClass("Bral_Hotel_Hotel");
		Zend_Loader::loadClass("Bral_Hotel_Voir");
		$box = new Bral_Hotel_Voir("voir", $this->_request, $this->view, "ask");
		$this->view->htmlContenu = $box->render();
	}
}
