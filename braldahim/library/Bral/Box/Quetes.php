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
class Bral_Box_Quetes extends Bral_Box_Box {

	public function getTitreOnglet() {
		return "Qu&ecirc;tes / Contrats";
	}

	function getNomInterne() {
		return "box_quetes";
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
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/quetes.phtml");
	}

	protected function data() {
		Zend_Loader :: loadClass("Bral_Quetes_Factory");
		$box = Bral_Quetes_Factory::getListe($this->_request, $this->view);
		$this->view->htmlContenu = $box->render();
	}

}
