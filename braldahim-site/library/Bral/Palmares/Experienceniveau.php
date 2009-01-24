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
class Bral_Palmares_Experienceniveau extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Niveaux";
	}
	
	function getNomInterne() {
		return "box_onglet_experienceniveau";		
	}
	
	function getNomClasse() {
		return "experienceniveau";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/experience_niveau.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("StatsExperience");
		$mdate = $this->getTabDateFiltre();
		$statsExperienceTable = new StatsExperience();
		$rowset = $statsExperienceTable->findByNiveau($mdate["dateDebut"], $mdate["dateFin"]);
		$this->view->niveaux = $rowset;
	}
}