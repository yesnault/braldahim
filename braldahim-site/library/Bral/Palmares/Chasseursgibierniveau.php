<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Chasseursgibierniveau.php 1366 2009-03-22 12:42:04Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-03-22 13:42:04 +0100 (Dim, 22 mar 2009) $
 * $LastChangedRevision: 1366 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Palmares_Chasseursgibierniveau extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Niveaux";
	}
	
	function getNomInterne() {
		return "box_onglet_chasseursgibierniveau";		
	}
	
	function getNomClasse() {
		return "chasseursgibierniveau";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/chasseursgibier_niveau.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("Evenement");
		$mdate = $this->getTabDateFiltre();
		$evenementTable = new Evenement();
		$type = $this->view->config->game->evenements->type->killgibier;
		$rowset = $evenementTable->findByNiveau($mdate["dateDebut"], $mdate["dateFin"], $type, true);
		$this->view->niveaux = $rowset;
	}
}