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
class Bral_Competences_Sequiper extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("TypeEmplacement");
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("LabanEquipement");
		Zend_Loader::loadClass("EquipementRune");
		Zend_Loader::loadClass("EquipementBonus");
		Zend_Loader::loadClass("Bral_Util_Equipement");

		$this->view->sequiperOk = false;
		$this->equipementPorte = null;
		$this->equipementLaban = null;
		$this->equipementAjoute = null;
		$this->equipementRetire = null;

		// on va chercher les emplacements
		$tabTypesEmplacement = null;
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacement = $typeEmplacementTable->fetchAll(null, "ordre_emplacement");
		$typesEmplacement = $typesEmplacement->toArray();

		foreach ($typesEmplacement as $t) {
			$affiche = "oui";
			$position = "gauche";
			if ($t["nom_systeme_type_emplacement"] == "deuxmains" ||
			$t["nom_systeme_type_emplacement"] == "mains" ||
			$t["nom_systeme_type_emplacement"] == "maingauche" ||
			$t["nom_systeme_type_emplacement"] == "maindroite") {
				$affiche = "non";
				$position = "droite";
			}

			if ($t["est_equipable_type_emplacement"] == "oui") {
				$tabTypesEmplacement[$t["nom_systeme_type_emplacement"]] = array(
						"nom_type_emplacement" => $t["nom_type_emplacement"],
						"id_type_emplacement" => $t["id_type_emplacement"],
						"ordre_emplacement" => $t["ordre_emplacement"],
						"equipementPorte" => null,
						"equipementLaban" => null,
						"affiche" => $affiche,
						"position" => $position,
				);
			}
		}

		// on va chercher l'équipement porté
		$tabEquipementPorte = null;
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementPorteRowset = $hobbitEquipementTable->findByIdHobbit($this->view->user->id_hobbit);

		$idEquipements = null;
		foreach ($equipementPorteRowset as $e) {
			$idEquipements[] = $e["id_equipement_hequipement"];
		}

		// on va chercher l'équipement présent dans le laban
		$tabEquipementLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipementLabanRowset = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);

		foreach ($equipementLabanRowset as $e) {
			$idEquipements[] = $e["id_laban_equipement"];
		}

		$equipementRuneTable = new EquipementRune();
		$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

		$equipementBonusTable = new EquipementBonus();
		$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
			
		foreach ($equipementPorteRowset as $e) {
			$this->view->sequiperOk = true;
			$runes = null;
			if (count($equipementRunes) > 0) {
				foreach($equipementRunes as $r) {
					if ($r["id_equipement_rune"] == $e["id_equipement_hequipement"]) {
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
					if ($b["id_equipement_bonus"] == $e["id_equipement_hequipement"]) {
						$bonus = $b;
						break;
					}
				}
			}

			$equipement = array(
					"id_equipement" => $e["id_equipement_hequipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"id_type_equipement" => $e["id_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"emplacement" => $e["nom_type_emplacement"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"nom_systeme_type_piece" => $e["nom_systeme_type_piece"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nb_runes" => $e["nb_runes_equipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
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
					"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"],
					"id_fk_region" => $e["id_fk_region_equipement"],
					"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
					"etat_courant" => $e["etat_courant_equipement"],
					"etat_initial" => $e["etat_initial_equipement"],
					"ingredient" => $e["nom_type_ingredient"],
					"poids" => $e["poids_recette_equipement"],
					"runes" => $runes,
					"bonus" => $bonus,
			);


			$this->equipementPorte[] = $equipement;
			$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementPorte"][] = $equipement;
			$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["affiche"] = "oui";
		}

		foreach ($equipementLabanRowset as $e) {
			$this->view->sequiperOk = true;
			$runes = null;
			$bonus = null;

			if ($e["est_equipable_type_emplacement"] == "oui" && floor($this->view->user->niveau_hobbit / 10) >= $e["niveau_recette_equipement"]) {
				if (count($equipementRunes) > 0) {
					foreach($equipementRunes as $r) {
						if ($r["id_equipement_rune"] == $e["id_laban_equipement"]) {
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

				if (count($equipementBonus) > 0) {
					foreach($equipementBonus as $b) {
						if ($b["id_equipement_bonus"] == $e["id_laban_equipement"]) {
							$bonus = $b;
							break;
						}
					}
				}

				$equipement = array(
						"id_equipement" => $e["id_laban_equipement"],
						"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
						"nom_standard" => $e["nom_type_equipement"],
						"qualite" => $e["nom_type_qualite"],
						"emplacement" => $e["nom_type_emplacement"],
						"niveau" => $e["niveau_recette_equipement"],
						"id_type_emplacement" => $e["id_type_emplacement"],
						"nom_systeme_type_piece" => $e["nom_systeme_type_piece"],
						"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
						"nb_runes" => $e["nb_runes_equipement"],
						"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
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
						"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"],
						"id_fk_region" => $e["id_fk_region_equipement"],
						"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
						"poids" => $e["poids_recette_equipement"],
						"runes" => $runes,
						"bonus" => $bonus,
				);
				$this->equipementLaban[] = $equipement;
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementLaban"][] = $equipement;
			}
		}

		$this->view->typesEmplacement = $tabTypesEmplacement;
		$this->view->nbTypesEmplacement = count($tabTypesEmplacement);
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification sequiper
		if ($this->view->sequiperOk == false) {
			throw new Zend_Exception(get_class($this)." Sequiper interdit ");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Equipement invalide : ".$this->request->get("valeur_1"));
		} else {
			$idEquipement = (int)$this->request->get("valeur_1");
		}

		// on verifie que l'id equipement est dans l'équipement porté
		$destination = "";
		if ($this->equipementPorte != null) {
			foreach ($this->equipementPorte as $p) {
				if ($p["id_equipement"] == $idEquipement) {
					$destination = "laban";
					$equipement = $p;
					break;
				}
			}
		}
		if ($destination == "" && $this->equipementLaban != null) { // soit dans le laban
			foreach ($this->equipementLaban as $p) {
				if ($p["id_equipement"] == $idEquipement) {
					$destination = "porte";
					$equipement = $p;
					break;
				}
			}
		}

		if ($destination == "") {
			throw new Zend_Exception(get_class($this)." Equipement interdit :" + $idEquipement);
		}

		// calcul des jets
		$this->calculSequiper($equipement, $destination);
		$this->view->equipementAjoute = $this->equipementAjoute;
		$this->view->equipementRetire = $this->equipementRetire;
		$this->setEvenementQueSurOkJet1(false);

		$this->setDetailsEvenement($details, $this->view->config->game->evenements->type->competence);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculSequiper($equipement, $destination) {
		if ($destination == "porte") {
			// mettre dans le laban présent à la place de la destination
			if ($this->equipementPorte != null) {
				foreach ($this->equipementPorte as $p) {
					if ($equipement["nom_systeme_type_emplacement"] == "deuxmains") {
						if ($p["nom_systeme_type_emplacement"] == "maingauche" ||
						$p["nom_systeme_type_emplacement"] == "maindroite" ||
						$p["nom_systeme_type_emplacement"] == "deuxmains") {
							$this->calculTransfertVersLaban($p);
							$this->calculRetireEffet($p);
						}
					} else if (($equipement["nom_systeme_type_emplacement"] == "maingauche" || $equipement["nom_systeme_type_emplacement"] == "maindroite")
					&& $p["nom_systeme_type_emplacement"] == "deuxmains") {
						$this->calculTransfertVersLaban($p);
						$this->calculRetireEffet($p);
					} else if ($equipement["id_type_emplacement"] == $p["id_type_emplacement"]) {
						$this->calculTransfertVersLaban($p);
						$this->calculRetireEffet($p);
					}
				}
			}
			$this->calculTransfertVersEquipement($equipement);
			$this->calculAjoutEffet($equipement);

		} else { // destination laban
			$this->calculTransfertVersLaban($equipement);
			$this->calculRetireEffet($equipement);
		}
	}

	private function calculTransfertVersEquipement($equipement) {
		$this->equipementAjoute[] = $equipement;

		$hobbitEquipementTable = new HobbitEquipement();
		$data = array(
			'id_equipement_hequipement' => $equipement["id_equipement"],
			'id_fk_hobbit_hequipement' => $this->view->user->id_hobbit,
		);
		$hobbitEquipementTable->insert($data);

		$labanEquipementTable = new LabanEquipement();
		$where = "id_laban_equipement=".$equipement["id_equipement"];
		$labanEquipementTable->delete($where);

		Zend_Loader::loadClass("Bral_Util_Quete");
		$this->view->estQueteEvenement = Bral_Util_Quete::etapeEquiper($this->view->user, $equipement["id_type_emplacement"]);

		$details = "[h".$this->view->user->id_hobbit."] a mis une pièce d'équipement";
		Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_EQUIPER_ID, $equipement["id_equipement"], $details);
	}

	private function calculTransfertVersLaban($equipement) {
		$this->equipementRetire[] = $equipement;

		$labanEquipementTable = new LabanEquipement();
		$data = array(
			'id_laban_equipement' => $equipement["id_equipement"],
			'id_fk_hobbit_laban_equipement' => $this->view->user->id_hobbit,
		);
		$labanEquipementTable->insert($data);

		$hobbitEquipementTable = new HobbitEquipement();
		$where = "id_equipement_hequipement=".$equipement["id_equipement"];
		$hobbitEquipementTable->delete($where);

		$details = "[h".$this->view->user->id_hobbit."] a enlevé une pièce d'équipement";
		Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_EQUIPER_ID, $equipement["id_equipement"], $details);
	}

	private function calculAjoutEffet($equipement) {

		$this->view->user->force_bm_hobbit = $this->view->user->force_bm_hobbit + $equipement["force"];
		$this->view->user->agilite_bm_hobbit = $this->view->user->agilite_bm_hobbit + $equipement["agilite"];
		$this->view->user->vigueur_bm_hobbit = $this->view->user->vigueur_bm_hobbit + $equipement["vigueur"];
		$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit + $equipement["sagesse"];
		$this->view->user->vue_bm_hobbit = $this->view->user->vue_bm_hobbit + $equipement["vue"];
		$this->view->user->armure_equipement_hobbit = $this->view->user->armure_equipement_hobbit + $equipement["armure"];
		$this->view->user->bm_attaque_hobbit = $this->view->user->bm_attaque_hobbit + $equipement["bm_attaque"];
		$this->view->user->bm_degat_hobbit = $this->view->user->bm_degat_hobbit + $equipement["bm_degat"];
		$this->view->user->bm_defense_hobbit = $this->view->user->bm_defense_hobbit + $equipement["bm_defense"];

		if ($equipement["bonus"] != null && count($equipement["bonus"]) > 0) {
			$b = $equipement["bonus"];
			$this->view->user->armure_equipement_hobbit = $this->view->user->armure_equipement_hobbit + $b["armure_equipement_bonus"] + $b["vernis_bm_armure_equipement_bonus"];
			$this->view->user->agilite_bm_hobbit = $this->view->user->agilite_bm_hobbit + $b["agilite_equipement_bonus"] + $b["vernis_bm_agilite_equipement_bonus"];
			$this->view->user->force_bm_hobbit = $this->view->user->force_bm_hobbit + $b["force_equipement_bonus"] + $b["vernis_bm_force_equipement_bonus"];
			$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit + $b["sagesse_equipement_bonus"] + $b["vernis_bm_sagesse_equipement_bonus"];
			$this->view->user->vigueur_bm_hobbit = $this->view->user->vigueur_bm_hobbit + $b["vigueur_equipement_bonus"] + $b["vernis_bm_vigueur_equipement_bonus"];

			$this->view->user->vue_bm_hobbit = intval($this->view->user->vue_bm_hobbit + $b["vernis_bm_vue_equipement_bonus"]);
			$this->view->user->bm_attaque_hobbit = intval($this->view->user->bm_attaque_hobbit + $b["vernis_bm_attaque_equipement_bonus"]);
			$this->view->user->bm_degat_hobbit = intval($this->view->user->bm_degat_hobbit + $b["vernis_bm_degat_equipement_bonus"]);
			$this->view->user->bm_defense_hobbit = intval($this->view->user->bm_defense_hobbit + $b["vernis_bm_defense_equipement_bonus"]);
		}
			
		if ($equipement["runes"] != null && count($equipement["runes"]) > 0) {
			foreach($equipement["runes"] as $r) {
				if ($r["nom_type_rune"] == "KR") {
					// KR Bonus de AGI = Niveau d'AGI/3 arrondi inférieur
					$this->view->user->agilite_bm_hobbit = $this->view->user->agilite_bm_hobbit + floor($this->view->user->agilite_base_hobbit / 3);
				} else if ($r["nom_type_rune"] == "ZE") {
					// ZE Bonus de FOR = Niveau de FOR/3 arrondi inférieur
					$this->view->user->force_bm_hobbit = $this->view->user->force_bm_hobbit + floor($this->view->user->force_base_hobbit / 3);
				} else if ($r["nom_type_rune"] == "IL") {
					// IL Réduit le tour de jeu de 10 minutes ==> on rajoute 10 minutes donc
					$this->view->user->duree_bm_tour_hobbit = $this->view->user->duree_bm_tour_hobbit - 10;
				} else if ($r["nom_type_rune"] == "MU") {
					// MU PV + niveau du Hobbit/10 arrondi inférieur
					$this->view->user->pv_max_bm_hobbit = $this->view->user->pv_max_bm_hobbit + floor($this->view->user->niveau_hobbit / 10);
				} else if ($r["nom_type_rune"] == "RE") {
					// RE ARM NAT + Niveau du Hobbit/10 arrondi inférieur
					$this->view->user->armure_naturelle_hobbit = $this->view->user->armure_naturelle_hobbit + floor($this->view->user->niveau_hobbit / 10);
				} else if ($r["nom_type_rune"] == "OG") {
					// OG Bonus de VIG = Niveau de VIG/3 arrondi inférieur
					$this->view->user->vigueur_bm_hobbit = $this->view->user->vigueur_bm_hobbit + floor($this->view->user->vigueur_base_hobbit / 3);
				} else if ($r["nom_type_rune"] == "OX") {
					// OX Poids maximum porté augmenté de Niveau du Hobbit/10 arrondi inférieur
					$this->view->user->poids_transportable_hobbit = $this->view->user->poids_transportable_hobbit + floor($this->view->user->niveau_hobbit / 10);
				} else if ($r["nom_type_rune"] == "UP") {
					// UP Bonus de SAG = Niveau de SAG/3 arrondi inférieur
					$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit + floor($this->view->user->sagesse_base_hobbit / 3);
				}
			}
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_b") {
			$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit + (2 * ($equipement["niveau"] + 1));
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_e") {
			$this->view->user->pv_max_bm_hobbit = $this->view->user->pv_max_bm_hobbit - ($equipement["niveau"] * 3);
			if ($this->view->user->pv_restant_hobbit > $this->view->user->pv_max_hobbit + $this->view->user->pv_max_bm_hobbit) {
				$this->view->user->pv_restant_hobbit = $this->view->user->pv_max_hobbit + $this->view->user->pv_max_bm_hobbit;
			}
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_k") {
			if ($equipement["bm_attaque"] > 0) { // positif
				$val = $equipement["bm_attaque"];
			} else { // negatif
				$val = abs($equipement["bm_attaque"]) / 2;
			}
			$this->view->user->bm_attaque_hobbit = $this->view->user->bm_attaque_hobbit + $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_m") {
			if ($equipement["bm_defense"] > 0) { // positif
				$val = $equipement["bm_defense"];
			} else { // negatif
				$val = abs($equipement["bm_defense"]) / 2;
			}
			$this->view->user->bm_defense_hobbit = $this->view->user->bm_defense_hobbit + $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_r") {
			Bral_Util_Commun::ajouteEffetMotR($this->view->user->id_hobbit);
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_v") {
			$this->view->user->vue_bm_hobbit = $this->view->user->vue_bm_hobbit + 2;
		}

		$this->majHobbitEffet();
	}

	private function calculRetireEffet($equipement) {

		$this->view->user->force_bm_hobbit = $this->view->user->force_bm_hobbit - $equipement["force"];
		$this->view->user->agilite_bm_hobbit = $this->view->user->agilite_bm_hobbit - $equipement["agilite"];
		$this->view->user->vigueur_bm_hobbit = $this->view->user->vigueur_bm_hobbit - $equipement["vigueur"];
		$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit - $equipement["sagesse"];
		$this->view->user->vue_bm_hobbit = $this->view->user->vue_bm_hobbit - $equipement["vue"];
		$this->view->user->armure_equipement_hobbit = $this->view->user->armure_equipement_hobbit - $equipement["armure"];
		$this->view->user->bm_attaque_hobbit = $this->view->user->bm_attaque_hobbit - $equipement["bm_attaque"];
		$this->view->user->bm_degat_hobbit = $this->view->user->bm_degat_hobbit - $equipement["bm_degat"];
		$this->view->user->bm_defense_hobbit = $this->view->user->bm_defense_hobbit - $equipement["bm_defense"];

		if ($equipement["bonus"] != null && count($equipement["bonus"]) > 0) {
			$b = $equipement["bonus"];
			$this->view->user->armure_equipement_hobbit = $this->view->user->armure_equipement_hobbit - $b["armure_equipement_bonus"] - $b["vernis_bm_armure_equipement_bonus"];
			$this->view->user->agilite_bm_hobbit = $this->view->user->agilite_bm_hobbit - $b["agilite_equipement_bonus"] - $b["vernis_bm_agilite_equipement_bonus"];
			$this->view->user->force_bm_hobbit = $this->view->user->force_bm_hobbit - $b["force_equipement_bonus"] - $b["vernis_bm_force_equipement_bonus"];
			$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit - $b["sagesse_equipement_bonus"] - $b["vernis_bm_sagesse_equipement_bonus"];
			$this->view->user->vigueur_bm_hobbit = $this->view->user->vigueur_bm_hobbit - $b["vigueur_equipement_bonus"] - $b["vernis_bm_vigueur_equipement_bonus"];

			$this->view->user->vue_bm_hobbit = intval($this->view->user->vue_bm_hobbit - $b["vernis_bm_vigueur_equipement_bonus"]);
			$this->view->user->bm_attaque_hobbit = intval($this->view->user->bm_attaque_hobbit - $b["vernis_bm_vigueur_equipement_bonus"]);
			$this->view->user->bm_degat_hobbit = intval($this->view->user->bm_degat_hobbit - $b["vernis_bm_vigueur_equipement_bonus"]);
			$this->view->user->bm_defense_hobbit = intval($this->view->user->bm_defense_hobbit - $b["vernis_bm_vigueur_equipement_bonus"]);
		}

		if ($equipement["runes"] != null && count($equipement["runes"]) > 0) {
			foreach($equipement["runes"] as $r) {
				if ($r["nom_type_rune"] == "KR") {
					// KR Bonus de AGI = Niveau d'AGI/3 arrondi inférieur
					$this->view->user->agilite_bm_hobbit = $this->view->user->agilite_bm_hobbit - floor($this->view->user->agilite_base_hobbit / 3);
				} else if ($r["nom_type_rune"] == "ZE") {
					// ZE Bonus de FOR = Niveau de FOR/3 arrondi inférieur
					$this->view->user->force_bm_hobbit = $this->view->user->force_bm_hobbit - floor($this->view->user->force_base_hobbit / 3);
				} else if ($r["nom_type_rune"] == "IL") {
					// IL Réduit le tour de jeu de 10 minutes ==> on rajoute 10 minutes donc
					$this->view->user->duree_bm_tour_hobbit = $this->view->user->duree_bm_tour_hobbit + 10;
				} else if ($r["nom_type_rune"] == "MU") {
					// MU PV + niveau du Hobbit/10 arrondi inférieur
					$this->view->user->pv_max_bm_hobbit = $this->view->user->pv_max_bm_hobbit - floor($this->view->user->niveau_hobbit / 10);
				} else if ($r["nom_type_rune"] == "RE") {
					// RE ARM NAT + Niveau du Hobbit/10 arrondi inférieur
					$this->view->user->armure_naturelle_hobbit = $this->view->user->armure_naturelle_hobbit - floor($this->view->user->niveau_hobbit / 10);
				} else if ($r["nom_type_rune"] == "OG") {
					// OG Bonus de VIG = Niveau de VIG/3 arrondi inférieur
					$this->view->user->vigueur_bm_hobbit = $this->view->user->vigueur_bm_hobbit - floor($this->view->user->vigueur_base_hobbit / 3);
				} else if ($r["nom_type_rune"] == "OX") {
					// OX Poids maximum porté augmenté de Niveau du Hobbit/10 arrondi inférieur
					$this->view->user->poids_transportable_hobbit = $this->view->user->poids_transportable_hobbit - floor($this->view->user->niveau_hobbit / 10);
				} else if ($r["nom_type_rune"] == "UP") {
					// UP Bonus de SAG = Niveau de SAG/3 arrondi inférieur
					$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit - floor($this->view->user->sagesse_base_hobbit / 3);
				}
			}
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_b") {
			$this->view->user->sagesse_bm_hobbit = $this->view->user->sagesse_bm_hobbit - (2 * ($equipement["niveau"] + 1));
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_e") {
			$this->view->user->pv_max_bm_hobbit = $this->view->user->pv_max_bm_hobbit + ($equipement["niveau"] * 3);
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_k") {
			if ($equipement["bm_attaque"] > 0) { // positif
				$val = $equipement["bm_attaque"];
			} else { // negatif
				$val = abs($equipement["bm_attaque"]) / 2;
			}
			$this->view->user->bm_attaque_hobbit = $this->view->user->bm_attaque_hobbit - $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_m") {
			if ($equipement["bm_defense"] > 0) { // positif
				$val = $equipement["bm_defense"];
			} else { // negatif
				$val = abs($equipement["bm_defense"]) / 2;
			}
			$this->view->user->bm_defense_hobbit = $this->view->user->bm_defense_hobbit - $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_r") {
			Bral_Util_Commun::retireEffetMotR($this->view->user->id_hobbit);
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_v") {
			$this->view->user->vue_bm_hobbit = $this->view->user->vue_bm_hobbit - 2;
		}

		$this->majHobbitEffet();
	}

	private function majHobbitEffet() {
		$hobbitTable = new Hobbit();
		$data = array(
				'vue_bm_hobbit' => $this->view->user->vue_bm_hobbit,
				'force_bm_hobbit' => $this->view->user->force_bm_hobbit,
				'agilite_bm_hobbit' => $this->view->user->agilite_bm_hobbit,
				'vigueur_bm_hobbit' => $this->view->user->vigueur_bm_hobbit,
				'sagesse_bm_hobbit' => $this->view->user->sagesse_bm_hobbit,
				'vue_bm_hobbit' => $this->view->user->vue_bm_hobbit,
				'armure_naturelle_hobbit' => $this->view->user->armure_naturelle_hobbit,
				'armure_equipement_hobbit' => $this->view->user->armure_equipement_hobbit,
				'poids_transportable_hobbit' => $this->view->user->poids_transportable_hobbit,
				'duree_prochain_tour_hobbit' => $this->view->user->duree_prochain_tour_hobbit,
				'pv_max_bm_hobbit' => $this->view->user->pv_max_bm_hobbit,
				'bm_attaque_hobbit' => $this->view->user->bm_attaque_hobbit,
				'bm_degat_hobbit' => $this->view->user->bm_degat_hobbit,
				'bm_defense_hobbit' => $this->view->user->bm_defense_hobbit,
				'duree_bm_tour_hobbit' => $this->view->user->duree_bm_tour_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_equipement", "box_laban"));
	}
}
