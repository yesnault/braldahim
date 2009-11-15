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
class Bral_Lieux_Notaire extends Bral_Lieux_Lieu {

	function prepareCommun() {
		Zend_Loader :: loadClass('Lieu');
		Zend_Loader :: loadClass("Region");
		Zend_Loader :: loadClass("Palissade");
		Zend_Loader :: loadClass("Echoppe");
		Zend_Loader :: loadClass("Champ");

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

		$idTypeCourant = $this->request->get("valeur_1");

		$selectedChamp = "";
		$selectedEchoppe = "";
		if ($idTypeCourant == "champ") {
			$selectedChamp = "selected";
		} else if ($idTypeCourant == "echoppe") {
			$selectedEchoppe = "selected";
		}

		if ($this->view->user->niveau_hobbit >= 5) {
			$tabTypeAchat[] = array("id_type_achat" => "champ", "texte" => "Un champ", "selected" => $selectedChamp);
		}
		$tabTypeAchat[] = array("id_type_achat" => "echoppe", "texte" => "Une échoppe", "selected" => $selectedEchoppe);

		$this->view->idTypeCourant = $idTypeCourant;
		$this->view->typeAchat = $tabTypeAchat;

		if ($this->view->idTypeCourant == "champ") {
			$this->prepareAchatChamp();
		} elseif ($this->view->idTypeCourant == "echoppe") {
			$this->prepareAchatEchoppe();
		}
		$this->view->coutCastars = $this->calculCoutCastars();
		$this->view->achatPossible = (($this->view->user->castars_hobbit - $this->view->coutCastars) >= 0);
	}

	private function prepareAchatChamp() {
		Zend_Loader :: loadClass("Champ");
		$champsTable = new Champ();
		$champsRowset = $champsTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabChamps = null;
		foreach ($champsRowset as $e) {
			$tabChamps[] = array (
				"id_champ" => $e["id_champ"],
				"x_champ" => $e["x_champ"],
				"y_champ" => $e["y_champ"],
				"id_region" => $e["id_region"],
				"nom_region" => $e["nom_region"]
			);
		}

		$this->view->nChamps = count($tabChamps);
		$this->view->nChampsPossibleMax = floor($this->view->user->niveau_hobbit / 10) + 1;
		$this->view->nChampsPossible = $this->view->nChampsPossibleMax - $this->view->nChamps;
	}

	private function prepareAchatEchoppe() {
		Zend_Loader :: loadClass("HobbitsMetiers");

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
					if ($e["id_metier"] == $m["id_metier"] && $this->view->tabRegionCourante["id_region"] == $e["id_region"]) {
						$this->view->aucuneEchoppe = false;
						break;
					}
				}
			}

			if ($m["est_actif_hmetier"] == "oui") {
				break;
			}
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->idTypeCourant == "champ") {
			$this->prepareResultatAchatChamp();
		} elseif ($this->view->idTypeCourant == "echoppe") {
			$this->prepareResultatAchatEchoppe();
		} else {
			throw new Zend_Exception(get_class($this) . "Erreur val1:" . $this->request->get("valeur_1"). " typeCourant:".$this->view->idTypeCourant);
		}
	}

	private function prepareResultatAchatChamp() {
		if ($this->view->nChampsPossible < 0 || $this->view->achatPossible !== true && $this->utilisationPaPossible !== true) {
			throw new Zend_Exception(get_class($this) . " Achat interdit");
		}

		if ($this->verificationPositions() == false) {
			return;
		}

		$champTable = new Champ();
		$data = array (
				'id_fk_hobbit_champ' => $this->view->user->id_hobbit,
				'x_champ' => $this->view->x_construction,
				'y_champ' => $this->view->y_construction,
				'z_champ' => 0,
				'date_creation_champ' => date("Y-m-d H:i:s"),
		);
		$champTable->insert($data);
		$this->view->constructionChampOk = true;

		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		$this->majHobbit();
	}

	private function prepareResultatAchatEchoppe() {

		if ($this->view->aucuneEchoppe !== true || $this->view->construireMetierPossible !== true || $this->view->achatPossible !== true && $this->utilisationPaPossible !== true) {
			throw new Zend_Exception(get_class($this) . " Construction interdite");
		}

		if ($this->verificationPositions() == false) {
			return;
		}

		$echoppesTable = new Echoppe();
		$data = array (
				'id_fk_hobbit_echoppe' => $this->view->user->id_hobbit,
				'x_echoppe' => $this->view->x_construction,
				'y_echoppe' => $this->view->y_construction,
				'z_echoppe' => 0,
				'id_fk_metier_echoppe' => $this->id_metier_courant,
				'date_creation_echoppe' => date("Y-m-d H:i:s"),
		);
		$echoppesTable->insert($data);
		$this->view->constructionEchoppeOk = true;

		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		$this->majHobbit();
	}

	private function verificationPositions() {
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

		$z = 0;

		$this->view->x_construction = $x;
		$this->view->y_construction = $y;

		// on verifie que l'on est pas sur un lieu
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->findByCase($x, $y, $z);

		$this->view->construireLieuOk = true;
		if (count($lieux) > 0) {
			$this->view->construireLieuOk = false;
			return false;
		}

		// on verifie que l'on est pas sur une echoppe
		$echoppesTable = new Echoppe();
		$echoppes = $echoppesTable->findByCase($x, $y, $z);

		$this->view->construireLieuEchoppeOk = true;
		if (count($echoppes) > 0) {
			$this->view->construireLieuEchoppeOk = false;
			return false;
		}

		// on verifie que l'on est pas sur une palissade
		$palissadesTable = new Palissade();
		$palissades = $palissadesTable->findByCase($x, $y, $z);

		$this->view->construireLieuPalissadeOk = true;
		if (count($palissades) > 0) {
			$this->view->construireLieuPalissadeOk = false;
			return false;
		}

		// on verifie que l'on est pas sur une palissade
		$champsTable = new Champ();
		$champs = $champsTable->findByCase($x, $y, $z);

		$this->view->construireLieuChampOk = true;
		if (count($champs) > 0) {
			$this->view->construireLieuChampOk = false;
			return false;
		}

		// on verifie que la position est dans la comté de la tentative
		$this->view->construireRegionOk = true;
		if ($this->regionCourante["x_min_region"] > $x || $this->regionCourante["x_max_region"] < $x || $this->regionCourante["y_min_region"] > $y || $this->regionCourante["y_max_region"] < $y) {
			$this->view->construireRegionOk = false;
			return false;
		}

		return true;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban", "box_vue", "box_echoppes", "box_champs"));
	}

	/* la premiere echoppe est gratuite */
	private function calculCoutCastars() {
		if ($this->view->idTypeCourant == "echoppe") {
			if ($this->view->nEchoppes < 1) {
				return 0;
			} else {
				return 500;
			}
		} elseif ($this->view->idTypeCourant == "champ") {
			return $this->view->nChamps * 10 + 10;
		}
	}
}