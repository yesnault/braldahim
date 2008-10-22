<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Box_Metier extends Bral_Box_Box {

	function getTitreOnglet() {
		return "M&eacute;tier";
	}

	function getNomInterne() {
		return "box_metier";
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
		return $this->view->render("interface/metier.phtml");
	}
	
	private function data() {
		Zend_Loader::loadClass("Bral_Util_Metier");
		$tab = Bral_Util_Metier::prepareMetier($this->view->user->id_hobbit, $this->view->user->sexe_hobbit);
		$this->view->tabMetierCourant = $tab["tabMetierCourant"];
		$this->view->tabMetiers = $tab["tabMetiers"];
		$this->view->possedeMetier = $tab["possedeMetier"];
		$this->view->nom_interne = $this->getNomInterne();
	}
}
