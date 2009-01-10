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
class Bral_Box_Equipement extends Bral_Box_Box {
	
	function getTitreOnglet() {
		return "&Eacute;quipement";
	}
	
	function getNomInterne() {
		return "box_equipement";		
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
		return $this->view->render("interface/equipement.phtml");
	}
	
	private function data() {
		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEmplacementsEquipement = Bral_Util_Equipement::getTabEmplacementsEquipement($this->view->user->id_hobbit);
		$this->view->typesEmplacement = $tabEmplacementsEquipement["tabTypesEmplacement"];
		$this->view->nom_interne = $this->getNomInterne();
	}
}
