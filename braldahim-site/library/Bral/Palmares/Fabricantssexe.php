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
class Bral_Palmares_Fabricantssexe extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Sexes";
	}
	
	function getNomInterne() {
		return "box_onglet_fabricantssexe";		
	}
	
	function getNomClasse() {
		return "fabricantsfamille";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/fabricants_sexe.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("StatsFabricants");
		$this->view->titreColonne2 = $this->getSelectTypeFabricant($this->view->type);
		$mdate = $this->getTabDateFiltre();
		$statsFabricantsTable = new StatsFabricants();
		$rowset = $statsFabricantsTable->findBySexe($mdate["dateDebut"], $mdate["dateFin"], $this->view->type, $this->view->config);
		$this->view->sexes = $rowset;
	}
}