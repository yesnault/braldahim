<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Personnage extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Personnage";
	}

	function getNomInterne() {
		return "box_personnage";
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
		return $this->view->render("interface/personnage.phtml");
	}

	private function prepareData() {
		$this->prepareOnglets();
	}

	private function prepareOnglets() {
		Zend_Loader::loadClass('Bral_Box_Box');
		Zend_Loader::loadClass('Bral_Box_Factory');

		$tabBox[] = Bral_Box_Factory::getProfil($this->_request, $this->view, true);
		$tabBox[] = Bral_Box_Factory::getMetier($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getTitres($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getEquipement($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getFamille($this->_request, $this->view, false);

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
		$this->view->conteneur = "box_profil_boxes";
		$this->view->onglets = $onglets;
	}
}
