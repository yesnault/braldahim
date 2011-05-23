<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Champs_Liste extends Bral_Champs_Champ {

	function getNomInterne() {
		return "box_champ";
	}
	function render() {
		return $this->view->render("champs/liste.phtml");
	}
	function prepareCommun() {
		Zend_Loader::loadClass("Champ");
		Zend_Loader::loadClass("Region");
		Zend_Loader::loadClass("Bral_Helper_Communaute");
		Zend_Loader::loadClass('Bral_Util_Communaute');
		Zend_Loader::loadClass('TypeLieu');

		$this->idChampCourant = null;

		$regionTable = new Region();
		$regions = $regionTable->fetchAll(null, 'nom_region');
		$regionsRowset = $regions->toArray();

		$regionCourante = null;

		$champTable = new Champ();

		$niveauGrenier = Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_GRENIER);

		if ($niveauGrenier != null && $niveauGrenier > 0) {
			$champsRowset = $champTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun);
		} else {
			$champsRowset = $champTable->findByIdBraldun($this->view->user->id_braldun);
		}

		$tabChamps = null;
		$tabRegions = null;

		if ($champsRowset > 0) {
			foreach($champsRowset as $c) {
				$champ = array(
					"id_champ" => $c["id_champ"],
					"nom_champ" => $c["nom_champ"],
					"x_champ" => $c["x_champ"],
					"y_champ" => $c["y_champ"],
					"z_champ" => $c["z_champ"],
					"id_region" => $c["id_region"],
					"nom_region" => $c["nom_region"],
					'braldun' => $c['prenom_braldun'].' '.$c['nom_braldun'].' ('.$c['id_braldun'].')',
					'phase_champ' => $c["phase_champ"],
					'date_seme_champ' => $c["date_seme_champ"],
					'date_fin_recolte_champ' => $c["date_fin_recolte_champ"],
					'date_fin_seme_champ' => $c["date_fin_seme_champ"],
					'nom_type_graine' => $c["nom_type_graine"],
				);
				if ($this->view->user->x_braldun == $c["x_champ"] &&
				$this->view->user->y_braldun == $c["y_champ"] &&
				$this->view->user->z_braldun == $c["z_champ"]) {
					$this->idChampCourant = $c["id_champ"];
				}
				$tabChamps[] = $champ;
			}
		}
		$this->view->champs = $tabChamps;
		$this->view->nChamps = count($tabChamps);
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->niveauGrenier = $niveauGrenier;
		return $this->idChampCourant; // utilise dans Bral_Box_Champs
	}

	public function getIdChampCourant() {
		return false; // toujours null ici, neccessaire pour ChampsController
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}
}
