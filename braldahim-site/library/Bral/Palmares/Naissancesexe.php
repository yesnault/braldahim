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
class Bral_Palmares_Naissancesexe extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Sexe";
	}
	
	function getNomInterne() {
		return "box_onglet_naissancesexe";		
	}
	
	function getNomClasse() {
		return "naissancesexe";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/naissance_sexe.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("Hobbit");
		$mdate = $this->getTabDateFiltre();
		$hobbitTable = new Hobbit();
		$rowset = $hobbitTable->findAllByDateCreationAndSexe($mdate["dateDebut"], $mdate["dateFin"]);
		$sexes = null;
		$total = 0;
		foreach($rowset as $r) {
			if ($r["sexe_hobbit"] == "masculin") {
				$nom = "Masculin";
			} else {
				$nom = "Féminin";
			}
			$sexes[] = array("nom" => $nom, "nombre" => $r["nombre"]);
			$total = $total + $r["nombre"];
		}
		$this->view->total = $total;
		$this->view->sexes = $sexes;
	}
}