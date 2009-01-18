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
		return $this->view->render("palmares/naissance_sexe.phtml");
	}
}