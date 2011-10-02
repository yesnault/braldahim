<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Coffre
{

	public static function prepareData(&$tabMetiers, &$view, $idBraldun, $idCommunaute)
	{
		Zend_Loader::loadClass("Coffre");
		Zend_Loader::loadClass("CoffreEquipement");
		Zend_Loader::loadClass("CoffreMateriel");
		Zend_Loader::loadClass("CoffreMinerai");
		Zend_Loader::loadClass("CoffrePartieplante");
		Zend_Loader::loadClass("CoffreAliment");
		Zend_Loader::loadClass("CoffreGraine");
		Zend_Loader::loadClass("CoffreIngredient");
		Zend_Loader::loadClass("CoffreMunition");
		Zend_Loader::loadClass("CoffrePotion");
		Zend_Loader::loadClass("CoffreRune");
		Zend_Loader::loadClass("CoffreTabac");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		Zend_Loader::loadClass("Bral_Helper_DetailRune");

		$tabCoffre = self::renderAutres(&$tabMetiers, $view, $idBraldun, $idCommunaute);
		self::renderRune($tabCoffre["id_coffre"], $view);
		self::renderMinerai($tabMetiers, $tabCoffre["id_coffre"], $view);
		self::renderPlante($tabMetiers, $tabCoffre["id_coffre"], $view);
		self::renderEquipement($tabCoffre["id_coffre"], $view);
		self::renderMunition($tabCoffre["id_coffre"], $view);
		self::renderPotion($tabCoffre["id_coffre"], $view);
		self::renderAliment($tabCoffre["id_coffre"], $view);
		self::renderGraine($tabCoffre["id_coffre"], $view);
		self::renderIngredient($tabMetiers, $tabCoffre, $view);
		self::renderTabac($tabCoffre["id_coffre"], $view);
		self::renderMateriel($tabCoffre["id_coffre"], $view);

		$view->coffre = $tabCoffre;
		$view->laban = $tabCoffre; // pour les poches

	}

	private static function renderAutres(&$tabMetiers, &$view, $idBraldun, $idCommunaute)
	{
		$tabCoffre = null;
		$coffreTable = new Coffre();
		if ($idBraldun != null) {
			$coffre = $coffreTable->findByIdBraldun($idBraldun);
		} elseif ($idCommunaute != null) {
			$coffre = $coffreTable->findByIdCommunaute($idCommunaute);
		} else {
			throw new Zend_Exception("Erreur Appel renderAutres");
		}

		unset($coffreTable);

		foreach ($coffre as $p) {
			$tabCoffre = array(
				"id_coffre" => $p["id_coffre"],
				"nb_peau" => $p["quantite_peau_coffre"],
				"nb_cuir" => $p["quantite_cuir_coffre"],
				"nb_fourrure" => $p["quantite_fourrure_coffre"],
				"nb_planche" => $p["quantite_planche_coffre"],
				"nb_castar" => $p["quantite_castar_coffre"],
				"nb_rondin" => $p["quantite_rondin_coffre"],
				"nb_viande" => 0, // remplit dans renderIngredient
				"nb_viande_poids_unitaire" => 0, // remplit dans renderIngredient
			);

			if ($p["quantite_peau_coffre"] > 0) {
				if (isset($tabMetiers["chasseur"])) {
					$tabMetiers["chasseur"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_cuir_coffre"] > 0 || $p["quantite_fourrure_coffre"] > 0) {
				if (isset($tabMetiers["tanneur"])) {
					$tabMetiers["tanneur"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_planche_coffre"] > 0) {
				if (isset($tabMetiers["menuisier"])) {
					$tabMetiers["menuisier"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_rondin_coffre"] > 0) {
				if (isset($tabMetiers["bucheron"])) {
					$tabMetiers["bucheron"]["a_afficher"] = true;
				}
			}
		}
		unset($coffre);

		return $tabCoffre;
	}

	private static function renderMinerai(&$tabMetiers, $idCoffre, &$view)
	{
		$tabMineraisBruts = null;
		$tabLingots = null;
		$coffreMineraiTable = new CoffreMinerai();
		$minerais = $coffreMineraiTable->findByIdCoffre($idCoffre);
		unset($coffreMineraiTable);

		foreach ($minerais as $m) {
			if ($m["quantite_brut_coffre_minerai"] > 0) {
				$tabMineraisBruts[] = array(
					"id_type_minerai" => $m["id_type_minerai"],
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_brut_coffre_minerai"],
					"poids" => $m["quantite_brut_coffre_minerai"] * Bral_Util_Poids::POIDS_MINERAI,
				);

				if (isset($tabMetiers["mineur"])) {
					$tabMetiers["mineur"]["a_afficher"] = true;
				}
			}
			if ($m["quantite_lingots_coffre_minerai"] > 0) {
				$tabLingots[] = array(
					"id_type_minerai" => $m["id_type_minerai"],
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_lingots_coffre_minerai"],
					"poids" => $m["quantite_lingots_coffre_minerai"] * Bral_Util_Poids::POIDS_LINGOT,
					"estLingot" => true,
				);

				if (isset($tabMetiers["forgeron"])) {
					$tabMetiers["forgeron"]["a_afficher"] = true;
				}
			}
		}
		unset($minerais);

		$view->mineraisBruts = $tabMineraisBruts;
		$view->lingots = $tabLingots;
	}

	private static function renderRune($idCoffre, &$view)
	{
		$tabRunesIdentifiees = null;
		$tabRunesNonIdentifiees = null;
		$coffreRuneTable = new CoffreRune();
		$runes = $coffreRuneTable->findByIdCoffre($idCoffre);
		unset($coffreRuneTable);
		$tri_type = null;
		foreach ($runes as $key => $r) {
			if ($r["est_identifiee_rune"] == "oui") {
				$tabRunesIdentifiees[] = array(
					"id_rune" => $r["id_rune_coffre_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
				);
				$tri_type[$key] = $r["nom_type_rune"];
			} else {
				$tabRunesNonIdentifiees[] = array(
					"id_rune" => $r["id_rune_coffre_rune"],
					"type" => $r["nom_type_rune"],
					"image" => "rune_inconnue.png",
					"est_identifiee" => $r["est_identifiee_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
				);
			}
		}
		if ($tabRunesIdentifiees != null) {
			array_multisort($tri_type, SORT_ASC, $tabRunesIdentifiees);
		}
		unset($runes);

		$view->nb_runes = count($tabRunesIdentifiees) + count($tabRunesNonIdentifiees);
		$view->runesIdentifiees = $tabRunesIdentifiees;
		$view->runesNonIdentifiees = $tabRunesNonIdentifiees;
	}

	private static function renderTabac($idCoffre, &$view)
	{
		$tabTabac = null;
		$coffreTabacTable = new CoffreTabac();
		$tabacs = $coffreTabacTable->findByIdCoffre($idCoffre);
		unset($coffreTabacTable);

		foreach ($tabacs as $m) {
			if ($m["quantite_feuille_coffre_tabac"] > 0) {
				$tabTabac[] = array(
					"type" => $m["nom_type_tabac"],
					"id_type_tabac" => $m["id_type_tabac"],
					"quantite" => $m["quantite_feuille_coffre_tabac"],
				);
			}
		}
		unset($tabacs);
		$view->tabac = $tabTabac;
	}

	private static function renderPlante(&$tabMetiers, $idCoffre, &$view)
	{
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);

		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();

		$tabTypePlantes = null;
		$coffrePartiePlanteTable = new CoffrePartieplante();
		$partiePlantes = $coffrePartiePlanteTable->findByIdCoffre($idCoffre);
		unset($coffrePartiePlanteTable);

		foreach ($typePartiePlantesRowset as $p) {
			foreach ($typePlantesRowset as $t) {
				$val = false;
				for ($i = 1; $i <= 4; $i++) {
					if ($t["id_fk_partieplante" . $i . "_type_plante"] == $p["id_type_partieplante"]) {
						$val = true;
					}
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
		unset($typePartiePlantesRowset);
		unset($typePlantesRowset);

		$tabTypePlantesBruts = $tabTypePlantes;
		$tabTypePlantesPrepares = $tabTypePlantes;

		foreach ($partiePlantes as $p) {
			if ($p["quantite_coffre_partieplante"] > 0) {
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_coffre_partieplante"];
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_coffre_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
				if (isset($tabMetiers["herboriste"])) {
					$tabMetiers["herboriste"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_preparee_coffre_partieplante"] > 0) {
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_preparee_coffre_partieplante"];
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_preparee_coffre_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;
				if (isset($tabMetiers["apothicaire"])) {
					$tabMetiers["apothicaire"]["a_afficher"] = true;
				}
			}
		}
		unset($partiePlantes);

		$view->typePlantesBruts = $tabTypePlantesBruts;
		$view->typePlantesPrepares = $tabTypePlantesPrepares;
	}

	private static function renderMateriel($idCoffre, &$view)
	{
		$tabMateriels = null;
		$coffreMaterielTable = new CoffreMateriel();
		$materiels = $coffreMaterielTable->findByIdCoffre($idCoffre);
		unset($coffreMaterielTable);

		$tabWhere = null;
		foreach ($materiels as $e) {
			$tabMateriels[$e["id_coffre_materiel"]] = array(
				"id_materiel" => $e["id_coffre_materiel"],
				'id_type_materiel' => $e["id_type_materiel"],
				'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
				'nom' => $e["nom_type_materiel"],
				'capacite' => $e["capacite_type_materiel"],
				'durabilite' => $e["durabilite_type_materiel"],
				'usure' => $e["usure_type_materiel"],
				'poids' => $e["poids_type_materiel"],
			);
			$tabWhere[] = $e["id_coffre_materiel"];
		}
		unset($materiels);

		$view->nb_materiels = count($tabMateriels);
		$view->materiels = $tabMateriels;
	}

	private static function renderEquipement($idCoffre, &$view)
	{
		$tabEquipements = null;
		$coffreEquipementTable = new CoffreEquipement();
		$equipements = $coffreEquipementTable->findByIdCoffre($idCoffre);
		unset($coffreEquipementTable);

		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEquipements = Bral_Util_Equipement::prepareTabEquipements($equipements);

		$tabRetour = null;
		if ($tabEquipements != null) {
			foreach ($tabEquipements as $e) {
				$tabRetour[$e["id_type_emplacement"]]["equipements"][] = $e;
				$tabRetour[$e["id_type_emplacement"]]["nom_type_emplacement"] = $e["emplacement"];
			}
		}

		$view->nb_equipements = count($tabEquipements);
		$view->equipements = $tabRetour;
	}

	private static function renderMunition($idCoffre, &$view)
	{
		$tabMunitions = null;
		$coffreMunitionTable = new CoffreMunition();
		$munitions = $coffreMunitionTable->findByIdCoffre($idCoffre);
		unset($coffreMunitionTable);

		foreach ($munitions as $m) {
			$tabMunitions[] = array(
				'id_type_munition' => $m['id_type_munition'],
				"type" => $m["nom_type_munition"],
				"quantite" => $m["quantite_coffre_munition"],
				"poids" => $m["quantite_coffre_munition"] * Bral_Util_Poids::POIDS_MUNITION,
			);
		}
		unset($munitions);

		$view->nb_munitions = count($tabMunitions);
		$view->munitions = $tabMunitions;
	}

	private static function renderPotion($idCoffre, &$view)
	{
		Zend_Loader::loadClass("Bral_Util_Potion");
		$tabPotions = null;
		$coffrePotionTable = new CoffrePotion();
		$potions = $coffrePotionTable->findByIdCoffre($idCoffre);
		unset($coffrePotionTable);

		foreach ($potions as $p) {
			$tabPotions[$p["id_coffre_potion"]] = array(
				"id_potion" => $p["id_coffre_potion"],
				"id_type_potion" => $p["id_type_potion"],
				"nom" => $p["nom_type_potion"],
				"qualite" => $p["nom_type_qualite"],
				"niveau" => $p["niveau_potion"],
				"caracteristique" => $p["caract_type_potion"],
				"bm_type" => $p["bm_type_potion"],
				"caracteristique2" => $p["caract2_type_potion"],
				"bm2_type" => $p["bm2_type_potion"],
				"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
			);
		}
		unset($potions);

		$view->nb_potions = count($tabPotions);
		$view->potions = $tabPotions;
	}

	private static function renderAliment($idCoffre, &$view)
	{
		$tabAliments = null;
		$coffreAlimentTable = new CoffreAliment();
		$aliments = $coffreAlimentTable->findByIdCoffre($idCoffre);
		unset($coffreAlimentTable);

		Zend_Loader::loadClass("Bral_Util_Aliment");
		foreach ($aliments as $p) {
			$tabAliments[$p["id_coffre_aliment"]] = array(
				"id_aliment" => $p["id_coffre_aliment"],
				"id_type_aliment" => $p["id_type_aliment"],
				"nom" => $p["nom_type_aliment"],
				"qualite" => $p["nom_aliment_type_qualite"],
				"bbdf" => $p["bbdf_aliment"],
				"recette" => Bral_Util_Aliment::getNomType($p["type_bbdf_type_aliment"]),
				"poids" => $p["poids_unitaire_type_aliment"],
			);
		}
		unset($aliments);

		$view->nb_aliments = count($tabAliments);
		$view->aliments = $tabAliments;
	}

	private static function renderGraine($idCoffre, &$view)
	{
		$tabGraines = null;
		$coffreGraineTable = new CoffreGraine();
		$graines = $coffreGraineTable->findByIdCoffre($idCoffre);
		unset($coffreGraineTable);

		foreach ($graines as $g) {
			if ($g["quantite_coffre_graine"] > 0) {
				$tabGraines[] = array(
					"type" => $g["nom_type_graine"],
					"id_type_graine" => $g["id_type_graine"],
					"quantite" => $g["quantite_coffre_graine"],
					"poids" => $g["quantite_coffre_graine"] * Bral_Util_Poids::POIDS_POIGNEE_GRAINES,
				);
			}
		}
		unset($graines);

		$view->nb_graines = count($tabGraines);
		$view->graines = $tabGraines;
	}

	private static function renderIngredient(&$tabMetiers, &$tabCoffre, &$view)
	{
		$tabIngredients = null;
		$coffreIngredientTable = new CoffreIngredient();
		$ingredients = $coffreIngredientTable->findByIdCoffre($tabCoffre["id_coffre"]);
		unset($coffreIngredientTable);

		Zend_Loader::loadClass("TypeIngredient");
		foreach ($ingredients as $g) {
			if ($g["quantite_coffre_ingredient"] > 0) {
				if ($g["id_type_ingredient"] == TypeIngredient::ID_TYPE_VIANDE_FRAICHE) {
					if (isset($tabMetiers["chasseur"])) {
						$tabMetiers["chasseur"]["a_afficher"] = true;
					}
					$tabCoffre["nb_viande"] = $g["quantite_coffre_ingredient"];
					$tabCoffre["nb_viande_poids_unitaire"] = $g["poids_unitaire_type_ingredient"];
				} else {
					$tabIngredients[] = array(
						"type" => $g["nom_type_ingredient"],
						"id_type_ingredient" => $g["id_type_ingredient"],
						"quantite" => $g["quantite_coffre_ingredient"],
						"poids" => $g["quantite_coffre_ingredient"] * $g["poids_unitaire_type_ingredient"],
					);
					if (isset($tabMetiers["cuisinier"])) {
						$tabMetiers["cuisinier"]["a_afficher"] = true;
					}
				}
			}
		}
		unset($ingredients);

		$view->nb_ingredients = count($tabIngredients);
		$view->ingredients = $tabIngredients;
	}
}
