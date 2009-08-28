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
class Bral_Competences_Utiliserpotion extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("LabanPotion");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		Zend_Loader::loadClass("Bral_Util_Potion");

		$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);

		$potionCourante = null;
		$idPotionCourante = $this->request->get("potion");

		foreach ($potions as $p) {
			$selected = "";
			if ($idPotionCourante == $p["id_laban_potion"]) {
				$selected = "selected";
			}

			$tabPotions[$p["id_laban_potion"]] = array(
					"id_potion" => $p["id_laban_potion"],
					"id_fk_type_potion" => $p["id_fk_type_laban_potion"],
					"id_fk_type_qualite_potion" => $p["id_fk_type_qualite_laban_potion"],
					"nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_laban_potion"],
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
		$tabHobbits = null;
		$tabMonstres = null;
		// recuperation des hobbits qui sont presents sur la case
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, -1, false);
		foreach($hobbits as $h) {
			$tab = array(
				'id_hobbit' => $h["id_hobbit"],
				'nom_hobbit' => $h["nom_hobbit"],
				'prenom_hobbit' => $h["prenom_hobbit"],
				'niveau_hobbit' => $h["niveau_hobbit"],
			);
			$tabHobbits[] = $tab;
		}

		// si le joueur courant est intangible, on le rajoute à la liste
		if ($this->view->user->est_intangible_hobbit == "oui") {
			$tab = array(
				'id_hobbit' => $this->view->user->id_hobbit,
				'nom_hobbit' => $this->view->user->nom_hobbit,
				'prenom_hobbit' => $this->view->user->prenom_hobbit,
				'niveau_hobbit' => $this->view->user->niveau_hobbit,
			);
			$tabHobbits[] = $tab;
		}

		// recuperation des monstres qui sont presents sur la case
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		foreach($monstres as $m) {
			if ($m["genre_type_monstre"] == 'feminin') {
				$m_taille = $m["nom_taille_f_monstre"];
			} else {
				$m_taille = $m["nom_taille_m_monstre"];
			}
			$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"]);
		}

		$this->view->tabHobbits = $tabHobbits;
		$this->view->nHobbits = count($tabHobbits);
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
		$equipementsLaban = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		$tabEquipementsLaban = null;
		foreach ($equipementsLaban as $e) {
			if ($this->view->potionCourante["type_potion"] == "vernis_enchanteur" ||
			($this->view->potionCourante["type_potion"] == "vernis_reparateur" && $this->view->potionCourante["id_fk_type_ingredient_type_potion"] == $e["id_fk_type_ingredient_base_type_equipement"])) {
				$tabEquipementsLaban[$e["id_laban_equipement"]] = array(
					"id_equipement" => $e["id_laban_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"niveau" => $e["niveau_recette_equipement"],
					"genre_type_equipement" => $e["genre_type_equipement"],
					"etat_initial_equipement" => $e["etat_initial_equipement"],
					"etat_courant_equipement" => $e["etat_courant_equipement"],
					"poids_recette_equipement" => $e["poids_recette_equipement"],
				);
			}
		}

		// recuperation des équipement qui sont presents dans la charrette
		Zend_Loader::loadClass("CharretteEquipement");
		$charretteEquipementTable = new CharretteEquipement();
		$equipementsCharrette = $charretteEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		$tabEquipementsCharrette = null;
		foreach ($equipementsCharrette as $e) {
			if ($this->view->potionCourante["type_potion"] == "vernis_enchanteur" ||
			($this->view->potionCourante["type_potion"] == "vernis_reparateur" && $this->view->potionCourante["id_fk_type_ingredient_type_potion"] == $e["id_fk_type_ingredient_base_type_equipement"])) {
				$tabEquipementsCharrette[$e["id_charrette_equipement"]] = array(
					"id_equipement" => $e["id_charrette_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"niveau" => $e["niveau_recette_equipement"],
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
		$this->majHobbit();
	}

	private function controlePotion($idPotion) {
		$idHobbit = null;
		$idMonstre = null;

		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_2"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_2");
		}

		if (((int)$this->request->get("valeur_3").""!=$this->request->get("valeur_3")."")) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide : ".$this->request->get("valeur_3"));
		} else {
			$idHobbit = (int)$this->request->get("valeur_3");
		}

		if ($idMonstre == -1 && $idHobbit == -1) {
			throw new Zend_Exception(get_class($this)." Monstre ou Hobbit invalide (==-1)");
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
		if ($idHobbit != -1 && $this->view->estRegionPvp == false && $potion["bm_type"] == "malus") {
			throw new Zend_Exception(get_class($this)." Potion invalide (".$idPotion.") region pve, idh:".$this->view->user->id_hobbit." x:".$this->view->user->x_hobbit. " y=".$this->view->user->y_hobbit);
		}

		$trouveH = false;
		if ($this->view->tabHobbits != null) {
			foreach($this->view->tabHobbits as $h) {
				if ($h["id_hobbit"] == $idHobbit) {
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
			throw new Zend_Exception(get_class($this)." id Monstre (".$idMonstre.") ou id Hobbit (".$idHobbit.") invalide");
		}

		$this->idHobbitCible = $idHobbit;
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
		$utiliserPotionHobbit = false;
		if ($this->idHobbitCible != -1) {
			if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
				foreach ($this->view->tabHobbits as $h) {
					if ($h["id_hobbit"] == $this->idHobbitCible) {
						$utiliserPotionHobbit = true;
						$this->retourPotion['cible'] = array('nom_cible' => $h["prenom_hobbit"]. " ". $h["nom_hobbit"],
													   'id_cible' => $h["id_hobbit"],
													   'niveau_cible' => $h["niveau_hobbit"]
						);
						break;
					}
				}
			}
			if ($utiliserPotionHobbit === false) {
				throw new Zend_Exception(get_class($this)." Hobbit invalide (".$this->idHobbitCible.")");
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

		$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a ";
		if ($this->retourPotion['cible']["id_cible"] == $this->view->user->id_hobbit && $utiliserPotionHobbit === true) {
			$this->detailEvenement .= "bu une potion";
		} else {
			if ($this->idHobbitCible != -1) {
				$this->detailEvenement .= "utilisé une potion sur le hobbit [h".$this->retourPotion['cible']["id_cible"]."]";
			} else {
				$this->detailEvenement .= "utilisé une potion sur le monstre [m".$this->retourPotion['cible']["id_cible"]."]";
			}
		}
		$this->setEvenementQueSurOkJet1(false);
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->competence);

		if ($utiliserPotionHobbit === true) {
			$this->utiliserPotionHobbit($potion, $this->idHobbitCible);
			if ($this->view->user->id_hobbit != $this->retourPotion['cible']["id_cible"]) {
				$detailsBot = $this->getDetailEvenementCible($potion);
				Bral_Util_Evenement::majEvenements($this->retourPotion['cible']["id_cible"], $this->view->config->game->evenements->type->competence, $this->detailEvenement, $detailsBot, $this->retourPotion['cible']["niveau_cible"], "hobbit", true, $this->view);
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

		if ($this->idEquipementLaban != null) {
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
		} else { // reparateur
			$this->appliqueVernisReparateur($potion, $equipement);
		}

		//$this->supprimeDuLaban($potion);
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
		$table = new EquipementBonus();
		$table->update($data, $where);

	}

	private function resetVernisBM($equipement) {
		$table = new EquipementBonus();
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
		$table->update($data, $where);
	}

	private function determineBonusMalus(&$data, &$detail, $caracteristique, $bmType, $potion, $equipement) {
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
					$valeur = -3 * $potion["niveau"];
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = -2 * $potion["niveau"];
				} else {
					$valeur = - $potion["niveau"];
				}
			} else {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = $potion["niveau"];
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = 2 * $potion["niveau"];
				} else {
					$valeur = 3 * $potion["niveau"];
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
				if ($valeur > $equipement["poids_recette_equipement"]) {
					$valeur = $equipement["poids_recette_equipement"];
				}

			}
		} elseif ($type == "D") {
			if ($bmType == "malus") {
				if ($potion["nom_systeme_type_qualite"] == "mediocre") {
					$valeur = -$potion["niveau"] -1;
				} elseif ($potion["nom_systeme_type_qualite"] == "standard") {
					$valeur = -$potion["niveau"];
				} else {
					$valeur = -$potion["niveau"] +1;
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

		$detail .= $bmType. " de ".$valeur." sur la caractéristique ".$caracteristique. " de la pièce d'équipement.<br>";
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
		$detail = "&Eacute;tat de l'équipement : +".$valeur."<br>";
		$detail .= "Nouvel &eacute;tat : ".$etat ." / ".$equipement["etat_initial_equipement"];
		$this->view->detail = $detail;
		$table->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_lieu", "box_laban", "box_effets"));
	}

	private function utiliserPotionHobbit($potion, $idHobbit) {
		Zend_Loader::loadClass("EffetPotionHobbit");

		$nbTour = $this->calculNbTour($potion);
		$potion["bm_effet_potion"] = $this->calculBm($potion["niveau"]);

		if ($nbTour >= 1) {
			$effetPotionHobbitTable = new EffetPotionHobbit();
			$data = array(
				  'id_effet_potion_hobbit' => $potion["id_potion"],
				  'id_fk_type_potion_effet_potion_hobbit' => $potion["id_fk_type_potion"],
				  'id_fk_hobbit_cible_effet_potion_hobbit' => $idHobbit,
				  'id_fk_hobbit_lanceur_effet_potion_hobbit' => $this->view->user->id_hobbit,
				  'id_fk_type_qualite_effet_potion_hobbit' => $potion["id_fk_type_qualite_potion"],
				  'nb_tour_restant_effet_potion_hobbit' => $nbTour,
				  'niveau_effet_potion_hobbit' => $potion["niveau"],
				  'bm_effet_potion_hobbit' => $potion["bm_effet_potion"],
			);
			$effetPotionHobbitTable->insert($data);
		}
		$this->supprimeDuLaban($potion);

		if ($this->view->user->id_hobbit == $idHobbit) {
			$hobbit = $this->view->user;
		} else {
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($idHobbit);
			$hobbit = $hobbitRowset->current();
		}

		$potion["nb_tour_restant"] = $nbTour;
		$this->retourPotion["effet"] = Bral_Util_EffetsPotion::appliquePotionSurHobbit($potion, $this->view->user->id_hobbit, $hobbit, false);
	}

	private function utiliserPotionMonstre($potion, $idMonstre) {
		Zend_Loader::loadClass("EffetPotionMonstre");

		$nbTour = $this->calculNbTour($potion);
		$potion["bm_effet_potion"] = $this->calculBm($potion["niveau"]);

		if ($nbTour > 1) {
			$effetPotionMonstreTable = new EffetPotionMonstre();
			$data = array(
				  'id_effet_potion_monstre' => $potion["id_potion"],
				  'id_fk_type_potion_effet_potion_monstre' => $potion["id_fk_type_potion"],
				  'id_fk_monstre_cible_effet_potion_monstre' => $idMonstre,
				  'id_fk_hobbit_lanceur_effet_potion_monstre' => $this->view->user->id_hobbit,
				  'id_fk_type_qualite_effet_potion_monstre' => $potion["id_fk_type_qualite_potion"],
				  'nb_tour_restant_effet_potion_monstre' => $nbTour,
				  'niveau_effet_potion_monstre' => $potion["niveau"],
				  'bm_effet_potion_monstre' => $potion["bm_effet_potion"],
			);
			$effetPotionMonstreTable->insert($data);
		}
		$this->supprimeDuLaban($potion);

		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->find($idMonstre);
		$monstre = $monstreRowset->current();

		$potion["nb_tour_restant"] = $nbTour;

		$this->retourPotion["effet"] = Bral_Util_EffetsPotion::appliquePotionSurMonstre($potion, $this->view->user->id_hobbit, $monstre, false);
	}

	private function calculBm($niveau) {
		return Bral_Util_De::getLanceDe3($niveau + 1);;
	}

	private function calculNbTour($potion) {
		$nbTour = Bral_Util_De::get_1d3();
		if ($potion["nom_systeme_type_qualite"] == 'standard') {
			$nbTour = $nbTour + 1;
		} else if ($potion["nom_systeme_type_qualite"] == 'bonne') {
			$nbTour = $nbTour + 2;
		}
		$nbTour = $nbTour - 1; // tour courant
		if ($nbTour < 1) {
			$nbTour = 1;
		}
		return $nbTour;
	}

	private function supprimeDuLaban($potion) {
		$labanPotionTable = new LabanPotion();
		$where = 'id_laban_potion = '.$potion["id_potion"];
		$labanPotionTable->delete($where);
	}

	private function getDetailEvenementCible($potion) {
		$retour = "";

		if ($this->view->user->id_hobbit != $this->retourPotion['cible']["id_cible"]) {
			$retour .= $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") ";
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

		$retour .= "
";
		$retour .= "L'effet de la potion porte sur ".$this->retourPotion['effet']['nb_tour_restant']." tour";
		if ($this->retourPotion['effet']['nb_tour_restant'] > 1): $retour .= 's'; endif;
		$retour .= ".
Vous venez de subir ".$this->retourPotion['effet']["nEffet"];
		$retour .= " point";
		if ($this->retourPotion['effet']["nEffet"] > 1): $retour .= 's'; endif;
		$retour .= " de ".$potion["bm_type"];
		$retour .= " sur ".$potion["caracteristique"];
		$retour .= "
L'effet est immédiat.";

		return $retour;
	}
}
