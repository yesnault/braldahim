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
abstract class Bral_Controller_Box extends Zend_Controller_Action {

	function init() {
		Zend_Loader::loadClass("Bral_Xml_Response");
		Zend_Loader::loadClass("Bral_Xml_Entry");
		Zend_Loader::loadClass("Bral_Util_String");
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	abstract function loadAction();
	
	protected function addBoxes($tab, $position) {
		foreach($tab as $t) {
			$this->m_list[$position][] = $t;
		}
	}
	
	protected function addBox($p, $position) {
		$this->m_list[$position][] = $p;
	}

	protected function getBoxesData() {
		return $this->getDataList("boite_a");
	}

	protected function getDataList($nom) {
		$l = $this->m_list[$nom];
		$liste = "";
		$data = "";
		$onglets = null;

		if ($nom != "aucune") {
			for ($i = 0; $i < count($l); $i ++) {
				if ($i == 0) {
					$css = "actif";
				} else {
					$css = "inactif";
				}
				$tab = array ("titre" => $l[$i]->getTitreOnglet(), "nom" => $l[$i]->getNomInterne(), "css" => $css, "chargementInBoxes" => $l[$i]->getChargementInBoxes());
				$onglets[] = $tab;
				$liste .= $l[$i]->getNomInterne();
				if ($i < count($l)-1 ) {
					$liste .= ",";
				}
			}

			for ($i = 0; $i < count($l); $i ++) {
				if ($i == 0) {
					$display = "block";
				} else {
					$display = "none";
				}

				$l[$i]->setDisplay($display);
				$data .= $l[$i]->render();
			}

			$this->view->onglets = $onglets;
			$this->view->liste = $liste;
			$this->view->data = $data;
			$this->view->conteneur = $nom;
			unset($onglets);
			unset($liste);
			unset($data);
			unset($nom);
		}
	}
}