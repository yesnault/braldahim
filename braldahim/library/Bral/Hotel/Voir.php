<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Hotel_Voir extends Bral_Hotel_Hotel {

	private $arBoutiqueBruts;
	private $arBoutiqueTransformes;

	function getNomInterne() {
		return $this->box_lieu;
	}

	public function getTitreAction() {
		return null;
	}

	function render() {
		Zend_Loader::loadClass("Bral_Helper_DetailEquipement");
		Zend_Loader::loadClass("Bral_Helper_DetailHotel");

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

		if ($this->request->get("hotel_menu_recherche_equipements") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_equipements"));
			$this->prepareMenuEquipements();
			$tabResultats = $this->prepareRechercheEquipement($numeroElement);
		} else if ($this->request->get("hotel_menu_recherche_materiels") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_materiels"));
			$this->prepareMenuMateriels();
		} else if ($this->request->get("hotel_menu_recherche_matieres_premieres") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_matieres_premieres"));
			$this->prepareMenusMatieres();
			$tabResultats = $this->prepareRechercheMatieres($numeroElement, "matieres_premieres");
		} else if ($this->request->get("hotel_menu_recherche_matieres_transformees") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_matieres_transformees"));
			$this->prepareMenusMatieres();
			$tabResultats = $this->prepareRechercheMatieres($numeroElement, "matieres_transformees");
		} else if ($this->request->get("hotel_menu_recherche_aliments") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_aliments"));
			$this->prepareMenuAliments();
			$tabResultats = $this->prepareRechercheAliment($numeroElement);
		} else if ($this->request->get("hotel_menu_recherche_potions") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_potions"));
			$this->prepareMenuPotions();
		} else if ($this->request->get("hotel_menu_recherche_runes") != "") {
			$numeroElement = Bral_Util_Controle::getValeurIntVerif($this->request->get("hotel_menu_recherche_runes"));
			$this->prepareMenuRunes();
		} else {
			$this->prepareMenuDefaut();
			$tabResultats = $this->prepareVentesDefaut();
			$this->box_lieu = "box_lieu";
		}

		$this->view->resultats = $tabResultats;
	}

	private function prepareVentesDefaut() {
		Zend_Loader::loadClass("Vente");
		$venteTable = new Vente();

		$ventes = $venteTable->findDernieres(50);

		$avecEquipements = false;
		$avecElements = false;
		$avecAliments = false;
		foreach ($ventes as $e) {
			$idVentes[] = $e["id_vente"];
			if ($e["type_vente"] == "equipement") {
				$avecEquipements = true;
			} elseif ($e["type_vente"] == "element") {
				$avecElements = true;
			} elseif ($e["type_vente"] == "aliment") {
				$avecAliments = true;
			}
		}

		$tabResultats = array();
		if ($avecEquipements) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheEquipement(null, $idVentes));
		}

		if ($avecElements) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheMatieres(null, "element", $idVentes));
		}

		if ($avecAliments) {
			$tabResultats = array_merge($tabResultats, $this->prepareRechercheAliment(null, $idVentes));
		}

		return $tabResultats;
	}

	private function prepareRechercheMatieres($numeroElement, $type, $idsVente = null) {

		$tabReturn = array();
		if ($numeroElement >= $this->numeroElementPemiereMatiereAutre || $type == "element") {
			$tabReturn = $this->prepareRechercheElement($numeroElement, $type, $idsVente);
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
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdsVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($elements) > 0) {
			foreach($elements as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$nom = $e["quantite_vente_element"]. " " .$this->getNomElement($e["quantite_vente_element"], $e["type_vente_element"]);
					
				$tabObjet = array(
					"id_element" => $e["id_vente_element"],
					"nom" => $nom,
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
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdsVente($idVentes);

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
								"id_fk_type_rune_equipement_rune" => $r["id_fk_type_rune_equipement_rune"],
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
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_vente_equipement"]),
					"id_type_equipement" => $e["id_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nb_runes" => $e["nb_runes_vente_equipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_vente_equipement"],
					"armure" => $e["armure_recette_equipement"],
					"force" => $e["force_recette_equipement"],
					"agilite" => $e["agilite_recette_equipement"],
					"vigueur" => $e["vigueur_recette_equipement"],
					"sagesse" => $e["sagesse_recette_equipement"],
					"vue" => $e["vue_recette_equipement"],
					"bm_attaque" => $e["bm_attaque_recette_equipement"],
					"bm_degat" => $e["bm_degat_recette_equipement"],
					"bm_defense" => $e["bm_defense_recette_equipement"],
					"poids" => $e["poids_recette_equipement"],
					"suffixe" => $e["suffixe_mot_runique"],
					"id_fk_mot_runique" => $e["id_fk_mot_runique_vente_equipement"],
					"id_fk_region" => $e["id_fk_region_vente_equipement"],
					"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
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

	private function prepareRechercheAliment($numeroElement, $idsVente = null) {
		Zend_Loader::loadClass("VenteAliment");

		$venteAlimentTable = new VenteAliment();
		if ($idsVente != null) {
			$aliments = $venteAlimentTable->findByIdVente($idsVente);
		} else {
			$aliments = $venteAlimentTable->findByIdType($this->view->menuRechercheAliment[$numeroAliment]["id_type_aliment"]);
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
			$ventePrixMinerai = $ventePrixMineraiTable->findByIdsVente($idVentes);

			Zend_Loader::loadClass("VentePrixPartiePlante");
			$ventePrixPartiePlanteTable = new VentePrixPartiePlante();
			$ventePrixPartiePlante = $ventePrixPartiePlanteTable->findByIdVente($idVentes);
		}

		if (count($aliments) > 0) {
			foreach($aliments as $e) {

				$minerai = $this->recuperePrixMineraiAvecIdVente($ventePrixMinerai, $e["id_vente"]);
				$partiesPlantes = $this->recuperePrixPartiePlantesAvecIdVente($ventePrixPartiePlante, $e["id_vente"]);

				$tabAliment = array(
					"id_vente_aliment" => $e["id_vente_aliment"],
					"id_type_aliment" => $e["id_type_aliment"],
					"nom" => $e["nom_type_aliment"],
					"bddf" => $e["bbdf_vente_aliment"],
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
			 	"id_hobbit" => $r["id_hobbit"],
				"prenom_hobbit" => $r["nom_hobbit"],
				"nom_hobbit" => $r["nom_hobbit"],
				"unite_1_vente" => $r["unite_1_vente"],
				"unite_2_vente" => $r["unite_2_vente"],
				"unite_3_vente" => $r["unite_3_vente"],
				"prix_1_vente" => $r["prix_1_vente"],
				"prix_2_vente" => $r["prix_2_vente"],
				"prix_3_vente" => $r["prix_3_vente"],
				"date_debut_vente" => $r["date_debut_vente"],
				"date_fin_vente" => $r["date_fin_vente"],
				"commentaire_vente" => $r["commentaire_vente"],
				"prix_minerais" => $minerai,
				"prix_parties_plantes" => $partiesPlantes,
		);

		return $tab;
	}

	private function prepareMenuDefaut() {
		$this->prepareMenuEquipements();
		$this->prepareMenuMateriels();
		$this->prepareMenusMatieres();
		$this->prepareMenuPotions();
		$this->prepareMenuRunes();
		$this->prepareMenuAliments();
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
			$numeroElement++;
			$elements[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_emplacement"], "id_type_emplacement" => $e["id_type_emplacement"]);
		}

		$retour["elements"] = $elements;

		return $retour;
	}

	private function prepareMenuEquipementTypes(&$numeroElement) {
		$retour = array("titre" => "Équipements par type");

		Zend_Loader::loadClass("TypeEquipement");
		$typeEquipementTable = new TypeEquipement();
		$typesEquipements = $typeEquipementTable->fetchAll(null, "nom_type_equipement");
		$typesEquipements = $typesEquipements->toArray();

		$elements = null;
		foreach($typesEquipements as $e) {
			$numeroElement++;
			$elements[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_equipement"], "id_type_equipement" => $e["id_type_equipement"]);
		}

		$retour["elements"] = $elements;

		return $retour;
	}

	private function prepareMenuMateriels() {
		Zend_Loader::loadClass("TypeMateriel");
		$typeMaterielTable = new TypeMateriel();
		$typesMateriels = $typeMaterielTable->fetchAll(null, "nom_type_materiel");
		$typesMateriels = $typesMateriels->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabMateriel["elements"] = null;
		foreach($typesMateriels as $e) {
			$numeroElement++;
			$tabMateriel["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_materiel"], "id_element" => $e["id_type_materiel"]);
		}

		$tab[] = $tabMateriel;
		$this->view->menuRechercheMateriel = $tab;
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
			$tabMineraisBruts["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Minerai Brut : ".$e["nom_type_minerai"], "table" => "TypeMinerai", "id_element" => $e["id_type_minerai"], "type_forme" => "brut");
			$tabLingots["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Lingot : ".$e["nom_type_minerai"], "table" => "TypeMinerai", "id_element" => $e["id_type_minerai"], "type_forme" => "lingot");
		}

		$tabMenuMatieresPremieres[] = $tabMineraisBruts;
		$tabMenuMatieresTransformees[] = $tabLingots;

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

		foreach($typePlanteRowset as $t) {
			$numeroElement++;
			$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
			$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");

			if ($t["id_fk_partieplante2_type_plante"] != "") {
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");
			}

			if ($t["id_fk_partieplante3_type_plante"] != "") {
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");
			}

			if ($t["id_fk_partieplante4_type_plante"] != "") {
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");
			}
		}

		$tabMenuMatieresPremieres[] = $tabPlantesBrutes;
		$tabMenuMatieresTransformees[] = $tabPlantesPreparees;

		$tabAutresPremieres = array("titre" => "Autres éléments");
		$tabAutresTransformees = array("titre" => "Autres éléments");

		$numeroElement++;
		$this->numeroElementPemiereMatiereAutre = $numeroElement;
		$tabAutresPremieres["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Peau", "type_element" => "peau");
		$tabAutresTransformees["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Cuir", "type_element" => "cuir");

		$numeroElement++;
		$tabAutresPremieres["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Viande fraîche", "type_element" => "viande");
		$tabAutresTransformees["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Fourrure", "type_element" => "fourrure");

		$numeroElement++;
		$tabAutresPremieres["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Rondin", "type_element" => "rondin");
		$tabAutresTransformees["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Planche", "type_element" => "planche");

		$numeroElement++;
		$tabAutresTransformees["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Viande préparée", "type_element" => "viande_preparee");

		$tabMenuMatieresPremieres["autres"] = $tabAutresPremieres;
		$tabMenuMatieresTransformees["autres"] = $tabAutresTransformees;

		$this->view->menuRechercheMatieresPremieres = $tabMenuMatieresPremieres;
		$this->view->menuRechercheMatieresTransformees = $tabMenuMatieresTransformees;
	}

	private function prepareUnitesRowPlante($type, $partiePlante, $num, $forme) {
		if ($forme == "brute") {
			$nomForme = "Brute";
		} else {
			$nomForme = "Préparée";
		}
		return array("id_type_plante" =>  $type["id_type_plante"],
					  "id_type_partieplante" => $type["id_fk_partieplante".$num."_type_plante"],
					  "nom_systeme_type_unite" => "plantebrute:".$type["nom_systeme_type_plante"] ,
					  "nom" => "Plante ".$nomForme.": ".$type["nom_type_plante"]. ' '.$partiePlante[$type["id_fk_partieplante".$num."_type_plante"]]["nom_partieplante"],
					  "table" => "TypePlante",
					  "type_forme" => $forme);
	}

	private function prepareMenuPotions() {
		Zend_Loader::loadClass("TypePotion");
		$typePotionTable = new TypePotion();
		$typesPotions = $typePotionTable->fetchAll(null, "nom_type_potion");
		$typesPotions = $typesPotions->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabPotion["elements"] = null;
		foreach($typesPotions as $e) {
			$numeroElement++;
			$tabPotion["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_potion"], "table" => "TypePotion", "id_element" => $e["id_type_potion"]);
		}

		$tab[] = $tabPotion;
		$this->view->menuRecherchePotion = $tab;
	}

	private function prepareMenuRunes() {
		Zend_Loader::loadClass("TypeRune");
		$typeRuneTable = new TypeRune();
		$typesRunes = $typeRuneTable->fetchAll(null, "nom_type_rune");
		$typesRunes = $typesRunes->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabRune["elements"] = null;
		foreach($typesRunes as $e) {
			$numeroElement++;
			$tabRune["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_rune"], "table" => "TypeRune", "id_element" => $e["id_type_rune"]);
		}

		$tab[] = $tabRune;
		$this->view->menuRechercheRune = $tab;
	}

	private function prepareMenuAliments() {
		Zend_Loader::loadClass("TypeAliment");
		$typeAlimentTable = new TypeAliment();
		$typesAliments = $typeAlimentTable->fetchAll(null, "nom_type_aliment");
		$typesAliments = $typesAliments->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabAliment["elements"] = null;
		foreach($typesAliments as $e) {
			$numeroElement++;
			$tabAliment["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_aliment"], "table" => "TypeAliment", "id_element" => $e["id_type_aliment"]);
		}

		$tab[] = $tabAliment;
		$this->view->menuRechercheAliment = $tab;
	}

	private function getNomElement($quantite, $element) {
		$s = "";
		if ($quantite > 1) {
			$s = "s";
		}

		if ($element == 'viande_preparee') {
			$nom = "viande".$s." préparée".$s;
		} elseif ($element == 'viande' && $quantite > 1) {
			$nom = "viande".$s." fraîche".$s;
		} else if ($element == 'peau' && $quantite > 1) {
			$nom = "peaux";
		} else {
			$nom = $element.$s;
		}

		return $nom;
	}
}