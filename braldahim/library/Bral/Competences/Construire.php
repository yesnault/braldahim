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
class Bral_Competences_Construire extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Route');
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Bral_Util_Marcher');
		Zend_Loader::loadClass('Bral_Util_Quete');

		$utilMarcher = new Bral_Util_Marcher();

		$calcul = $utilMarcher->calcul($this->view->user, null, true);

		$this->view->assezDePa  = $calcul["assezDePa"];
		$this->view->nb_cases = $calcul["nb_cases"];
		$this->view->tableau = $calcul["tableau"];
		$this->tableauValidation = $calcul["tableauValidation"];

		$this->view->x_min = $calcul["x_min"];
		$this->view->x_max = $calcul["x_max"];
		$this->view->y_min = $calcul["y_min"];
		$this->view->y_max = $calcul["y_max"];

		$this->view->construireOk = false;
		$this->view->construireRouteContinueOk = false;

		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$routeTable = new Route();
		$routes = $routeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$zoneTable = new Zone();
		$zone = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($zoneTable);

		if (count($zone) == 1) {
			$case = $zone[0];
			$this->environnement = $case["nom_systeme_environnement"];
			$this->view->environnement = $case["nom_environnement"];
		} else {
			throw new Zend_Exception(get_class($this)."::calculNbPa : Nombre de case invalide");
		}
		unset($zone);

		$this->view->route = null;

		if (count($routes) > 0) {
			$this->view->route = $routes[0];
		}

		if (count($monstres) <= 0 && count($hobbits) == 1 && count($palissades) <= 0 && $this->view->route != null && $this->view->route["est_route"] == "non" && $this->estEnvironnementValid($this->environnement)) {
			$this->view->construireOk = true;

			$routesAutour = $routeTable->selectVue($this->view->user->x_hobbit - 1, $this->view->user->y_hobbit - 1, $this->view->user->x_hobbit + 1, $this->view->user->y_hobbit + 1);
			if ($routesAutour != null && count($routesAutour) > 1) {
				$this->view->construireRouteContinueOk = true;
			}
		}

	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		if ($this->view->construireOk == false) {
			throw new Zend_Exception(get_class($this)." Construire interdit");
		}

		$bmJet1 = 0;

		if ($this->view->construireRouteContinueOk == true) {
			$x_y = $this->request->get("valeur_1");
			list ($offset_x, $offset_y) = split("h", $x_y);

			if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
				throw new Zend_Exception(get_class($this)." Deplacement X impossible : ".$offset_x);
			}

			if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
				throw new Zend_Exception(get_class($this)." Deplacement Y impossible : ".$offset_y);
			}

			if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
				throw new Zend_Exception(get_class($this)." Deplacement XY impossible : ".$offset_x.$offset_y);
			}
			$bmJet1 = -10;
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculConstruire();
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeConstuire($this->view->user, $this->nom_systeme);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculConstruire() {

		$maitrise = $this->hobbit_competence["pourcentage_hcomp"] / 100;

		$chance_a = -0.375 * $maitrise + 53.75 ;
		$chance_b = 0.25 * $maitrise + 42.5 ;
		$chance_c = 0.125 * $maitrise + 3.75 ;

		$tirage = Bral_Util_De::get_1d100();

		$qualite = -1;
		if ($tirage > 0 && $tirage <= $chance_a) {
			$qualite = 1;
			$this->view->qualite = "m&eacute;diocre";
			$nbJours = $this->calculJetForce();
		} elseif ($tirage > $chance_a && $tirage <= $chance_a + $chance_b) {
			$qualite = 2;
			$this->view->qualite = "standard";
			$nbJours = $this->calculJetForce() + $this->calculJetVigueur();
		} else {
			$qualite = 3;
			$this->view->qualite = "bonne";
			$nbJours = $this->calculJetForce() + $this->calculJetVigueur() + $this->calculJetSagesse();
		}

		$date_creation = date("Y-m-d H:i:s");
		$date_fin = Bral_Util_ConvertDate::get_date_add_day_to_date($date_creation, $nbJours);

		$data = array(
			"id_fk_hobbit_route" => $this->view->user->id_hobbit,
			"est_route" => "oui",
			"date_creation_route" => $date_creation,
			"date_fin_route" => $date_fin,
			"id_fk_type_qualite_route" => $qualite,
		);
		$where = "x_route = ".$this->view->user->x_hobbit. " and y_route=".$this->view->user->y_hobbit;
		$routeTable = new Route();
		$routeTable->update($data, $where);
		unset($routeTable);

		$this->view->route = $data;

		Zend_Loader::loadClass("StatsFabricants");
		$statsFabricants = new StatsFabricants();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataFabricants["niveau_hobbit_stats_fabricants"] = $this->view->user->niveau_hobbit;
		$dataFabricants["id_fk_hobbit_stats_fabricants"] = $this->view->user->id_hobbit;
		$dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
		$dataFabricants["nb_piece_stats_fabricants"] = 1;
		$dataFabricants["id_fk_metier_stats_fabricants"] = $this->view->config->game->metier->terrassier->id;
		$statsFabricants->insertOrUpdate($dataFabricants);

		if ($this->view->construireRouteContinueOk == true) {
			$x_y = $this->request->get("valeur_1");
			list ($offset_x, $offset_y) = split("h", $x_y);

			$this->view->user->x_hobbit = $this->view->user->x_hobbit + $offset_x;
			$this->view->user->y_hobbit = $this->view->user->y_hobbit + $offset_y;
		}
		
		$this->calculGainCastars();
	}

	private function calculJetForce() {
		$jet = 0;
		for ($i=1; $i <= ($this->view->config->game->base_force + $this->view->user->force_base_hobbit) ; $i++) {
			$jet = $jet + Bral_Util_De::get_1d6();
		}
		$jet = $jet + $this->view->user->force_bm_hobbit + $this->view->user->force_bbdf_hobbit;
		if ($jet < 0) {
			$jet = 0;
		}
		return $jet;
	}

	private function calculJetVigueur() {
		$jet = 0;
		for ($i=1; $i <= ($this->view->config->game->base_vigueur + $this->view->user->vigueur_base_hobbit) ; $i++) {
			$jet = $jet + Bral_Util_De::get_1d6();
		}
		$jet = $jet + $this->view->user->vigueur_bm_hobbit + $this->view->user->vigueur_bbdf_hobbit;
		if ($jet < 0) {
			$jet = 0;
		}
		return $jet;
	}

	private function calculJetSagesse() {
		$jet = 0;
		for ($i=1; $i <= ($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit) ; $i++) {
			$jet = $jet + Bral_Util_De::get_1d6();
		}
		$jet = $jet + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;
		if ($jet < 0) {
			$jet = 0;
		}
		return $jet;
	}

	private function calculGainCastars() {

		//Le gain de castars de base est (gainBase) : (niv SAG/2) arrondi inf avec 1 comme minimum
		$gainBase = floor($this->view->user->sagesse_base_hobbit / 2);
		if ($gainBase < 1) {
			$gainBase = 1;
		}

		/**
		 * Plaine : -1
		 * Forêt : 1
		 * Montagne : 2
		 * Marais : 2
		 * Gazon 0
		 */
		switch($this->environnement) {
			case "plaine" :
				$coefT = -1;
				break;
			case "marais" :
				$coefT = 2;
				break;
			case "montagne" :
				$coefT = 2;
				break;
			case "foret" :
				$coefT = 1;
				break;
			case "caverne" :
			case "gazon" :
				$coefT = 0;
				break;
			default:
				throw new Zend_Exception(get_class($this)."::environnement invalide :".$this->environnement);
		}

		/*
		 * Si moins de 200 cases en route alors coefD = 2
		 * Si de 201 à 400 alors coefD = 0
		 * Si de 401 à 600 alors coefD = -2
		 * Si de 601 à 800 alors coefD = -4
		 * Si plus de 800 cases en route alors = -6
		 */
		$nbRoutes = $this->getNbRouteDansComte();
		if ($nbRoutes < 200) {
			$coefD = 2;
		} else if ($nbRoutes < 400) {
			$coefD = 0;
		} else if ($nbRoutes < 600) {
			$coefD = -2;
		} else if ($nbRoutes < 800) {
			$coefD = -4;
		} else {
			$coefD = -6;
		}

		$this->view->nbCastarsGagnes = $gainBase + $coefT + $coefD;
		if ($this->view->nbCastarsGagnes < 0) {
			$this->view->nbCastarsGagnes = 0;
		}

		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$this->view->nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);

		if ($this->view->nbCastarsPossible < $this->view->nbCastarsGagnes) {
			$this->view->nbCastarsLaban = $this->view->nbCastarsPossible;
			$this->view->nbCastarsATerre =  $this->view->nbCastarsGagnes - $this->view->nbCastarsLaban;

			Zend_Loader::loadClass("Element");
			$elementTable = new Element();
			$data = array(
				"quantite_castar_element" => $nb,
				"x_element" => $this->view->user->x_hobbit,
				"y_element" => $this->view->user->y_hobbit,
			);
			$elementTable->insertOrUpdate($data);
		} else {
			$this->view->nbCastarsLaban = $this->view->nbCastarsGagnes;
		}

		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit + $this->view->nbCastarsLaban;
	}

	private function estEnvironnementValid($environnement) {
		$retour = false;
		switch($environnement) {
			case "plaine" :
			case "marais" :
			case "montagne" :
			case "foret" :
			case "gazon" :
				$retour = true;
				break;
			case "caverne" :
				$retour = false;
				break;
			default:
				throw new Zend_Exception(get_class($this)."::environnement invalide :".$environnement);
		}
		return $retour;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue", "box_laban", "box_lieu"));
	}

	private function getNbRouteDansComte() {
		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$region = $regionTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		if ($region == null) {
			throw new Zend_Exception(get_class($this)."::calculNbRouteDansComte region invalide x:".$this->view->user->x_hobbit. " y:". $this->view->user->y_hobbit);
		}
		
		$routeTable = new Route();
		$nbRoutes = $routeTable->countVue($region["x_min_region"], $region["y_min_region"], $region["x_max_region"], $region["y_max_region"]);
		
		return $nbRoutes;
	}
}
