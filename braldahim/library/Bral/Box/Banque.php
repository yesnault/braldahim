<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Bbois.php 626 2008-11-13 22:59:43Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-13 23:59:43 +0100 (Thu, 13 Nov 2008) $
 * $LastChangedRevision: 626 $
 * $LastChangedBy: yvonnickesnault $
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
			$this->preData();
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/banque.phtml");
	}
	
	private function preData() {
		Zend_Loader::loadClass("Lieu");
		
		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
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
		
		$this->view->nom_interne = $this->getNomInterne();
	}
	
	private function data() {
		
		Zend_Loader::loadClass("Coffre");
		Zend_Loader::loadClass("CoffreEquipement");
		Zend_Loader::loadClass("CoffreMinerai");
		Zend_Loader::loadClass("CoffrePartieplante");
		Zend_Loader::loadClass("CoffrePotion");
		Zend_Loader::loadClass("CoffreRune");
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
		
		$tabMineraisBruts = null;
		$tabLingots = null;
		$coffreMineraiTable = new CoffreMinerai();
		$minerais = $coffreMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreMineraiTable);
	
		foreach ($minerais as $m) {
			if ($m["quantite_brut_coffre_minerai"] > 0) {
				$tabMineraisBruts[] = array(
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_brut_coffre_minerai"],
				);
			
				if (isset($tabMetiers["mineur"])) {
					$tabMetiers["mineur"]["a_afficher"] = true; 
				}
			}
			if ($m["quantite_lingots_coffre_minerai"] > 0) {
				$tabLingots[] = array(
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_lingots_coffre_minerai"],
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
				"nb_viande" => $p["quantite_viande_coffre"],
				"nb_viande_preparee" => $p["quantite_viande_preparee_coffre"],
				"nb_ration" => $p["quantite_ration_coffre"],
				"nb_cuir" => $p["quantite_cuir_coffre"],
				"nb_fourrure" => $p["quantite_fourrure_coffre"],
				"nb_planche" => $p["quantite_planche_coffre"],
			);
			
			if ($p["quantite_peau_coffre"] > 0 || $p["quantite_viande_coffre"] > 0) {
				if (isset($tabMetiers["chasseur"])) {
					$tabMetiers["chasseur"]["a_afficher"] = true; 
				}
			}
			
			if ($p["quantite_viande_preparee_coffre"] > 0 || $p["quantite_ration_coffre"] > 0) {
				if (isset($tabMetiers["cuisinier"])) {
					$tabMetiers["cuisinier"]["a_afficher"] = true; 
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
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_rune"],
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
		$this->view->coffre = $tabCoffre;
		
		$this->renderPlante($tabMetiers);
		$this->view->tabMetiers = $tabMetiers;
		$this->renderEquipement();
		$this->renderPotion();
		
		$this->view->estEquipementsPotionsEtal = false;
		$this->view->estEquipementsPotionsEtalAchat = false;
		
		$this->view->nom_interne = $this->getNomInterne();
		
		unset($tabHobbitMetiers);
		unset($tabMetiers);
		unset($tabMineraisBruts);
		unset($tabLingots);
		unset($tabRunesIdentifiees);
		unset($tabRunesNonIdentifiees);
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
				if (isset($tabMetiers["herboriste"])) {
					$tabMetiers["herboriste"]["a_afficher"] = true;
				}
			}
			
			if ($p["quantite_preparee_coffre_partieplante"] > 0) {
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_preparee_coffre_partieplante"];
				if (isset($tabMetiers["apothicaire"])) {
					$tabMetiers["apothicaire"]["a_afficher"] = true; 
				}
			}
		}
		unset($partiePlantes);

		$this->view->typePlantesBruts = $tabTypePlantesBruts;
		$this->view->typePlantesPrepares = $tabTypePlantesPrepares;
	}
	
	private function renderEquipement() {
		$tabEquipements = null;
		$coffreEquipementTable = new CoffreEquipement();
		$equipements = $coffreEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffreEquipementTable);
		
		$tabWhere = null;
		foreach ($equipements as $e) {
			$tabEquipements[$e["id_coffre_equipement"]] = array(
					"id_equipement" => $e["id_coffre_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_coffre_equipement"],
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
			);
			$tabWhere[] = $e["id_coffre_equipement"];
		}
		unset($equipements);
		
		if ($tabWhere != null) {
			Zend_Loader::loadClass("EquipementRune");
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($tabWhere);
			unset($equipementRuneTable);
			
			foreach($equipementRunes as $e) {
				$tabEquipements[$e["id_equipement_rune"]]["runes"][] = array(
					"id_rune_equipement_rune" => $e["id_rune_equipement_rune"],
					"id_fk_type_rune_equipement_rune" => $e["id_fk_type_rune_equipement_rune"],
					"nom_type_rune" => $e["nom_type_rune"],
					"image_type_rune" => $e["image_type_rune"],
					"effet_type_rune" => $e["effet_type_rune"],
				);
			}
			unset($equipementRunes);
		}
		
		$this->view->nb_equipements = count($tabEquipements);
		$this->view->equipements = $tabEquipements;
	}
	
	private function renderPotion() {
		$tabPotions = null;
		$coffrePotionTable = new CoffrePotion();
		$potions = $coffrePotionTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($coffrePotionTable);
		
		foreach ($potions as $p) {
			$tabPotions[$p["id_coffre_potion"]] = array(
					"id_potion" => $p["id_coffre_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_coffre_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
			);
		}
		unset($potions);
		
		$this->view->nb_potions = count($tabPotions);
		$this->view->potions = $tabPotions;
	}
}
