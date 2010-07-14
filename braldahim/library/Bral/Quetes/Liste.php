<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Voir.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Quetes_Liste extends Bral_Quetes_Quetes {

	function getNomInterne() {
		return "box_quete_interne";
	}

	function render() {
		return $this->view->render("quetes/liste.phtml");
	}

	function getTitreAction() {}
	public function calculNbPa() {}

	function prepareCommun() {

		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findAllLieuQueteAvecRegion();

		Zend_Loader::loadClass("Quete");
		$queteTable = new Quete();
		$quetes = $queteTable->findByIdBraldun($this->view->user->id_braldun);

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
					if ($q["id_fk_lieu_quete"] == $l["id_lieu"]) {
						$lieu["en_cours"] = true;
					}
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

	function prepareFormulaire() {}
	function prepareResultat() {}
	function getListBoxRefresh() {}

}