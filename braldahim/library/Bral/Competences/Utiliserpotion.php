<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Utiliserpotion extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("LabanPotion");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		Zend_Loader::loadClass("Bral_Util_Potion");
		Zend_Loader::loadClass("Potion");

		$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_braldun, $this->view->user->y_braldun);

		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdBraldun($this->view->user->id_braldun);

		$potionCourante = null;
		$idPotionCourante = $this->request->get("potion");

		foreach ($potions as $p) {
			$selected = "";
			if ($idPotionCourante == $p["id_laban_potion"]) {
				$selected = "selected";
			}

			$tabPotions[$p["id_laban_potion"]] = array(
					"id_potion" => $p["id_laban_potion"],
					"id_fk_type_potion" => $p["id_fk_type_potion"],
					"id_fk_type_qualite_potion" => $p["id_fk_type_qualite_potion"],
					"nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
					"nom" => $p["nom_type_potion"],
					"de" => $p["de_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
					"type_potion" => $p["type_potion"],
					'selected' => $selected,
					'template_m_type_potion' => $p["template_m_type_potion"],
					'template_f_type_potion' => $p["template_f_type_potion"],
					'id_fk_type_ingredient_type_potion' => $p["id_fk_type_ingredient_type_potion"],
			);

			if ($idPotionCourante == $p["id_laban_potion"]) {
				$potionCourante = $p;
			}
		}

		$this->view->estRegionPvp = $estRegionPvp;
		$this->view->nPotions = count($tabPotions);
		$this->view->tabPotions = $tabPotions;
		$this->view->potionCourante = $potionCourante;

		if (isset($potionCourante)) {
			if ($potionCourante["type_potion"] == "potion") {
				$this->preparePotion();
			} else {
				$this->prepareVernis();
			}
		}

	}

	private function preparePotion() {
		$tabBralduns = null;
		$tabMonstres = null;
		// recuperation des bralduns qui sont presents sur la case
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, -1, false);
		foreach($bralduns as $h) {
			$tab = array(
				'id_braldun' => $h["id_braldun"],
				'nom_braldun' => $h["nom_braldun"],
				'prenom_braldun' => $h["prenom_braldun"],
				'niveau_braldun' => $h["niveau_braldun"],
			);
			$tabBralduns[] = $tab;
		}

		// si le joueur courant est intangible, on le rajoute à la liste
		if ($this->view->user->est_intangible_braldun == "oui") {
			$tab = array(
				'id_braldun' => $this->view->user->id_braldun,
				'nom_braldun' => $this->view->user->nom_braldun,
				'prenom_braldun' => $this->view->user->prenom_braldun,
				'niveau_braldun' => $this->view->user->niveau_braldun,
			);
			$tabBralduns[] = $tab;
		}

		// recuperation des monstres qui sont presents sur la case
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		foreach($monstres as $m) {
			if ($m["genre_type_monstre"] == 'feminin') {
				$m_taille = $m["nom_taille_f_monstre"];
			} else {
				$m_taille = $m["nom_taille_m_monstre"];
			}
			$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"]);
		}

		$this->view->tabBralduns = $tabBralduns;
		$this->view->nBralduns = count($tabBralduns);
		$this->view->tabMonstres = $tabMonstres;
		$this->view->nMonstres = count($tabMonstres);
	}

	private function prepareVernis() {
		$tabEquipementsLaban = null;
		$tabEquipementsCharrette = null;

		Zend_Loader::loadClass("Bral_Util_Equipement");

		// recuperation des équipement qui sont presents dans le laban
		Zend_Loader::loadClass("LabanEquipement");
		$labanEquipementTable = new LabanEquipement();
		$equipementsLaban = $labanEquipementTable->findByIdBraldun($this->view->user->id_braldun);
		$tabEquipementsLaban = null;
		foreach ($equipementsLaban as $e) {
			if (
			$this->view->potionCourante["niveau_potion"] >=$e["niveau_recette_equipement"] &&
			($this->view->potionCourante["type_potion"] == "vernis_enchanteur" ||
			($this->view->potionCourante["type_potion"] == "vernis_reparateur" && $this->view->potionCourante["id_fk_type_ingredient_type_potion"] == $e["id_fk_type_ingredient_base_type_equipement"]))) {
				$tabEquipementsLaban[$e["id_laban_equipement"]] = array(
					"id_equipement" => $e["id_laban_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"niveau" => $e["niveau_recette_equipement"],
					"genre_type_equipement" => $e["genre_type_equipement"],
					"etat_initial_equipement" => $e["etat_initial_equipement"],
					"etat_courant_equipement" => $e["etat_courant_equipement"],
					"poids_equipement" => $e["poids_equipement"],
				);
			}
		}

		// recuperation des équipement qui sont presents dans la charrette
		Zend_Loader::loadClass("CharretteEquipement");
		$charretteEquipementTable = new CharretteEquipement();
		$equipementsCharrette = $charretteEquipementTable->findByIdBraldun($this->view->user->id_braldun);
		$tabEquipementsCharrette = null;
		foreach ($equipementsCharrette as $e) {
			if (
			$this->view->potionCourante["niveau_potion"] >=$e["niveau_recette_equipement"] &&
			($this->view->potionCourante["type_potion"] == "vernis_enchanteur" ||
			($this->view->potionCourante["type_potion"] == "vernis_reparateur" && $this->view->potionCourante["id_fk_type_ingredient_type_potion"] == $e["id_fk_type_ingredient_base_type_equipement"]))) {
				$tabEquipementsCharrette[$e["id_charrette_equipement"]] = array(
					"id_equipement" => $e["id_charrette_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"niveau" => $e["niveau_recette_equipement"],
					"genre_type_equipement" => $e["genre_type_equipement"],
					"etat_initial_equipement" => $e["etat_initial_equipement"],
					"etat_courant_equipement" => $e["etat_courant_equipement"],
				);
			}
		}

		$this->view->tabEquipementsLaban = $tabEquipementsLaban;
		$this->view->nEquipementsLaban = count($tabEquipementsLaban);
		$this->view->tabEquipementsCharrette = $tabEquipementsCharrette;
		$this->view->nEquipementsCharrette = count($tabEquipementsCharrette);
	}

	function prepareFormulaire() {
		// rien a faire ici
	}

	function prepareResultat() {

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Potion invalide : ".$this->request->get("valeur_1"));
		} else {
			$idPotion = (int)$this->request->get("valeur_1");
		}

		if (isset($this->view->potionCourante)) {
			if ($idPotion == $this->view->potionCourante["id_laban_potion"]) {
				if ($this->view->potionCourante["type_potion"] == "potion") {
					$potion = $this->controlePotion($idPotion);
					$this->appliquePotion($potion);
				} else {
					$vernis = $this->controleVernis($idPotion);
					$this->appliqueVernis($vernis);
				}
			} else {
				throw new Zend_Exception(get_class($this)." Potion invalide 2 : ".$this->request->get("valeur_1") . " id2:".$this->view->potionCourante["id_laban_potion"]);
			}
		} else {
			throw new Zend_Exception(get_class($this)." Potion invalide 3 : ".$this->request->get("valeur_1"));
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function controlePotion($idPotion) {
		$idBraldun = null;
		$idMonstre = null;

		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_2"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_2");
		}

		if (((int)$this->request->get("valeur_3").""!=$this->request->get("valeur_3")."")) {
			throw new Zend_Exception(get_class($this)." Braldûn invalide : ".$this->request->get("valeur_3"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_3");
		}

		if ($idMonstre == -1 && $idBraldun == -1) {
			throw new Zend_Exception(get_class($this)." Monstre ou Braldûn invalide (==-1)");
		}

		$potion = null;
		foreach ($this->view->tabPotions as $p) {
			if ($p["id_potion"] == $idPotion && $p["type_potion"] == "potion") {
				$potion = $p;
				break;
			}
		}

		if ($potion == null) {
			throw new Zend_Exception(get_class($this)." Potion invalide (".$idPotion.")");
		}

		// pas de potion de malus en zone pve
		if ($idBraldun != -1 && $this->view->estRegionPvp == false && $potion["bm_type"] == "malus") {
			throw new Zend_Exception(get_class($this)." Potion invalide (".$idPotion.") region pve, idh:".$this->view->user->id_braldun." x:".$this->view->user->x_braldun. " y=".$this->view->user->y_braldun);
		}

		$trouveH = false;
		if ($this->view->tabBralduns != null) {
			foreach($this->view->tabBralduns as $h) {
				if ($h["id_braldun"] == $idBraldun) {
					$trouveH = true;
					break;
				}
			}
		}

		$trouveM = false;
		if ($this->view->tabMonstres != null) {
			foreach ($this->view->tabMonstres as $m) {
				if ($m["id_monstre"] == $idMonstre) {
					$trouveM = true;
					break;
				}
			}
		}

		if ($trouveH == false && $trouveM == false) {
			throw new Zend_Exception(get_class($this)." id Monstre (".$idMonstre.") ou id Braldûn (".$idBraldun.") invalide");
		}

		$this->idBraldunCible = $idBraldun;
		$this->idMonstreCible = $idMonstre;

		return $potion;
	}

	private function controleVernis($idPotion) {
		$idEquipementLaban = null;
		$idEquipementCharrette = null;

		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Equipement Laban invalide : ".$this->request->get("valeur_2"));
		} else {
			$idEquipementLaban = (int)$this->request->get("valeur_2");
		}

		if (((int)$this->request->get("valeur_3").""!=$this->request->get("valeur_3")."")) {
			throw new Zend_Exception(get_class($this)." Equipement Charrette invalide : ".$this->request->get("valeur_3"));
		} else {
			$idEquipementCharrette = (int)$this->request->get("valeur_3");
		}

		if ($idEquipementCharrette == -1 && $idEquipementLaban == -1) {
			throw new Zend_Exception(get_class($this)." Equipement laban ou Equipement charrette invalide (==-1)");
		}

		$vernis = null;
		foreach ($this->view->tabPotions as $p) {
			if ($p["id_potion"] == $idPotion &&  ($p["type_potion"] == "vernis_reparateur" || $p["type_potion"] == "vernis_enchanteur")) {
				$vernis = $p;
				break;
			}
		}

		if ($vernis == null) {
			throw new Zend_Exception(get_class($this)." Vernis invalide (".$idPotion.")");
		}

		$trouveL = false;
		if ($this->view->tabEquipementsLaban != null) {
			foreach($this->view->tabEquipementsLaban as $l) {
				if ($l["id_equipement"] == $idEquipementLaban) {
					$trouveL = true;
					break;
				}
			}
		}

		$trouveC = false;
		if ($this->view->tabEquipementsCharrette != null) {
			foreach ($this->view->tabEquipementsCharrette as $c) {
				if ($c["id_equipement"] == $idEquipementCharrette) {
					$trouveC = true;
					break;
				}
			}
		}

		if ($trouveL == false && $trouveC == false) {
			throw new Zend_Exception(get_class($this)." id Equipement Laban (".$idEquipementLaban.") ou id Equipement Charrette (".$idEquipementCharrette.") invalide");
		}

		$this->idEquipementLaban = $idEquipementLaban;
		$this->idEquipementCharrette = $idEquipementCharrette;

		return $vernis;
	}

	private function appliquePotion($potion) {
		$this->retourPotion = null;

		$utiliserPotionMonstre = false;
		$utiliserPotionBraldun = false;
		if ($this->idBraldunCible != -1) {
			if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
				foreach ($this->view->tabBralduns as $h) {
					if ($h["id_braldun"] == $this->idBraldunCible) {
						$utiliserPotionBraldun = true;
						$this->retourPotion['cible'] = array('nom_cible' => $h["prenom_braldun"]. " ". $h["nom_braldun"],
													   'id_cible' => $h["id_braldun"],
													   'niveau_cible' => $h["niveau_braldun"]
						);
						break;
					}
				}
			}
			if ($utiliserPotionBraldun === false) {
				throw new Zend_Exception(get_class($this)." Braldûn invalide (".$this->idBraldunCible.")");
			}
		} else {
			if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
				foreach ($this->view->tabMonstres as $m) {
					if ($m["id_monstre"] == $this->idMonstreCible) {
						$utiliserPotionMonstre = true;
						$this->retourPotion['cible'] = array('nom_cible' => $m["nom_monstre"],
													   'id_cible' => $m["id_monstre"],
														'niveau_cible' => $m["niveau_monstre"],
						);
						break;
					}
				}
			}
			if ($utiliserPotionMonstre === false) {
				throw new Zend_Exception(get_class($this)." Monstre invalide (".$this->idMonstreCible.")");
			}
		}

		Zend_Loader::loadClass("Bral_Util_EffetsPotion");

		$this->detailEvenement = "[b".$this->view->user->id_braldun."] a ";
		if ($this->retourPotion['cible']["id_cible"] == $this->view->user->id_braldun && $utiliserPotionBraldun === true) {
			$this->detailEvenement .= "bu une potion";
		} else {
			if ($this->idBraldunCible != -1) {
				$this->detailEvenement .= "utilisé une potion sur [b".$this->retourPotion['cible']["id_cible"]."]";
			} else {
				$this->detailEvenement .= "utilisé une potion sur le monstre [m".$this->retourPotion['cible']["id_cible"]."]";
			}
		}
		$this->setEvenementQueSurOkJet1(false);
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->competence);

		Bral_Util_Potion::insertHistorique(Bral_Util_Potion::HISTORIQUE_UTILISER_ID, $potion["id_potion"], $this->detailEvenement);
			
		if ($utiliserPotionBraldun === true) {
			$this->utiliserPotionBraldun($potion, $this->idBraldunCible);
			if ($this->view->user->id_braldun != $this->retourPotion['cible']["id_cible"]) {
				$detailsBot = $this->getDetailEvenementCible($potion);
				Bral_Util_Evenement::majEvenements($this->retourPotion['cible']["id_cible"], $this->view->config->game->evenements->type->competence, $this->detailEvenement, $detailsBot, $this->retourPotion['cible']["niveau_cible"], "braldun", true, $this->view);
			}
		} elseif ($utiliserPotionMonstre === true) {
			$this->utiliserPotionMonstre($potion, $this->idMonstreCible);
			$this->setDetailsEvenementCible($this->idMonstreCible, "monstre", $this->retourPotion['cible']["niveau_cible"]);
		} else {
			throw new Zend_Exception(get_class($this)." Erreur inconnue");
		}

		$this->retourPotion['potion'] = $potion;
		$this->view->retourPotion = $this->retourPotion;
	}

	private function appliqueVernis($potion) {

		if ($this->idEquipementLaban != null && $this->idEquipementLaban != -1) {
			$equipement = $this->view->tabEquipementsLaban[$this->idEquipementLaban];
		} else { // Charrette
			$equipement = $this->view->tabEquipementsCharrette[$this->idEquipementCharrette];
		}

		Zend_Loader::loadClass("Equipement");
		$table = new Equipement();
		if ($equipement["genre_type_equipement"] == "masculin") {
			$template = $potion["template_m_type_potion"];
		} else {
			$template = $potion["template_f_type_potion"];
		}
		$data = array(
			'vernis_template_equipement' => $template
		);
		$where = "id_equipement = ".$equipement["id_equipement"];
		$table->update($data, $where);

		Zend_Loader::loadClass("EquipementBonus");
		if ($potion["type_potion"] == "vernis_enchanteur") {
			$this->appliqueVernisEnchanteur($equipement, $potion);
			$details = "[b".$this->view->user->id_braldun."] a verni la pièce d'équipement n°".$equipement["id_equipement"];
			Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_VERNIR_ID, $equipement["id_equipement"], $details);
			$details = "[b".$this->view->user->id_braldun."] a utilisé le verni n°".$potion["id_potion"]." sur la pièce d'équipement n°".$equipement["id_equipement"];
			Bral_Util_Potion::insertHistorique(Bral_Util_Potion::HISTORIQUE_UTILISER_ID, $potion["id_potion"], $details);
		} else { // reparateur
			$this->appliqueVernisReparateur($potion, $equipement);
			$details = "[b".$this->view->user->id_braldun."] a réparé la pièce d'équipement n°".$equipement["id_equipement"];
			Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_REPARER_ID, $equipement["id_equipement"], $details);
			$details = "[b".$this->view->user->id_braldun."] a utilisé le verni n°".$potion["id_potion"]." pour réparer la pièce d'équipement n°".$equipement["id_equipement"];
			Bral_Util_Potion::insertHistorique(Bral_Util_Potion::HISTORIQUE_UTILISER_ID, $potion["id_potion"], $details);
		}

		$this->supprimeDuLaban($potion);
		$this->view->equipement = $equipement;
	}

	private function appliqueVernisEnchanteur($equipement, $potion) {
		$this->resetVernisBM($equipement);
		$data = array();
		$detail = "";
		$this->determineBonusMalus(&$data, $detail, $potion["caracteristique"], $potion["bm_type"], $potion, $equipement);
		$this->determineBonusMalus(&$data, $detail, $potion["caracteristique2"], $potion["bm2_type"], $potion, $equipement);
		$this->view->detail = $detail;
		$where = "id_equipement_bonus = ".$equipement["id_equipement"];
		$equipementBonusTable = new EquipementBonus();
		$equipementBonusTable->update($data, $where);

		// mise à jour du poids
		Zend_Loader::loadClass("Equipement");
		$equipementTable = new Equipement();
		$data = array('poids_equipement' => $equipement["poids_equipement"]);
		$where = "id_equipement=".$equipement["id_equipement"];
		$equipementTable->update($data, $where);
	}

	private function resetVernisBM(&$equipement) {

		// recalcul du poids
		$equipementBonusTable = new EquipementBonus();
		$equipementBonus = $equipementBonusTable->findByIdEquipement($equipement["id_equipement"]);

		if (count($equipementBonus) > 0) {
			foreach($equipementBonus as $b) {
				$equipement["poids_equipement"] = $equipement["poids_equipement"] - $b["vernis_bm_poids_equipement_bonus"];
			}
		}

		$data = array(
			'vernis_bm_vue_equipement_bonus' => null,
			'vernis_bm_armure_equipement_bonus' => null,
			'vernis_bm_poids_equipement_bonus' => null,
			'vernis_bm_agilite_equipement_bonus' => null,
			'vernis_bm_force_equipement_bonus' => null,
			'vernis_bm_sagesse_equipement_bonus' => null,
			'vernis_bm_vigueur_equipement_bonus' => null,
			'vernis_bm_attaque_equipement_bonus' => null,
			'vernis_bm_degat_equipement_bonus' => null,
			'vernis_bm_defense_equipement_bonus' => null,
		);

		$where = "id_equipement_bonus = ".$equipement["id_equipement"];
		$equipementBonusTable->update($data, $where);
	}

	private function determineBonusMalus(&$data, &$detail, $caracteristique, $bmType, $potion, &$equipement) {
		if ($caracteristique == "VUE") {
			$type = "A";
			$nom = "vernis_bm_vue_equipement_bonus";
		} else if ($caracteristique == "ARM") {
			$type = "B";
			$nom = "vernis_bm_armure_equipement_bonus";
		} else if ($caracteristique == "POIDS") {
			$type = "C";
			$nom = "vernis_bm_poids_equipement_bonus";
		} else if ($caracteristique == "AGI") {
			$type = "D";
			$nom = "vernis_bm_agilite_equipement_bonus";
		} else if ($caracteristique == "FOR") {
			$type = "D";
			$nom = "vernis_bm_force_equipement_bonus";
		} else if ($caracteristique == "SAG") {
			$type = "D";
			$nom = "vernis_bm_sagesse_equipement_bonus";
		} else if ($caracteristique == "VIG") {
			$type = "D";
			$nom = "vernis_bm_vigueur_equipement_bonus";
		} else if ($caracteristique == "ATT") {
			$type = "D";
			$nom = "vernis_bm_attaque_equipement_bonus";
		} else if ($caracteristique == "DEG") {
			$type = "D";
			$nom = "vernis_bm_degat_equipement_bonus";
		} else if ($caracteristique == "DEF") {
			$type = "D";
			$nom = "vernis_bm_defense_equipement_bonus";
		}

		if ($type == "A") {
			if ($bmType == "malus") {
				$valeur = -1;
			} else {
				$valeur = 1;
			}
		} elseif ($type == "B") {
			if ($bmType == "malus") {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = -$potion["niveau"] - 3;
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = -$potion["niveau"] - 2;
				} else {
					$valeur = -$potion["niveau"] - 1;
				}
			} else {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = $potion["niveau"] -1;
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = $potion["niveau"];
				} else {
					$valeur = $potion["niveau"] +1;
				}
			}
		} elseif ($type == "C") { // poids
			if ($bmType == "malus") {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = 0.2 * $potion["niveau"];
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = 0.1 * $potion["niveau"];
				} else {
					$valeur = 0;
				}
			} else {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = 0;
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = -0.1 * $potion["niveau"];
				} else {
					$valeur = -0.2 * $potion["niveau"];
				}

				// il ne faut pas que la valeur dépasse le poids de l'équipement.
				if ($valeur > $equipement["poids_equipement"]) {
					$valeur = $equipement["poids_equipement"];
				}

				// mise à jour du poids
				$equipement["poids_equipement"] = $equipement["poids_equipement"] + $valeur;

			}
		} elseif ($type == "D") {
			if ($bmType == "malus") {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = -$potion["niveau"] - 3;
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = -$potion["niveau"] - 2;
				} else {
					$valeur = -$potion["niveau"] - 1;
				}
			} else {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = $potion["niveau"] -1;
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = $potion["niveau"];
				} else {
					$valeur = $potion["niveau"] +1;
				}
			}
		}

		$detail .= $bmType. " de ".$valeur." sur la caractéristique ".$caracteristique. " de la pièce d'équipement.<br />";
		$data[$nom] = $valeur;
	}

	private function appliqueVernisReparateur($potion, $equipement) {
		$this->resetVernisBM($equipement);

		if ($potion["nom_systeme_type_qualite"] == "mediocre") {
			$valeur = +500;
		} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
			$valeur = +1000;
		} else {
			$valeur = +1500;
		}

		Zend_Loader::loadClass("Equipement");
		$table = new Equipement();

		$etat = $equipement["etat_courant_equipement"] + $valeur;

		if ($etat > $equipement["etat_initial_equipement"]) {
			$etat = $equipement["etat_initial_equipement"];
		}
		$data = array(
			'etat_courant_equipement' => $etat,
		);
		$where = "id_equipement = ".$equipement["id_equipement"];
		$detail = "&Eacute;tat de l'équipement : +".$valeur."<br />";
		$detail .= "Nouvel &eacute;tat : ".$etat ." / ".$equipement["etat_initial_equipement"];
		$this->view->detail = $detail;
		$table->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_lieu", "box_laban", "box_effets", "box_titres"));
	}

	private function utiliserPotionBraldun($potion, $idBraldun) {
		Zend_Loader::loadClass("EffetPotionBraldun");

		if ($this->view->user->id_braldun == $idBraldun) {
			$braldun = $this->view->user;
		} else {
			$braldunTable = new Braldun();
			$braldunRowset = $braldunTable->find($idBraldun);
			$braldun = $braldunRowset->current();
		}

		$this->retourPotion["effet"] = Bral_Util_EffetsPotion::appliquePotionSurBraldun($potion, $this->view->user->id_braldun, $braldun, false, true, true);
		if ($this->view->user->id_braldun != $idBraldun && $potion["bm_type"] == 'malus' && $braldunCible->points_gredin_braldun <= 0) { // cible sans points de gredin
			$this->view->user->points_gredin_braldun = $this->view->user->points_gredin_braldun + 1;
			if ($this->view->user->points_redresseur_braldun > 0) { // s'il est redresseur
				$this->view->user->points_redresseur_braldun = $this->view->user->points_redresseur_braldun - 3;
				if ($this->view->user->points_redresseur_braldun < 0) {
					$this->view->user->points_redresseur_braldun = 0;
				}
			}
		}
		$this->supprimeDuLaban($potion);
	}

	private function utiliserPotionMonstre($potion, $idMonstre) {
		Zend_Loader::loadClass("EffetPotionMonstre");

		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->find($idMonstre);
		$monstre = $monstreRowset->current();
		$monstre = $monstre->toArray();

		$this->retourPotion["effet"] = Bral_Util_EffetsPotion::appliquePotionSurMonstre($potion, $this->view->user->id_braldun, $monstre, false, true, true);
		$this->supprimeDuLaban($potion);
	}

	private function supprimeDuLaban($potion) {
		$labanPotionTable = new LabanPotion();
		$where = 'id_laban_potion = '.$potion["id_potion"];
		$labanPotionTable->delete($where);

		$potionTable = new Potion();
		$where = 'id_potion = '.$potion["id_potion"];
		$data = array('date_utilisation_potion' => date("Y-m-d H:i:s"));
		$potionTable->update($data, $where);
	}

	private function getDetailEvenementCible($potion) {
		$retour = "";

		if ($this->view->user->id_braldun != $this->retourPotion['cible']["id_cible"]) {
			$retour .= $this->view->user->prenom_braldun ." ". $this->view->user->nom_braldun ." (".$this->view->user->id_braldun.") ";
		}

		if ($potion["bm_type"] == "bonus") {
			$retour .= "vous a lancé une potion ";
			$retour .= htmlspecialchars($potion["nom"])." de qualité ";
			$retour .= htmlspecialchars($potion["qualite"]);
			$retour .= " que vous avez immédiatement bu !";
		} else {
			$retour .= "vous a jetté à la figure une fiole qui éclate. ";
			$retour .= "La potion ";
			$retour .= htmlspecialchars($potion["nom"])." de qualité ";
			$retour .= htmlspecialchars($potion["qualite"]);
			$retour .= " commence à faire effet...";
		}

		$retour .= PHP_EOL;
		$retour .= "L'effet de la potion porte sur ".$this->retourPotion['effet']['nb_tour_restant']." tour";
		if ($this->retourPotion['effet']['nb_tour_restant'] > 1): $retour .= 's'; endif;
		$retour .= ".".PHP_EOL."Vous venez de subir ".$this->retourPotion['effet']["nEffet"];
		$retour .= " point";
		if ($this->retourPotion['effet']["nEffet"] > 1): $retour .= 's'; endif;
		$retour .= " de ".$potion["bm_type"];
		$retour .= " sur ".$potion["caracteristique"];
		$retour .= PHP_EOL."L'effet est immédiat.";

		return $retour;
	}
}
