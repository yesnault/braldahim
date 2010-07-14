<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Redresseurssexe.php 1049 2009-01-24 15:31:36Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-24 16:31:36 +0100 (Sam, 24 jan 2009) $
 * $LastChangedRevision: 1049 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Palmares_Redresseurssexe extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Sexes";
	}
	
	function getNomInterne() {
		return "box_onglet_redresseurssexe";		
	}
	
	function getNomClasse() {
		return "redresseursfamille";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/redresseurs_sexe.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("StatsReputation");
		$mdate = $this->getTabDateFiltre();
		$statsReputationTable = new StatsReputation();
		$rowset = $statsReputationTable->findBySexe($mdate["dateDebut"], $mdate["dateFin"], "redresseur");
		$this->view->sexes = $rowset;
	}
}