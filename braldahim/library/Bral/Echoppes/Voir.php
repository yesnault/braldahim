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
		
		$this->arBoutiqueCaisse["castars"]  = array("nom_systeme" => "castars", "nom" => "Castars", "a_afficher" => true);
		$this->arBoutiqueCaisse["minerais"] = array("nom_systeme" => "minerais", "nom" => "Minerais Bruts", "a_afficher" => false);
		$this->arBoutiqueCaisse["rondins"]  = array("nom_systeme" => "rondins", "nom" => "Rondins", "a_afficher" => false);
		$this->arBoutiqueCaisse["plantes_bruts"] = array("nom_systeme" => "plantes_bruts", "nom" => "Plantes Brutes", "a_afficher" => false);
		$this->arBoutiqueCaisse["peaux"]  = array("nom_systeme" => "peaux", "nom" => "Peaux", "a_afficher" => false);
		
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->estSurEchoppe == false;
		$this->view->afficheType = "equipements";

		$tabEchoppe = null;
		$id_metier = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe) {
				if ($this->view->user->sexe_hobbit == 'feminin') {
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
				
				if ($e["quantite_fourrure_arriere_echoppe"] > 0 || $e["quantite_planche_arriere_echoppe"] > 0) {
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
				
				if ($this->view->user->x_hobbit == $e["x_echoppe"] &&
				$this->view->user->y_hobbit == $e["y_echoppe"]) {
					$this->view->estSurEchoppe = true;
				}
				if ($e["nom_systeme_metier"] == "apothicaire") {
					$this->view->afficheType = "potions";
				}
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_hobbit." ide:".$id_echoppe);
		}

		Zend_Loader::loadClass("HobbitsCompetences");
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);

		$competence = null;
		$tabCompetences = null;
		foreach($hobbitCompetences as $c) {
			if ($id_metier == $c["id_fk_metier_competence"]) {
				$tabCompetences[] = array("id_competence" => $c["id_fk_competence_hcomp"],
					"nom" => $c["nom_competence"],
					"pa_utilisation" => $c["pa_utilisation_competence"],
					"pourcentage" => Bral_Util_Commun::getPourcentage($c, $this->view->config),
					"nom_systeme" => $c["nom_systeme_competence"],
				);
			}
		}

		$this->prepareCommunRessources($tabEchoppe["id_echoppe"]);
		$this->prepareCommunEquipements($tabEchoppe["id_echoppe"]);
		$this->prepareCommunPotions($tabEchoppe["id_echoppe"]);
		
		$this->view->arBoutiqueBruts = $this->arBoutiqueBruts;
		$this->view->arBoutiqueTransformes = $this->arBoutiqueTransformes;
		$this->view->arBoutiqueCaisse = $this->arBoutiqueCaisse;
		
		$this->view->competences = $tabCompetences;
		$this->view->echoppe = $tabEchoppe;
		$this->view->estEquipementsPotionsEtal = true;
		$this->view->estEquipementsPotionsEtalAchat = false;
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
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		
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
					$tabPartiePlantesCaisse[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_caisse_echoppe_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
				}
				
				if ($p["quantite_arriere_echoppe_partieplante"] > 0) {
					$this->arBoutiqueBruts["plantes_bruts"]["a_afficher"] = true;
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_arriere_echoppe_partieplante"];
					$tabPartiePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_arriere_echoppe_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
				}
				
				if ($p["quantite_preparees_echoppe_partieplante"] > 0) {
					$this->arBoutiqueTransformes["plantes_preparees"]["a_afficher"] = true;
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_preparees_echoppe_partieplante"];
					$tabPartiePlantesPreparees[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["poids"] = $p["quantite_preparees_echoppe_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;
				}
				
				$this->view->nb_caissePartiePlantes = $this->view->nb_caissePartiePlantes + $p["quantite_caisse_echoppe_partieplante"];
				$this->view->nb_arrierePartiePlantes = $this->view->nb_arrierePartiePlantes + $p["quantite_arriere_echoppe_partieplante"];
				$this->view->nb_prepareePartiePlantes = $this->view->nb_prepareePartiePlantes  + $p["quantite_preparees_echoppe_partieplante"];
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
					"quantite" => $m["quantite_arriere_echoppe_minerai"],
					"poids" => $m["quantite_arriere_echoppe_minerai"] * Bral_Util_Poids::POIDS_MINERAI,
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
					"quantite" => $m["quantite_caisse_echoppe_minerai"],
					"poids" => $m["quantite_caisse_echoppe_minerai"] * Bral_Util_Poids::POIDS_MINERAI,
				);
				
				if ($m["quantite_arriere_echoppe_minerai"] > 0) {
					$this->arBoutiqueBruts["minerais"]["a_afficher"] = true;
				}
				
				if ($m["quantite_lingots_echoppe_minerai"] > 0) {
					$this->arBoutiqueTransformes["lingots"]["a_afficher"] = true;
				}
				
				if ($m["quantite_caisse_echoppe_minerai"] > 0) {
					$this->arBoutiqueCaisse["minerais"]["a_afficher"] = true;
				}
				
				$this->view->nb_caisseMinerai = $this->view->nb_caisseMinerai + $m["quantite_caisse_echoppe_minerai"];
			}
		}

		$this->view->mineraisArriere = $tabMineraisArriere;
		$this->view->mineraisCaisse = $tabMineraisCaisse;
		$this->view->lingots = $tabLingots;
	}

	private function prepareCommunEquipements($idEchoppe) {
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("EchoppeEquipementMinerai");
		Zend_Loader::loadClass("EchoppeEquipementPartiePlante");
		Zend_Loader::loadClass("EquipementRune");
	
		$tabEquipementsArriereBoutique = null;
		$tabEquipementsEtal = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($idEchoppe);
		$idEquipements = null;
		
		foreach ($equipements as $e) {
			$idEquipements[] = $e["id_echoppe_equipement"];
		}
		
		if (count($idEquipements) > 0) {
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);
			
			$echoppeEquipementMineraiTable = new EchoppeEquipementMinerai();
			$echoppeEquipementMinerai = $echoppeEquipementMineraiTable->findByIdsEquipement($idEquipements);
			
			$echoppeEquipementPartiePlanteTable = new EchoppeEquipementPartiePlante();
			$echoppeEquipementPartiePlante = $echoppeEquipementPartiePlanteTable->findByIdsEquipement($idEquipements);
		}
		
		if (count($equipements) > 0) {
			foreach($equipements as $e) {
			
				$runes = null;
				if (count($equipementRunes) > 0) {
					foreach($equipementRunes as $r) {
						if ($r["id_equipement_rune"] == $e["id_echoppe_equipement"]) {
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
				
				$minerai = null;
				if (count($echoppeEquipementMinerai) > 0) {
					foreach($echoppeEquipementMinerai as $r) {
						if ($r["id_fk_echoppe_equipement_minerai"] == $e["id_echoppe_equipement"]) {
							$minerai[] = array(
								"prix_echoppe_equipement_minerai" => $r["prix_echoppe_equipement_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}
				
				$partiesPlantes = null;
				if (count($echoppeEquipementPartiePlante) > 0) {
					foreach($echoppeEquipementPartiePlante as $p) {
						if ($p["id_fk_echoppe_equipement_partieplante"] == $e["id_echoppe_equipement"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_equipement_partieplante" => $p["prix_echoppe_equipement_partieplante"],
								"nom_type_plante" => $p["nom_type_plante"],
								"nom_type_partieplante" => $p["nom_type_partieplante"],
								"prefix_type_plante" => $p["prefix_type_plante"],
							);
						}
					}
				}
				
				$equipement = array(
					"id_equipement" => $e["id_echoppe_equipement"],
					"nom" => $e["nom_type_equipement"],
					"id_type_equipement" => $e["id_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nb_runes" => $e["nb_runes_echoppe_equipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_echoppe_equipement"],
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
					"id_fk_mot_runique" => $e["id_fk_mot_runique_echoppe_equipement"],
					"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
					"prix_1_vente_echoppe_equipement" => $e["prix_1_vente_echoppe_equipement"],
					"prix_2_vente_echoppe_equipement" => $e["prix_2_vente_echoppe_equipement"],
					"prix_3_vente_echoppe_equipement" => $e["prix_3_vente_echoppe_equipement"],
					"unite_1_vente_echoppe_equipement" => $e["unite_1_vente_echoppe_equipement"],
					"unite_2_vente_echoppe_equipement" => $e["unite_2_vente_echoppe_equipement"],
					"unite_3_vente_echoppe_equipement" => $e["unite_3_vente_echoppe_equipement"],
					"commentaire_vente_echoppe_equipement" => $e["commentaire_vente_echoppe_equipement"],
					"runes" => $runes,
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
					"poids" => $e["poids_recette_equipement"],
				);
				
				if ($e["type_vente_echoppe_equipement"] == "aucune") {
					$tabEquipementsArriereBoutique[] = $equipement;
				} else {
					$tabEquipementsEtal[] = $equipement;
				}
			}
		}
		$this->view->equipementsArriereBoutique = $tabEquipementsArriereBoutique;
		$this->view->equipementsEtal = $tabEquipementsEtal;
	}
	
	private function prepareCommunPotions($idEchoppe) {
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("EchoppePotionMinerai");
		Zend_Loader::loadClass("EchoppePotionPartiePlante");

		$tabPotionsArriereBoutique = null;
		$tabPotionsEtal = null;
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($idEchoppe);
		
		$idPotions = null;
		
		foreach ($potions as $p) {
			$idPotions[] = $p["id_echoppe_potion"];
		}
		
		if (count($idPotions) > 0) {
			$echoppPotionMineraiTable = new EchoppePotionMinerai();
			$echoppePotionMinerai = $echoppPotionMineraiTable->findByIdsPotion($idPotions);
				
			$echoppePotionPartiePlanteTable = new EchoppePotionPartiePlante();
			$echoppePotionPartiePlante = $echoppePotionPartiePlanteTable->findByIdsPotion($idPotions);
		}
		
		if (count($potions) > 0) {
			foreach($potions as $p) {
				$minerai = null;
				if (count($echoppePotionMinerai) > 0) {
					foreach($echoppePotionMinerai as $r) {
						if ($r["id_fk_echoppe_potion_minerai"] == $p["id_echoppe_potion"]) {
							$minerai[] = array(
								"prix_echoppe_potion_minerai" => $r["prix_echoppe_potion_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}
				
				$partiesPlantes = null;
				if (count($echoppePotionPartiePlante) > 0) {
					foreach($echoppePotionPartiePlante as $a) {
						if ($a["id_fk_echoppe_potion_partieplante"] == $p["id_echoppe_potion"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_potion_partieplante" => $a["prix_echoppe_potion_partieplante"],
								"nom_type_plante" => $a["nom_type_plante"],
								"nom_type_partieplante" => $a["nom_type_partieplante"],
								"prefix_type_plante" => $a["prefix_type_plante"],
							);
						}
					}
				}
				
				$tab = array(
					"id_potion" => $p["id_echoppe_potion"],
					"nom" => $p["nom_type_potion"],
					"id_type_potion" => $p["id_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_echoppe_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"prix_1_vente_echoppe_potion" => $p["prix_1_vente_echoppe_potion"],
					"prix_2_vente_echoppe_potion" => $p["prix_2_vente_echoppe_potion"],
					"prix_3_vente_echoppe_potion" => $p["prix_3_vente_echoppe_potion"],
					"unite_1_vente_echoppe_potion" => $p["unite_1_vente_echoppe_potion"],
					"unite_2_vente_echoppe_potion" => $p["unite_2_vente_echoppe_potion"],
					"unite_3_vente_echoppe_potion" => $p["unite_3_vente_echoppe_potion"],
					"commentaire_vente_echoppe_potion" => $p["commentaire_vente_echoppe_potion"],
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
				);
					
				if ($p["type_vente_echoppe_potion"] == "aucune") {
					$tabPotionsArriereBoutique[] = $tab;
				} else {
					$tabPotionsEtal[] = $tab;
				}
			}
		}
		$this->view->potionsArriereBoutique = $tabPotionsArriereBoutique;
		$this->view->potionsEtal = $tabPotionsEtal;
	}
	
	public function getIdEchoppeCourante() {
		return false;
	}
}