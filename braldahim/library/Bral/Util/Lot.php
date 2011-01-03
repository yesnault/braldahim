<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Lot {

	public static function getLotsByIdEchoppe($idEchoppe, $visiteur) {
		Zend_Loader::loadClass("Lot");
		$lotTable = new Lot();

		$lots = $lotTable->findByIdEchoppe($idEchoppe);
		$retourLots["lots"] = self::prepareLots($lots);

		if ($visiteur) {
			$retourLots["visiteur"] = true;
		} else {
			$retourLots["visiteur"] = false;
		}

		return $retourLots;
	}

	public static function getLotByIdLot($idLot) {
		Zend_Loader::loadClass("Lot");
		$lotTable = new Lot();

		$lots = $lotTable->findByIdLot($idLot);
		$tabLots = self::prepareLots($lots);
		if (count($tabLots) != 1) {
			throw new Zend_Exception("getLotByIdLot - Lot invalide:".$idLot);
		}

		return $tabLots[$idLot];
	}

	private static function prepareLots($lots) {

		if (count($lots) == 0 || $lots == null) {
			return null;
		}

		$idsLot = null;

		foreach ($lots as $l) {
			$idsLot[] = $l["id_lot"];
		}

		Zend_Loader::loadClass("LotPrixMinerai");
		$lotPrixMineraiTable = new LotPrixMinerai();
		$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

		Zend_Loader::loadClass("LotPrixPartiePlante");
		$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
		$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);

		$tabLots = null;

		foreach ($lots as $l) {
			$minerai = null;
			$partiesPlantes = null;

			if ($lotPrixMinerai != null) {
				foreach($lotPrixMinerai as $m) {
					if ($m["id_fk_lot_prix_minerai"] == $l["id_lot"]) {
						$minerai[] = self::recuperePrixMineraiAvecIdLot($m, $l["id_lot"]);
					}
				}
			}

			if ($lotPrixPartiePlante != null) {
				foreach($lotPrixPartiePlante as $p) {
					if ($p["id_fk_lot_prix_partieplante"] == $l["id_lot"]) {
						$partiesPlantes[] = self::recuperePrixPartiePlantesAvecIdLot($p, $l["id_lot"]);
					}
				}
			}

			$tabLots[$l["id_lot"]] = self::prepareRowLot($l, $minerai, $partiesPlantes);
		}

		self::prepareLotsContenus($idsLot, $tabLots);

		return $tabLots;
	}

	// TODO améliorer perf si l'on vient d'une échoppe
	private static function prepareLotsContenus($idsLot, &$lots) {

		self::prepareLotEquipement($idsLot, $lots);
		self::prepareLotMateriel($idsLot, $lots);

		/*self::prepareLotAliment($idsLot, $lots);
		 self::prepareLotElement($idsLot, $lots);

		 self::prepareLotGraine($idsLot, $lots);
		 self::prepareLotIngredient($idsLot, $lots);

		 self::prepareLotMunition($idsLot, $lots);
		 self::prepareLotPartieplante($idsLot, $lots);
		 self::prepareLotPotion($idsLot, $lots);
		 self::prepareLotRune($idsLot, $lots);

		 */
	}

	private static function prepareLotEquipement($idsLot, &$lots) {
		Zend_Loader::loadClass("LotEquipement");
		Zend_Loader::loadClass("Bral_Util_Equipement");

		$lotEquipementTable = new LotEquipement();

		if ($idsLot != null) {
			$equipements = $lotEquipementTable->findByIdLot($idsLot);
		}

		$tabReturn = array();

		$idEquipements = null;
		//$idsLot = null;
		if ($equipements != null) {
			foreach ($equipements as $e) {
				$idEquipements[] = $e["id_lot_equipement"];
				//$idsLot[] = $e["id_lot"];
			}
		}

		if ($idEquipements != null && count($idEquipements) > 0) {
			Zend_Loader::loadClass("EquipementRune");
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

			Zend_Loader::loadClass("EquipementBonus");
			$equipementBonusTable = new EquipementBonus();
			$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
		}

		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEquipements = Bral_Util_Equipement::prepareTabEquipements($equipements);

		$tabRetour = null;
		if ($tabEquipements != null) {
			foreach($tabEquipements as $e) {
				$lots[$e["id_lot"]]["equipements"][$e["id_type_emplacement"]]["equipements"][] = $e;
				$lots[$e["id_lot"]]["equipements"][$e["id_type_emplacement"]]["nom_type_emplacement"] = $e["emplacement"];
			}
		}
	}

	private static function prepareLotMinerai($idsLot) {
		Zend_Loader::loadClass("LotMinerai");

		$lotMineraiTable = new LotMinerai();
		if ($idsLot != null) {
			$minerais = $lotMineraiTable->findByIdLot($idsLot);
		} else {
			$typeMinerai = null;
			if ($type == "matieres_premieres") {
				$typeMinerai = $this->view->menuRechercheMatieresPremieres["minerais"]["elements"][$numeroElement]["id_type_minerai"];
			} elseif ($type == "matieres_transformees") {
				$typeMinerai = $this->view->menuRechercheMatieresTransformees["minerais"]["elements"][$numeroElement]["id_type_minerai"];
			}
			if ($numeroElement < $this->numeroElementPemiereMatierePlante && $typeMinerai != null) {
				$minerais = $lotMineraiTable->findByIdType($typeMinerai);
			}
		}

		$tabReturn = array();

		$idMinerais = null;
		$idsLot = null;
		if ($minerais != null) {
			foreach ($minerais as $e) {
				$idMinerais[] = $e["id_lot_minerai"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idMinerais != null && count($idMinerais) > 0) {
			Zend_Loader::loadClass("LotPrixMinerai");
			$lotPrixMineraiTable = new LotPrixMinerai();
			$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($minerais) > 0) {
			foreach($minerais as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdLot($lotPrixMinerai, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$s = "";
				if ($e["quantite_lot_minerai"] > 1) {
					$s = "s";
				}

				if ($e["type_lot_minerai"] == "lingot") {
					$nom = $e["nom_type_minerai"]. " : ".$e["quantite_lot_minerai"]. " lingot".$s;
					$image = "type_minerai_".$e["id_type_minerai"]."_p";
				} else {
					$nom = $e["nom_type_minerai"]. " : ".$e["quantite_lot_minerai"]. " minerai".$s. " brut".$s;
					$image = "type_minerai_".$e["id_type_minerai"];
				}
					
				$tabObjet = array(
					"id_minerai" => $e["id_lot_minerai"],
					"image" => $image,
					"nom" => $nom,
				);

				$tabReturn[] = array(
					"type" => "minerai",
					"lot" => $this->prepareRowLot($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private static function prepareLotPartieplante($idsLot, &$lots) {
		Zend_Loader::loadClass("LotPartieplante");

		$lotPartieplanteTable = new LotPartieplante();
		if ($idsLot != null) {
			$elements = $lotPartieplanteTable->findByIdLot($idsLot);
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
				$elements = $lotPartieplanteTable->findByIdType($typePlante, $typePartiePlante);
			}
		}

		$tabReturn = array();

		$idPartieplantes = null;
		$idsLot = null;
		if ($elements != null) {
			foreach ($elements as $e) {
				$idPartieplantes[] = $e["id_lot_partieplante"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idPartieplantes != null && count($idPartieplantes) > 0) {
			Zend_Loader::loadClass("LotPrixMinerai");
			$lotPrixMineraiTable = new LotPrixMinerai();
			$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($elements) > 0) {
			foreach($elements as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdLot($lotPrixMinerai, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$s = "";
				if ($e["quantite_lot_partieplante"] > 1) {
					$s = "s";
				}

				if ($e["type_lot_partieplante"] == "preparee") {
					$nom = $e["quantite_lot_partieplante"]. " ".$e["nom_type_partieplante"].$s." preparée".$s." ".$e["prefix_type_plante"].$e["nom_type_plante"];
					$image = "type_partieplante_".$e["id_type_partieplante"]."_p";
				} else {
					$nom = $e["quantite_lot_partieplante"]. " ".$e["nom_type_partieplante"].$s." brute".$s." ".$e["prefix_type_plante"].$e["nom_type_plante"];
					$image = "type_partieplante_".$e["id_type_partieplante"];
				}
					
				$tabObjet = array(
					"id_element" => $e["id_lot_partieplante"],
					"nom" => $nom,
					"image" => $image,
				);

				$tabReturn[] = array(
					"type" => "partieplante",
					"lot" => $this->prepareRowLot($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private static function prepareLotGraine($idsLot, &$lots) {
		Zend_Loader::loadClass("LotGraine");

		$lotGraineTable = new LotGraine();
		$graines = null;
		if ($idsLot != null) {
			$graines = $lotGraineTable->findByIdLot($idsLot);
		} else {
			$typeGraine = null;
			if ($type == "graine") {
				$typeGraine = $this->view->menuRechercheMatieresPremieres["graines"]["elements"][$numeroElement]["id_type_graine"];
			}

			$typeGraine = $this->view->menuRechercheMatieresPremieres["graines"]["elements"][$numeroElement]["id_type_graine"];
			if ($numeroElement < $this->numeroElementPemiereMatiereAutre && $typeGraine != null) {
				$graines = $lotGraineTable->findByIdType($typeGraine);
			}
		}

		$tabReturn = array();

		$idGraines = null;
		$idsLot = null;
		if ($graines != null) {
			foreach ($graines as $e) {
				$idGraines[] = $e["id_lot_graine"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idGraines != null && count($idGraines) > 0) {
			Zend_Loader::loadClass("LotPrixGraine");
			$lotPrixGraineTable = new LotPrixGraine();
			$lotPrixGraine = $lotPrixGraineTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($graines) > 0) {
			foreach($graines as $e) {

				$minerais = $this->recuperePrixMineraiAvecIdLot($lotPrixGraine, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$s = "";
				if ($e["quantite_lot_graine"] > 1) {
					$s = "s";
				}

				$nom = $e["nom_type_graine"]. " : ".$e["quantite_lot_graine"]. " poignée".$s. " de graines";
				$image = "type_graine_".$e["id_type_graine"];

				$tabObjet = array(
					"id_graine" => $e["id_lot_graine"],
					"image" => $image,
					"nom" => $nom,
				);

				$tabReturn[] = array(
					"type" => "graine",
					"lot" => $this->prepareRowLot($e, $minerais, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private static function prepareLotIngredient($idsLot, &$lots) {
		Zend_Loader::loadClass("LotIngredient");

		$lotIngredientTable = new LotIngredient();
		if ($idsLot != null) {
			$ingredients = $lotIngredientTable->findByIdLot($idsLot);
		} else {
			$ingredients = $lotIngredientTable->findByIdType($this->view->menuRechercheAlimentsIngredients["ingredients"]["elements"][$numeroElement]["id_type_ingredient"]);
		}

		$tabReturn = array();

		$idIngredients = null;
		$idsLot = null;
		if ($ingredients != null) {
			foreach ($ingredients as $e) {
				$idIngredients[] = $e["id_lot_ingredient"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idIngredients != null && count($idIngredients) > 0) {
			Zend_Loader::loadClass("LotPrixIngredient");
			$lotPrixIngredientTable = new LotPrixIngredient();
			$lotPrixIngredient = $lotPrixIngredientTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($ingredients) > 0) {
			foreach($ingredients as $e) {

				$minerais = $this->recuperePrixMineraiAvecIdLot($lotPrixIngredient, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$s = "";
				if ($e["quantite_lot_ingredient"] > 1) {
					$s = "s";
				}

				$nom = $e["nom_type_ingredient"]. " : ".$e["quantite_lot_ingredient"]. " élément".$s;
				$image = "type_ingredient_".$e["id_type_ingredient"];

				$tabObjet = array(
					"id_ingredient" => $e["id_lot_ingredient"],
					"image" => $image,
					"nom" => $nom,
				);

				$tabReturn[] = array(
					"type" => "ingredient",
					"lot" => $this->prepareRowLot($e, $minerais, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private static function prepareLotElement($idsLot, &$lots) {
		Zend_Loader::loadClass("LotElement");

		$lotElementTable = new LotElement();
		if ($idsLot != null) {
			$elements = $lotElementTable->findByIdLot($idsLot);
		} else {
			$typeElement = null;
			if ($type == "matieres_premieres") {
				$typeElement = $this->view->menuRechercheMatieresPremieres["autres"]["elements"][$numeroElement]["type_element"];
			} elseif ($type == "matieres_transformees") {
				$typeElement = $this->view->menuRechercheMatieresTransformees["autres"]["elements"][$numeroElement]["type_element"];
			}
			if ($numeroElement >= $this->numeroElementPemiereMatiereAutre && $typeElement != null) {
				$elements = $lotElementTable->findByType($typeElement);
			}
		}

		$tabReturn = array();

		$idElements = null;
		$idsLot = null;
		if ($elements != null) {
			foreach ($elements as $e) {
				$idElements[] = $e["id_lot_element"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idElements != null && count($idElements) > 0) {
			Zend_Loader::loadClass("LotPrixMinerai");
			$lotPrixMineraiTable = new LotPrixMinerai();
			$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($elements) > 0) {
			foreach($elements as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdLot($lotPrixMinerai, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$nom = $e["quantite_lot_element"]. " " .$this->getNomElement($e["quantite_lot_element"], $e["type_lot_element"]);

				$image = $e["type_lot_element"];

				$tabObjet = array(
					"id_element" => $e["id_lot_element"],
					"nom" => $nom,
					"image" => $image,
				);

				$tabReturn[] = array(
					"type" => "element",
					"lot" => $this->prepareRowLot($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);

			}
		}
		return $tabReturn;
	}

	private static function prepareLotMunition($idsLot, &$lots) {
		Zend_Loader::loadClass("LotMunition");
		Zend_Loader::loadClass("Bral_Util_Equipement");

		$lotMunitionTable = new LotMunition();

		if ($idsLot != null) {
			$munitions = $lotMunitionTable->findByIdLot($idsLot);
		} else {
			$munitions = $lotMunitionTable->findAllByIdTypeMunition($this->view->menuRechercheMunition[$numeroElement]["id_type_munition"]);
		}

		$tabReturn = array();

		$idMunitions = null;
		$idsLot = null;
		if ($munitions != null) {
			foreach ($munitions as $e) {
				$idMunitions[] = $e["id_lot_munition"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idMunitions != null && count($idMunitions) > 0) {
			Zend_Loader::loadClass("LotPrixMinerai");
			$lotPrixMineraiTable = new LotPrixMinerai();
			$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($munitions) > 0) {
			foreach($munitions as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdLot($lotPrixMinerai, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$nom = $e["quantite_lot_munition"]. " ";
				if ($e["quantite_lot_munition"] <= 1) {
					$nom .= $e["nom_type_munition"];
				} else {
					$nom .= $e["nom_pluriel_type_munition"];
				}

				$tabObjet = array(
					"id_munition" => $e["id_lot_munition"],
					"nom" => $nom,
					"id_type_munition" => $e["id_type_munition"],
				);

				$tabReturn[] = array(
					"type" => "munition",
					"lot" => $this->prepareRowLot($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
				);
			}
		}

		return $tabReturn;
	}

	private static function prepareLotAliment($idsLot, &$lots) {
		Zend_Loader::loadClass("LotAliment");

		$lotAlimentTable = new LotAliment();
		if ($idsLot != null) {
			$aliments = $lotAlimentTable->findByIdLot($idsLot);
		} else {
			$aliments = $lotAlimentTable->findByIdType($this->view->menuRechercheAlimentsIngredients["aliments"]["elements"][$numeroAliment]["id_type_aliment"]);
		}

		$tabReturn = array();

		$idAliments = null;
		$idsLot = null;
		if ($aliments != null) {
			foreach ($aliments as $e) {
				$idAliments[] = $e["id_lot_aliment"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idAliments != null && count($idAliments) > 0) {
			Zend_Loader::loadClass("LotPrixMinerai");
			$lotPrixMineraiTable = new LotPrixMinerai();
			$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($aliments) > 0) {
			Zend_Loader::loadClass("Bral_Util_Aliment");
			foreach($aliments as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdLot($lotPrixMinerai, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$tabAliment = array(
					"id_lot_aliment" => $e["id_lot_aliment"],
					"id_type_aliment" => $e["id_type_aliment"],
					"nom" => $e["nom_type_aliment"],
					"bbdf" => $e["bbdf_aliment"],
					"qualite" => $e["nom_aliment_type_qualite"],
					"recette" => Bral_Util_Aliment::getNomType($e["type_bbdf_type_aliment"]),
				);

				if (array_key_exists($e["id_lot"], $tabReturn)) {
					$tabReturn[$e["id_lot"]]["objet"][] = $tabAliment;
				} else {
					$tabObjet = null;
					$tabObjet[] = $tabAliment;

					$tabReturn[$e["id_lot"]] = array(
					"type" => "aliment",
					"lot" => $this->prepareRowLot($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
					);
				}

			}
		}
		return $tabReturn;
	}


	private static function prepareLotMateriel($idsLot, &$lots) {
		Zend_Loader::loadClass("LotMateriel");
		Zend_Loader::loadClass("Bral_Util_Materiel");

		$lotMaterielTable = new LotMateriel();

		if ($idsLot != null) {
			$materiels = $lotMaterielTable->findByIdLot($idsLot);
		}

		$tabReturn = array();

		$idMateriels = null;
		//$idsLot = null;
		if ($materiels != null) {
			foreach ($materiels as $e) {
				$idMateriels[] = $e["id_lot_materiel"];
				//$idsLot[] = $e["id_lot"];
			}
		}

		if (count($materiels) > 0) {
			foreach($materiels as $e) {
				if (substr($e["nom_systeme_type_materiel"], 0, 9) == "charrette") {
					$lots[$e["id_fk_lot_lot_materiel"]]["estLotCharrette"] = true;
				}
				$tabMateriel = array(
					"id_lot_materiel" => $e["id_lot_materiel"],
					"id_type_materiel" => $e["id_type_materiel"],
					"nom" => $e["nom_type_materiel"],
					"id_materiel" => $e["id_lot_materiel"],
					"id_type_materiel" => $e["id_type_materiel"],
					'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
					'capacite' => $e["capacite_type_materiel"],
					'durabilite' => $e["durabilite_type_materiel"],
					'usure' => $e["usure_type_materiel"],
					'poids' => $e["poids_type_materiel"],
					'force_base_min_type_materiel' => $e["force_base_min_type_materiel"],
					'agilite_base_min_type_materiel' => $e["agilite_base_min_type_materiel"],
					'sagesse_base_min_type_materiel' => $e["sagesse_base_min_type_materiel"],
					'vigueur_base_min_type_materiel' => $e["vigueur_base_min_type_materiel"],
				);
				$lots[$e["id_fk_lot_lot_materiel"]]["materiels"][] = $tabMateriel;
			}
		}
	}

	private static function prepareLotRune($idsLot, &$lots) {
		Zend_Loader::loadClass("LotRune");

		$lotRuneTable = new LotRune();
		if ($idsLot != null) {
			$runes = $lotRuneTable->findByIdLot($idsLot);
		} else {
			if ($this->view->menuRechercheRune[$numeroRune]["id_type_rune"] != -1) {
				$runes = $lotRuneTable->findByIdType($this->view->menuRechercheRune[$numeroRune]["id_type_rune"]);
			} else {
				$runes = $lotRuneTable->findNonIdentifiee();
			}
		}

		$tabReturn = array();

		$idRunes = null;
		$idsLot = null;
		if ($runes != null) {
			foreach ($runes as $e) {
				$idRunes[] = $e["id_rune_lot_rune"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idRunes != null && count($idRunes) > 0) {
			Zend_Loader::loadClass("LotPrixMinerai");
			$lotPrixMineraiTable = new LotPrixMinerai();
			$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		if (count($runes) > 0) {
			foreach($runes as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdLot($lotPrixMinerai, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				if ($e['est_identifiee_rune'] == "oui") {
					$nom = $e["nom_type_rune"];
					$image = $e["image_type_rune"];
				} else {
					$nom = "Rune non identifiée";
					$image = "rune_inconnue.png";
				}

				$tabRune = array(
					"id_rune" => $e["id_rune_lot_rune"],
					"id_type_rune" => $e["id_type_rune"],
					"nom" => $nom,
					"image" => $image,
					"est_identifiee" => $e['est_identifiee_rune'],
					"effet_type_rune" => $e["effet_type_rune"],
					"type" => $e["nom_type_rune"],
				);

				if (array_key_exists($e["id_lot"], $tabReturn)) {
					$tabReturn[$e["id_lot"]]["objet"][] = $tabRune;
				} else {
					$tabObjet = null;
					$tabObjet[] = $tabRune;

					$tabReturn[$e["id_lot"]] = array(
						"type" => "rune",
						"lot" => $this->prepareRowLot($e, $minerai, $partiesPlantes),
						"objet" => $tabObjet,
					);
				}

			}
		}
		return $tabReturn;
	}

	private static function prepareLotPotion($idsLot, &$lots) {
		Zend_Loader::loadClass("LotPotion");

		$lotPotionTable = new LotPotion();
		if ($idsLot != null) {
			$potions = $lotPotionTable->findByIdLot($idsLot);
		} else {
			$potions = $lotPotionTable->findByIdType($this->view->menuRecherchePotion[$numeroPotion]["id_type_potion"]);
		}

		$tabReturn = array();

		$idPotions = null;
		$idsLot = null;
		if ($potions != null) {
			foreach ($potions as $e) {
				$idPotions[] = $e["id_lot_potion"];
				$idsLot[] = $e["id_lot"];
			}
		}

		if ($idPotions != null && count($idPotions) > 0) {
			Zend_Loader::loadClass("LotPrixMinerai");
			$lotPrixMineraiTable = new LotPrixMinerai();
			$lotPrixMinerai = $lotPrixMineraiTable->findByIdLot($idsLot);

			Zend_Loader::loadClass("LotPrixPartiePlante");
			$lotPrixPartiePlanteTable = new LotPrixPartiePlante();
			$lotPrixPartiePlante = $lotPrixPartiePlanteTable->findByIdLot($idsLot);
		}

		Zend_Loader::loadClass("Bral_Util_Potion");
		Zend_Loader::loadClass("Bral_Helper_DetailPotion");
		if (count($potions) > 0) {
			foreach($potions as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdLot($lotPrixMinerai, $e["id_lot"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdLot($lotPrixPartiePlante, $e["id_lot"]);

				$tabPotion = array(
					"id_lot_potion" => $e["id_lot_potion"],
					"id_type_potion" => $e["id_type_potion"],
					"nom" => $e["nom_type_potion"],
					"id_potion" => $e["id_lot_potion"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_potion"],
					"caracteristique" => $e["caract_type_potion"],
					"bm_type" => $e["bm_type_potion"],
					"caracteristique2" => $e["caract2_type_potion"],
					"bm2_type" => $e["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($e["type_potion"]),
				);

				if (array_key_exists($e["id_lot"], $tabReturn)) {
					$tabReturn[$e["id_lot"]]["objet"][] = $tabPotion;
				} else {
					$tabObjet = null;
					$tabObjet[] = $tabPotion;

					$tabReturn[$e["id_lot"]] = array(
					"type" => "potion",
					"lot" => $this->prepareRowLot($e, $minerai, $partiesPlantes),
					"objet" => $tabObjet,
					);
				}

			}
		}
		return $tabReturn;
	}

	private static function recuperePrixMineraiAvecIdLot($r, $idLot) {
		$minerai = array(
						"prix_lot_prix_minerai" => $r["prix_lot_prix_minerai"],
						"nom_type_minerai" => $r["nom_type_minerai"],
		);
		return $minerai;
	}

	private static function recuperePrixPartiePlantesAvecIdLot($p, $idLot) {
		$partiesPlantes = array(
						"prix_lot_prix_partieplante" => $p["prix_lot_prix_partieplante"],
						"nom_type_plante" => $p["nom_type_plante"],
						"nom_type_partieplante" => $p["nom_type_partieplante"],
						"prefix_type_plante" => $p["prefix_type_plante"],
		);
		return $partiesPlantes;
	}

	private static function prepareRowLot($r, $minerai, $partiesPlantes) {

		$tab = array("id_lot" => $r["id_lot"],
				"unite_1_lot" => $r["unite_1_lot"],
				"unite_2_lot" => $r["unite_2_lot"],
				"unite_3_lot" => $r["unite_3_lot"],
				"prix_1_lot" => $r["prix_1_lot"],
				"prix_2_lot" => $r["prix_2_lot"],
				"prix_3_lot" => $r["prix_3_lot"],
				"date_debut_lot" => $r["date_debut_lot"],
				"commentaire_lot" => $r["commentaire_lot"],
				"prix_minerais" => $minerai,
				"prix_parties_plantes" => $partiesPlantes,
				"estLotCharrette" => false,
				"equipements" => null,
				"materiels" => null,
		);

		if ($r["date_fin_lot"] != null) {
			$tab["date_fin_lot"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y à H\h ', $r["date_fin_lot"]);
		}

		return $tab;
	}

	public static function transfertLot($idLot, $destination, $idDestination) {

		if ($destination != "caisse_echoppe"
		&& $destination != "arriere_echoppe"
		&& $destination != "laban"
		&& $destination != "charrette"
		&& $destination != "coffre") {
			throw new Zend_exception("Erreur Appel Bral_Util_Lot::transfertLot : idLot:".$idLot." destination".$destination);
		}

		$preSuffixe = "";
		if ($destination == "caisse_echoppe") {
			$preSuffixe = "caisse_";
			$destination = "echoppe";
		} elseif ($destination == "arriere_echoppe") {
			$preSuffixe = "arriere_";
			$destination = "echoppe";
		}

		$suffixe1 = strtolower($destination);
		$nomTable = Bral_Util_String::firstToUpper($destination);

		$suffixe2 = $suffixe1."_";
		if ($destination == "laban") {
			$suffixe2 = "braldun_";
		} elseif ($destination == "charrette") {
			$suffixe2 = "";
		}

		self::transfertLotEquipement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotMateriel($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);

		self::transfertLotAliment($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotElement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe);

		self::transfertLotGraine($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotIngredient($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);

		self::transfertLotMunition($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotMinerai($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe);
		self::transfertLotPartieplante($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe);
		self::transfertLotPotion($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotRune($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		
		self::supprimeLot($idLot);
	}

	public static function supprimeLot($idLot) {
		Zend_Loader::loadClass("Lot");
		$lotTable = new Lot();
		$where = "id_lot = ".intval($idLot);
		$lotTable->delete($where);
	}
	
	private static function transfertLotEquipement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass("LotEquipement");

		$lotEquipementTable = new LotEquipement();
		$lots = $lotEquipementTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Equipement";
		Zend_Loader::loadClass($table);
		$equipementTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_equipement' => $lot["id_lot_equipement"], //idEquipement,
				'id_fk_'.$suffixe2.$suffixe1.'_equipement' => $idDestination, //idDestination
			);

			$equipementTable->insert($data);
		}
	}

	private static function transfertLotMateriel($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {

		Zend_Loader::loadClass("LotMateriel");

		$lotMaterielTable = new LotMateriel();
		$lots = $lotMaterielTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Materiel";
		Zend_Loader::loadClass($table);
		$materielTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_materiel' => $lot["id_lot_materiel"], //idMateriel,
				'id_fk_'.$suffixe2.$suffixe1.'_materiel' => $idDestination, //idDestination
			);

			$materielTable->insert($data);
		}

	}

	private static function transfertLotAliment($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass("LotAliment");

		$lotAlimentTable = new LotAliment();
		$lots = $lotAlimentTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Aliment";
		Zend_Loader::loadClass($table);
		$alimentTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_aliment' => $lot["id_lot_aliment"], //idAliment,
				'id_fk_'.$suffixe2.$suffixe1.'_aliment' => $idDestination, //idDestination
			);

			$alimentTable->insert($data);
		}
	}

	private static function transfertLotElement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe) {
		Zend_Loader::loadClass("Lot");

		$lotTable = new Lot();
		$lots = $lotTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable;
		Zend_Loader::loadClass($table);
		$elementTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1 => $idDestination,
				'quantite_peau_'.$preSuffixe.$suffixe1 => $lot["quantite_peau_lot"],
				'quantite_cuir_'.$preSuffixe.$suffixe1 => $lot["quantite_cuir_lot"],
				'quantite_fourrure_'.$preSuffixe.$suffixe1 => $lot["quantite_fourrure_lot"],
				'quantite_planche_'.$preSuffixe.$suffixe1 => $lot["quantite_planche_lot"],
				'quantite_rondin_'.$preSuffixe.$suffixe1 => $lot["quantite_rondin_lot"],
			);

			if ($preSuffixe != "arriere_") {
				$data['quantite_castar_'.$preSuffixe.$suffixe1] = $lot["quantite_castar_lot"];
			}

			$elementTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotGraine($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass("LotGraine");

		$lotGraineTable = new LotGraine();
		$lots = $lotGraineTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Graine";
		Zend_Loader::loadClass($table);
		$graineTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$suffixe1.'_graine' => $lot["quantite_lot_graine"],
				'id_fk_type_'.$suffixe1.'_graine' => $lot["id_fk_type_lot_graine"], 
				'id_fk_'.$suffixe2.$suffixe1.'_graine' => $idDestination, 
			);

			$graineTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotIngredient($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass("LotIngredient");

		$lotIngredientTable = new LotIngredient();
		$lots = $lotIngredientTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Ingredient";
		Zend_Loader::loadClass($table);
		$ingredientTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$suffixe1.'_ingredient' => $lot["quantite_lot_ingredient"],
				'id_fk_type_'.$suffixe1.'_ingredient' => $lot["id_fk_type_lot_ingredient"], 
				'id_fk_'.$suffixe2.$suffixe1.'_ingredient' => $idDestination, 
			);

			$ingredientTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotMunition($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass("LotMunition");

		$lotMunitionTable = new LotMunition();
		$lots = $lotMunitionTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Munition";
		Zend_Loader::loadClass($table);
		$munitionTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$suffixe1.'_munition' => $lot["quantite_lot_munition"],
				'id_fk_type_'.$suffixe1.'_munition' => $lot["id_fk_type_lot_munition"], 
				'id_fk_'.$suffixe2.$suffixe1.'_munition' => $idDestination, 
			);

			$munitionTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotPartieplante($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe) {
		Zend_Loader::loadClass("LotPartieplante");

		$lotPartieplanteTable = new LotPartieplante();
		$lots = $lotPartieplanteTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Partieplante";
		Zend_Loader::loadClass($table);
		$partieplanteTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$preSuffixe.$suffixe1.'_partieplante' => $lot["quantite_lot_partieplante"],
				'quantite_preparee_'.$suffixe1.'_partieplante' => $lot["quantite_preparee_lot_partieplante"],
				'id_fk_type_plante_'.$suffixe1.'_partieplante' => $lot["id_fk_type_plante_lot_partieplante"],
				'id_fk_type_'.$suffixe1.'_partieplante' => $lot["id_fk_type_lot_partieplante"], 
				'id_fk_'.$suffixe2.$suffixe1.'_partieplante' => $idDestination, 
			);

			$partieplanteTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotMinerai($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe) {
		Zend_Loader::loadClass("LotMinerai");

		$lotMineraiTable = new LotMinerai();
		$lots = $lotMineraiTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Minerai";
		Zend_Loader::loadClass($table);
		$mineraiTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_brut_'.$preSuffixe.$suffixe1.'_minerai' => $lot["quantite_brut_lot_minerai"],
				'quantite_lingots_'.$suffixe1.'_minerai' => $lot["quantite_lingots_lot_minerai"],
				'id_fk_type_'.$suffixe1.'_minerai' => $lot["id_fk_type_lot_minerai"], 
				'id_fk_'.$suffixe2.$suffixe1.'_minerai' => $idDestination, 
			);

			$mineraiTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotPotion($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass("LotPotion");

		$lotPotionTable = new LotPotion();
		$lots = $lotPotionTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Potion";
		Zend_Loader::loadClass($table);
		$potionTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_potion' => $lot["id_lot_potion"], //idPotion,
				'id_fk_'.$suffixe2.$suffixe1.'_potion' => $idDestination, //idDestination
			);

			$potionTable->insert($data);
		}
	}

	private static function transfertLotRune($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass("LotRune");

		$lotRuneTable = new LotRune();
		$lots = $lotRuneTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable."Rune";
		Zend_Loader::loadClass($table);
		$runeTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_rune' => $lot["id_lot_rune"], //idRune,
				'id_fk_'.$suffixe2.$suffixe1.'_rune' => $idDestination, //idDestination
			);

			$runeTable->insert($data);
		}
	}
}