<?php

class Bral_Box_Equipement {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "Equipement";
	}
	
	function getNomInterne() {
		return "box_equipement";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		Zend_Loader::loadClass("TypeEmplacement");
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("EquipementRune");
		
		// on va chercher les emplacements
		$tabTypesEmplacement = null;
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacement = $typeEmplacementTable->fetchAll(null, "ordre_emplacement");
		$typesEmplacement = $typesEmplacement->toArray();
		
		foreach ($typesEmplacement as $t) {
			$affiche = "oui";
			$position = "gauche";
			if ($t["nom_systeme_type_emplacement"] == "deuxmains" ||
				$t["nom_systeme_type_emplacement"] == "main" ||
				$t["nom_systeme_type_emplacement"] == "maingauche" ||
				$t["nom_systeme_type_emplacement"] == "maindroite") {
				$affiche = "non";
				$position = "droite";
			}
			
			$tabTypesEmplacement[$t["nom_systeme_type_emplacement"]] = array(
					"id_type_emplacement" => $t["id_type_emplacement"],
					"nom_type_emplacement" => $t["nom_type_emplacement"],
					"ordre_emplacement" => $t["ordre_emplacement"],
					"equipementPorte" => null,
					"affiche" => $affiche,
					"position" => $position,
			);
		}
		
		// on va chercher l'équipement porté
		$tabEquipementPorte = null;
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementPorteRowset = $hobbitEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($equipementPorteRowset) > 0) {
			$tabWhere = null;
			$equipementRuneTable = new EquipementRune();
			$equipements = null;
			
			$idEquipements = null;
			
			foreach ($equipementPorteRowset as $e) {
				$idEquipements[] = $e["id_equipement_hequipement"];
			}
			
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);
			
			foreach ($equipementPorteRowset as $e) {
				$runes = null;
				if (count($equipementRunes) > 0) {
					foreach($equipementRunes as $r) {
						if ($r["id_equipement_rune"] == $e["id_equipement_hequipement"]) {
							$runes[] = array(
							"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
							"id_fk_type_rune_equipement_rune" => $r["id_fk_type_rune_equipement_rune"],
							"nom_type_rune" => $r["nom_type_rune"],
							"image_type_rune" => $r["image_type_rune"],
							);
						}
					}
				}
			
				$equipement = array(
						"id_equipement" => $e["id_equipement_hequipement"],
						"nom" => $e["nom_type_equipement"],
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_recette_equipement"],
						"id_type_emplacement" => $e["id_type_emplacement"],
						"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
						"nb_runes" => $e["nb_runes_hequipement"],
						"id_fk_recette_equipement" => $e["id_fk_recette_hequipement"],
						"armure" => $e["armure_recette_equipement"],
						"force" => $e["force_recette_equipement"],
						"agilite" => $e["agilite_recette_equipement"],
						"vigueur" => $e["vigueur_recette_equipement"],
						"sagesse" => $e["sagesse_recette_equipement"],
						"vue" => $e["vue_recette_equipement"],
						"bm_attaque" => $e["bm_attaque_recette_equipement"],
						"bm_degat" => $e["bm_degat_recette_equipement"],
						"bm_defense" => $e["bm_defense_recette_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"runes" => $runes,
				);
				$this->equipementPorte[] = $equipement;
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["affiche"] = "oui";
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementPorte"][] = $equipement;
			}
		}
		$this->view->typesEmplacement = $tabTypesEmplacement;
		
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/equipement.phtml");
	}
}
?>