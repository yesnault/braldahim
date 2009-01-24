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
class Bral_Palmares_Naissancefamille extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Familles";
	}
	
	function getNomInterne() {
		return "box_onglet_naissancefamille";		
	}
	
	function getNomClasse() {
		return "naissancefamille";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/naissance_famille.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("Hobbit");
		$mdate = $this->getTabDateFiltre();
		$hobbitTable = new Hobbit();
		$rowset = $hobbitTable->findAllByDateCreationAndFamille($mdate["dateDebut"], $mdate["dateFin"]);
		$familles = null;
		$total = 0;
		foreach($rowset as $r) {
			$familles[] = array("nom" => $r["nom"], "nombre" => $r["nombre"]);
			$total = $total + $r["nombre"];
		}
		$this->view->total = $total;
		$this->view->familles = $familles;
	}
}