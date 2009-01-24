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
class Bral_Palmares_Motsruniquestypepiece extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Types Pièces";
	}
	
	function getNomInterne() {
		return "box_onglet_motsruniquestypepiece";		
	}
	
	function getNomClasse() {
		return "motsruniquestypepiece";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/motsruniques_typepiece.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("StatsMotsRuniques");
		$mdate = $this->getTabDateFiltre();
		$statsMotsRuniquesTable = new StatsMotsRuniques();
		$rowset = $statsMotsRuniquesTable->findByTypePiece($mdate["dateDebut"], $mdate["dateFin"]);
		$this->view->mots = $rowset;
	}
}