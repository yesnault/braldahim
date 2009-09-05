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
		return "Qu&ecirc;tes";
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

		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findAllLieuQueteAvecRegion();

		Zend_Loader::loadClass("Quete");
		$queteTable = new Quete();

		$quetes = $queteTable->findByIdHobbit($this->view->user->id_hobbit);

		$idQueteEnCours = -1;

		$lieuxQuetes = null;
		foreach($lieux as $l) {
			$lieu = array(
				'nom_lieu' => $l["nom_lieu"],
				'nom_ville' => $l["nom_ville"],
				'date_fin_quete' => null,
				'id_quete' => null,
				'en_cours' => false,
			);

			foreach($quetes as $q) {
				if ($q["date_fin_quete"] == null) {
					$idQueteEnCours = $q["id_quete"];
					$lieu["en_cours"] = true;
				}
				if ($q["id_fk_lieu_quete"] == $l["id_lieu"]) {
					$lieu["date_fin_quete"] = $q["date_fin_quete"];
					$lieu["id_quete"] = $q["id_quete"];
				}
			}

			$lieuxQuetes[$l["nom_systeme_region"]]["lieux"][] = $lieu;
			$lieuxQuetes[$l["nom_systeme_region"]]["nom"] = $l["nom_region"];
		}

		$this->view->lieuxQuetes = $lieuxQuetes;

		if ($idQueteEnCours != -1) {
			Zend_Loader::loadClass("Bral_Quete_Factory");
			$voir = Bral_Quete_Factory::getVoir($this->_request, $this->view, $idQueteEnCours);
			$this->view->htmlQuete = $voir->render();
		}
	}

}
