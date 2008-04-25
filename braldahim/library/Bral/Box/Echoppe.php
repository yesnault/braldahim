<?php

class Bral_Box_Echoppe extends Bral_Box_Box {
	
	function getTitreOnglet() {
		return "&Eacute;choppe";
	}
	
	function getNomInterne() {
		return "box_lieu";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		Zend_Loader::loadClass("Echoppe");
		
		$echoppesTable = new Echoppe();
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
		} else if (count($echoppeRowset) == 0) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide = 0 !");
		}
		
		$echoppe = $echoppeRowset[0];
		$this->view->estLieuCourant = true;
				
		$nom = "Échoppe";
		if ($echoppe["nom_masculin_metier"] == "A") {
			$nom .= " d'";
			
		} else {
			$nom .= " de ";
		}
		if ($echoppe["sexe_hobbit"] == "masculin") {
			$nom .= $echoppe["nom_masculin_metier"];
		} else {
			$nom .= $echoppe["nom_feminin_metier"];
		}
		$nom = htmlentities($nom). "<br>";
		$nom .= " appartenant &agrave ".htmlentities($echoppe["prenom_hobbit"]);
		$nom .= " ".htmlentities($echoppe["nom_hobbit"]);
		$nom .= " n°".$echoppe["id_hobbit"];
		
		if ($echoppe["nom_systeme_metier"] == "apothicaire") {
			$this->view->afficheType = "potions";
			$this->prepareCommunPotions($echoppe["id_echoppe"]);
		} else {
			$this->view->afficheType = "equipements";
			$this->prepareCommunEquipements($echoppe["id_echoppe"]);
		}
		
		$this->view->nomEchoppe = $nom;
		
		$this->view->estEquipementsPotionsEtal = true;
		$this->view->estEquipementsPotionsEtalAchat = true;
		return $this->view->render("interface/echoppe.phtml");
	}
	
	
	private function prepareCommunEquipements($idEchoppe) {
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("EchoppeEquipementMinerai");
		Zend_Loader::loadClass("EchoppeEquipementPartiePlante");
		Zend_Loader::loadClass("EquipementRune");
	
		$tabEquipementsEtal = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($idEchoppe);
		
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
				);
				
				if ($e["type_vente_echoppe_equipement"] == "publique") {
					$tabEquipementsEtal[] = $equipement;
				}
			}
		}
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
				
				if ($p["type_vente_echoppe_potion"] == "publique") {
					$tabPotionsEtal[] = array(
						"id_potion" => $p["id_echoppe_potion"],
						"nom" => $p["nom_type_potion"],
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
				}
			}
		}
		$this->view->potionsEtal = $tabPotionsEtal;
	}
}
