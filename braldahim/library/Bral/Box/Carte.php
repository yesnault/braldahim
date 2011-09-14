<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Carte extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Carte";
	}

	function getNomInterne() {
		return "box_carte";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/carte.phtml");
	}

	function data() {

		$this->view->nom_interne = $this->getNomInterne();
	}

}
