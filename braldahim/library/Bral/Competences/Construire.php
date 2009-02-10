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
	
		$this->view->construireOk = false;
		
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

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculConstruire();
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
		$this->calculEvenement();
		
		Zend_Loader::loadClass("StatsFabricants");
		$statsFabricants = new StatsFabricants();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataFabricants["niveau_hobbit_stats_fabricants"] = $this->view->user->niveau_hobbit;
		$dataFabricants["id_fk_hobbit_stats_fabricants"] = $this->view->user->id_hobbit;
		$dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
		$dataFabricants["nb_piece_stats_fabricants"] = 1;
		$dataFabricants["id_fk_metier_stats_fabricants"] = $this->view->config->game->metier->terrassier->id;
		$statsFabricants->insertOrUpdate($dataFabricants);
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
	
	private function calculEvenement() {
		$estEvenement = false;
		$evenementMinerai = null;
		
		$de = Bral_Util_De::get_1d2();
		$de10 = Bral_Util_De::get_1d10();
		if ($de == 1) {
			if ($de10 == 1) {
				$estEvenement = true;
				$evenementMinerai = $this->calculEvenementMinerai();
			}
		} else {
			if ($de10 == 1) {
				$estEvenement = true;
				$this->calculEvenementRune();
			}
		}
		
		$this->view->estEvenement = $estEvenement;
		$this->view->evenementMinerai = $evenementMinerai;
	}
	
	private function calculEvenementMinerai() {
		Zend_Loader::loadClass("ElementMinerai");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("TypeMinerai");
		
		$retour["dansLaban"] = false;
		
		$typeMinerai = new TypeMinerai();
		$types = $typeMinerai->fetchAll();
		
		$nb = count($types);
		$deType = Bral_Util_De::get_de_specifique(1, $nb);
		foreach ($types as $t) {
			if ($t["id_type_minerai"] == $deType) {
				$retour["typeMinerai"] = $t["nom_type_minerai"];
				break;
			}
		}
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbMineraisPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_MINERAI);
		
		if ($nbMineraisPossible >= 1) { // depot dans le laban
			$labanMineraiTable = new LabanMinerai();
			$data = array(
				"quantite_brut_laban_minerai" => 1,
				"id_fk_type_laban_minerai" => $deType,
				"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
			);
			$labanMineraiTable->insertOrUpdate($data);
			$retour["dansLaban"] = true;
		} else { // depot a terre
			$elementMineraiTable = new ElementMinerai();
			$data = array (
				"x_element_minerai" => $this->view->user->x_hobbit,
				"y_element_minerai" => $this->view->user->y_hobbit,
				"id_fk_type_element_minerai" => $deType,
				"quantite_brut_element_minerai" => 1,
			);
			$elementMineraiTable->insertOrUpdate($data);
			$retour["dansLaban"] = false;
		}
		
		return $retour;
	}
	
	private function calculEvenementRune() {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Bral_Monstres_VieMonstre::dropRune($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->niveau_hobbit);
	}
	
	private function estEnvironnementValid($environnement) {
		$retour = false;
		switch($environnement) {
			case "plaine" :
			case "marais" :
			case "montagne" :
			case "foret" :
				$retour = true;
				break;
			case "caverne" :
			case "gazon" :
				$retour = false;
				break;
			default:
				throw new Zend_Exception(get_class($this)."::environnement invalide :".$this->nom_systeme_environnement);
		}
		return $retour;
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue", "box_laban"));
	}
}
