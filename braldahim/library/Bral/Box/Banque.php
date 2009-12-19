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
class Bral_Box_Banque extends Bral_Box_Box {

	public function getTitreOnglet() {
		return "Banque";
	}

	function getNomInterne() {
		return "box_lieu";
	}

	function getChargementInBoxes() {
		return false;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->view->nom_interne = $this->getNomInterne();
			$this->preData();
			$this->data();
			$this->view->pocheNom = "Tiroir";
			$this->view->pocheNomSysteme = "Banque";
			$this->view->afficheTabac = false;
			$this->view->nb_castars = $this->view->coffre["nb_castar"];
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/banque.phtml");
	}

	protected function preData() {
		Zend_Loader::loadClass("Lieu");

		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);
		unset($lieuxTable);

		if (count($lieuRowset) <= 0) {
			throw new Zend_Exception("Bral_Box_Banque::nombre de lieux invalide <= 0 !");
		} elseif (count($lieuRowset) > 1) {
			throw new Zend_Exception("Bral_Box_Banque::nombre de lieux invalide > 1 !");
		} elseif (count($lieuRowset) == 1) {
			$lieu = $lieuRowset[0];
			unset($lieuRowset);
			$this->view->nomLieu = $lieu["nom_lieu"];
			$this->view->paUtilisationBanque = $lieu["pa_utilisation_type_lieu"];
		}

	}

	protected function data() {

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
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("Metier");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");

		Zend_Loader::loadClass("Bral_Helper_DetailRune");

		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		unset($hobbitsMetiersTable);

		$metiersTable = new Metier();
		$metiersRowset = $metiersTable->fetchall(null, "nom_masculin_metier");
		unset($metiersTable);
		$metiersRowset = $metiersRowset->toArray();
		$tabHobbitMetiers = null;
		$tabMetiers = null;

		foreach($metiersRowset as $m) {
			if ($this->view->user->sexe_hobbit == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}

			$possedeMetier = false;
			foreach($hobbitsMetierRowset as $h) {
				if ($h["id_metier"] == $m["id_metier"]) {
					$possedeMetier = true;
					break;
				}
			}

			if ($possedeMetier == true) {
				$tabHobbitMetiers[$m["nom_systeme_metier"]] = array(
						"id_metier" => $m["id_metier"],
						"nom" => $nom_metier,
						"nom_systeme" => $m["nom_systeme_metier"],
						"a_afficher" => true,
				);
			} else {
				$tabMetiers[$m["nom_systeme_metier"]] = array(
					"id_metier" => $m["id_metier"],
					"nom" => $m["nom_masculin_metier"],
					"nom_systeme" => $m["nom_systeme_metier"],
					"a_afficher" => false,
				);
			}
		}
		unset($metiersRowset);

		$tabMineraisBruts = null;
		$tabLingots = null;
		$coffreMineraiTable = new CoffreMinerai();
		$minerais = $coffreMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
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

		$tabCoffre = null;
		$coffreTable = new Coffre();
		$coffre = $coffreTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreTable);

		foreach ($coffre as $p) {
			$tabCoffre = array(
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

		$tabRunesIdentifiees = null;
		$tabRunesNonIdentifiees = null;
		$coffreRuneTable = new CoffreRune();
		$runes = $coffreRuneTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreRuneTable);

		foreach ($runes as $r) {
			if ($r["est_identifiee_rune"] == "oui") {
				$tabRunesIdentifiees[] = array(
					"id_rune" => $r["id_rune_coffre_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
				);
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
		unset($runes);

		$this->view->mineraisBruts = $tabMineraisBruts;
		$this->view->lingots = $tabLingots;

		$this->view->nb_runes = count($tabRunesIdentifiees) + count($tabRunesNonIdentifiees);
		$this->view->runesIdentifiees = $tabRunesIdentifiees;
		$this->view->runesNonIdentifiees = $tabRunesNonIdentifiees;

		$this->renderPlante($tabMetiers);
		$this->renderEquipement();
		$this->renderMunition();
		$this->renderPotion();
		$this->renderAliment();
		$this->renderGraine();
		$this->renderIngredient($tabMetiers, $tabCoffre);
		$this->renderTabac();
		$this->renderMateriel();

		$this->view->tabMetiers = $tabMetiers;
		$this->view->tabHobbitMetiers = $tabHobbitMetiers;
		$this->view->coffre = $tabCoffre;
		$this->view->laban = $tabCoffre; // pour les poches

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = false;

		$this->view->nom_interne = $this->getNomInterne();

		unset($tabHobbitMetiers);
		unset($tabMetiers);
		unset($tabMineraisBruts);
		unset($tabLingots);
		unset($tabRunesIdentifiees);
		unset($tabRunesNonIdentifiees);
	}

	private function renderTabac() {
		$tabTabac = null;
		$coffreTabacTable = new CoffreTabac();
		$tabacs = $coffreTabacTable->findByIdHobbit($this->view->user->id_hobbit);
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
		$this->view->tabac = $tabTabac;
	}

	private function renderPlante(&$tabMetiers) {
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);

		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();

		$tabTypePlantes = null;
		$coffrePartiePlanteTable = new CoffrePartieplante();
		$partiePlantes = $coffrePartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffrePartiePlanteTable);

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

		$this->view->typePlantesBruts = $tabTypePlantesBruts;
		$this->view->typePlantesPrepares = $tabTypePlantesPrepares;
	}

	private function renderMateriel() {
		$tabMateriels = null;
		$coffreMaterielTable = new CoffreMateriel();
		$materiels = $coffreMaterielTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreMaterielTable);

		$tabWhere = null;
		foreach ($materiels as $e) {
			$tabMateriels[$e["id_coffre_materiel"]] = array(
					"id_materiel" => $e["id_coffre_materiel"],
					'id_type_materiel' => $e["id_type_materiel"],
					'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
					'nom' =>$e["nom_type_materiel"],
					'capacite' => $e["capacite_type_materiel"], 
					'durabilite' => $e["durabilite_type_materiel"], 
					'usure' => $e["usure_type_materiel"], 
					'poids' => $e["poids_type_materiel"], 
			);
			$tabWhere[] = $e["id_coffre_materiel"];
		}
		unset($materiels);

		$this->view->nb_materiels = count($tabMateriels);
		$this->view->materiels = $tabMateriels;
	}

	private function renderEquipement() {
		$tabEquipements = null;
		$coffreEquipementTable = new CoffreEquipement();
		$equipements = $coffreEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreEquipementTable);

		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEquipements = Bral_Util_Equipement::prepareTabEquipements($equipements);

		$tabRetour = null;
		if ($tabEquipements != null) {
			foreach($tabEquipements as $e) {
				$tabRetour[$e["id_type_emplacement"]]["equipements"][] = $e;
				$tabRetour[$e["id_type_emplacement"]]["nom_type_emplacement"] = $e["emplacement"];
			}
		}

		$this->view->nb_equipements = count($tabEquipements);
		$this->view->equipements = $tabRetour;
	}

	private function renderMunition() {
		$tabMunitions = null;
		$coffreMunitionTable = new CoffreMunition();
		$munitions = $coffreMunitionTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreMunitionTable);

		foreach ($munitions as $m) {
			$tabMunitions[] = array(
				"type" => $m["nom_type_munition"],
				"quantite" => $m["quantite_coffre_munition"],
				"poids" =>  $m["quantite_coffre_munition"] * Bral_Util_Poids::POIDS_MUNITION,
			);
		}
		unset($munitions);

		$this->view->nb_munitions = count($tabMunitions);
		$this->view->munitions = $tabMunitions;
	}

	private function renderPotion() {
		Zend_Loader::loadClass("Bral_Util_Potion");
		$tabPotions = null;
		$coffrePotionTable = new CoffrePotion();
		$potions = $coffrePotionTable->findByIdHobbit($this->view->user->id_hobbit);
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

		$this->view->nb_potions = count($tabPotions);
		$this->view->potions = $tabPotions;
	}

	private function renderAliment() {
		$tabAliments = null;
		$coffreAlimentTable = new CoffreAliment();
		$aliments = $coffreAlimentTable->findByIdHobbit($this->view->user->id_hobbit);
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
			);
		}
		unset($aliments);

		$this->view->nb_aliments = count($tabAliments);
		$this->view->aliments = $tabAliments;
	}

	private function renderGraine() {
		$tabGraines = null;
		$coffreGraineTable = new CoffreGraine();
		$graines = $coffreGraineTable->findByIdHobbit($this->view->user->id_hobbit);
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

		$this->view->nb_graines = count($tabGraines);
		$this->view->graines = $tabGraines;
	}

	private function renderIngredient(&$tabMetiers, &$tabCoffre) {
		$tabIngredients = null;
		$coffreIngredientTable = new CoffreIngredient();
		$ingredients = $coffreIngredientTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreIngredientTable);

		Zend_Loader::loadClass("TypeIngredient");
		foreach ($ingredients as $g) {
			if ($g["quantite_coffre_ingredient"] > 0) {
				if ($g["id_type_ingredient"] ==  TypeIngredient::ID_TYPE_VIANDE_FRAICHE) {
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
				}
			}
		}
		unset($ingredients);

		$this->view->nb_ingredients = count($tabIngredients);
		$this->view->ingredients = $tabIngredients;
	}
}
