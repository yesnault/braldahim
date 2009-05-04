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
class Bral_Box_Charrette extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Charrette";
	}

	function getNomInterne() {
		return "box_charrette";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		Zend_Loader::loadClass('Charrette');
		$charretteTable = new Charrette();
		$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
		if ($nombre > 0) {
			$this->view->possedeCharrette = true;
			$this->prepareCharrette();
		} else {
			$this->view->possedeCharrette = false;
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/charrette.phtml");
	}
	
	private function prepareCharrette() {
		$tabPoidsRondins = Bral_Util_Poids::calculPoidsCharretteTransportable($this->view->user->id_hobbit, $this->view->user->vigueur_base_hobbit);
		$this->view->nbRondins = $tabPoidsRondins["nb_rondins_presents"];
		$this->view->nbRondinsTransportables = $tabPoidsRondins["nb_rondins_transportables"];
	}
}
