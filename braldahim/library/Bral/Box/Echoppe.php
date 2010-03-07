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
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
		} else if (count($echoppeRowset) == 0) {
			throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide = 0 !");
		}

		$echoppe = $echoppeRowset[0];
		$this->view->estLieuCourant = true;

		$nom = "Échoppe";
		if ($echoppe["nom_masculin_metier"]{0} == "A") {
			$nom .= " d'";
		} else {
			$nom .= " de ";
		}
		if ($echoppe["sexe_hobbit"] == "masculin") {
			$nom .= $echoppe["nom_masculin_metier"];
		} else {
			$nom .= $echoppe["nom_feminin_metier"];
		}
		$nom = htmlspecialchars($nom). "<br>";
		$nom .= " appartenant &agrave ".htmlspecialchars($echoppe["prenom_hobbit"]);
		$nom .= " ".htmlspecialchars($echoppe["nom_hobbit"]);
		$nom .= " n°".$echoppe["id_hobbit"];

		if ($echoppe["nom_systeme_metier"] == "apothicaire") {
			$this->view->afficheType = "potions";
			$this->prepareCommunPotions($echoppe["id_echoppe"]);
		} elseif ($echoppe["nom_systeme_metier"] == "cuisinier") {
			$this->view->afficheType = "aliments";
			$this->prepareCommunAliments($echoppe["id_echoppe"]);
		} else {
			$this->view->afficheType = "equipements";
			$this->prepareCommunEquipements($echoppe["id_echoppe"]);
		}

		$this->prepareCommunMateriels($echoppe["id_echoppe"]);

		$this->view->nomEchoppe = $nom;

		$this->view->estElementsEtal = true;
		$this->view->estElementsEtalAchat = true;
		$this->view->estElementsAchat = false;

		return $this->view->render("interface/echoppe.phtml");
	}


	private function prepareCommunEquipements($idEchoppe) {
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("EchoppeEquipementMinerai");
		Zend_Loader::loadClass("EchoppeEquipementPartiePlante");
		Zend_Loader::loadClass("EquipementRune");
		Zend_Loader::loadClass("EquipementBonus");
		Zend_Loader::loadClass("Bral_Util_Equipement");

		$tabEquipementsEtal = null;
		$idEquipements = null;
		$equipementRunes = null;
		$echoppeEquipementMinerai = null;
		$echoppeEquipementPartiePlante = null;

		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($idEchoppe);

		foreach ($equipements as $e) {
			$idEquipements[] = $e["id_echoppe_equipement"];
		}

		if ($idEquipements != null && count($idEquipements) > 0) {
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

			$equipementBonusTable = new EquipementBonus();
			$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);

			$echoppeEquipementMineraiTable = new EchoppeEquipementMinerai();
			$echoppeEquipementMinerai = $echoppeEquipementMineraiTable->findByIdsEquipement($idEquipements);

			$echoppeEquipementPartiePlanteTable = new EchoppeEquipementPartiePlante();
			$echoppeEquipementPartiePlante = $echoppeEquipementPartiePlanteTable->findByIdsEquipement($idEquipements);
		}

		if ($idEquipements != null && count($equipements) > 0) {
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
					"prix_1_vente_echoppe_equipement" => $e["prix_1_vente_echoppe_equipement"],
					"prix_2_vente_echoppe_equipement" => $e["prix_2_vente_echoppe_equipement"],
					"prix_3_vente_echoppe_equipement" => $e["prix_3_vente_echoppe_equipement"],
					"unite_1_vente_echoppe_equipement" => $e["unite_1_vente_echoppe_equipement"],
					"unite_2_vente_echoppe_equipement" => $e["unite_2_vente_echoppe_equipement"],
					"unite_3_vente_echoppe_equipement" => $e["unite_3_vente_echoppe_equipement"],
					"commentaire_vente_echoppe_equipement" => $e["commentaire_vente_echoppe_equipement"],
					"runes" => $runes,
					"bonus" => $bonus,
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
				);

				if ($e["type_vente_echoppe_equipement"] == "publique") {
					$tabEquipementsEtal[$e["id_type_emplacement"]]["equipements"][] = $equipement;
					$tabEquipementsEtal[$e["id_type_emplacement"]]["nom_type_emplacement"] = $e["nom_type_emplacement"];
				}
			}
		}
		$this->view->equipementsEtal = $tabEquipementsEtal;
		$this->view->idEquipementsEtalTable = "idEquipementsEtalTableEchoppe";
	}

	private function prepareCommunMateriels($idEchoppe) {
		Zend_Loader::loadClass("EchoppeMateriel");
		Zend_Loader::loadClass("EchoppeMaterielMinerai");
		Zend_Loader::loadClass("EchoppeMaterielPartiePlante");

		$tabMaterielsEtal = null;
		$idMateriels = null;
		$echoppeMaterielMinerai = null;
		$echoppeMaterielPartiePlante = null;

		$echoppeMaterielTable = new EchoppeMateriel();
		$materiels = $echoppeMaterielTable->findByIdEchoppe($idEchoppe);

		foreach ($materiels as $e) {
			$idMateriels[] = $e["id_echoppe_materiel"];
		}

		if ($idMateriels != null && count($idMateriels) > 0) {
			$echoppeMaterielMineraiTable = new EchoppeMaterielMinerai();
			$echoppeMaterielMinerai = $echoppeMaterielMineraiTable->findByIdsMateriel($idMateriels);

			$echoppeMaterielPartiePlanteTable = new EchoppeMaterielPartiePlante();
			$echoppeMaterielPartiePlante = $echoppeMaterielPartiePlanteTable->findByIdsMateriel($idMateriels);
		}

		if ($idMateriels != null && count($materiels) > 0) {
			foreach($materiels as $e) {
					
				$minerai = null;
				if (count($echoppeMaterielMinerai) > 0) {
					foreach($echoppeMaterielMinerai as $r) {
						if ($r["id_fk_echoppe_materiel_minerai"] == $e["id_echoppe_materiel"]) {
							$minerai[] = array(
								"prix_echoppe_materiel_minerai" => $r["prix_echoppe_materiel_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}

				$partiesPlantes = null;
				if (count($echoppeMaterielPartiePlante) > 0) {
					foreach($echoppeMaterielPartiePlante as $p) {
						if ($p["id_fk_echoppe_materiel_partieplante"] == $e["id_echoppe_materiel"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_materiel_partieplante" => $p["prix_echoppe_materiel_partieplante"],
								"nom_type_plante" => $p["nom_type_plante"],
								"nom_type_partieplante" => $p["nom_type_partieplante"],
								"prefix_type_plante" => $p["prefix_type_plante"],
							);
						}
					}
				}

				$materiel = array(
					"id_materiel" => $e["id_echoppe_materiel"],
					"id_type_materiel" => $e["id_type_materiel"],
					'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
					'nom' =>$e["nom_type_materiel"],
					'capacite' => $e["capacite_type_materiel"], 
					'durabilite' => $e["durabilite_type_materiel"], 
					'usure' => $e["usure_type_materiel"], 
					'poids' => $e["poids_type_materiel"], 
					"prix_1_vente_echoppe_materiel" => $e["prix_1_vente_echoppe_materiel"],
					"prix_2_vente_echoppe_materiel" => $e["prix_2_vente_echoppe_materiel"],
					"prix_3_vente_echoppe_materiel" => $e["prix_3_vente_echoppe_materiel"],
					"unite_1_vente_echoppe_materiel" => $e["unite_1_vente_echoppe_materiel"],
					"unite_2_vente_echoppe_materiel" => $e["unite_2_vente_echoppe_materiel"],
					"unite_3_vente_echoppe_materiel" => $e["unite_3_vente_echoppe_materiel"],
					"commentaire_vente_echoppe_materiel" => $e["commentaire_vente_echoppe_materiel"],
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
				);

				if ($e["type_vente_echoppe_materiel"] == "publique") {
					$tabMaterielsEtal[] = $materiel;
				}
			}
		}
		$this->view->materielsEtal = $tabMaterielsEtal;
		$this->view->idMaterielsEtalTable = "idMaterielsEtalTableEchoppe";
	}

	private function prepareCommunPotions($idEchoppe) {
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("EchoppePotionMinerai");
		Zend_Loader::loadClass("EchoppePotionPartiePlante");
		Zend_Loader::loadClass("Bral_Util_Potion");

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
						"id_type_potion" => $p["id_type_potion"],
						"qualite" => $p["nom_type_qualite"],
						"niveau" => $p["niveau_potion"],
						"caracteristique" => $p["caract_type_potion"],
						"bm_type" => $p["bm_type_potion"],
						"caracteristique2" => $p["caract2_type_potion"],
						"bm2_type" => $p["bm2_type_potion"],
						"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
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
		$this->view->idPotionsEtalTable = "idPotionsEtalTableEchoppe";
	}

	private function prepareCommunAliments($idEchoppe) {
		Zend_Loader::loadClass("EchoppeAliment");
		Zend_Loader::loadClass("EchoppeAlimentMinerai");
		Zend_Loader::loadClass("EchoppeAlimentPartiePlante");
		Zend_Loader::loadClass("Bral_Util_Aliment");

		$tabAlimentsArriereBoutique = null;
		$tabAlimentsEtal = null;
		$echoppeAlimentTable = new EchoppeAliment();
		$aliments = $echoppeAlimentTable->findByIdEchoppe($idEchoppe);

		$idAliments = null;

		foreach ($aliments as $p) {
			$idAliments[] = $p["id_echoppe_aliment"];
		}

		if (count($idAliments) > 0) {
			$echoppAlimentMineraiTable = new EchoppeAlimentMinerai();
			$echoppeAlimentMinerai = $echoppAlimentMineraiTable->findByIdsAliment($idAliments);

			$echoppeAlimentPartiePlanteTable = new EchoppeAlimentPartiePlante();
			$echoppeAlimentPartiePlante = $echoppeAlimentPartiePlanteTable->findByIdsAliment($idAliments);
		}

		if (count($aliments) > 0) {
			foreach($aliments as $p) {
				$minerai = null;
				if (count($echoppeAlimentMinerai) > 0) {
					foreach($echoppeAlimentMinerai as $r) {
						if ($r["id_fk_echoppe_aliment_minerai"] == $p["id_echoppe_aliment"]) {
							$minerai[] = array(
								"prix_echoppe_aliment_minerai" => $r["prix_echoppe_aliment_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}

				$partiesPlantes = null;
				if (count($echoppeAlimentPartiePlante) > 0) {
					foreach($echoppeAlimentPartiePlante as $a) {
						if ($a["id_fk_echoppe_aliment_partieplante"] == $p["id_echoppe_aliment"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_aliment_partieplante" => $a["prix_echoppe_aliment_partieplante"],
								"nom_type_plante" => $a["nom_type_plante"],
								"nom_type_partieplante" => $a["nom_type_partieplante"],
								"prefix_type_plante" => $a["prefix_type_plante"],
							);
						}
					}
				}

				if ($p["type_vente_echoppe_aliment"] == "publique") {
					$tabAlimentsEtal[] = array(
						"id_aliment" => $p["id_echoppe_aliment"],
						'id_type_aliment' => $p["id_type_aliment"],
						'nom_systeme_type_aliment' => $p["nom_systeme_type_aliment"],
						'nom' =>$p["nom_type_aliment"],
						'poids' => $p["poids_unitaire_type_aliment"],
						"qualite" => $p["nom_aliment_type_qualite"],
						"bbdf" => $p["bbdf_aliment"],
						"recette" => Bral_Util_Aliment::getNomType($p["type_bbdf_type_aliment"]),
						"prix_1_vente_echoppe_aliment" => $p["prix_1_vente_echoppe_aliment"],
						"prix_2_vente_echoppe_aliment" => $p["prix_2_vente_echoppe_aliment"],
						"prix_3_vente_echoppe_aliment" => $p["prix_3_vente_echoppe_aliment"],
						"unite_1_vente_echoppe_aliment" => $p["unite_1_vente_echoppe_aliment"],
						"unite_2_vente_echoppe_aliment" => $p["unite_2_vente_echoppe_aliment"],
						"unite_3_vente_echoppe_aliment" => $p["unite_3_vente_echoppe_aliment"],
						"commentaire_vente_echoppe_aliment" => $p["commentaire_vente_echoppe_aliment"],
						"prix_minerais" => $minerai,
						"prix_parties_plantes" => $partiesPlantes,
					);
				}
			}
		}
		$this->view->alimentsEtal = $tabAlimentsEtal;
		$this->view->idAlimentsEtalTable = "idAlimentsEtalTableEchoppe";
	}
}
