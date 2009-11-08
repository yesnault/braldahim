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
class Bral_Box_Champs extends Bral_Box_Box {
	
	function getTitreOnglet() {
		return "Champs";
	}
	
	function getNomInterne() {
		return "box_champs";		
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
		return $this->view->render("interface/champs.phtml");
	}
	
	private function data() {
		Zend_Loader::loadClass("Bral_Champs_Champ");
		Zend_Loader::loadClass("Bral_Champs_Liste");
		$box = new Bral_Champs_Liste("liste", $this->_request, $this->view, "ask");
		$idChampCourant = $box->prepareCommun();

		if ($idChampCourant != false) {
			Zend_Loader :: loadClass("Bral_Champs_Factory");
			$box = Bral_Champs_Factory::getVoir($this->_request, $this->view, $idChampCourant);
			$this->view->htmlContenu = $box->render();
		} else {
			$this->view->htmlContenu = $box->render();
		}
	}
}
