<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Sequiper extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("TypeEmplacement");
		Zend_Loader::loadClass("BraldunEquipement");
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
				$t["nom_systeme_type_emplacement"] == "maindroite"
			) {
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
		$braldunEquipementTable = new BraldunEquipement();
		$equipementPorteRowset = $braldunEquipementTable->findByIdBraldun($this->view->user->id_braldun);
		$tabEquipementPorte = Bral_Util_Equipement::prepareTabEquipements($equipementPorteRowset, false, $this->view->user->niveau_braldun);

		if ($tabEquipementPorte != null) {
			foreach ($tabEquipementPorte as $e) {
				$this->view->sequiperOk = true;
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementPorte"][] = $e;
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["affiche"] = "oui";
			}
		}

		// on va chercher l'équipement présent dans le laban
		$labanEquipementTable = new LabanEquipement();
		$equipementLabanRowset = $labanEquipementTable->findByIdBraldun($this->view->user->id_braldun);
		$tabEquipementLaban = Bral_Util_Equipement::prepareTabEquipements($equipementLabanRowset, true, $this->view->user->niveau_braldun);

		if ($tabEquipementLaban != null) {
			foreach ($tabEquipementLaban as $e) {
				$this->view->sequiperOk = true;
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementLaban"][] = $e;
			}
		}

		$this->equipementPorte = $tabEquipementPorte;
		$this->equipementLaban = $tabEquipementLaban;

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
			throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
		}

		// Verification sequiper
		if ($this->view->sequiperOk == false) {
			throw new Zend_Exception(get_class($this) . " Sequiper interdit ");
		}

		if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
			throw new Zend_Exception(get_class($this) . " Equipement invalide : " . $this->request->get("valeur_1"));
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
			throw new Zend_Exception(get_class($this) . " Equipement interdit :" + $idEquipement);
		}

		// calcul des jets
		$this->calculSequiper($equipement, $destination);
		$this->view->equipementAjoute = $this->equipementAjoute;
		$this->view->equipementRetire = $this->equipementRetire;
		$this->setEvenementQueSurOkJet1(false);

		if ($destination == "porte") {
			$details = "[b" . $this->view->user->id_braldun . "] a mis une pièce d'équipement";
		} else {
			$details = "[b" . $this->view->user->id_braldun . "] a enlevé une pièce d'équipement";
		}
		$this->setDetailsEvenement($details, $this->view->config->game->evenements->type->competence);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function calculSequiper($equipement, $destination) {
		if ($destination == "porte") {
			// mettre dans le laban présent à la place de la destination
			if ($this->equipementPorte != null) {
				foreach ($this->equipementPorte as $p) {
					if ($equipement["nom_systeme_type_emplacement"] == "deuxmains") {
						if ($p["nom_systeme_type_emplacement"] == "maingauche" ||
							$p["nom_systeme_type_emplacement"] == "maindroite" ||
							$p["nom_systeme_type_emplacement"] == "deuxmains"
						) {
							$this->calculTransfertVersLaban($p);
							$this->calculRetireEffet($p);
						}
					} else if (($equipement["nom_systeme_type_emplacement"] == "maingauche" || $equipement["nom_systeme_type_emplacement"] == "maindroite")
							   && $p["nom_systeme_type_emplacement"] == "deuxmains"
					) {
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

		$braldunEquipementTable = new BraldunEquipement();
		$data = array(
			'id_equipement_hequipement' => $equipement["id_equipement"],
			'id_fk_braldun_hequipement' => $this->view->user->id_braldun,
		);
		$braldunEquipementTable->insert($data);

		$labanEquipementTable = new LabanEquipement();
		$where = "id_laban_equipement=" . $equipement["id_equipement"];
		$labanEquipementTable->delete($where);

		Zend_Loader::loadClass("Bral_Util_Quete");
		$this->view->estQueteEvenement = Bral_Util_Quete::etapeEquiper($this->view->user, $equipement["id_type_emplacement"]);

		$details = "[b" . $this->view->user->id_braldun . "] a mis une pièce d'équipement";
		Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_EQUIPER_ID, $equipement["id_equipement"], $details);
	}

	private function calculTransfertVersLaban($equipement) {
		$this->equipementRetire[] = $equipement;

		$labanEquipementTable = new LabanEquipement();
		$data = array(
			'id_laban_equipement' => $equipement["id_equipement"],
			'id_fk_braldun_laban_equipement' => $this->view->user->id_braldun,
		);
		$labanEquipementTable->insert($data);

		$braldunEquipementTable = new BraldunEquipement();
		$where = "id_equipement_hequipement=" . $equipement["id_equipement"];
		$braldunEquipementTable->delete($where);

		$details = "[b" . $this->view->user->id_braldun . "] a enlevé une pièce d'équipement";
		Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_EQUIPER_ID, $equipement["id_equipement"], $details);
	}

	private function calculAjoutEffet($equipement) {
		$this->view->user->force_bm_braldun = $this->view->user->force_bm_braldun + $equipement["force"];
		$this->view->user->agilite_bm_braldun = $this->view->user->agilite_bm_braldun + $equipement["agilite"];
		$this->view->user->vigueur_bm_braldun = $this->view->user->vigueur_bm_braldun + $equipement["vigueur"];
		$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun + $equipement["sagesse"];
		$this->view->user->vue_bm_braldun = $this->view->user->vue_bm_braldun + $equipement["vue"];
		$this->view->user->armure_equipement_braldun = $this->view->user->armure_equipement_braldun + $equipement["armure"];
		$this->view->user->bm_attaque_braldun = $this->view->user->bm_attaque_braldun + $equipement["attaque"];
		$this->view->user->bm_degat_braldun = $this->view->user->bm_degat_braldun + $equipement["degat"];
		$this->view->user->bm_defense_braldun = $this->view->user->bm_defense_braldun + $equipement["defense"];

		if ($equipement["bonus"] != null && count($equipement["bonus"]) > 0) {
			$b = $equipement["bonus"];
			$this->view->user->armure_equipement_braldun = $this->view->user->armure_equipement_braldun + $b["armure_equipement_bonus"] + $b["vernis_bm_armure_equipement_bonus"];
			$this->view->user->agilite_bm_braldun = $this->view->user->agilite_bm_braldun + $b["agilite_equipement_bonus"] + $b["vernis_bm_agilite_equipement_bonus"];
			$this->view->user->force_bm_braldun = $this->view->user->force_bm_braldun + $b["force_equipement_bonus"] + $b["vernis_bm_force_equipement_bonus"];
			$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun + $b["sagesse_equipement_bonus"] + $b["vernis_bm_sagesse_equipement_bonus"];
			$this->view->user->vigueur_bm_braldun = $this->view->user->vigueur_bm_braldun + $b["vigueur_equipement_bonus"] + $b["vernis_bm_vigueur_equipement_bonus"];

			$this->view->user->vue_bm_braldun = intval($this->view->user->vue_bm_braldun + $b["vernis_bm_vue_equipement_bonus"]);
			$this->view->user->bm_attaque_braldun = intval($this->view->user->bm_attaque_braldun + $b["vernis_bm_attaque_equipement_bonus"]);
			$this->view->user->bm_degat_braldun = intval($this->view->user->bm_degat_braldun + $b["vernis_bm_degat_equipement_bonus"]);
			$this->view->user->bm_defense_braldun = intval($this->view->user->bm_defense_braldun + $b["vernis_bm_defense_equipement_bonus"]);
		}

		if ($equipement["runes"] != null && count($equipement["runes"]) > 0) {
			foreach ($equipement["runes"] as $r) {
				if ($r["nom_type_rune"] == "KR") {
					// KR Bonus de AGI = Niveau d'AGI/3 arrondi inférieur
					$this->view->user->agilite_bm_braldun = $this->view->user->agilite_bm_braldun + floor($this->view->user->agilite_base_braldun / 3);
				} else if ($r["nom_type_rune"] == "ZE") {
					// ZE Bonus de FOR = Niveau de FOR/3 arrondi inférieur
					$this->view->user->force_bm_braldun = $this->view->user->force_bm_braldun + floor($this->view->user->force_base_braldun / 3);
				} else if ($r["nom_type_rune"] == "IL") {
					// IL Réduit le tour de jeu de 10 minutes ==> on rajoute 10 minutes donc
					$this->view->user->duree_bm_tour_braldun = $this->view->user->duree_bm_tour_braldun - 10;
				} else if ($r["nom_type_rune"] == "MU") {
					// MU PV + niveau du Braldun/10 arrondi inférieur
					$this->view->user->pv_max_bm_braldun = $this->view->user->pv_max_bm_braldun + floor($this->view->user->niveau_braldun / 10) + 1;
				} else if ($r["nom_type_rune"] == "RE") {
					// RE ARM NAT + Niveau du Braldun/10 arrondi inférieur
					$this->view->user->armure_naturelle_braldun = $this->view->user->armure_naturelle_braldun + floor($this->view->user->niveau_braldun / 10);
				} else if ($r["nom_type_rune"] == "OG") {
					// OG Bonus de VIG = Niveau de VIG/3 arrondi inférieur
					$this->view->user->vigueur_bm_braldun = $this->view->user->vigueur_bm_braldun + floor($this->view->user->vigueur_base_braldun / 3);
				} else if ($r["nom_type_rune"] == "OX") {
					// OX Poids maximum porté augmenté de Niveau du Braldun/10 arrondi inférieur
					$this->view->user->poids_transportable_braldun = $this->view->user->poids_transportable_braldun + floor($this->view->user->niveau_braldun / 10);
				} else if ($r["nom_type_rune"] == "UP") {
					// UP Bonus de SAG = Niveau de SAG/3 arrondi inférieur
					$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun + floor($this->view->user->sagesse_base_braldun / 3);
				}
			}
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_b") {
			$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun + (3 * ($equipement["niveau"] + 1));
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_e") {
			$this->view->user->pv_max_bm_braldun = $this->view->user->pv_max_bm_braldun - ($equipement["niveau"] * 10);
			if ($this->view->user->pv_restant_braldun > $this->view->user->pv_max_braldun + $this->view->user->pv_max_bm_braldun) {
				$this->view->user->pv_restant_braldun = $this->view->user->pv_max_braldun + $this->view->user->pv_max_bm_braldun;
			}
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_k") {
			if ($equipement["attaque"] > 0) { // positif
				$val = $equipement["attaque"];
			} else { // negatif
				$val = abs($equipement["attaque"]) / 2;
			}
			$this->view->user->bm_attaque_braldun = $this->view->user->bm_attaque_braldun + $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_m") {
			if ($equipement["defense"] > 0) { // positif
				$val = $equipement["defense"];
			} else { // negatif
				$val = abs($equipement["defense"]) / 2;
			}
			$this->view->user->bm_defense_braldun = $this->view->user->bm_defense_braldun + $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_r") {
			Bral_Util_Commun::ajouteEffetMotR($this->view->user->id_braldun);
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_v") {
			$this->view->user->vue_bm_braldun = $this->view->user->vue_bm_braldun + 2;
		}

		$this->majBraldunEffet();
	}

	private function calculRetireEffet($equipement) {

		$this->view->user->force_bm_braldun = $this->view->user->force_bm_braldun - $equipement["force"];
		$this->view->user->agilite_bm_braldun = $this->view->user->agilite_bm_braldun - $equipement["agilite"];
		$this->view->user->vigueur_bm_braldun = $this->view->user->vigueur_bm_braldun - $equipement["vigueur"];
		$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun - $equipement["sagesse"];
		$this->view->user->vue_bm_braldun = $this->view->user->vue_bm_braldun - $equipement["vue"];
		$this->view->user->armure_equipement_braldun = $this->view->user->armure_equipement_braldun - $equipement["armure"];
		$this->view->user->bm_attaque_braldun = $this->view->user->bm_attaque_braldun - $equipement["attaque"];
		$this->view->user->bm_degat_braldun = $this->view->user->bm_degat_braldun - $equipement["degat"];
		$this->view->user->bm_defense_braldun = $this->view->user->bm_defense_braldun - $equipement["defense"];

		if ($equipement["bonus"] != null && count($equipement["bonus"]) > 0) {
			$b = $equipement["bonus"];
			$this->view->user->armure_equipement_braldun = $this->view->user->armure_equipement_braldun - $b["armure_equipement_bonus"] - $b["vernis_bm_armure_equipement_bonus"];
			$this->view->user->agilite_bm_braldun = $this->view->user->agilite_bm_braldun - $b["agilite_equipement_bonus"] - $b["vernis_bm_agilite_equipement_bonus"];
			$this->view->user->force_bm_braldun = $this->view->user->force_bm_braldun - $b["force_equipement_bonus"] - $b["vernis_bm_force_equipement_bonus"];
			$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun - $b["sagesse_equipement_bonus"] - $b["vernis_bm_sagesse_equipement_bonus"];
			$this->view->user->vigueur_bm_braldun = $this->view->user->vigueur_bm_braldun - $b["vigueur_equipement_bonus"] - $b["vernis_bm_vigueur_equipement_bonus"];

			$this->view->user->vue_bm_braldun = intval($this->view->user->vue_bm_braldun - $b["vernis_bm_vue_equipement_bonus"]);
			$this->view->user->bm_attaque_braldun = intval($this->view->user->bm_attaque_braldun - $b["vernis_bm_vigueur_equipement_bonus"]);
			$this->view->user->bm_degat_braldun = intval($this->view->user->bm_degat_braldun - $b["vernis_bm_vigueur_equipement_bonus"]);
			$this->view->user->bm_defense_braldun = intval($this->view->user->bm_defense_braldun - $b["vernis_bm_vigueur_equipement_bonus"]);
		}

		if ($equipement["runes"] != null && count($equipement["runes"]) > 0) {
			foreach ($equipement["runes"] as $r) {
				if ($r["nom_type_rune"] == "KR") {
					// KR Bonus de AGI = Niveau d'AGI/3 arrondi inférieur
					$this->view->user->agilite_bm_braldun = $this->view->user->agilite_bm_braldun - floor($this->view->user->agilite_base_braldun / 3);
				} else if ($r["nom_type_rune"] == "ZE") {
					// ZE Bonus de FOR = Niveau de FOR/3 arrondi inférieur
					$this->view->user->force_bm_braldun = $this->view->user->force_bm_braldun - floor($this->view->user->force_base_braldun / 3);
				} else if ($r["nom_type_rune"] == "IL") {
					// IL Réduit le tour de jeu de 10 minutes ==> on rajoute 10 minutes donc
					$this->view->user->duree_bm_tour_braldun = $this->view->user->duree_bm_tour_braldun + 10;
				} else if ($r["nom_type_rune"] == "MU") {
					// MU PV + niveau du Braldun/10 arrondi inférieur
					$this->view->user->pv_max_bm_braldun = $this->view->user->pv_max_bm_braldun - floor($this->view->user->niveau_braldun / 10);
				} else if ($r["nom_type_rune"] == "RE") {
					// RE ARM NAT + Niveau du Braldun/10 arrondi inférieur
					$this->view->user->armure_naturelle_braldun = $this->view->user->armure_naturelle_braldun - floor($this->view->user->niveau_braldun / 10);
				} else if ($r["nom_type_rune"] == "OG") {
					// OG Bonus de VIG = Niveau de VIG/3 arrondi inférieur
					$this->view->user->vigueur_bm_braldun = $this->view->user->vigueur_bm_braldun - floor($this->view->user->vigueur_base_braldun / 3);
				} else if ($r["nom_type_rune"] == "OX") {
					// OX Poids maximum porté augmenté de Niveau du Braldun/10 arrondi inférieur
					$this->view->user->poids_transportable_braldun = $this->view->user->poids_transportable_braldun - floor($this->view->user->niveau_braldun / 10);
				} else if ($r["nom_type_rune"] == "UP") {
					// UP Bonus de SAG = Niveau de SAG/3 arrondi inférieur
					$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun - floor($this->view->user->sagesse_base_braldun / 3);
				}
			}
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_b") {
			$this->view->user->sagesse_bm_braldun = $this->view->user->sagesse_bm_braldun - (3 * ($equipement["niveau"] + 1));
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_e") {
			$this->view->user->pv_max_bm_braldun = $this->view->user->pv_max_bm_braldun + ($equipement["niveau"] * 10);
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_k") {
			if ($equipement["attaque"] > 0) { // positif
				$val = $equipement["attaque"];
			} else { // negatif
				$val = abs($equipement["attaque"]) / 2;
			}
			$this->view->user->bm_attaque_braldun = $this->view->user->bm_attaque_braldun - $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_m") {
			if ($equipement["defense"] > 0) { // positif
				$val = $equipement["defense"];
			} else { // negatif
				$val = abs($equipement["defense"]) / 2;
			}
			$this->view->user->bm_defense_braldun = $this->view->user->bm_defense_braldun - $val;
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_r") {
			Bral_Util_Commun::retireEffetMotR($this->view->user->id_braldun);
		}

		if ($equipement["nom_systeme_mot_runique"] == "mot_v") {
			$this->view->user->vue_bm_braldun = $this->view->user->vue_bm_braldun - 2;
		}

		$this->majBraldunEffet();
	}

	private function majBraldunEffet() {
		$braldunTable = new Braldun();
		$data = array(
			'vue_bm_braldun' => $this->view->user->vue_bm_braldun,
			'force_bm_braldun' => $this->view->user->force_bm_braldun,
			'agilite_bm_braldun' => $this->view->user->agilite_bm_braldun,
			'vigueur_bm_braldun' => $this->view->user->vigueur_bm_braldun,
			'sagesse_bm_braldun' => $this->view->user->sagesse_bm_braldun,
			'vue_bm_braldun' => $this->view->user->vue_bm_braldun,
			'armure_naturelle_braldun' => $this->view->user->armure_naturelle_braldun,
			'armure_equipement_braldun' => $this->view->user->armure_equipement_braldun,
			'poids_transportable_braldun' => $this->view->user->poids_transportable_braldun,
			'duree_prochain_tour_braldun' => $this->view->user->duree_prochain_tour_braldun,
			'pv_max_bm_braldun' => $this->view->user->pv_max_bm_braldun,
			'bm_attaque_braldun' => $this->view->user->bm_attaque_braldun,
			'bm_degat_braldun' => $this->view->user->bm_degat_braldun,
			'bm_defense_braldun' => $this->view->user->bm_defense_braldun,
			'duree_bm_tour_braldun' => $this->view->user->duree_bm_tour_braldun,
		);
		$where = "id_braldun=" . $this->view->user->id_braldun;
		$braldunTable->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences", "box_equipement", "box_laban"));
	}
}
