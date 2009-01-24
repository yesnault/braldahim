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
class Bral_Palmares_Motsruniquesniveaupiece extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Niveaux Pièces";
	}
	
	function getNomInterne() {
		return "box_onglet_motsruniquesniveaupiece";		
	}
	
	function getNomClasse() {
		return "motsruniquesniveaupiece";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/motsruniques_niveaupiece.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("StatsMotsRuniques");
		$mdate = $this->getTabDateFiltre();
		$statsMotsRuniquesTable = new StatsMotsRuniques();
		$rowset = $statsMotsRuniquesTable->findByNiveauPiece($mdate["dateDebut"], $mdate["dateFin"]);
		$this->view->mots = $rowset;
	}
}