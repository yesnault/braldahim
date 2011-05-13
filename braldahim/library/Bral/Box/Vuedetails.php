<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Vuedetails extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Détails";
	}

	function getNomInterne() {
		return "box_vuedetails";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function getChargementInBoxes() {
		return false;
	}
	
	function render() {
		//$this->data();
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/vuedetails.phtml");
	}

	function data() {
	}
}
