<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Carnet extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Carnet";
	}

	function getNomInterne() {
		return "box_carnet";
	}

	function getChargementInBoxes() {
		return false;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/carnet.phtml");
	}

	function data() {
		Zend_Loader::loadClass("Carnet");
		$this->view->nbMaxNote = Carnet::MAX_NOTE;
		$carnetTable = new Carnet();
		$carnets = $carnetTable->findByIdBraldun($this->view->user->id_braldun);

		for($i=1; $i<=Carnet::MAX_NOTE; $i++) {
			$tabCarnets[$i]["id_carnet"] = $i;
			$tabCarnets[$i]["texte_carnet"] = "vide";
		}
		foreach($carnets as $c) {
			$tabCarnets[$c["id_carnet"]] = $c;
		}
		$this->view->carnets = $tabCarnets;
	}
}
