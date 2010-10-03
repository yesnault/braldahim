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

		$this->idChampCourant = null;

		$regionTable = new Region();
		$regions = $regionTable->fetchAll(null, 'nom_region');
		$regionsRowset = $regions->toArray();

		$regionCourante = null;

		$champsTable = new Champ();
		$champsRowset = $champsTable->findByIdBraldun($this->view->user->id_braldun);

		$tabChamps = null;
		$tabRegions = null;

		foreach ($regionsRowset as $r) {
			
			$region = $r;
			$region["champs"] = null;
			if ($r["x_min_region"] <= $this->view->user->x_braldun &&
			$r["x_max_region"] >= $this->view->user->x_braldun &&
			$r["y_min_region"] <= $this->view->user->y_braldun &&
			$r["y_max_region"] >= $this->view->user->y_braldun) {
				$regionCourante = $r;
			}
				
			if ($champsRowset > 0) {
				foreach($champsRowset as $c) {
					$champ = array(
						"id_champ" => $c["id_champ"],
						"nom_champ" => $c["nom_champ"],
						"x_champ" => $c["x_champ"],
						"y_champ" => $c["y_champ"],
						"z_champ" => $c["z_champ"],
						"id_region" => $c["id_region"],
						"nom_region" => $c["nom_region"]
					);
					if ($this->view->user->x_braldun == $c["x_champ"] &&
					$this->view->user->y_braldun == $c["y_champ"] &&
					$this->view->user->z_braldun == $c["z_champ"]) {
						$this->idChampCourant = $c["id_champ"];
					}
					if ($c["id_region"] == $r["id_region"]) {
						$region["champs"][] = $champ;
					}
					$tabChamps[] = $champ;
				}
			}
			
			$tabRegions[] = $region;
		}

		$this->view->tabRegions = $tabRegions;
		$this->view->tabRegionCourante = $regionCourante;
		$this->view->champs = $tabChamps;
		$this->view->nChamps = count($tabChamps);

		$this->view->nom_interne = $this->getNomInterne();

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