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
			if ($r["x_min_region"] <= $this->view->user->x_braldun && $r["x_max_region"] >= $this->view->user->x_braldun && $r["y_min_region"] <= $this->view->user->y_braldun && $r["y_max_region"] >= $this->view->user->y_braldun) {
				$this->view->regionCourante = $r;
				break;
			}
		}

		if ($this->view->regionCourante == null) {
			throw new Zend_Exception(get_class($this) . " Region inconnue x:" . $this->view->user->x_braldun . " y:" . $this->view->user->y_braldun);
		}
		$this->view->tabRegionCourante = $this->view->regionCourante;

		$this->idSelection = 0;
		if ($this->request->get("valeur_1") != null) {
			list ($this->view->idTypeCourant, $this->idSelection) = split("_", $this->request->get("valeur_1"));
		}

		$selectedChamp = "";
		$selectedEchoppe = "";
		if ($this->view->idTypeCourant == "acheterchamp") {
			$selectedChamp = "selected";
		} else if ($this->view->idTypeCourant == "acheterechoppe") {
			$selectedEchoppe = "selected";
		}

		if ($this->view->user->niveau_braldun >= 5) {
			$tabTypeAchat[] = array("id_type_action" => "acheterchamp_0", "texte" => "Acheter un champ", "selected" => $selectedChamp);
		}
		$tabTypeAchat[] = array("id_type_action" => "acheterechoppe_0", "texte" => "Acheter une échoppe", "selected" => $selectedEchoppe);

		$this->prepareEchoppes($tabTypeAchat);
		$this->view->typeAction = $tabTypeAchat;

		if ($this->view->idTypeCourant == "acheterchamp") {
			$this->prepareAcheterChamp();
		} elseif ($this->view->idTypeCourant == "acheterechoppe") {
			$this->prepareAcheterEchoppe();
		} elseif ($this->view->idTypeCourant == "deplacerechoppe") {
			$this->prepareDeplacerEchoppe();
		} elseif ($this->view->idTypeCourant == "supprimerechoppe") {
			$this->prepareSupprimerEchoppe();
		}
		$this->view->coutCastars = $this->calculCoutCastars();
		$this->view->achatPossible = (($this->view->user->castars_braldun - $this->view->coutCastars) >= 0);
	}

	private function prepareEchoppes(&$tabTypeAchat) {
		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdBraldun($this->view->user->id_braldun);

		$idRecu = $this->view->idTypeCourant."_".$this->idSelection;

		$tabEchoppes = null;
		foreach ($echoppesRowset as $e) {
			$echoppe = array (
				"id_echoppe" => $e["id_echoppe"],
				"x_echoppe" => $e["x_echoppe"],
				"y_echoppe" => $e["y_echoppe"],
				"nom_metier" => $e["nom_masculin_metier"],
				"id_metier" => $e["id_metier"],
				"id_region" => $e["id_region"],
				"nom_region" => $e["nom_region"]
			);

			if ($this->view->tabRegionCourante["id_region"] == $e["id_region"]) {
				$idDeplacement = "deplacerechoppe_".$e["id_echoppe"];
				$idSuppression = "supprimerechoppe_".$e["id_echoppe"];
					
				$selectedDeplacement = "";
				$selectedSuppression = "";
				if ($idRecu == $idDeplacement) {
					$selectedDeplacement = "selected";
					$this->echoppeCourante = $echoppe;
				} elseif ($idRecu == $idSuppression) {
					$selectedSuppression = "selected";
					$this->echoppeCourante = $echoppe;
				}
					
				$nom = $e["nom_masculin_metier"]. " x:".$e["x_echoppe"]." y:".$e["y_echoppe"];
				$tabTypeAchat[] = array("id_type_action" => $idDeplacement, "texte" => "Déplacer l'échoppe ".$nom, "selected" => $selectedDeplacement);
				$tabTypeAchat[] = array("id_type_action" => $idSuppression, "texte" => "Supprimer l'échoppe ".$nom, "selected" => $selectedSuppression);
			}
				
			$tabEchoppes[] = $echoppe;
		}

		$this->view->nEchoppes = count($tabEchoppes);
		$this->echoppes = $tabEchoppes;
	}

	private function prepareAcheterChamp() {
		Zend_Loader :: loadClass("Champ");
		$champsTable = new Champ();
		$champsRowset = $champsTable->findByIdBraldun($this->view->user->id_braldun);

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
		$this->view->nChampsPossibleMax = floor($this->view->user->niveau_braldun / 10) + 1;
		$this->view->nChampsPossible = $this->view->nChampsPossibleMax - $this->view->nChamps;
	}

	private function prepareAcheterEchoppe() {
		Zend_Loader :: loadClass("BraldunsMetiers");

		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersEchoppeByBraldunId($this->view->user->id_braldun);

		$this->view->aucuneEchoppe = true;
		$this->view->construireMetierPossible = false;

		foreach ($braldunsMetierRowset as $m) {
			if ($m["est_actif_hmetier"] != "oui") {
				continue;
			} else {
				$this->view->construireMetierPossible = true;
				$this->id_metier_courant = $m["id_metier"];
			}

			if ($this->view->user->sexe_braldun == 'feminin') {
				$this->view->nom_metier_courant = $m["nom_feminin_metier"];
			} else {
				$this->view->nom_metier_courant = $m["nom_masculin_metier"];
			}

			if (count($this->echoppes) > 0) {
				foreach ($this->echoppes as $e) {
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

	private function prepareDeplacerEchoppe() {
		$this->verificationEchoppes();
	}

	private function prepareSupprimerEchoppe() {
		$this->verificationEchoppes();
	}

	private function verificationEchoppes() {
		$trouvee = false;
		foreach($this->echoppes as $e) {
			if ($e["id_echoppe"] == $this->idSelection) {
				$trouvee = true;
			}
		}
		if ($trouvee == false) {
			throw new Zend_Exception("Erreur Echoppe ".$this->idSelection);
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->idTypeCourant == "acheterchamp") {
			$this->prepareResultatAchatChamp();
		} elseif ($this->view->idTypeCourant == "acheterechoppe") {
			$this->prepareResultatAchatEchoppe();
		} elseif ($this->view->idTypeCourant == "deplacerechoppe") {
			$this->prepareResultatDeplacerEchoppe();
		} elseif ($this->view->idTypeCourant == "supprimerechoppe") {
			$this->prepareResultatSupprimerEchoppe();
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
				'id_fk_braldun_champ' => $this->view->user->id_braldun,
				'x_champ' => $this->view->x_construction,
				'y_champ' => $this->view->y_construction,
				'z_champ' => 0,
				'date_creation_champ' => date("Y-m-d H:i:s"),
		);
		$champTable->insert($data);
		$this->view->constructionChampOk = true;

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->coutCastars;
		$this->majBraldun();
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
				'id_fk_braldun_echoppe' => $this->view->user->id_braldun,
				'x_echoppe' => $this->view->x_construction,
				'y_echoppe' => $this->view->y_construction,
				'z_echoppe' => 0,
				'id_fk_metier_echoppe' => $this->id_metier_courant,
				'date_creation_echoppe' => date("Y-m-d H:i:s"),
		);
		$idEchoppe = $echoppesTable->insert($data);
		$this->view->constructionEchoppeOk = true;

		$this->idSelection = $idEchoppe;
		$this->constructionRoute();

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->coutCastars;
		$this->majBraldun();
	}

	private function constructionRoute() {
		$routeTable = new Route();
		// Suppression d'une route s'il y en a une
		$where = "x_route = ".$this->view->x_construction. " AND y_route=".$this->view->y_construction;
		$routeTable->delete($where);

		// et construction d'une route d'échoppe
		$data = array(
			"x_route" => $this->view->x_construction,
			"y_route" => $this->view->y_construction,
			"z_route" => 0,
			"id_fk_braldun_route" => null,
			"id_fk_echoppe_route" => $this->idSelection,
			"date_creation_route" => date("Y-m-d H:i:s"),
			"id_fk_type_qualite_route"  => null,
			"type_route" => "echoppe",
			"est_visible_route" => 'oui',
		);

		$idRoute = $routeTable->insert($data);
	}

	private function prepareResultatDeplacerEchoppe() {

		$this->verificationEchoppes();

		if ($this->verificationPositions() == false) {
			return;
		}

		$echoppesTable = new Echoppe();
		$data = array (
			'x_echoppe' => $this->view->x_construction,
			'y_echoppe' => $this->view->y_construction,
		);
		$where = "id_echoppe = ".intval($this->idSelection);
		$echoppesTable->update($data, $where);
		$this->view->deplacerEchoppeOk = true;

		$routeTable = new Route();
		// Suppression d'une route s'il y en a une
		$where = "x_route = ".$this->echoppeCourante["x_echoppe"]. " AND y_route=".$this->echoppeCourante["y_echoppe"];
		$routeTable->delete($where);

		$this->constructionRoute();

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->coutCastars;
		$this->majBraldun();
	}

	private function prepareResultatSupprimerEchoppe() {
		$this->verificationEchoppes();

		$echoppesTable = new Echoppe();
		$where = "id_echoppe = ".intval($this->idSelection);
		$echoppesTable->delete($where);

		//Suppression route automatique (cascade)
		$this->view->supprimerEchoppeOk = true;

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->coutCastars;
		$this->majBraldun();
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

		// on verifie que l'on est pas sur une route
		Zend_Loader::loadClass("Route");
		$routeTable = new Route();
		$routes = $routeTable->findByCaseHorsBalise($x, $y, $z);

		$this->view->construireLieuRouteOk = true;
		if (count($routes) > 0) {
			$this->view->construireLieuRouteOk = false;
			return false;
		}

		// on verifie que l'on est pas sur une eau
		Zend_Loader::loadClass("Eau");
		$eauTable = new Eau();
		$eaux = $eauTable->countByCase($x, $y, $z);

		$this->view->construireLieuEauOk = true;
		if (count($eaux) > 0) {
			$this->view->construireLieuEauOk = false;
			return false;
		}

		// on verifie que la position est dans la comté de la tentative
		$this->view->construireRegionOk = true;
		if ($this->view->regionCourante["x_min_region"] > $x || $this->view->regionCourante["x_max_region"] < $x || $this->view->regionCourante["y_min_region"] > $y || $this->view->regionCourante["y_max_region"] < $y) {
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
		if ($this->view->idTypeCourant == "acheterechoppe") {
			if ($this->view->nEchoppes < 1) {
				return 0;
			} else {
				return 500;
			}
		} elseif ($this->view->idTypeCourant == "deplacerechoppe" || $this->view->idTypeCourant == "supprimerechoppe") {
			return 500;
		} elseif ($this->view->idTypeCourant == "acheterchamp") {
			return $this->view->nChamps * 10 + 10;
		}
	}
}