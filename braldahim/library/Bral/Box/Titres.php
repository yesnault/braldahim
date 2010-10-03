<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Titres extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Réputation";
	}

	function getNomInterne() {
		return "box_titres";
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
		return $this->view->render("interface/titres.phtml");
	}

	private function data() {
		Zend_Loader::loadClass("Bral_Util_Titre");
		$tab = Bral_Util_Titre::prepareTitre($this->view->user->id_braldun, $this->view->user->sexe_braldun);
		$this->view->tabTitres = $tab["tabTitres"];
		$this->view->possedeTitre = $tab["possedeTitre"];
		$this->view->nom_interne = $this->getNomInterne();

		Zend_Loader::loadClass("Bral_Util_Distinction");
		$tabDistinctions = null;
		$tabDistinctions = Bral_Util_Distinction::prepareDistinctions($this->view->user->id_braldun);
		$this->view->tabDistinctions = $tabDistinctions["tabDistinctions"];
	}
}
