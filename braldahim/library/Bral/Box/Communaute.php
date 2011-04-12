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
		$backFlag = $this->view->affichageInterne;
		if ($this->view->affichageInterne) {
			$this->prepareData();
		}
		$this->view->affichageInterne = $backFlag;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute.phtml");
	}

	private function prepareData() {
		Zend_Loader::loadClass('Bral_Util_Communaute');

		$communaute = null;

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

		$this->view->communaute = $communaute;
		$this->prepareOnglets();
	}

	private function prepareOnglets() {
		Zend_Loader::loadClass('Bral_Box_Box');
		Zend_Loader::loadClass('Bral_Box_Factory');

		$tabBox[] = Bral_Box_Factory::getCommunauteBatiments($this->_request, $this->view, true);
		$tabBox[] = Bral_Box_Factory::getCommunauteCoffre($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getCommunauteMembres($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getCommunauteEvenements($this->_request, $this->view, false);
		$tabBox[] = Bral_Box_Factory::getCommunauteGestion($this->_request, $this->view, false);

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
		$this->view->conteneur = "box_communaute_boxes";
		$this->view->onglets = $onglets;
	}
}
