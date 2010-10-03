<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Communaute extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Communaut&eacute;";
	}

	function getNomInterne() {
		return "box_communaute";
	}
	
	function getChargementInBoxes() {
		return false;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$estGestionnaire = false;
		$communaute = null;
		
		Zend_Loader::loadClass("RangCommunaute");
		$rangCommunauteTable = new RangCommunaute();
		$rang = $rangCommunauteTable->findRangCreateur($this->view->user->id_fk_communaute_braldun);

		if ($this->view->user->id_fk_rang_communaute_braldun == $rang["id_rang_communaute"]) { // rang 1 : Gestionnaire
			$estGestionnaire = true;
		}
		if ($this->view->user->id_fk_communaute_braldun != null) {
			Zend_Loader::loadClass("Communaute");
			$communauteTable = new Communaute();
			$communaute = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
			if (count($communaute) == 1) {
				$communaute = $communaute[0];
			} else {
				$communaute = null;
			}
			$estDansCommunaute = true;
		}
		
		$this->view->estGestionnaire = $estGestionnaire;
		$this->view->communaute = $communaute;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute.phtml");
	}
}
