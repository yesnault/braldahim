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
class Bral_Box_Coffre extends Bral_Box_Banque {
	
	public function getTitreOnglet() {
		return "Coffre";
	}
	
	function getNomInterne() {
		return "box_coffre";
	}

	function getChargementInBoxes() {
		return false;
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->view->nom_interne = $this->getNomInterne();
			$this->data();
			$this->view->pocheNom = "Tiroir";
			$this->view->pocheNomSysteme = "Coffre";
			$this->view->nb_castars = $this->view->coffre["nb_castar"];
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/coffre.phtml");
	}
}
