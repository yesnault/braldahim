<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Interface extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Interface";
	}

	function getNomInterne() {
		return "box_interface";
	}

	function getChargementInBoxes() {
		return false;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$backFlag = $this->view->affichageInterne;
		if ($this->view->affichageInterne) {
			$this->prepareData();
		}
		$this->view->affichageInterne = $backFlag;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/interface.phtml");
	}

	private function prepareData() {
		$this->prepareOnglets();
	}

	private function prepareOnglets() {
		Zend_Loader::loadClass('Bral_Box_Box');
		Zend_Loader::loadClass('Bral_Box_Factory');

		$tabBox[] = Bral_Box_Factory::getBlabla($this->_request, $this->view, true);
		$tabBox[] = Bral_Box_Factory::getLieu($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getLaban($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getCharrette($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getCoffre($this->_request, $this->view, false);
		//$tabBox[] = Bral_Box_Factory::getEvenements($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getSoule($this->_request, $this->view, false);

		$liste = "";
		$data = "";

		for ($i = 0; $i < count($tabBox); $i ++) {
			$box = $tabBox[$i];
			if ($i == 0) {
				$css = "actif";
			} else {
				$css = "inactif";
			}

			$tab = array ("titre" => $box->getTitreOnglet(), "nom" => $box->getNomInterne(), "css" => $css, "chargementInBoxes" => $box->getChargementInBoxes());
			$onglets[] = $tab;
			$liste .= $box->getNomInterne();
			if ($i < count($tabBox)-1 ) {
				$liste .= ",";
			}
		}

		for ($i = 0; $i < count($tabBox); $i ++) {
			$box = $tabBox[$i];
			if ($i == 0) {
				$display = "block";
			} else {
				$display = "none";
			}
			$box->setDisplay($display);
			$data .= $box->render();
		}

		$this->view->liste = $liste;
		$this->view->data = $data;
		$this->view->conteneur = "box_interface_boxes";
		$this->view->onglets = $onglets;
	}
}
