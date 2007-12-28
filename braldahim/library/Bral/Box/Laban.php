<?php

class Bral_Box_Laban {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass('Laban');
		Zend_Loader::loadClass('LabanEquipement');
		Zend_Loader::loadClass('LabanMinerai');
		Zend_Loader::loadClass('LabanPartieplante');
		Zend_Loader::loadClass('LabanRune');
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}

	function getTitreOnglet() {
		return "Laban";
	}

	function getNomInterne() {
		return "box_laban";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$tabPartiePlantes = null;
		$tabPartiePlantesPreparees = null;
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);

		foreach ($partiePlantes as $p) {
			if ($p["quantite_laban_partieplante"] > 0) {
				$tabPartiePlantes[] = array(
				"nom_type" => $p["nom_type_partieplante"],
				"nom_plante" => $p["nom_type_plante"],
				"quantite" => $p["quantite_laban_partieplante"],
				);
			}
			if ($p["quantite_preparee_laban_partieplante"] > 0) {
				$tabPartiePlantesPreparees[] = array(
				"nom_type" => $p["nom_type_partieplante"],
				"nom_plante" => $p["nom_type_plante"],
				"quantite" => $p["quantite_preparee_laban_partieplante"],
				);
			}
		}

		$tabMinerais = null;
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);

		foreach ($minerais as $m) {
			$tabMinerais[] = array(
			"type" => $m["nom_type_minerai"],
			"quantite" => $m["quantite_laban_minerai"],
			);
		}

		$tabLaban = null;
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		foreach ($laban as $p) {
			$tabLaban = array(
			"nb_peau" => $p["quantite_peau_laban"],
			"nb_viande" => $p["quantite_viande_laban"],
			"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
			"nb_ration" => $p["quantite_ration_laban"],
			"nb_cuir" => $p["quantite_cuir_laban"],
			"nb_fourrure" => $p["quantite_fourrure_laban"],
			"nb_planche" => $p["quantite_planche_laban"],
			);
		}
		
		$tabRunes = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);

		foreach ($runes as $r) {
			$tabRunes[] = array(
			"id_rune" => $r["id_rune_laban_rune"],
			"type" => $r["nom_type_rune"],
			"image" => $r["image_type_rune"],
			"est_identifiee" => $r["est_identifiee_rune"]
			);
		}
		
		$this->view->nb_partieplantes = count($tabPartiePlantes);
		$this->view->partieplantes = $tabPartiePlantes;
		$this->view->nb_partieplantesPreparees = count($tabPartiePlantesPreparees);
		$this->view->partieplantesPreparees = $tabPartiePlantesPreparees;
		$this->view->nb_minerais = count($tabMinerais);
		$this->view->minerais = $tabMinerais;
		$this->view->nb_runes = count($tabRunes);
		$this->view->runes = $tabRunes;
		$this->view->laban = $tabLaban;
		$this->view->nom_interne = $this->getNomInterne();
		
		$this->renderEquipement();
		return $this->view->render("interface/laban.phtml");
	}
	
	private function renderEquipement() {
		$tabEquipements = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabWhere = null;
		foreach ($equipements as $e) {
			$tabEquipements[$e["id_laban_equipement"]] = array(
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_laban_equipement"],
					"runes" => array(),
			);
			$tabWhere[] = $e["id_laban_equipement"];
		}
		
		if ($tabWhere != null) {
			Zend_Loader::loadClass("EquipementRune");
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($tabWhere);
			
			foreach($equipementRunes as $e) {
				$tabEquipements[$e["id_equipement_rune"]]["runes"][] = array(
				"id_rune_equipement_rune" => $e["id_rune_equipement_rune"],
				"id_fk_type_rune_equipement_rune" => $e["id_fk_type_rune_equipement_rune"],
				"nom_type_rune" => $e["nom_type_rune"],
				"image_type_rune" => $e["image_type_rune"],
				);
			}
			
			
		}
		
		$this->view->nb_equipements = count($tabEquipements);
		$this->view->equipements = $tabEquipements;
	
	}
}
