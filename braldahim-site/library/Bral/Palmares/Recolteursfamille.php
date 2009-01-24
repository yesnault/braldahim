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
class Bral_Palmares_Recolteursfamille extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Familles";
	}
	
	function getNomInterne() {
		return "box_onglet_recolteursfamille";		
	}
	
	function getNomClasse() {
		return "recolteursfamille";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/recolteurs_famille.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("StatsRecolteurs");
		$this->view->titreColonne2 = $this->getSelectTypeRecolteur($this->view->type);
		$mdate = $this->getTabDateFiltre();
		$statsRecolteursTable = new StatsRecolteurs();
		$rowset = $statsRecolteursTable->findByFamille($mdate["dateDebut"], $mdate["dateFin"], $this->view->type);
		$this->view->familles = $rowset;
	}
}