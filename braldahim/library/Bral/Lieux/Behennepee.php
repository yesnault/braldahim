<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Lieux_Behennepee extends Bral_Lieux_Lieu {

	function prepareCommun() {
		Zend_Loader :: loadClass('Lieu');
		Zend_Loader :: loadClass("Region");
		Zend_Loader :: loadClass("Palissade");

		$regionTable = new Region();
		$regions = $regionTable->fetchAll(null, 'nom_region');
		$regions = $regions->toArray();

		$regionCourante = null;
		foreach ($regions as $r) {
			if ($r["x_min_region"] <= $this->view->user->x_hobbit && $r["x_max_region"] >= $this->view->user->x_hobbit && $r["y_min_region"] <= $this->view->user->y_hobbit && $r["y_max_region"] >= $this->view->user->y_hobbit) {
				$this->regionCourante = $r;
				break;
			}
		}

		if ($this->regionCourante == null) {
			throw new Zend_Exception(get_class($this) . " Region inconnue x:" . $this->view->user->x_hobbit . " y:" . $this->view->user->y_hobbit);
		}
		$this->view->tabRegionCourante = $this->regionCourante;

		Zend_Loader :: loadClass("Echoppe");
		Zend_Loader :: loadClass("HobbitsMetiers");
		Zend_Loader :: loadClass("Region");

		$regionTable = new Region();
		$regions = $regionTable->fetchAll(null, 'nom_region');
		$regions = $regions->toArray();

		$regionCourante = null;
		foreach ($regions as $r) {
			if ($r["x_min_region"] <= $this->view->user->x_hobbit && $r["x_max_region"] >= $this->view->user->x_hobbit && $r["y_min_region"] <= $this->view->user->x_hobbit && $r["y_max_region"] >= $this->view->user->x_hobbit) {
				$this->view->regionCourante = $r;
				break;
			}
		}

		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabEchoppes = null;
		foreach ($echoppesRowset as $e) {
			$tabEchoppes[] = array (
				"id_echoppe" => $e["id_echoppe"],
				"x_echoppe" => $e["x_echoppe"],
				"y_echoppe" => $e["y_echoppe"],
				"nom_metier" => $e["nom_masculin_metier"],
				"id_metier" => $e["id_metier"],
				"id_region" => $e["id_region"],
				"nom_region" => $e["nom_region"]
			);
		}

		$this->view->nEchoppes = count($tabEchoppes);

		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersEchoppeByHobbitId($this->view->user->id_hobbit);

		$this->view->aucuneEchoppe = true;
		$this->view->construireMetierPossible = false;

		foreach ($hobbitsMetierRowset as $m) {
			if ($m["est_actif_hmetier"] != "oui") {
				continue;
			} else {
				$this->view->construireMetierPossible = true;
				$this->id_metier_courant = $m["id_metier"];
			}

			if ($this->view->user->sexe_hobbit == 'feminin') {
				$this->view->nom_metier_courant = $m["nom_feminin_metier"];
			} else {
				$this->view->nom_metier_courant = $m["nom_masculin_metier"];
			}

			if (count($tabEchoppes) > 0) {
				foreach ($tabEchoppes as $e) {
					if ($e["id_metier"] == $m["id_metier"] && $this->view->regionCourante["id_region"] == $e["id_region"]) {
						$this->view->aucuneEchoppe = false;
						break;
					}
				}
			}

			if ($m["est_actif_hmetier"] == "oui") {
				break;
			}
		}
		$this->view->coutCastars = $this->calculCoutCastars();

		$this->view->achatPossible = (($this->view->user->castars_hobbit - $this->view->coutCastars) > 0);
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->aucuneEchoppe !== true || $this->view->construireMetierPossible !== true || $this->view->achatPossible !== true) {
			throw new Zend_Exception(get_class($this) . " Construction interdite");
		}
		$x = $this->request->get("valeur_2");
		$y = $this->request->get("valeur_3");

		if ($x == "") {
			throw new Zend_Exception(get_class($this) . " X interdit");
		}

		if ($y == "") {
			throw new Zend_Exception(get_class($this) . " y interdit");
		}
		$x = intval($x);
		$y = intval($y);

		$this->view->x_construction = $x;
		$this->view->y_construction = $y;

		// on verifie que l'on est pas sur un lieu
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->findByCase($x, $y);

		$this->view->construireLieuOk = true;
		if (count($lieux) > 0) {
			$this->view->construireLieuOk = false;
			return;
		}
		
		// on verifie que l'on est pas sur une echoppe
		$echoppesTable = new Echoppe();
		$echoppes = $echoppesTable->findByCase($x, $y);

		$this->view->construireLieuEchoppeOk = true;
		if (count($echoppes) > 0) {
			$this->view->construireLieuEchoppeOk = false;
			return;
		}
		
		// on verifie que l'on est pas sur une palissade
		$palissadesTable = new Palissade();
		$palissades = $palissadesTable->findByCase($x, $y);

		$this->view->construireLieuPalissadeOk = true;
		if (count($palissades) > 0) {
			$this->view->construireLieuPalissadeOk = false;
			return;
		}

		// on verifie que la position est dans la comté de la tentative
		$this->view->construireRegionOk = true;
		if ($this->regionCourante["x_min_region"] > $x || $this->regionCourante["x_max_region"] < $x || $this->regionCourante["y_min_region"] > $y || $this->regionCourante["y_max_region"] < $y) {
			$this->view->construireRegionOk = false;
			return;
		} else {
			$echoppesTable = new Echoppe();
			$data = array (
				'id_fk_hobbit_echoppe' => $this->view->user->id_hobbit,
				'x_echoppe' => $x,
				'y_echoppe' => $y,
				'id_fk_metier_echoppe' => $this->id_metier_courant,
				'date_creation_echoppe' => date("Y-m-d H:i:s"),
			);
			$echoppesTable->insert($data);
			$this->view->constructionEchoppeOk = true;

			$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
			$this->majHobbit();
		}
	}

	function getListBoxRefresh() {
		return array (
			"box_profil",
			"box_vue",
			"box_laban",
			"box_echoppes"
		);
	}

	/* la premiere echoppe est gratuite */
	private function calculCoutCastars() {
		if ($this->view->nEchoppes < 1) {
			return 0;
		} else {
			return 500;
		}
	}
}