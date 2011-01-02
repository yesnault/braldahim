<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Echoppes_Voir extends Bral_Echoppes_Echoppe {

	private $arBoutiqueBruts;
	private $arBoutiqueTransformes;

	function __construct($nomSystemeAction, $request, $view, $action, $id_echoppe = false) {
		Zend_Loader::loadClass("Echoppe");

		if ($id_echoppe !== false) {
			$this->idEchoppe = $id_echoppe;
		}
		parent::__construct($nomSystemeAction, $request, $view, $action);
	}

	function getNomInterne() {
		return "box_echoppe";
	}

	function render() {
		Zend_Loader::loadClass("Bral_Helper_DetailPrix");
		return $this->view->render("echoppes/voir.phtml");
	}

	function prepareCommun() {
		
		if (!isset($this->idEchoppe)) {
			$id_echoppe = (int)$this->request->get("valeur_1");
		} else {
			$id_echoppe = $this->idEchoppe;
		}

		$this->arBoutiqueBruts["rondins"] = array("nom_systeme" => "rondins", "nom" => "Rondins", "a_afficher" => false);
		$this->arBoutiqueBruts["minerais"] = array("nom_systeme" => "minerais", "nom" => "Minerais Bruts", "a_afficher" => false);
		$this->arBoutiqueBruts["plantes_bruts"] = array("nom_systeme" => "plantes_bruts", "nom" => "Plantes Brutes", "a_afficher" => false);
		$this->arBoutiqueBruts["peaux"] = array("nom_systeme" => "peaux", "nom" => "Peaux", "a_afficher" => false);

		$this->arBoutiqueTransformes["planches"] = array("nom_systeme" => "planches", "nom" => "Planches", "a_afficher" => false);
		$this->arBoutiqueTransformes["lingots"] = array("nom_systeme" => "lingots", "nom" => "Lingots", "a_afficher" => false);
		$this->arBoutiqueTransformes["plantes_preparees"] = array("nom_systeme" => "plantes_preparees", "nom" => "Plantes Préparées", "a_afficher" => false);
		$this->arBoutiqueTransformes["cuir_fourrure"] = array("nom_systeme" => "cuir_fourrure", "nom" => "Cuir / Fourrure", "a_afficher" => false);
		$this->arBoutiqueTransformes["ingredients"] = array("nom_systeme" => "ingredients", "nom" => "Ingredients", "a_afficher" => false);
		$this->arBoutiqueTransformes["potions"] = array("nom_systeme" => "potions", "nom" => "Potions", "a_afficher" => false);

		$this->arBoutiqueCaisse["castars"]  = array("nom_systeme" => "castars", "nom" => "Castars", "a_afficher" => true);
		$this->arBoutiqueCaisse["minerais"] = array("nom_systeme" => "minerais", "nom" => "Minerais Bruts", "a_afficher" => false);
		$this->arBoutiqueCaisse["rondins"]  = array("nom_systeme" => "rondins", "nom" => "Rondins", "a_afficher" => false);
		$this->arBoutiqueCaisse["plantes_bruts"] = array("nom_systeme" => "plantes_bruts", "nom" => "Plantes Brutes", "a_afficher" => false);
		$this->arBoutiqueCaisse["peaux"]  = array("nom_systeme" => "peaux", "nom" => "Peaux", "a_afficher" => false);
		$this->arBoutiqueCaisse["ingredients"]  = array("nom_systeme" => "ingredients", "nom" => "Ingrédients", "a_afficher" => false);

		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdBraldun($this->view->user->id_braldun);

		$this->view->estSurEchoppe == false;
		$this->view->afficheType = "equipements";

		$tabEchoppe = null;
		$id_metier = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe) {
				if ($this->view->user->sexe_braldun == 'feminin') {
					$nom_metier = $e["nom_feminin_metier"];
				} else {
					$nom_metier = $e["nom_masculin_metier"];
				}
				$id_metier = $e["id_metier"];
				$tabEchoppe = array(
					'id_echoppe' => $e["id_echoppe"],
					'x_echoppe' => $e["x_echoppe"],
					'y_echoppe' => $e["y_echoppe"],
					'id_metier' => $e["id_metier"],
					'nom_metier' => $nom_metier,
					'nom_region' => $e["nom_region"],
					'nom_echoppe' => $e["nom_echoppe"],
					'commentaire_echoppe' => $e["commentaire_echoppe"],
					'quantite_castar_caisse_echoppe' => $e["quantite_castar_caisse_echoppe"],
					'quantite_rondin_caisse_echoppe' => $e["quantite_rondin_caisse_echoppe"],
					'quantite_peau_caisse_echoppe' => $e["quantite_peau_caisse_echoppe"],
					'quantite_rondin_arriere_echoppe' => $e["quantite_rondin_arriere_echoppe"],
					'quantite_peau_arriere_echoppe' => $e["quantite_peau_arriere_echoppe"],
					'quantite_cuir_arriere_echoppe' => $e["quantite_cuir_arriere_echoppe"],
					'quantite_fourrure_arriere_echoppe' => $e["quantite_fourrure_arriere_echoppe"],
					'quantite_planche_arriere_echoppe' => $e["quantite_planche_arriere_echoppe"],
				);

				if ($e["quantite_rondin_arriere_echoppe"] > 0) {
					$this->arBoutiqueBruts["rondins"]["a_afficher"] = true;
				}

				if ($e["quantite_peau_arriere_echoppe"] > 0) {
					$this->arBoutiqueBruts["peaux"]["a_afficher"] = true;
				}

				if ($e["quantite_planche_arriere_echoppe"] > 0) {
					$this->arBoutiqueTransformes["planches"]["a_afficher"] = true;
				}

				if ($e["quantite_fourrure_arriere_echoppe"] > 0 || $e["quantite_cuir_arriere_echoppe"] > 0) {
					$this->arBoutiqueTransformes["cuir_fourrure"]["a_afficher"] = true;
				}

				if ($e["quantite_castar_caisse_echoppe"] > 0) {
					$this->arBoutiqueCaisse["castars"]["a_afficher"] = true;
				}

				if ($e["quantite_rondin_caisse_echoppe"] > 0) {
					$this->arBoutiqueCaisse["rondins"]["a_afficher"] = true;
				}

				if ($e["quantite_peau_caisse_echoppe"] > 0) {
					$this->arBoutiqueCaisse["peaux"]["a_afficher"] = true;
				}

				if ($this->view->user->x_braldun == $e["x_echoppe"] &&
				$this->view->user->y_braldun == $e["y_echoppe"]) {
					$this->view->estSurEchoppe = true;
				}
				if ($e["nom_systeme_metier"] == "apothicaire") {
					$this->view->afficheType = "potions";
				} elseif ($e["nom_systeme_metier"] == "cuisinier") {
					$this->view->afficheType = "aliments";
				}

				$this->view->afficheTypeMunitions = false;
				if ($e["nom_systeme_metier"] == "menuisier") {
					$this->view->afficheTypeMunitions = true;
				}
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_braldun." ide:".$id_echoppe);
		}

		Zend_Loader::loadClass("BraldunsCompetences");
		$braldunsCompetencesTables = new BraldunsCompetences();
		$braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($this->view->user->id_braldun);

		$competence = null;
		$tabCompetences = null;
		foreach($braldunCompetences as $c) {
			if ($id_metier == $c["id_fk_metier_competence"]) {
				$pa_texte = $c["pa_utilisation_competence"];
				if ($c["nom_systeme_competence"] == "cuisiner") {
					$pa_texte = "2 ou 4";
				}

				$tabCompetences[] = array("id_competence" => $c["id_fk_competence_hcomp"],
					"nom" => $c["nom_competence"],
					"pa_utilisation" => $c["pa_utilisation_competence"],
					"pa_texte" => $pa_texte,
					"pourcentage" => Bral_Util_Commun::getPourcentage($c, $this->view->config),
					"nom_systeme" => $c["nom_systeme_competence"],
					"pourcentage_init" => $c["pourcentage_init_competence"],
				);
			}
		}

		$this->prepareCommunRessources($tabEchoppe["id_echoppe"]);
		$this->prepareCommunEquipements($tabEchoppe["id_echoppe"]);
		if ($this->view->afficheType == "potions") {
			$this->prepareCommunPotions($tabEchoppe["id_echoppe"]);
		}
		if ($this->view->afficheTypeMunitions == true) {
			$this->prepareCommunMunitions($tabEchoppe["id_echoppe"]);
		}
		$this->prepareCommunMateriels($tabEchoppe["id_echoppe"]);
		$this->prepareCommunAliments($tabEchoppe["id_echoppe"]);

		Zend_Loader::loadClass("Bral_Util_Lot");
		$this->view->lots = Bral_Util_Lot::getLotsByIdEchoppe($tabEchoppe["id_echoppe"], $this->view, false);

		$this->view->arBoutiqueBruts = $this->arBoutiqueBruts;
		$this->view->arBoutiqueTransformes = $this->arBoutiqueTransformes;
		$this->view->arBoutiqueCaisse = $this->arBoutiqueCaisse;

		$this->view->competences = $tabCompetences;
		$this->view->echoppe = $tabEchoppe;
		$this->view->estElementsEtal = true;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = false;
		$this->view->pocheNom = "Poche";
		$this->view->pocheNomSysteme = "Etal";
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}

	private function prepareCommunRessources($idEchoppe) {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass("EchoppeIngredient");
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		Zend_Loader::loadClass("Bral_Util_Potion");

		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();

		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();

		$tabPartiePlantesCaisse = null;
		$tabPartiePlantesPreparees = null;
		$tabPartiePlantesBruts = null;

		foreach($typePartiePlantesRowset as $p) {
			foreach($typePlantesRowset as $t) {
				$val = false;
				if ($t["id_fk_partieplante1_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante2_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante3_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante4_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}

				if (!isset($tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]])) {
					$tab = array(
						'nom_type_plante' => $t["nom_type_plante"],
						'nom_systeme_type_plante' => $t["nom_systeme_type_plante"],
					);
					$tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]] = $tab;
				}

				$tabTypePlantes[$t["categorie_type_plante"]]["a_afficher"] = false;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["a_afficher"] = false;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["possible"] = $val;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = 0;
			}
		}

		$tabPartiePlantesCaisse = $tabTypePlantes;
		$tabPartiePlantesPreparees = $tabTypePlantes;
		$tabPartiePlantesBruts = $tabTypePlantes;

		$echoppePartiePlanteTable = new EchoppePartieplante();
		$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($idEchoppe);

		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				if ($p["quantite_caisse_echoppe_partieplante"] > 0) {
					$this->arBoutiqueCaisse["plantes_bruts"]["a_afficher"] = true;
					$tabPartiePlantesCaisse[$p["categorie_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesCaisse[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesCaisse[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_caisse_echoppe_partieplante"];
					$tabPartiePlantesCaisse[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["id_type_partieplante"] = $p["id_type_partieplante"];
					$tabPartiePlantesCaisse[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["estPreparee"] = false;
					$tabPartiePlantesCaisse[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_caisse_echoppe_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
				}

				if ($p["quantite_arriere_echoppe_partieplante"] > 0) {
					$this->arBoutiqueBruts["plantes_bruts"]["a_afficher"] = true;
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_arriere_echoppe_partieplante"];
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["id_type_partieplante"] = $p["id_type_partieplante"];
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["estPreparee"] = false;
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_arriere_echoppe_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
				}

				if ($p["quantite_preparee_echoppe_partieplante"] > 0) {
					$this->arBoutiqueTransformes["plantes_preparees"]["a_afficher"] = true;
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_preparee_echoppe_partieplante"];
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["id_type_partieplante"] = $p["id_type_partieplante"];
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["estPreparee"] = true;
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_preparee_echoppe_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;
				}

				$this->view->nb_caissePartiePlantes = $this->view->nb_caissePartiePlantes + $p["quantite_caisse_echoppe_partieplante"];
				$this->view->nb_arrierePartiePlantes = $this->view->nb_arrierePartiePlantes + $p["quantite_arriere_echoppe_partieplante"];
				$this->view->nb_prepareePartiePlantes = $this->view->nb_prepareePartiePlantes  + $p["quantite_preparee_echoppe_partieplante"];
			}
		}

		$this->view->typePlantesCaisse = $tabPartiePlantesCaisse;
		$this->view->typePlantesBruts = $tabPartiePlantesBruts;
		$this->view->typePlantesPrepares = $tabPartiePlantesPreparees;

		$tabMineraisArriere = null;
		$tabMineraisCaisse = null;
		$tabLingots = null;

		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_caisseMinerai = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				$tabMineraisArriere[] = array(
					"type" => $m["nom_type_minerai"],
					"id_type_minerai" => $m["id_type_minerai"],
					"estLingot" => false,
					"quantite" => $m["quantite_brut_arriere_echoppe_minerai"],
					"poids" => $m["quantite_brut_arriere_echoppe_minerai"] * Bral_Util_Poids::POIDS_MINERAI,
				);
				$tabLingots[] = array(
					"type" => $m["nom_type_minerai"],
					"id_type_minerai" => $m["id_type_minerai"],
					"estLingot" => true,
					"quantite" => $m["quantite_lingots_echoppe_minerai"],
					"poids" => $m["quantite_lingots_echoppe_minerai"] * Bral_Util_Poids::POIDS_LINGOT,
				);
				$tabMineraisCaisse[] = array(
					"type" => $m["nom_type_minerai"],
					"id_type_minerai" => $m["id_type_minerai"],
					"estLingot" => false,
					"quantite" => $m["quantite_brut_caisse_echoppe_minerai"],
					"poids" => $m["quantite_brut_caisse_echoppe_minerai"] * Bral_Util_Poids::POIDS_MINERAI,
				);

				if ($m["quantite_brut_arriere_echoppe_minerai"] > 0) {
					$this->arBoutiqueBruts["minerais"]["a_afficher"] = true;
				}

				if ($m["quantite_lingots_echoppe_minerai"] > 0) {
					$this->arBoutiqueTransformes["lingots"]["a_afficher"] = true;
				}

				if ($m["quantite_brut_caisse_echoppe_minerai"] > 0) {
					$this->arBoutiqueCaisse["minerais"]["a_afficher"] = true;
				}

				$this->view->nb_caisseMinerai = $this->view->nb_caisseMinerai + $m["quantite_brut_caisse_echoppe_minerai"];
			}
		}

		$this->view->mineraisArriere = $tabMineraisArriere;
		$this->view->mineraisCaisse = $tabMineraisCaisse;
		$this->view->lingots = $tabLingots;

		$tabIngredientsArriere = null;
		$tabIngredientsCaisse = null;

		$echoppeIngredientTable = new EchoppeIngredient();
		$ingredients = $echoppeIngredientTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_caisseIngredient = 0;

		if ($ingredients != null) {
			foreach ($ingredients as $m) {
				$tabIngredientsArriere[] = array(
					"type" => $m["nom_type_ingredient"],
					"id_type_ingredient" => $m["id_type_ingredient"],
					"quantite" => $m["quantite_arriere_echoppe_ingredient"],
					"poids" => $m["quantite_arriere_echoppe_ingredient"] * $m["poids_unitaire_type_ingredient"],
				);
				$tabIngredientsCaisse[] = array(
					"type" => $m["nom_type_ingredient"],
					"id_type_ingredient" => $m["id_type_ingredient"],
					"quantite" => $m["quantite_caisse_echoppe_ingredient"],
					"poids" => $m["quantite_caisse_echoppe_ingredient"] * $m["poids_unitaire_type_ingredient"],
				);

				if ($m["quantite_arriere_echoppe_ingredient"] > 0) {
					$this->arBoutiqueTransformes["ingredients"]["a_afficher"] = true;
				}

				if ($m["quantite_caisse_echoppe_ingredient"] > 0) {
					$this->arBoutiqueCaisse["ingredients"]["a_afficher"] = true;
				}

				$this->view->nb_caisseIngredient = $this->view->nb_caisseIngredient + $m["quantite_caisse_echoppe_ingredient"];
			}
		}

		$this->view->ingredientsArriere = $tabIngredientsArriere;
		$this->view->ingredientsCaisse = $tabIngredientsCaisse;

		$tabPotionsArriere = null;

		if ($this->view->afficheType == "aliments") {
			Zend_Loader::loadClass("Bral_Util_Potion");
			$echoppePotionTable = new EchoppePotion();
			$potions = $echoppePotionTable->findByIdEchoppe($idEchoppe);

			if ($potions != null) {
				foreach ($potions as $p) {
					$tabPotionsArriere[] = array(
						"id_potion" => $p["id_echoppe_potion"],
						"nom" => $p["nom_type_potion"],
						"id_type_potion" => $p["id_type_potion"],
						"qualite" => $p["nom_type_qualite"],
						"niveau" => $p["niveau_potion"],
						"caracteristique" => $p["caract_type_potion"],
						"bm_type" => $p["bm_type_potion"],
						"caracteristique2" => $p["caract2_type_potion"],
						"bm2_type" => $p["bm2_type_potion"],
						"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
					);

					$this->arBoutiqueTransformes["potions"]["a_afficher"] = true;
				}
			}
		}

		$this->view->potionsArriere = $tabPotionsArriere;
		$this->view->idPotionsArriereTable = "idPotionsArriereTable";
	}

	private function prepareCommunEquipements($idEchoppe) {
		Zend_Loader::loadClass("Bral_Util_Equipement");
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("EquipementRune");
		Zend_Loader::loadClass("EquipementBonus");

		$tabEquipementsArriereBoutique = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($idEchoppe);
		$idEquipements = null;

		foreach ($equipements as $e) {
			$idEquipements[] = $e["id_echoppe_equipement"];
		}

		if (count($idEquipements) > 0) {
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

			$equipementBonusTable = new EquipementBonus();
			$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
		}

		if (count($equipements) > 0) {
			foreach($equipements as $e) {
					
				$runes = null;
				if (count($equipementRunes) > 0) {
					foreach($equipementRunes as $r) {
						if ($r["id_equipement_rune"] == $e["id_echoppe_equipement"]) {
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
						if ($b["id_equipement_bonus"] == $e["id_echoppe_equipement"]) {
							$bonus = $b;
							break;
						}
					}
				}

				$equipement = array(
					"id_equipement" => $e["id_echoppe_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"id_type_equipement" => $e["id_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"emplacement" => $e["nom_type_emplacement"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nom_systeme_type_piece" => $e["nom_systeme_type_piece"],
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
					"suffixe" => $e["suffixe_mot_runique"],
					"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"],
					"id_fk_region" => $e["id_fk_region_equipement"],
					"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
					"etat_courant" => $e["etat_courant_equipement"],
					"etat_initial" => $e["etat_initial_equipement"],
					"ingredient" => $e["nom_type_ingredient"],
					"runes" => $runes,
					"bonus" => $bonus,
					"poids" => $e["poids_equipement"],
				);

				$tabEquipementsArriereBoutique[$e["id_type_emplacement"]]["equipements"][] = $equipement;
				$tabEquipementsArriereBoutique[$e["id_type_emplacement"]]["nom_type_emplacement"] = $e["nom_type_emplacement"];
			}
		}
		$this->view->equipementsArriereBoutique = $tabEquipementsArriereBoutique;

		$this->view->idEquipementsArriereBoutiqueTable = "idEquipementsArriereBoutiqueTable";
	}

	private function prepareCommunMateriels($idEchoppe) {
		Zend_Loader::loadClass("EchoppeMateriel");

		$tabMaterielsArriereBoutique = null;
		$echoppeMaterielTable = new EchoppeMateriel();
		$materiels = $echoppeMaterielTable->findByIdEchoppe($idEchoppe);

		if (count($materiels) > 0) {
			foreach($materiels as $e) {
				$materiel = array(
					"id_materiel" => $e["id_echoppe_materiel"],
					'id_type_materiel' => $e["id_type_materiel"],
					'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
					'nom' =>$e["nom_type_materiel"],
					'capacite' => $e["capacite_type_materiel"], 
					'durabilite' => $e["durabilite_type_materiel"], 
					'usure' => $e["usure_type_materiel"], 
					'poids' => $e["poids_type_materiel"], 
				);

				$tabMaterielsArriereBoutique[] = $materiel;
			}
		}
		$this->view->materielsArriereBoutique = $tabMaterielsArriereBoutique;
		$this->view->idMaterielsArriereBoutiqueTable = "idMaterielsArriereBoutiqueTable";
	}

	private function prepareCommunAliments($idEchoppe) {
		Zend_Loader::loadClass("EchoppeAliment");
		Zend_Loader::loadClass("Bral_Util_Aliment");

		$tabAlimentsArriereBoutique = null;
		$echoppeAlimentTable = new EchoppeAliment();
		$aliments = $echoppeAlimentTable->findByIdEchoppe($idEchoppe);

		if (count($aliments) > 0) {
			foreach($aliments as $e) {
				$aliment = array(
					"id_aliment" => $e["id_echoppe_aliment"],
					'id_type_aliment' => $e["id_type_aliment"],
					'nom_systeme_type_aliment' => $e["nom_systeme_type_aliment"],
					'nom' =>$e["nom_type_aliment"],
					'poids' => $e["poids_unitaire_type_aliment"],
					"qualite" => $e["nom_aliment_type_qualite"],
					"bbdf" => $e["bbdf_aliment"],
					"recette" => Bral_Util_Aliment::getNomType($e["type_bbdf_type_aliment"]),
				);

				$tabAlimentsArriereBoutique[] = $aliment;
			}
		}
		$this->view->alimentsArriereBoutique = $tabAlimentsArriereBoutique;
		$this->view->idAlimentsArriereBoutiqueTable = "idAlimentsArriereBoutiqueTable";
	}

	private function prepareCommunMunitions($idEchoppe) {
		Zend_Loader::loadClass("EchoppeMunition");

		$tabMunitionsArriereBoutique = null;
		$echoppeMunitionTable = new EchoppeMunition();
		$munitions = $echoppeMunitionTable->findByIdEchoppe($idEchoppe);

		if (count($munitions) > 0) {
			foreach($munitions as $e) {
					
				$munition = array(
					'type' => $e["nom_type_munition"],
					'id_type' => $e["id_type_munition"],
					'nom_systeme_type_munition' => $e["nom_systeme_type_munition"],
					'nom' => $e["nom_type_munition"],
					'quantite' => $e["quantite_echoppe_munition"],
					'poids' => $e["quantite_echoppe_munition"] * Bral_Util_Poids::POIDS_MUNITION,
				);

				$tabMunitionsArriereBoutique[] = $munition;
			}
		}
		$this->view->munitionsArriereBoutique = $tabMunitionsArriereBoutique;
	}

	private function prepareCommunPotions($idEchoppe) {
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("Bral_Util_Potion");

		$tabPotionsArriereBoutique = null;
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($idEchoppe);

		if (count($potions) > 0) {
			foreach($potions as $p) {
				$tab = array(
					"id_potion" => $p["id_echoppe_potion"],
					"nom" => $p["nom_type_potion"],
					"id_type_potion" => $p["id_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
				);
					
				$tabPotionsArriereBoutique[] = $tab;
			}
		}
		$this->view->potionsArriereBoutique = $tabPotionsArriereBoutique;
		$this->view->idPotionsArriereBoutiqueTable = "idPotionsArriereBoutiqueTable";
	}

	public function getIdEchoppeCourante() {
		return false;
	}

	public function getTablesHtmlTri() {
		$tab = false;
		if ($this->view->afficheType == "equipements") {
			/*if (count($this->view->equipementsArriereBoutique) > 0) {
				$tab[] = $this->view->idEquipementsArriereBoutiqueTable;
				}
				}*/
		} elseif ($this->view->afficheType == "potions") {
			if (count($this->view->potionsArriereBoutique) > 0) {
				$tab[] = $this->view->idPotionsArriereBoutiqueTable;
			}
		} elseif ($this->view->afficheType == "aliments") {
			if (count($this->view->alimentsArriereBoutique) > 0) {
				$tab[] = $this->view->idAlimentsArriereBoutiqueTable;
			}
		}

		if ($this->view->afficheType == "equipements" || $this->view->afficheType == "potions") {
			if (count($this->view->materielsArriereBoutique) > 0) {
				$tab[] = $this->view->idMaterielsArriereBoutiqueTable;
			}
		}
		return $tab;
	}
}