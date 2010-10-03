<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Hotel_Voir extends Bral_Hotel_Hotel {

	function getNomInterne() {
		return $this->box_lieu;
	}

	public function getTitreAction() {
		return null;
	}

	function render() {
		Zend_Loader::loadClass("Bral_Helper_DetailEquipement");
		Zend_Loader::loadClass("Bral_Helper_DetailHotel");
		Zend_Loader::loadClass("Bral_Helper_DetailMateriel");
		Zend_Loader::loadClass("Bral_Helper_DetailRune");

		if ($this->box_lieu == "box_hotel_resultats") {
			return $this->view->render("hotel/voir/resultats.phtml");
		} else {
			return $this->view->render("hotel/voir.phtml");
		}
	}

	function prepareCommun() {
		$this->prepare();
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}

	private function prepare() {
		$tabResultats = null;

		$this->box_lieu = "box_hotel_resultats";
		$this->view->venteDefaut = false;

		if ($this->request->get("hotel_menu_recherche_equipements") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_equipements"));
			$this->prepareMenuEquipements();
			$tabResultats = $this->prepareRechercheEquipement($numeroElement);
		} else if ($this->request->get("hotel_menu_recherche_munitions") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_munitions"));
			$this->prepareMenuMunitions();
			$tabResultats = $this->prepareRechercheMunition($numeroElement);
		} else if ($this->request->get("hotel_menu_recherche_materiels") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_materiels"));
			$this->prepareMenuMateriels();
			$tabResultats = $this->prepareRechercheMateriel($numeroElement);
		} else if ($this->request->get("hotel_menu_recherche_matieres_premieres") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_matieres_premieres"));
			$this->prepareMenusMatieres();
			$tabResultats = $this->prepareRechercheMatieres($numeroElement, "matieres_premieres");
		} else if ($this->request->get("hotel_menu_recherche_matieres_transformees") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_matieres_transformees"));
			$this->prepareMenusMatieres();
			$tabResultats = $this->prepareRechercheMatieres($numeroElement, "matieres_transformees");
		} else if ($this->request->get("hotel_menu_recherche_aliments_ingredients") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_aliments_ingredients"));
			$this->prepareMenuAlimentsIngredients();
			if ($numeroElement <= $this->numeroElementFinAliment) {
				$tabResultats = $this->prepareRechercheAliment($numeroElement);
			} else {
				$tabResultats = $this->prepareRechercheIngredient($numeroElement);
			}
		} else if ($this->request->get("hotel_menu_recherche_potions") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_potions"));
			$this->prepareMenuPotions();
			$tabResultats = $this->prepareRecherchePotion($numeroElement);
		} else if ($this->request->get("hotel_menu_recherche_runes") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_runes"));
			$this->prepareMenuRunes();
			$tabResultats = $this->prepareRechercheRune($numeroElement);
		} else if ($this->request->get("hotel_menu_recherche_pratique") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_pratique"));
			$this->prepareMenuDefaut();
			$tabResultats = $this->prepareVentesDefaut($numeroElement);
		} else {
			$this->view->venteDefaut = true;
			$this->prepareMenuDefaut();
			$tabResultats = $this->prepareVentesDefaut();
			$this->box_lieu = "box_lieu";
		}

		$this->view->resultats = $tabResultats;
	}

	private function prepareVentesDefaut($numeroElement = null) {
		Zend_Loader::loadClass("Vente");
		$venteTable = new Vente();

		if ($numeroElement == 2) {
			$ventes = $venteTable->findATerme(50);
		} else {
			$ventes = $venteTable->findDernieres(50);
		}

		$avecEquipements = false;
		$avecElements = false;
		$avecMunitions = false;
		$avecAliments = false;
		$avecIngredients = false;
		$avecGraines = false;
		$avecPotions = false;
		$avecRunes = false;
		$avecMateriels = false;
		$avecMinerais = false;
		$avecPlantes = false;
		foreach ($ventes as $e) {
			$idVentes[] = $e["id_vente"];
			if ($e["type_vente"] == "equipement") {
				$avecEquipements = true;
			} elseif ($e["type_vente"] == "munition") {
				$avecMunitions = true;
			} elseif ($e["type_vente"] == "element") {
				$avecElements = true;
			} elseif ($e["type_vente"] == "aliment") {
				$avecAliments = true;
			} elseif ($e["type_vente"] == "ingredient") {
				$avecIngredients = true;
			} elseif ($e["type_vente"] == "potion") {
				$avecPotions = true;
			} elseif ($e["type_vente"] == "rune") {
				$avecRunes = true;
			} elseif ($e["type_vente"] == "materiel") {
				$avecMateriels = true;
			} elseif ($e["type_vente"] == "minerai") {
				$avecMinerais = true;
			} elseif ($e["type_vente"] == "partieplante") {
				$avecPlantes = true;
			} elseif ($e["type_vente"] == "graine") {
				$avecGraines = true;
			}
		}

		$tabResultats = array();
		if ($avecEquipements) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheEquipement(null, $idVentes));
		}

		if ($avecMunitions) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheMunition(null, $idVentes));
		}

		if ($avecElements) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheMatieres(null, "element", $idVentes));
		}

		if ($avecMinerais) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheMatieres(null, "minerai", $idVentes));
		}

		if ($avecPlantes) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheMatieres(null, "partieplante", $idVentes));
		}

		if ($avecAliments) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheAliment(null, $idVentes));
		}

		if ($avecIngredients) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheIngredient(null, $idVentes));
		}

		if ($avecGraines) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheGraine(null, "graine", $idVentes));
		}

		if ($avecPotions) {
			$tabResultats = array_merge($tabResultats, $this->prepareRecherchePotion(null, $idVentes));
		}

		if ($avecRunes) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheRune(null, $idVentes));
		}

		if ($avecMateriels) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheMateriel(null, $idVentes));
		}

		return $tabResultats;
	}

	private function prepareRechercheMatieres($numeroElement, $type, $idsVente = null) {

		$tabReturn = array();
		if ($numeroElement >= $this->numeroElementPemiereMatiereAutre || $type == "element") {
			$tabReturn = $this->prepareRechercheElement($numeroElement, $type, $idsVente);
		} else if ($numeroElement >= $this->numeroElementPemiereMatiereGraine || $type == "graine") {
			$tabReturn = $this->prepareRechercheGraine($numeroElement, $type, $idsVente);
		} elseif ($numeroElement >= $this->numeroElementPemiereMatierePlante || $type == "partieplante") {
			$tabReturn = $this->prepareRecherchePartieplante($numeroElement, $type, $idsVente);
		} else if ($numeroElement < $this->numeroElementPemiereMatierePlante || $type == "minerai") {
			$tabReturn = $this->prepareRechercheMinerai($numeroElement, $type, $idsVente);
		}

		return $tabReturn;
	}

	private function prepareRechercheMinerai($numeroElement, $type, $idsVente = null) {
		Zend_Loader::loadClass("VenteMinerai");

		$venteMineraiTable = new VenteMinerai();
		if ($idsVente != null) {
			$minerais = $venteMineraiTable->findByIdVente($idsVente);
		} else {
			$typeMinerai = null;
			if ($type == "matieres_premieres") {
				$typeMinerai = $this->view->menuRechercheMatieresPremieres["minerais"]["elements"][$numeroElement]["id_type_minerai"];
			} elseif ($type == "matieres_transformees") {
				$typeMinerai = $this->view->menuRechercheMatieresTransformees["minerais"]["elements"][$numeroElement]["id_type_minerai"];
			}
			if ($numeroElement < $this->numeroElementPemiereMatierePlante && $typeMinerai != null) {
				$minerais = $venteMineraiTable->findByIdType($typeMinerai);
			}
		}

		$tabReturn = array();

		$idMinerais = null;
		$idVentes = null;
		if ($minerais != null) {
			foreach ($minerais as $e) {
				$idMinerais[] = $e["id_vente_minerai"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idMinerais != null && count($idMinerais) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($minerais) > 0) {
			foreach($minerais as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$s = "";
				if ($e["quantite_vente_minerai"] > 1) {
					$s = "s";
				}

				if ($e["type_vente_minerai"] == "lingot") {
					$nom = $e["nom_type_minerai"]. " : ".$e["quantite_vente_minerai"]. " lingot".$s;
					$image = "type_minerai_".$e["id_type_minerai"]."_p";
				} else {
					$nom = $e["nom_type_minerai"]. " : ".$e["quantite_vente_minerai"]. " minerai".$s. " brut".$s;
					$image = "type_minerai_".$e["id_type_minerai"];
				}
					
				$tabObjet = array(
					"id_minerai" => $e["id_vente_minerai"],
					"image" => $image,
					"nom" => $nom,
				);

				$tabReturn[] = array(
					"type" => "minerai",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private function prepareRecherchePartieplante($numeroElement, $type, $idsVente = null) {
		Zend_Loader::loadClass("VentePartieplante");

		$ventePartieplanteTable = new VentePartieplante();
		if ($idsVente != null) {
			$elements = $ventePartieplanteTable->findByIdVente($idsVente);
		} else {
			$typePlante = null;
			$typePartiePlante = null;
			if ($type == "matieres_premieres") {
				$typePlante = $this->view->menuRechercheMatieresPremieres["plantes"]["elements"][$numeroElement]["id_type_plante"];
				$typePartiePlante = $this->view->menuRechercheMatieresPremieres["plantes"]["elements"][$numeroElement]["id_type_partieplante"];
			} elseif ($type == "matieres_transformees") {
				$typePlante = $this->view->menuRechercheMatieresTransformees["plantes"]["elements"][$numeroElement]["id_type_plante"];
				$typePartiePlante = $this->view->menuRechercheMatieresTransformees["plantes"]["elements"][$numeroElement]["id_type_partieplante"];
			}
			if ($numeroElement < $this->numeroElementPemiereMatiereGraine && $typePlante != null && $typePartiePlante != null) {
				$elements = $ventePartieplanteTable->findByIdType($typePlante, $typePartiePlante);
			}
		}

		$tabReturn = array();

		$idPartieplantes = null;
		$idVentes = null;
		if ($elements != null) {
			foreach ($elements as $e) {
				$idPartieplantes[] = $e["id_vente_partieplante"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idPartieplantes != null && count($idPartieplantes) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($elements) > 0) {
			foreach($elements as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$s = "";
				if ($e["quantite_vente_partieplante"] > 1) {
					$s = "s";
				}

				if ($e["type_vente_partieplante"] == "preparee") {
					$nom = $e["quantite_vente_partieplante"]. " ".$e["nom_type_partieplante"].$s." preparée".$s." ".$e["prefix_type_plante"].$e["nom_type_plante"];
					$image = "type_partieplante_".$e["id_type_partieplante"]."_p";
				} else {
					$nom = $e["quantite_vente_partieplante"]. " ".$e["nom_type_partieplante"].$s." brute".$s." ".$e["prefix_type_plante"].$e["nom_type_plante"];
					$image = "type_partieplante_".$e["id_type_partieplante"];
				}
					
				$tabObjet = array(
					"id_element" => $e["id_vente_partieplante"],
					"nom" => $nom,
					"image" => $image,
				);

				$tabReturn[] = array(
					"type" => "partieplante",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private function prepareRechercheGraine($numeroElement, $type, $idsVente = null) {
		Zend_Loader::loadClass("VenteGraine");

		$venteGraineTable = new VenteGraine();
		$graines = null;
		if ($idsVente != null) {
			$graines = $venteGraineTable->findByIdVente($idsVente);
		} else {
			$typeGraine = null;
			if ($type == "graine") {
				$typeGraine = $this->view->menuRechercheMatieresPremieres["graines"]["elements"][$numeroElement]["id_type_graine"];
			}

			$typeGraine = $this->view->menuRechercheMatieresPremieres["graines"]["elements"][$numeroElement]["id_type_graine"];
			if ($numeroElement < $this->numeroElementPemiereMatiereAutre && $typeGraine != null) {
				$graines = $venteGraineTable->findByIdType($typeGraine);
			}
		}

		$tabReturn = array();

		$idGraines = null;
		$idVentes = null;
		if ($graines != null) {
			foreach ($graines as $e) {
				$idGraines[] = $e["id_vente_graine"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idGraines != null && count($idGraines) > 0) {
			Zend_Loader::loadClass("VentePrixGraine");
			$ventePrixGraineTable = new VentePrixGraine();
			$ventePrixGraine = $ventePrixGraineTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($graines) > 0) {
			foreach($graines as $e) {

				$minerais = $this->recuperePrixMineraiAvecIdVente($ventePrixGraine, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$s = "";
				if ($e["quantite_vente_graine"] > 1) {
					$s = "s";
				}

				$nom = $e["nom_type_graine"]. " : ".$e["quantite_vente_graine"]. " poignée".$s. " de graines";
				$image = "type_graine_".$e["id_type_graine"];

				$tabObjet = array(
					"id_graine" => $e["id_vente_graine"],
					"image" => $image,
					"nom" => $nom,
				);

				$tabReturn[] = array(
					"type" => "graine",
					"vente" => $this->prepareRowVente($e, $minerais, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private function prepareRechercheIngredient($numeroElement, $idsVente = null) {
		Zend_Loader::loadClass("VenteIngredient");

		$venteIngredientTable = new VenteIngredient();
		if ($idsVente != null) {
			$ingredients = $venteIngredientTable->findByIdVente($idsVente);
		} else {
			$ingredients = $venteIngredientTable->findByIdType($this->view->menuRechercheAlimentsIngredients["ingredients"]["elements"][$numeroElement]["id_type_ingredient"]);
		}

		$tabReturn = array();

		$idIngredients = null;
		$idVentes = null;
		if ($ingredients != null) {
			foreach ($ingredients as $e) {
				$idIngredients[] = $e["id_vente_ingredient"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idIngredients != null && count($idIngredients) > 0) {
			Zend_Loader::loadClass("VentePrixIngredient");
			$ventePrixIngredientTable = new VentePrixIngredient();
			$ventePrixIngredient = $ventePrixIngredientTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($ingredients) > 0) {
			foreach($ingredients as $e) {

				$minerais = $this->recuperePrixMineraiAvecIdVente($ventePrixIngredient, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$s = "";
				if ($e["quantite_vente_ingredient"] > 1) {
					$s = "s";
				}

				$nom = $e["nom_type_ingredient"]. " : ".$e["quantite_vente_ingredient"]. " élément".$s;
				$image = "type_ingredient_".$e["id_type_ingredient"];

				$tabObjet = array(
					"id_ingredient" => $e["id_vente_ingredient"],
					"image" => $image,
					"nom" => $nom,
				);

				$tabReturn[] = array(
					"type" => "ingredient",
					"vente" => $this->prepareRowVente($e, $minerais, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private function prepareRechercheElement($numeroElement, $type, $idsVente = null) {
		Zend_Loader::loadClass("VenteElement");

		$venteElementTable = new VenteElement();
		if ($idsVente != null) {
			$elements = $venteElementTable->findByIdVente($idsVente);
		} else {
			$typeElement = null;
			if ($type == "matieres_premieres") {
				$typeElement = $this->view->menuRechercheMatieresPremieres["autres"]["elements"][$numeroElement]["type_element"];
			} elseif ($type == "matieres_transformees") {
				$typeElement = $this->view->menuRechercheMatieresTransformees["autres"]["elements"][$numeroElement]["type_element"];
			}
			if ($numeroElement >= $this->numeroElementPemiereMatiereAutre && $typeElement != null) {
				$elements = $venteElementTable->findByType($typeElement);
			}
		}

		$tabReturn = array();

		$idElements = null;
		$idVentes = null;
		if ($elements != null) {
			foreach ($elements as $e) {
				$idElements[] = $e["id_vente_element"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idElements != null && count($idElements) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($elements) > 0) {
			foreach($elements as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$nom = $e["quantite_vente_element"]. " " .$this->getNomElement($e["quantite_vente_element"], $e["type_vente_element"]);

				$image = $e["type_vente_element"];

				$tabObjet = array(
					"id_element" => $e["id_vente_element"],
					"nom" => $nom,
					"image" => $image,
				);

				$tabReturn[] = array(
					"type" => "element",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private function prepareRechercheEquipement($numeroElement, $idsVente = null) {
		Zend_Loader::loadClass("VenteEquipement");
		Zend_Loader::loadClass("Bral_Util_Equipement");

		$venteEquipementTable = new VenteEquipement();

		if ($idsVente != null) {
			$equipements = $venteEquipementTable->findByIdVente($idsVente);
		} elseif ($numeroElement <= $this->numeroFinEmplacement) {
			$equipements = $venteEquipementTable->findAllByIdTypeEmplacement($this->view->menuRechercheEquipement["type_emplacement"]["elements"][$numeroElement]["id_type_emplacement"]);
		} else {
			$equipements = $venteEquipementTable->findAllByIdTypeEquipement($this->view->menuRechercheEquipement["type_equipement"]["elements"][$numeroElement]["id_type_equipement"]);
		}

		$tabReturn = array();

		$idEquipements = null;
		$idVentes = null;
		if ($equipements != null) {
			foreach ($equipements as $e) {
				$idEquipements[] = $e["id_vente_equipement"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idEquipements != null && count($idEquipements) > 0) {
			Zend_Loader::loadClass("EquipementRune");
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

			Zend_Loader::loadClass("EquipementBonus");
			$equipementBonusTable = new EquipementBonus();
			$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);

			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($equipements) > 0) {
			foreach($equipements as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$runes = null;
				if (count($equipementRunes) > 0) {
					foreach($equipementRunes as $r) {
						if ($r["id_equipement_rune"] == $e["id_vente_equipement"]) {
							$runes[] = array(
								"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
								"id_fk_type_rune" => $r["id_fk_type_rune"],
								"nom_type_rune" => $r["nom_type_rune"],
								"image_type_rune" => $r["image_type_rune"],
								"effet_type_rune" => $r["effet_type_rune"],
							);
						}
					}
				}

				$bonus = null;
				if (count($equipementBonus) > 0) {
					foreach($equipementBonus as $b) {
						if ($b["id_equipement_bonus"] == $e["id_vente_equipement"]) {
							$bonus = $b;
							break;
						}
					}
				}

				$tabObjet = array(
					"id_equipement" => $e["id_vente_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"id_type_equipement" => $e["id_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"emplacement" => $e["nom_type_emplacement"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nb_runes" => $e["nb_runes_equipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
					"armure" => $e["armure_equipement"],
					"force" => $e["force_equipement"],
					"agilite" => $e["agilite_equipement"],
					"vigueur" => $e["vigueur_equipement"],
					"sagesse" => $e["sagesse_equipement"],
					"vue" => $e["vue_recette_equipement"],
					"attaque" => $e["attaque_equipement"],
					"degat" => $e["degat_equipement"],
					"defense" => $e["defense_equipement"],
					"poids" => $e["poids_equipement"],
					"suffixe" => $e["suffixe_mot_runique"],
					"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"],
					"id_fk_region" => $e["id_fk_region_equipement"],
					"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
					"etat_courant" => $e["etat_courant_equipement"],
					"etat_initial" => $e["etat_initial_equipement"],
					"ingredient" => $e["nom_type_ingredient"],
					"runes" => $runes,
					"bonus" => $bonus,
				);

				$tabReturn[] = array(
					"type" => "equipement",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}

		return $tabReturn;
	}

	private function prepareRechercheMunition($numeroElement, $idsVente = null) {
		Zend_Loader::loadClass("VenteMunition");
		Zend_Loader::loadClass("Bral_Util_Equipement");

		$venteMunitionTable = new VenteMunition();

		if ($idsVente != null) {
			$munitions = $venteMunitionTable->findByIdVente($idsVente);
		} else {
			$munitions = $venteMunitionTable->findAllByIdTypeMunition($this->view->menuRechercheMunition[$numeroElement]["id_type_munition"]);
		}

		$tabReturn = array();

		$idMunitions = null;
		$idVentes = null;
		if ($munitions != null) {
			foreach ($munitions as $e) {
				$idMunitions[] = $e["id_vente_munition"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idMunitions != null && count($idMunitions) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($munitions) > 0) {
			foreach($munitions as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$nom = $e["quantite_vente_munition"]. " ";
				if ($e["quantite_vente_munition"] <= 1) {
					$nom .= $e["nom_type_munition"];
				} else {
					$nom .= $e["nom_pluriel_type_munition"];
				}

				$tabObjet = array(
					"id_munition" => $e["id_vente_munition"],
					"nom" => $nom,
					"id_type_munition" => $e["id_type_munition"],
				);

				$tabReturn[] = array(
					"type" => "munition",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);
			}
		}

		return $tabReturn;
	}

	private function prepareRechercheAliment($numeroAliment, $idsVente = null) {
		Zend_Loader::loadClass("VenteAliment");

		$venteAlimentTable = new VenteAliment();
		if ($idsVente != null) {
			$aliments = $venteAlimentTable->findByIdVente($idsVente);
		} else {
			$aliments = $venteAlimentTable->findByIdType($this->view->menuRechercheAlimentsIngredients["aliments"]["elements"][$numeroAliment]["id_type_aliment"]);
		}

		$tabReturn = array();

		$idAliments = null;
		$idVentes = null;
		if ($aliments != null) {
			foreach ($aliments as $e) {
				$idAliments[] = $e["id_vente_aliment"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idAliments != null && count($idAliments) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($aliments) > 0) {
			Zend_Loader::loadClass("Bral_Util_Aliment");
			foreach($aliments as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$tabAliment = array(
					"id_vente_aliment" => $e["id_vente_aliment"],
					"id_type_aliment" => $e["id_type_aliment"],
					"nom" => $e["nom_type_aliment"],
					"bbdf" => $e["bbdf_aliment"],
					"qualite" => $e["nom_aliment_type_qualite"],
					"recette" => Bral_Util_Aliment::getNomType($e["type_bbdf_type_aliment"]),
				);

				if (array_key_exists($e["id_vente"], $tabReturn)) {
					$tabReturn[$e["id_vente"]]["objet"][] = $tabAliment;
				} else {
					$tabObjet = null;
					$tabObjet[] = $tabAliment;

					$tabReturn[$e["id_vente"]] = array(
					"type" => "aliment",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
					);
				}

			}
		}
		return $tabReturn;
	}


	private function prepareRechercheMateriel($numeroMateriel, $idsVente = null) {
		Zend_Loader::loadClass("VenteMateriel");

		$venteMaterielTable = new VenteMateriel();
		if ($idsVente != null) {
			$materiels = $venteMaterielTable->findByIdVente($idsVente);
		} else {
			$materiels = $venteMaterielTable->findByIdType($this->view->menuRechercheMateriel[$numeroMateriel]["id_type_materiel"]);
		}

		$tabReturn = array();

		$idMateriels = null;
		$idVentes = null;
		if ($materiels != null) {
			foreach ($materiels as $e) {
				$idMateriels[] = $e["id_vente_materiel"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idMateriels != null && count($idMateriels) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($materiels) > 0) {
			foreach($materiels as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$tabMateriel = array(
					"id_vente_materiel" => $e["id_vente_materiel"],
					"id_type_materiel" => $e["id_type_materiel"],
					"nom" => $e["nom_type_materiel"],
					"id_materiel" => $e["id_vente_materiel"],
					"id_type_materiel" => $e["id_type_materiel"],
					'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
					'capacite' => $e["capacite_type_materiel"], 
					'durabilite' => $e["durabilite_type_materiel"], 
					'usure' => $e["usure_type_materiel"], 
					'poids' => $e["poids_type_materiel"], 
				);

				if (array_key_exists($e["id_vente"], $tabReturn)) {
					$tabReturn[$e["id_vente"]]["objet"][] = $tabMateriel;
				} else {
					$tabObjet = null;
					$tabObjet[] = $tabMateriel;

					$tabReturn[$e["id_vente"]] = array(
					"type" => "materiel",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
					);
				}

			}
		}
		return $tabReturn;
	}

	private function prepareRechercheRune($numeroRune, $idsVente = null) {
		Zend_Loader::loadClass("VenteRune");

		$venteRuneTable = new VenteRune();
		if ($idsVente != null) {
			$runes = $venteRuneTable->findByIdVente($idsVente);
		} else {
			if ($this->view->menuRechercheRune[$numeroRune]["id_type_rune"] != -1) {
				$runes = $venteRuneTable->findByIdType($this->view->menuRechercheRune[$numeroRune]["id_type_rune"]);
			} else {
				$runes = $venteRuneTable->findNonIdentifiee();
			}
		}

		$tabReturn = array();

		$idRunes = null;
		$idVentes = null;
		if ($runes != null) {
			foreach ($runes as $e) {
				$idRunes[] = $e["id_rune_vente_rune"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idRunes != null && count($idRunes) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($runes) > 0) {
			foreach($runes as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				if ($e['est_identifiee_rune'] == "oui") {
					$nom = $e["nom_type_rune"];
					$image = $e["image_type_rune"];
				} else {
					$nom = "Rune non identifiée";
					$image = "rune_inconnue.png";
				}

				$tabRune = array(
					"id_rune" => $e["id_rune_vente_rune"],
					"id_type_rune" => $e["id_type_rune"],
					"nom" => $nom,
					"image" => $image,
					"est_identifiee" => $e['est_identifiee_rune'],
					"effet_type_rune" => $e["effet_type_rune"],
					"type" => $e["nom_type_rune"],
				);

				if (array_key_exists($e["id_vente"], $tabReturn)) {
					$tabReturn[$e["id_vente"]]["objet"][] = $tabRune;
				} else {
					$tabObjet = null;
					$tabObjet[] = $tabRune;

					$tabReturn[$e["id_vente"]] = array(
						"type" => "rune",
						"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
						"objet" => $tabObjet,
					);
				}

			}
		}
		return $tabReturn;
	}

	private function prepareRecherchePotion($numeroPotion, $idsVente = null) {
		Zend_Loader::loadClass("VentePotion");

		$ventePotionTable = new VentePotion();
		if ($idsVente != null) {
			$potions = $ventePotionTable->findByIdVente($idsVente);
		} else {
			$potions = $ventePotionTable->findByIdType($this->view->menuRecherchePotion[$numeroPotion]["id_type_potion"]);
		}

		$tabReturn = array();

		$idPotions = null;
		$idVentes = null;
		if ($potions != null) {
			foreach ($potions as $e) {
				$idPotions[] = $e["id_vente_potion"];
				$idVentes[] = $e["id_vente"];
			}
		}

		if ($idPotions != null && count($idPotions) > 0) {
			Zend_Loader::loadClass("VentePrixMinerai");
			$ventePrixMineraiTable = new VentePrixMinerai();
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		Zend_Loader::loadClass("Bral_Util_Potion");
		Zend_Loader::loadClass("Bral_Helper_DetailPotion");
		if (count($potions) > 0) {
			foreach($potions as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$tabPotion = array(
					"id_vente_potion" => $e["id_vente_potion"],
					"id_type_potion" => $e["id_type_potion"],
					"nom" => $e["nom_type_potion"],
					"id_potion" => $e["id_vente_potion"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_potion"],
					"caracteristique" => $e["caract_type_potion"],
					"bm_type" => $e["bm_type_potion"],
					"caracteristique2" => $e["caract2_type_potion"],
					"bm2_type" => $e["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($e["type_potion"]),
				);

				if (array_key_exists($e["id_vente"], $tabReturn)) {
					$tabReturn[$e["id_vente"]]["objet"][] = $tabPotion;
				} else {
					$tabObjet = null;
					$tabObjet[] = $tabPotion;

					$tabReturn[$e["id_vente"]] = array(
					"type" => "potion",
					"vente" => $this->prepareRowVente($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
					);
				}

			}
		}
		return $tabReturn;
	}

	private function recuperePrixMineraiAvecIdVente($ventePrixMinerai, $idVente) {
		$minerai = null;
		if (count($ventePrixMinerai) > 0) {
			foreach($ventePrixMinerai as $r) {
				if ($r["id_fk_vente_prix_minerai"] == $idVente) {
					$minerai[] = array(
								"prix_vente_prix_minerai" => $r["prix_vente_prix_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
					);
				}
			}
		}
		return $minerai;
	}

	private function recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $idVente) {
		$partiesPlantes = null;
		if (count($ventePrixPartiePlante) > 0) {
			foreach($ventePrixPartiePlante as $p) {
				if ($p["id_fk_vente_prix_partieplante"] == $idVente) {
					$partiesPlantes[] = array(
								"prix_vente_prix_partieplante" => $p["prix_vente_prix_partieplante"],
								"nom_type_plante" => $p["nom_type_plante"],
								"nom_type_partieplante" => $p["nom_type_partieplante"],
								"prefix_type_plante" => $p["prefix_type_plante"],
					);
				}
			}
		}
		return $partiesPlantes;
	}

	private function prepareRowVente($r, $minerai, $partiesPlantes) {

		$tab = array("id_vente" => $r["id_vente"],
			 	"id_braldun" => $r["id_braldun"],
				"prenom_braldun" => $r["prenom_braldun"],
				"nom_braldun" => $r["nom_braldun"],
				"unite_1_vente" => $r["unite_1_vente"],
				"unite_2_vente" => $r["unite_2_vente"],
				"unite_3_vente" => $r["unite_3_vente"],
				"prix_1_vente" => $r["prix_1_vente"],
				"prix_2_vente" => $r["prix_2_vente"],
				"prix_3_vente" => $r["prix_3_vente"],
				"date_debut_vente" => $r["date_debut_vente"],
				"date_fin_vente" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y à H\h ', $r["date_fin_vente"]),
				"commentaire_vente" => $r["commentaire_vente"],
				"prix_minerais" => $minerai,
				"prix_parties_plantes" => $partiesPlantes,
		);

		return $tab;
	}

	private function prepareMenuDefaut() {
		$this->prepareMenuPratique();
		$this->prepareMenuEquipements();
		$this->prepareMenuMunitions();
		$this->prepareMenuMateriels();
		$this->prepareMenusMatieres();
		$this->prepareMenuPotions();
		$this->prepareMenuRunes();
		$this->prepareMenuAlimentsIngredients();
	}

	private function prepareMenuPratique() {
		$tab[1] = array('numero_element' => 1, 'nom' => "50 dernières ventes inscrites", "selected" => "selected");
		$tab[2] = array('numero_element' => 2, 'nom' => "50 ventes arrivant à terme", "selected" => "");

		$this->view->menuRecherchePratique = $tab;
	}

	private function prepareMenuEquipements() {
		$numeroElement = 0;
		$tabMenuEquipement["type_emplacement"] = $this->prepareMenuEquipementEmplacements($numeroElement);
		$this->numeroFinEmplacement = $numeroElement;
		$tabMenuEquipement["type_equipement"] = $this->prepareMenuEquipementTypes($numeroElement);
		$this->view->menuRechercheEquipement = $tabMenuEquipement;
	}

	private function prepareMenuEquipementEmplacements(&$numeroElement) {
		$retour = array("titre" => "Équipements par emplacement");

		Zend_Loader::loadClass("TypeEmplacement");
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacements = $typeEmplacementTable->fetchAll(null, "nom_type_emplacement");
		$typesEmplacements = $typesEmplacements->toArray();

		$elements = null;
		foreach($typesEmplacements as $e) {
			if ($e["nom_systeme_type_emplacement"] != "laban") {
				$numeroElement++;
				$elements[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_emplacement"], "id_type_emplacement" => $e["id_type_emplacement"]);
			}
		}

		$retour["elements"] = $elements;
		return $retour;
	}

	private function prepareMenuEquipementTypes(&$numeroElement) {
		$retour = array("titre" => "Équipements par type");

		Zend_Loader::loadClass("TypeEquipement");
		$typeEquipementTable = new TypeEquipement();
		$typesEquipements = $typeEquipementTable->findAll("nom_type_equipement ASC");

		$elements = null;
		foreach($typesEquipements as $e) {
			if ($e["nom_systeme_type_piece"] != "munition") {
				$numeroElement++;
				$elements[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_equipement"], "id_type_equipement" => $e["id_type_equipement"]);
			}
		}

		$retour["elements"] = $elements;
		return $retour;
	}

	private function prepareMenuMunitions() {
		Zend_Loader::loadClass("TypeMunition");
		$typeMunitionTable = new TypeMunition();
		$typesMunitions = $typeMunitionTable->fetchAll(null, "nom_type_munition");
		$typesMunitions = $typesMunitions->toArray();

		$tabMunition = null;
		$numeroElement = 0;
		foreach($typesMunitions as $e) {
			$numeroElement++;
			$tabMunition[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_munition"], "id_type_munition" => $e["id_type_munition"]);
		}

		$this->view->menuRechercheMunition = $tabMunition;
	}

	private function prepareMenuMateriels() {
		Zend_Loader::loadClass("TypeMateriel");
		$typeMaterielTable = new TypeMateriel();
		$typesMateriels = $typeMaterielTable->fetchAll(null, "nom_type_materiel");
		$typesMateriels = $typesMateriels->toArray();

		$tabMateriel = null;
		$numeroElement = 0;
		foreach($typesMateriels as $e) {
			$numeroElement++;
			$tabMateriel[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_materiel"], "id_type_materiel" => $e["id_type_materiel"]);
		}

		$this->view->menuRechercheMateriel = $tabMateriel;
	}

	private function prepareMenusMatieres() {

		Zend_Loader::loadClass("TypeMinerai");
		$typeMineraiTable = new TypeMinerai();
		$typesMinerais = $typeMineraiTable->fetchAll(null, "nom_type_minerai");
		$typesMinerais = $typesMinerais->toArray();

		$numeroElement = 0;

		$tabMenuMatieresPremieres = null;
		$tabMenuMatieresTransformees = null;

		$tabMineraisBruts = array("titre" => "Minerais bruts");
		$tabLingots = array("titre" => "Lingots");

		foreach($typesMinerais as $e) {
			$numeroElement++;
			$tabMineraisBruts["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Minerai Brut : ".$e["nom_type_minerai"], "id_type_minerai" => $e["id_type_minerai"], "type_forme" => "brut");
			$tabLingots["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Lingot : ".$e["nom_type_minerai"], "id_type_minerai" => $e["id_type_minerai"], "type_forme" => "lingot");
		}

		$tabMenuMatieresPremieres["minerais"] = $tabMineraisBruts;
		$tabMenuMatieresTransformees["minerais"] = $tabLingots;

		$tabPlantesBrutes = array("titre" => "Parties de plantes brutes");
		$tabPlantesPreparees = array("titre" => "Parties de plantes préparées");

		Zend_Loader::loadClass("TypePartieplante");
		$typePartiePlanteTable = new TypePartieplante();
		$typePartiePlanteRowset = $typePartiePlanteTable->fetchall(null, "nom_type_partieplante");
		$typePartiePlanteRowset = $typePartiePlanteRowset->toArray();
		foreach($typePartiePlanteRowset as $t) {
			$partiePlante[$t["id_type_partieplante"]] = array("nom_partieplante" => $t["nom_type_partieplante"], "nom_systeme_partieplante" => $t["nom_systeme_type_partieplante"]);
		}

		Zend_Loader::loadClass("TypePlante");
		$typePlanteTable = new TypePlante();
		$typePlanteRowset = $typePlanteTable->fetchall(null, "nom_type_plante");
		$typePlanteRowset = $typePlanteRowset->toArray();

		$this->numeroElementPemiereMatierePlante = $numeroElement + 1;

		foreach($typePlanteRowset as $t) {
			$numeroElement++;
			$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 1, "brute");
			$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 1, "preparee");

			if ($t["id_fk_partieplante2_type_plante"] != "") {
				$numeroElement++;
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 2, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 2, "preparee");
			}

			if ($t["id_fk_partieplante3_type_plante"] != "") {
				$numeroElement++;
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 3, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 3, "preparee");
			}

			if ($t["id_fk_partieplante4_type_plante"] != "") {
				$numeroElement++;
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 4, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($numeroElement, $t, $partiePlante, 4, "preparee");
			}
		}

		$tabMenuMatieresPremieres["plantes"] = $tabPlantesBrutes;
		$tabMenuMatieresTransformees["plantes"] = $tabPlantesPreparees;

		Zend_Loader::loadClass("TypeGraine");
		$typeGraineTable = new TypeGraine();
		$typesGraines = $typeGraineTable->fetchAll(null, "nom_type_graine");
		$typesGraines = $typesGraines->toArray();
		$tabGraines = array("titre" => "Graines");

		$this->numeroElementPemiereMatiereGraine = $numeroElement + 1;

		foreach($typesGraines as $e) {
			$numeroElement++;
			$tabGraines["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Graine : ".$e["nom_type_graine"], "id_type_graine" => $e["id_type_graine"]);
		}
		$tabMenuMatieresPremieres["graines"] = $tabGraines;

		$tabAutresPremieres = array("titre" => "Autres éléments");
		$tabAutresTransformees = array("titre" => "Autres éléments");

		$numeroElement++;
		$this->numeroElementPemiereMatiereAutre = $numeroElement;
		$tabAutresPremieres["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Peau", "type_element" => "peau");
		$tabAutresTransformees["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Cuir", "type_element" => "cuir");

		$numeroElement++;
		$tabAutresPremieres["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Rondin", "type_element" => "rondin");
		$tabAutresTransformees["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Fourrure", "type_element" => "fourrure");

		$numeroElement++;
		$tabAutresTransformees["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Planche", "type_element" => "planche");

		$tabMenuMatieresPremieres["autres"] = $tabAutresPremieres;
		$tabMenuMatieresTransformees["autres"] = $tabAutresTransformees;

		$this->view->menuRechercheMatieresPremieres = $tabMenuMatieresPremieres;
		$this->view->menuRechercheMatieresTransformees = $tabMenuMatieresTransformees;
	}

	private function prepareUnitesRowPlante($numeroElement, $type, $partiePlante, $num, $forme) {
		if ($forme == "brute") {
			$nomForme = "Brute";
		} else {
			$nomForme = "Préparée";
		}
		return array( "numero_element" => $numeroElement,
					  "id_type_plante" =>  $type["id_type_plante"],
					  "id_type_partieplante" => $type["id_fk_partieplante".$num."_type_plante"],
					  "nom_systeme_type_unite" => "plantebrute:".$type["nom_systeme_type_plante"] ,
					  "nom" => "Plante ".$nomForme.": ".$type["nom_type_plante"]. ' '.$partiePlante[$type["id_fk_partieplante".$num."_type_plante"]]["nom_partieplante"],
					  "table" => "TypePlante",
					  "type_forme" => $forme);
	}

	private function prepareMenuPotions() {
		Zend_Loader::loadClass("TypePotion");
		$typePotionTable = new TypePotion();
		$typesPotions = $typePotionTable->fetchAll(null, array("type_potion ASC", "nom_type_potion ASC"));
		$typesPotions = $typesPotions->toArray();

		Zend_Loader::loadClass("Bral_Util_Potion");

		$tabPotion = null;
		$numeroElement = 0;
		foreach($typesPotions as $e) {
			$numeroElement++;
			$tabPotion[$numeroElement] = array('numero_element' => $numeroElement, 'nom_type' => Bral_Util_Potion::getNomType($e["type_potion"]), 'nom' => $e["nom_type_potion"], "id_type_potion" => $e["id_type_potion"]);
		}

		$this->view->menuRecherchePotion = $tabPotion;
	}

	private function prepareMenuRunes() {
		Zend_Loader::loadClass("TypeRune");
		$typeRuneTable = new TypeRune();
		$typesRunes = $typeRuneTable->fetchAll(null, "nom_type_rune");
		$typesRunes = $typesRunes->toArray();

		$tabRune = null;
		$numeroElement = 1;
		$tabRune[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Rune non identifiée", "id_type_rune" => -1);
		foreach($typesRunes as $e) {
			$numeroElement++;
			$tabRune[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_rune"], "id_type_rune" => $e["id_type_rune"]);
		}

		$this->view->menuRechercheRune = $tabRune;
	}

	private function prepareMenuAlimentsIngredients() {
		Zend_Loader::loadClass("TypeAliment");
		$typeAlimentTable = new TypeAliment();
		$typesAliments = $typeAlimentTable->fetchAll(null, "nom_type_aliment");
		$typesAliments = $typesAliments->toArray();

		$tabAliment = null;
		$numeroElement = 0;
		foreach($typesAliments as $e) {
			$numeroElement++;
			$tabAliment[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_aliment"], "id_type_aliment" => $e["id_type_aliment"]);
		}

		$this->numeroElementFinAliment = $numeroElement;

		$tabAlimentsIngredients["aliments"] = array("elements" => $tabAliment, "titre" => 'Aliments / Boissons');

		Zend_Loader::loadClass("TypeIngredient");
		$typeIngredientTable = new TypeIngredient();
		$typesIngredients = $typeIngredientTable->fetchAll("est_cuisinier_type_ingredient='oui'", "nom_type_ingredient");
		$typesIngredients = $typesIngredients->toArray();

		$tabIngredient = null;
		foreach($typesIngredients as $e) {
			$numeroElement++;
			$tabIngredient[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_ingredient"], "id_type_ingredient" => $e["id_type_ingredient"]);
		}

		$tabAlimentsIngredients["ingredients"] = array("elements" => $tabIngredient, "titre" => 'Ingrédients');

		$this->view->menuRechercheAlimentsIngredients = $tabAlimentsIngredients;
	}

	private function getNomElement($quantite, $element) {
		$s = "";
		if ($quantite > 1) {
			$s = "s";
		}

		if ($element == 'peau' && $quantite > 1) {
			$nom = "peaux";
		} else {
			$nom = $element.$s;
		}

		return $nom;
	}

}