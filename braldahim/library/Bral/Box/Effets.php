<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Effets extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Effets";
	}

	function getNomInterne() {
		return "box_effets";
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
		return $this->view->render("interface/effets.phtml");
	}
	
	private function data() {
		Zend_Loader::loadClass("Bral_Util_EffetsPotion");
		Zend_Loader::loadClass("Bral_Util_Effets");
		$this->view->potions = Bral_Util_EffetsPotion::calculPotionBraldun($this->view->user, false);
		$this->view->effets = Bral_Util_Effets::calculEffetBraldun($this->view->user, false);
		$this->view->nom_interne = $this->getNomInterne();
	}
}
