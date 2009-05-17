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
class Bral_Box_Charrette extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Charrette";
	}

	function getNomInterne() {
		return "box_charrette";
	}

	function getChargementInBoxes() {
		return false;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {

		if ($this->view->affichageInterne) {
			Zend_Loader::loadClass('Charrette');
			$charretteTable = new Charrette();
			$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
			if ($nombre > 0) {
				$this->view->possedeCharrette = true;

				$this->view->tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit);

				$this->data();

				$this->view->pocheNom = "Case";
				$this->view->pocheNomSysteme = "Charrette";
				$this->view->nb_castars = $this->view->charrette["nb_castar"];
			} else {
				$this->view->possedeCharrette = false;
			}
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/charrette.phtml");
	}

	protected function data() {

		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharretteEquipement");
		Zend_Loader::loadClass("CharretteMateriel");
		Zend_Loader::loadClass("CharretteMinerai");
		Zend_Loader::loadClass("CharrettePartieplante");
		Zend_Loader::loadClass("CharretteAliment");
		Zend_Loader::loadClass("CharretteMunition");
		Zend_Loader::loadClass("CharrettePotion");
		Zend_Loader::loadClass("CharretteRune");
		Zend_Loader::loadClass("CharretteTabac");
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("Metier");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");

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
		
		$charrette = null;
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($charretteTable);

		if ($charrettes != null && count($charrettes) == 1) {
			$p = $charrettes[0];
			$charrette = array(
				"id_charrette" => $p["id_charrette"],
				"nom_charrette" => $p["nom_type_materiel"],
				"nb_peau" => $p["quantite_peau_charrette"],
				"nb_viande" => $p["quantite_viande_charrette"],
				"nb_viande_preparee" => $p["quantite_viande_preparee_charrette"],
				"nb_cuir" => $p["quantite_cuir_charrette"],
				"nb_fourrure" => $p["quantite_fourrure_charrette"],
				"nb_planche" => $p["quantite_planche_charrette"],
				"nb_castar" => $p["quantite_castar_charrette"],
				"nb_rondin" => $p["quantite_rondin_charrette"],
				"durabilite_max" => $p["durabilite_max_charrette"],
				"durabilite_actuelle" => $p["durabilite_actuelle_charrette"],
				"poids_transportable" => $p["poids_transportable_charrette"],
				"poids_transporte" => $p["poids_transporte_charrette"],
			);

			if ($p["quantite_peau_charrette"] > 0 || $p["quantite_viande_charrette"] > 0) {
				if (isset($tabMetiers["chasseur"])) {
					$tabMetiers["chasseur"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_viande_preparee_charrette"] > 0) {
				if (isset($tabMetiers["cuisinier"])) {
					$tabMetiers["cuisinier"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_cuir_charrette"] > 0 || $p["quantite_fourrure_charrette"] > 0) {
				if (isset($tabMetiers["tanneur"])) {
					$tabMetiers["tanneur"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_planche_charrette"] > 0) {
				if (isset($tabMetiers["menuisier"])) {
					$tabMetiers["menuisier"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_rondin_charrette"] > 0) {
				if (isset($tabMetiers["bucheron"])) {
					$tabMetiers["bucheron"]["a_afficher"] = true;
				}
			}
		}

		$tabMineraisBruts = null;
		$tabLingots = null;
		$charretteMineraiTable = new CharretteMinerai();
		$minerais = $charretteMineraiTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteMineraiTable);

		foreach ($minerais as $m) {
			if ($m["quantite_brut_charrette_minerai"] > 0) {
				$tabMineraisBruts[] = array(
					"id_type_minerai" => $m["id_type_minerai"],
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_brut_charrette_minerai"],
					"poids" => $m["quantite_brut_charrette_minerai"] * Bral_Util_Poids::POIDS_MINERAI,
				);
					
				if (isset($tabMetiers["mineur"])) {
					$tabMetiers["mineur"]["a_afficher"] = true;
				}
			}
			if ($m["quantite_lingots_charrette_minerai"] > 0) {
				$tabLingots[] = array(
					"id_type_minerai" => $m["id_type_minerai"],
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_lingots_charrette_minerai"],
					"poids" => $m["quantite_lingots_charrette_minerai"] * Bral_Util_Poids::POIDS_LINGOT,
				);
					
				if (isset($tabMetiers["forgeron"])) {
					$tabMetiers["forgeron"]["a_afficher"] = true;
				}
			}
		}
		unset($minerais);

		$tabRunesIdentifiees = null;
		$tabRunesNonIdentifiees = null;
		$charretteRuneTable = new CharretteRune();
		$runes = $charretteRuneTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteRuneTable);

		foreach ($runes as $r) {
			if ($r["est_identifiee_charrette_rune"] == "oui") {
				$tabRunesIdentifiees[] = array(
					"id_rune" => $r["id_rune_charrette_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_charrette_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
				);
			} else {
				$tabRunesNonIdentifiees[] = array(
					"id_rune" => $r["id_rune_charrette_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_charrette_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
				);
			}
		}
		unset($runes);

		$this->view->tabHobbitMetiers = $tabHobbitMetiers;


		$this->view->mineraisBruts = $tabMineraisBruts;
		$this->view->lingots = $tabLingots;

		$this->view->nb_runes = count($tabRunesIdentifiees) + count($tabRunesNonIdentifiees);
		$this->view->runesIdentifiees = $tabRunesIdentifiees;
		$this->view->runesNonIdentifiees = $tabRunesNonIdentifiees;
		$this->view->charrette = $charrette;
		$this->view->laban = $charrette; // pour les poches

		$this->renderPlante($tabMetiers, $charrette);
		$this->view->tabMetiers = $tabMetiers;
		$this->renderEquipement($charrette);
		$this->renderMateriel($charrette);
		$this->renderMunition($charrette);
		$this->renderPotion($charrette);
		$this->renderAliment($charrette);
		$this->renderTabac($charrette);
		$this->renderAmeliorations($charrette);

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

	private function renderTabac($charrette) {
		$tabTabac = null;
		$charretteTabacTable = new CharretteTabac();
		$tabacs = $charretteTabacTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteTabacTable);

		foreach ($tabacs as $m) {
			if ($m["quantite_feuille_charrette_tabac"] > 0) {
				$tabTabac[] = array(
					"type" => $m["nom_type_tabac"],
					"id_type_tabac" => $m["id_type_tabac"],
					"quantite" => $m["quantite_feuille_charrette_tabac"],
				);
			}
		}
		unset($tabacs);
		$this->view->tabac = $tabTabac;
	}

	private function renderPlante(&$tabMetiers, $charrette) {
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);

		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();

		$tabTypePlantes = null;
		$charrettePartiePlanteTable = new CharrettePartieplante();
		$partiePlantes = $charrettePartiePlanteTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charrettePartiePlanteTable);

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
			if ($p["quantite_charrette_partieplante"] > 0) {
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_charrette_partieplante"];
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_charrette_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
				if (isset($tabMetiers["herboriste"])) {
					$tabMetiers["herboriste"]["a_afficher"] = true;
				}
			}

			if ($p["quantite_preparee_charrette_partieplante"] > 0) {
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_preparee_charrette_partieplante"];
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_preparee_charrette_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;
				if (isset($tabMetiers["apothicaire"])) {
					$tabMetiers["apothicaire"]["a_afficher"] = true;
				}
			}
		}
		unset($partiePlantes);

		$this->view->typePlantesBruts = $tabTypePlantesBruts;
		$this->view->typePlantesPrepares = $tabTypePlantesPrepares;
	}

	private function renderMateriel($charrette) {
		$tabMateriels = null;
		$charretteMaterielTable = new CharretteMateriel();
		$materiels = $charretteMaterielTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteMaterielTable);

		$tabWhere = null;
		foreach ($materiels as $e) {
			$tabMateriels[$e["id_charrette_materiel"]] = array(
					"id_materiel" => $e["id_charrette_materiel"],
					'id_type_materiel' => $e["id_type_materiel"],
					'nom' =>$e["nom_type_materiel"],
					'capacite' => $e["capacite_type_materiel"], 
					'durabilite' => $e["durabilite_type_materiel"], 
					'usure' => $e["usure_type_materiel"], 
					'poids' => $e["poids_type_materiel"], 
			);
			$tabWhere[] = $e["id_charrette_materiel"];
		}
		unset($materiels);

		$this->view->nb_materiels = count($tabMateriels);
		$this->view->materiels = $tabMateriels;
	}

	private function renderEquipement($charrette) {
		$tabEquipements = null;
		$charretteEquipementTable = new CharretteEquipement();
		$equipements = $charretteEquipementTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteEquipementTable);

		Zend_Loader::loadClass("Bral_Util_Equipement");

		$tabWhere = null;
		foreach ($equipements as $e) {
			$tabEquipements[$e["id_charrette_equipement"]] = array(
					"id_equipement" => $e["id_charrette_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_charrette_equipement"]),
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_charrette_equipement"],
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
					"poids" => $e["poids_recette_equipement"],
					"runes" => array(),
					"bonus" => array(),
			);
			$tabWhere[] = $e["id_charrette_equipement"];
		}
		unset($equipements);

		if ($tabWhere != null) {
			Zend_Loader::loadClass("Bral_Util_Equipement");
			Bral_Util_Equipement::populateRune($tabEquipements, $tabWhere);
			Bral_Util_Equipement::populateBonus($tabEquipements, $tabWhere);
		}

		$this->view->nb_equipements = count($tabEquipements);
		$this->view->equipements = $tabEquipements;
	}

	private function renderMunition($charrette) {
		$tabMunitions = null;
		$charretteMunitionTable = new CharretteMunition();
		$munitions = $charretteMunitionTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteMunitionTable);

		foreach ($munitions as $m) {
			$tabMunitions[] = array(
				"type" => $m["nom_type_munition"],
				"quantite" => $m["quantite_charrette_munition"],
				"poids" =>  $m["quantite_charrette_munition"] * Bral_Util_Poids::POIDS_MUNITION,
			);
		}
		unset($munitions);

		$this->view->nb_munitions = count($tabMunitions);
		$this->view->munitions = $tabMunitions;
	}

	private function renderPotion($charrette) {
		$tabPotions = null;
		$charrettePotionTable = new CharrettePotion();
		$potions = $charrettePotionTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charrettePotionTable);

		foreach ($potions as $p) {
			$tabPotions[$p["id_charrette_potion"]] = array(
					"id_potion" => $p["id_charrette_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_charrette_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
			);
		}
		unset($potions);

		$this->view->nb_potions = count($tabPotions);
		$this->view->potions = $tabPotions;
	}

	private function renderAliment($charrette) {
		$tabAliments = null;
		$charretteAlimentTable = new CharretteAliment();
		$aliments = $charretteAlimentTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteAlimentTable);

		foreach ($aliments as $p) {
			$tabAliments[$p["id_charrette_aliment"]] = array(
					"id_aliment" => $p["id_charrette_aliment"],
					"id_type_aliment" => $p["id_type_aliment"],
					"nom" => $p["nom_type_aliment"],
					"qualite" => $p["nom_aliment_type_qualite"],
					"bbdf" => $p["bbdf_charrette_aliment"],
			);
		}
		unset($aliments);

		$this->view->nb_aliments = count($tabAliments);
		$this->view->aliments = $tabAliments;
	}

	private function renderAmeliorations($charrette) {
		Zend_Loader::loadClass("CharretteMaterielAssemble");

		$charretteMaterielAssembleTable = new CharretteMaterielAssemble();

		$materiels = $charretteMaterielAssembleTable->findByIdCharrette($charrette["id_charrette"]);
		unset($charretteMaterielAssembleTable);
		
		$tabMateriel = null;
		foreach ($materiels as $m) {
			$tabMateriel[] = array(
					"id_materiel" => $m["id_type_materiel"],
					"nom" => $m["nom_type_materiel"],
					"id_type_materiel" => $m["id_type_materiel"],
					'capacite' => $m["capacite_type_materiel"], 
					'durabilite' => $m["durabilite_type_materiel"], 
					'usure' => $m["usure_type_materiel"], 
					'poids' => $m["poids_type_materiel"], 
			);
		}
		$this->view->materielsAssembles = $tabMateriel;
	}
}
