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
class Bral_Box_Tour extends Bral_Box_Box {
	
	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Util_Log");
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$this->hobbit = $hobbitRowset->current();

		$this->nomsTour = Zend_Registry::get('nomsTour');
		$this->view->user->nom_tour = $this->nomsTour[$this->view->user->tour_position_hobbit];
		$this->calculInfoTour();
	}

	function getTitreOnglet() {
		return false;
	}
	
	function getNomInterne() {
		return "box_tour";
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->user->nom_tour = $this->nomsTour[$this->view->user->tour_position_hobbit];
		return $this->view->render("interface/tour.phtml");
	}

	public function modificationTour() {
		Bral_Util_Log::tour()->debug(get_class($this)." modificationTour - enter - user=".$this->view->user->id_hobbit);
		
		$this->is_update_tour = false;
		$this->is_nouveau_tour = false;

		if ($this->view->user->activation === false) {
			return false;
		}
	
		// Calcul de la nouvelle date de fin
		$date_courante = date("Y-m-d H:i:s");
		$this->is_nouveau_tour = $this->calcul_debut_nouveau($date_courante);

		// nouveau tour (ou mort : en cas de mort : la date de fin de tour doit être positionnee à la mort) 
		if ($this->is_nouveau_tour) {
			Bral_Util_Log::tour()->debug(get_class($this)." Nouveau tour");
			$this->calculDLA();
			
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_latence;
			$this->is_update_tour = true;
		}

		/* Si des DLA ont ete manquees, on prend comme date de debut la date courante
		 * et la date de fin, la date courante + 6 heures, le joueur se trouve
		 * directement en position de cumul
		 */
		
		Bral_Util_Log::tour()->debug(get_class($this)." date_fin_latence=".$this->hobbit->date_fin_latence_hobbit);
		Bral_Util_Log::tour()->debug(get_class($this)." date_debut_cumul".$this->hobbit->date_debut_cumul_hobbit);
		Bral_Util_Log::tour()->debug(get_class($this)." date_courante=".$date_courante);
		Bral_Util_Log::tour()->debug(get_class($this)." date fin tour=".$this->hobbit->date_fin_tour_hobbit);
		Bral_Util_Log::tour()->debug(get_class($this)." tour position=".$this->hobbit->tour_position_hobbit);

		$this->is_tour_manque = false;
		// Mise a jour du nombre de PA + position tour
		if ($date_courante > $this->hobbit->date_fin_tour_hobbit) { // Perte d'un tour
			Bral_Util_Log::tour()->debug(get_class($this)." Perte d'un tour");
			$this->hobbit->date_fin_tour_hobbit = Bral_Util_ConvertDate::get_date_add_time_to_date($date_courante, $this->view->config->game->tour->duree_tour_manque);
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_cumul;
			$this->hobbit->pa_hobbit = $this->view->config->game->pa_max_cumul;
			$this->is_tour_manque  = true;
			$this->is_update_tour = true;
		} elseif(($date_courante < $this->hobbit->date_fin_latence_hobbit) // Latence
		&& $this->is_nouveau_tour) {
			Bral_Util_Log::tour()->debug(get_class($this)." Latence Tour");
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_latence;
			$this->hobbit->pa_hobbit = 0;
			$this->is_update_tour = true;
		} elseif(($date_courante >= $this->hobbit->date_fin_latence_hobbit && $date_courante < $this->hobbit->date_debut_cumul_hobbit) // Milieu
		&& ( (!$this->is_nouveau_tour && ($this->hobbit->tour_position_hobbit != $this->view->config->game->tour->position_milieu))
		|| ($this->is_nouveau_tour))) {
			Bral_Util_Log::tour()->debug(get_class($this)." Milieu Tour");
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_milieu;
			$this->hobbit->pa_hobbit = $this->view->config->game->pa_max;
			$this->is_update_tour = true;
		} elseif(($date_courante >= $this->hobbit->date_debut_cumul_hobbit && $date_courante < $this->hobbit->date_fin_tour_hobbit)  // Cumul
		&& ( (!$this->is_nouveau_tour && ($this->hobbit->tour_position_hobbit != $this->view->config->game->tour->position_cumul))
		|| ($this->is_nouveau_tour))) {
			Bral_Util_Log::tour()->debug(get_class($this)." Cumul tour");
			// Si le joueur a déjà eu des PA
			if ($this->hobbit->tour_position_hobbit == $this->view->config->game->tour->position_milieu && !$this->is_nouveau_tour) {
				Bral_Util_Log::tour()->debug(get_class($this)." Le joueur a deja eu des PA");
				$this->hobbit->pa_hobbit = $this->hobbit->pa_hobbit + $this->view->config->game->pa_max;
			} else { // S'il vient d'activer et qu'il n'a jamais eu de PA dans ce tour
				Bral_Util_Log::tour()->debug(get_class($this)." Le joueur n'a pas encore eu de PA");
				$this->hobbit->pa_hobbit = $this->view->config->game->pa_max_cumul;
			}
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_cumul;
			$this->is_update_tour = true;
		}
		
		if (($this->is_update_tour) || ($this->is_nouveau_tour) || ($this->hobbit->est_mort_hobbit == "oui")) {
			Bral_Util_Log::tour()->debug(get_class($this)." modificationTour - exit - true");
			return true;
		} else {
			Bral_Util_Log::tour()->debug(get_class($this)." modificationTour - exit - false");
			return false;
		}
	}

	public function activer() {
		Bral_Util_Log::tour()->trace(get_class($this)." activer - enter -");
		
		$this->view->effetPotion = false;
		
		$this->view->effetMotB = false;
		$this->view->effetMotE = false;
		$this->view->effetMotK = false;
		$this->view->effetMotM = false;
		$this->view->effetMotN = false;
		$this->view->effetMotO = false;
		$this->view->effetMotU = false;
		$this->view->effetMotV = false;
		
		$this->view->ciblesEffetN = null;
		$this->view->ciblesEffetO = null;
		$this->view->ciblesEffetU = null;
		
		$this->is_tour_manque = false;
		
		$this->modificationTour();

		// Mise a jour en cas de mort
		$this->calcul_mort();

		// Si c'est un nouveau tour, on met les BM de force, agi, sag, vue, vig à 0 
		// Ensuite, on les recalcule suivant l'équipement porté et les potions en cours
		if ($this->is_nouveau_tour) {
			Bral_Util_Log::tour()->trace(get_class($this)." activer - is_nouveau_tour - true");
			$this->hobbit->force_bm_hobbit = 0;
			$this->hobbit->agilite_bm_hobbit = 0;
			$this->hobbit->vigueur_bm_hobbit = 0;
			$this->hobbit->sagesse_bm_hobbit = 0;
			$this->hobbit->vue_bm_hobbit = 0;
			$this->hobbit->regeneration_hobbit = 0;
			$this->hobbit->armure_naturelle_hobbit = 0;
			$this->hobbit->armure_equipement_hobbit = 0;
			$this->hobbit->pv_max_bm_hobbit = 0;
			$this->hobbit->bm_attaque_hobbit = 0;
			$this->hobbit->bm_degat_hobbit = 0;
			$this->hobbit->bm_defense_hobbit = 0;
			
			// Recalcul de l'armure naturelle
			$this->hobbit->armure_naturelle_hobbit = intval(($this->hobbit->force_base_hobbit + $this->hobbit->vigueur_base_hobbit) / 5) + 1;
			
			/* Application du malus de vue. */
			$this->hobbit->vue_bm_hobbit = $this->hobbit->vue_malus_hobbit;
			/* Remise à zéro du malus de vue. */
			$this->hobbit->vue_malus_hobbit = 0;
			
			/* Application du malus d'agilite. */
			$this->hobbit->agilite_bm_hobbit = $this->hobbit->agilite_bm_hobbit;
			/* Remise à zéro du malus d'agilite. */
			$this->hobbit->agilite_malus_hobbit = 0;

			// Calcul du poids transportable. // c'est aussi mis à jour dans l'eujimnasiumne
			Zend_Loader::loadClass("Bral_Util_Poids");
			$this->hobbit->poids_transportable_hobbit = Bral_Util_Poids::calculPoidsTransportable($this->hobbit->force_base_hobbit);
			$this->hobbit->poids_transporte_hobbit = Bral_Util_Poids::calculPoidsTransporte($this->hobbit->id_hobbit, $this->hobbit->castars_hobbit);
			
			$this->calculBMEquipement();
			$this->calculBMPotion();
			
			// Mise à jour de la regeneration // c'est aussi mis à jour dans l'eujimnasiumne
			$this->hobbit->regeneration_hobbit = floor($this->hobbit->vigueur_base_hobbit / 4) + 1;
			
			// calcul des pvs restants avec la regeneration
			$this->hobbit->pv_max_hobbit = Bral_Util_Commun::calculPvMaxSansEffetMotE($this->view->config, $this->hobbit->vigueur_base_hobbit, $this->hobbit->pv_max_bm_hobbit);
			
			$effetMotE = Bral_Util_Commun::getEffetMotE($this->view->user->id_hobbit);
			if ($effetMotE != null) {
				Bral_Util_Log::tour()->trace(get_class($this)." activer - effetMotE Actif - effetMotE=".$effetMotE);
				$this->view->effetMotE = true;
				$this->hobbit->pv_max_hobbit = $this->hobbit->pv_max_hobbit - ($effetMotE * 3);
			}
			
			Bral_Util_Log::tour()->trace(get_class($this)." activer - this->hobbit->regeneration_malus_hobbit=".$this->hobbit->regeneration_malus_hobbit);
			$this->view->jetRegeneration = $this->hobbit->regeneration_malus_hobbit;
			/* Remise à zéro du malus de regénération. */
			$this->hobbit->regeneration_malus_hobbit = 0;
			
			$this->calculPv();
			
			Bral_Util_Faim::calculBalanceFaim($this->hobbit);
		}

		if ($this->is_update_tour) {
			Bral_Util_Log::tour()->trace(get_class($this)." activer - is_update_tour - true");
			$this->updateDb();
		}

		$this->view->is_update_tour = $this->is_update_tour;
		$this->view->is_nouveau_tour = $this->is_nouveau_tour;
		$this->view->is_tour_manque = $this->is_tour_manque;
		$this->view->is_mort = $this->est_mort;

		if (($this->is_update_tour) || ($this->is_nouveau_tour)) {
			$this->calculInfoTour();
			Bral_Util_Log::tour()->trace(get_class($this)." activer - exit - true");
			return true;
		} else {
			Bral_Util_Log::tour()->trace(get_class($this)." activer - exit - false");
			return false;
		}
	}
	/* Verification que c'est bien le debut d'un
	 * nouveau tour pour le joueur
	 * @return false si non
	 * @return true si oui
	 */
	private function calcul_debut_nouveau($date_courante) {
		Bral_Util_Log::tour()->trace(get_class($this)." calcul_debut_nouveau - enter -");
		Bral_Util_Log::tour()->debug(get_class($this)." calcul_debut_nouveau - this->hobbit->date_fin_tour_hobbit=".$this->hobbit->date_fin_tour_hobbit);
		Bral_Util_Log::tour()->debug(get_class($this)." calcul_debut_nouveau - date_courante=".$date_courante);
		Bral_Util_Log::tour()->debug(get_class($this)." calcul_debut_nouveau - this->hobbit->est_mort_hobbit=".$this->hobbit->est_mort_hobbit);
		if ($this->hobbit->date_fin_tour_hobbit < $date_courante || $this->hobbit->est_mort_hobbit == 'oui') {
			Bral_Util_Log::tour()->debug(get_class($this)." calcul_debut_nouveau - exit - true");
			return true;
		} else {
			Bral_Util_Log::tour()->debug(get_class($this)." calcul_debut_nouveau - exit - false");
			return false;
		}
	}

	private function calcul_mort() {
		Bral_Util_Log::tour()->trace(get_class($this)." calcul_mort - enter -");
		$this->est_mort = ($this->hobbit->est_mort_hobbit == "oui");

		if ($this->est_mort) {
			Zend_Loader::loadClass('Lieu');
			Zend_Loader::loadClass('Bral_Util_De');
			$this->is_update_tour = true;

			// remise en vu
			$this->hobbit->est_mort_hobbit = "non";

			// perte des PX
			$this->hobbit->px_commun_hobbit = 0;
			$this->hobbit->px_perso_hobbit = floor($this->hobbit->px_perso_hobbit / 3);

			// balance de faim
			$this->hobbit->balance_faim_hobbit = 50;

			// points de vie
			$this->hobbit->pv_restant_hobbit = floor(($this->view->config->game->pv_base + $this->hobbit->vigueur_base_hobbit*$this->view->config->game->pv_max_coef) / 2);

			// recalcul de la position
			$lieuTable = new Lieu();
			$chuRowset = $lieuTable->findByType($this->view->config->game->lieu->type->ceachehu);
			$de = Bral_Util_De::get_de_specifique(0, count($chuRowset)-1);
			$lieu = $chuRowset[$de];

			$this->hobbit->x_hobbit = $lieu["x_lieu"];
			$this->hobbit->y_hobbit = $lieu["y_lieu"];
		}
		Bral_Util_Log::tour()->trace(get_class($this)." calcul_mort - exit -");
	}

	private function calculBMEquipement() {
		Bral_Util_Log::tour()->trace(get_class($this)." calculBMEquipement - enter -");
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("EquipementRune");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		
		// on va chercher l'équipement porté et les runes
		$tabEquipementPorte = null;
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementPorteRowset = $hobbitEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($hobbitEquipementTable);
		
		if (count($equipementPorteRowset) > 0) {
			
			Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement nb equipement porte:".count($equipementPorteRowset));
			
			$tabWhere = null;
			$equipementRuneTable = new EquipementRune();
			$equipements = null;
			
			$idEquipements = null;
			
			foreach ($equipementPorteRowset as $e) {
				$idEquipements[] = $e["id_equipement_hequipement"];
				
				/*$equipement = array(
						"id_equipement" => $e["id_equipement_hequipement"],
						"nom" => $e["nom_type_equipement"],
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_recette_equipement"],
						"id_type_emplacement" => $e["id_type_emplacement"],
						"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
						"nb_runes" => $e["nb_runes_hequipement"],
						"id_fk_recette_equipement" => $e["id_fk_recette_hequipement"],
						"armure" => $e["armure_recette_equipement"],
						"force" => $e["force_recette_equipement"],
						"agilite" => $e["agilite_recette_equipement"],
						"vigueur" => $e["vigueur_recette_equipement"],
						"sagesse" => $e["sagesse_recette_equipement"],
						"vue" => $e["vue_recette_equipement"],
						"bm_attaque" => $e["bm_attaque_recette_equipement"],
						"bm_degat" => $e["bm_degat_recette_equipement"],
						"bm_defense" => $e["bm_defense_recette_equipement"],
						"poids" => $e["poids_recette_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"id_mot_runique" =>  $e["id_fk_mot_runique_hequipement"],
				);*/
				
				$this->hobbit->force_bm_hobbit = $this->hobbit->force_bm_hobbit + $e["force_recette_equipement"];
				$this->hobbit->agilite_bm_hobbit = $this->hobbit->agilite_bm_hobbit + $e["agilite_recette_equipement"];
				$this->hobbit->vigueur_bm_hobbit = $this->hobbit->vigueur_bm_hobbit + $e["vigueur_recette_equipement"];
				$this->hobbit->sagesse_bm_hobbit = $this->hobbit->sagesse_bm_hobbit + $e["sagesse_recette_equipement"];
				$this->hobbit->vue_bm_hobbit = $this->hobbit->vue_bm_hobbit + $e["vue_recette_equipement"];
				$this->hobbit->armure_equipement_hobbit = $this->hobbit->armure_equipement_hobbit + $e["armure_recette_equipement"];
				$this->hobbit->bm_attaque_hobbit = $this->hobbit->bm_attaque_hobbit + $e["bm_attaque_recette_equipement"];
				$this->hobbit->bm_degat_hobbit = $this->hobbit->bm_degat_hobbit + $e["bm_degat_recette_equipement"];
				$this->hobbit->bm_defense_hobbit = $this->hobbit->bm_defense_hobbit + $e["bm_defense_recette_equipement"];
			
				if ($e["nom_systeme_mot_runique"] == "mot_b") {
					$this->view->effetMotB = true;
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotB actif - avant : this->hobbit->sagesse_bm_hobbit".$this->hobbit->sagesse_bm_hobbit);
					$this->hobbit->sagesse_bm_hobbit = $this->hobbit->sagesse_bm_hobbit + (2 * $e["niveau_recette_equipement"]);
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotB actif - apres : this->hobbit->sagesse_bm_hobbit".$this->hobbit->sagesse_bm_hobbit. " ajout de :".(2 * $e["niveau_recette_equipement"]));
				}
				
				if ($e["nom_systeme_mot_runique"] == "mot_k") {
					$this->view->effetMotK = true;
					if ($e["bm_attaque_recette_equipement"] > 0) { // positif
						$val = $e["bm_attaque_recette_equipement"];
					} else { // negatif
						$val = abs($e["bm_attaque_recette_equipement"]) / 2;
					}
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotK actif - avant : val a ajouer au bm_attaque_hobbit=".$val);
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotK actif - avant : this->hobbit->bm_attaque_hobbit".$this->hobbit->bm_attaque_hobbit);
					$this->hobbit->bm_attaque_hobbit = $this->hobbit->bm_attaque_hobbit + $val;
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotK actif - apres : this->hobbit->bm_attaque_hobbit".$this->hobbit->bm_attaque_hobbit);
				}
				
				if ($e["nom_systeme_mot_runique"] == "mot_m") {
					$this->view->effetMotM = true;
					if ($e["bm_defense_recette_equipement"] > 0) { // positif
						$val = $e["bm_defense_recette_equipement"];
					} else { // negatif
						$val = abs($e["bm_defense_recette_equipement"]) / 2;
					}
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotM actif - avant : val a ajouer au bm_defense_hobbit=".$val);
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotM actif - avant : this->hobbit->bm_defense_hobbit".$this->hobbit->bm_defense_hobbit);
					$this->hobbit->bm_defense_hobbit = $this->hobbit->bm_defense_hobbit + $val;
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotM actif - apres : this->hobbit->bm_defense_hobbit".$this->hobbit->bm_defense_hobbit);
				}
				
				if ($e["nom_systeme_mot_runique"] == "mot_n") {
					$this->view->effetMotN = true;
					$this->view->ciblesEffetN = Bral_Util_Attaque::calculDegatCase($this->view->config, $this->hobbit, 2 * $e["niveau_recette_equipement"]);
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotN actif - logs presents dans bral_attaque.log");
				}
				
				if ($e["nom_systeme_mot_runique"] == "mot_o") {
					$this->view->effetMotO = true;
					$this->view->ciblesEffetO = Bral_Util_Attaque::calculSoinCase($this->view->config, $this->hobbit, 2 * $e["niveau_recette_equipement"]);
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotO actif - logs presents dans bral_attaque.log");
				}
				
				if ($e["nom_systeme_mot_runique"] == "mot_u") {
					$this->view->effetMotU = true;
					$ciblesEffetU = Bral_Util_Attaque::calculDegatCase($this->view->config, $this->hobbit, $e["niveau_recette_equipement"] / 2);
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotU actif - avant recuperation pv this->hobbit->pv_restant_hobbit=".$this->hobbit->pv_restant_hobbit);
					if ($ciblesEffetU != null && $ciblesEffetU["n_cible"] != null) {
						$this->hobbit->pv_restant_hobbit = $this->hobbit->pv_restant_hobbit + $ciblesEffetU["n_cible"];
					}
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotU actif - apres recuperation pv this->hobbit->pv_restant_hobbit=".$this->hobbit->pv_restant_hobbit);
					$this->view->ciblesEffetU = $ciblesEffetU;
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotU actif - logs presents dans bral_attaque.log");
				}
				
				if ($e["nom_systeme_mot_runique"] == "mot_v") {
					$this->view->effetMotV = true;
					$this->hobbit->vue_bm_hobbit = $this->hobbit->vue_bm_hobbit + 2;
					Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - effetMotV actif - this->hobbit->vue_bm_hobbit=".$this->hobbit->vue_bm_hobbit);
				}
				
			}
			
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);
			
			unset($equipementRuneTable);
			
			if (count($equipementRunes) > 0) {
				foreach($equipementRunes as $r) {
					if ($r["nom_type_rune"] == "KR") {
						// KR Bonus de AGI = Niveau d'AGI/3 arrondi inférieur
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune KR active - avant this->hobbit->agilite_bm_hobbit=".$this->hobbit->agilite_bm_hobbit);
						$this->hobbit->agilite_bm_hobbit = $this->hobbit->agilite_bm_hobbit + floor($this->hobbit->agilite_base_hobbit / 3);
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune KR active - apres this->hobbit->agilite_bm_hobbit=".$this->hobbit->agilite_bm_hobbit);
					} else if ($r["nom_type_rune"] == "ZE") {
						// ZE Bonus de FOR = Niveau de FOR/3 arrondi inférieur
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune ZE active - avant this->hobbit->force_bm_hobbit=".$this->hobbit->force_bm_hobbit);
						$this->hobbit->force_bm_hobbit = $this->hobbit->force_bm_hobbit + floor($this->hobbit->force_base_hobbit / 3); 
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune Ze active - apres this->hobbit->force_bm_hobbit=".$this->hobbit->force_bm_hobbit);
					} else if ($r["nom_type_rune"] == "IL") {
						// IL Réduit le tour de jeu de 10 minutes
						//$this->hobbit->duree_courant_tour_hobbit = Bral_Util_ConvertDate::get_time_remove_time_to_time($this->hobbit->duree_courant_tour_hobbit, "00:10:00");
						// effectué dans la compétence s'équiper, pour mettre à jour le temps du prochain tour.
					} else if ($r["nom_type_rune"] == "MU") {
						// MU PV + niveau du Hobbit/10 arrondi inférieur
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune MU active - avant this->hobbit->pv_max_bm_hobbit=".$this->hobbit->pv_max_bm_hobbit);
						$this->hobbit->pv_max_bm_hobbit = $this->hobbit->pv_max_bm_hobbit + floor($this->hobbit->niveau_hobbit / 10);
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune MU active - apres this->hobbit->pv_max_bm_hobbit=".$this->hobbit->pv_max_bm_hobbit);
					} else if ($r["nom_type_rune"] == "RE") {
						// RE ARM NAT + Niveau du Hobbit/10 arrondi inférieur
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune RE active - apres this->hobbit->armure_naturelle_hobbit=".$this->hobbit->armure_naturelle_hobbit);
						$this->hobbit->armure_naturelle_hobbit = $this->hobbit->armure_naturelle_hobbit + floor($this->hobbit->niveau_hobbit / 10);
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune RE active - apres this->hobbit->armure_naturelle_hobbit=".$this->hobbit->armure_naturelle_hobbit);
					} else if ($r["nom_type_rune"] == "OG") {
						// OG Bonus de VIG = Niveau de VIG/3 arrondi inférieur
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune OG active - avant this->hobbit->vigueur_bm_hobbit=".$this->hobbit->vigueur_bm_hobbit);
						$this->hobbit->vigueur_bm_hobbit = $this->hobbit->vigueur_bm_hobbit + floor($this->hobbit->vigueur_base_hobbit / 3); 
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune OG active - avant this->hobbit->vigueur_bm_hobbit=".$this->hobbit->vigueur_bm_hobbit);
					} else if ($r["nom_type_rune"] == "OX") {
						// OX Poids maximum porté augmenté de Niveau du Hobbit/10 arrondi inférieur
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune OX active - avant this->hobbit->poids_transportable_hobbit=".$this->hobbit->poids_transportable_hobbit);
						$this->hobbit->poids_transportable_hobbit = $this->hobbit->poids_transportable_hobbit + floor($this->hobbit->niveau_hobbit / 10);
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune OX active - avant this->hobbit->poids_transportable_hobbit=".$this->hobbit->poids_transportable_hobbit);
					} else if ($r["nom_type_rune"] == "UP") {
						// UP Bonus de SAG = Niveau de SAG/3 arrondi inférieur
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune UP active - avant this->hobbit->sagesse_bm_hobbit=".$this->hobbit->sagesse_bm_hobbit);
						$this->hobbit->sagesse_bm_hobbit = $this->hobbit->sagesse_bm_hobbit + floor($this->hobbit->sagesse_base_hobbit / 3); 
						Bral_Util_Log::tour()->debug(get_class($this)." calculBMEquipement - rune UP active - avant this->hobbit->sagesse_bm_hobbit=".$this->hobbit->sagesse_bm_hobbit);
					}
				}
				unset($equipementRunes);
			}
			unset($equipementPorteRowset);
		}
		Bral_Util_Log::tour()->trace(get_class($this)." calculBMEquipement - exit -");
	}
	
	private function calculBMPotion() {
		Bral_Util_Log::tour()->trace(get_class($this)." calculBMPotion - enter -");
		Zend_Loader::loadClass("Bral_Util_EffetsPotion");
		$effetsPotions = Bral_Util_EffetsPotion::calculPotionHobbit($this->hobbit);
		
		if (count($effetsPotions) > 0) {
			$this->view->effetPotion = true;
			$this->view->effetPotionPotions = $effetsPotions; 
		}
		Bral_Util_Log::tour()->trace(get_class($this)." calculBMPotion - exit -");
	}
	
	private function calculInfoTour() {
		Bral_Util_Log::tour()->trace(get_class($this)." calculInfoTour - enter -");
		$info = "";
		if ($this->view->user->tour_position_hobbit == $this->view->config->game->tour->position_latence) {
			$info = "Fin latence &agrave; ".$this->hobbit->date_fin_latence_hobbit;
		} else if ($this->view->user->tour_position_hobbit == $this->view->config->game->tour->position_milieu) {
			$info = "Cumul &agrave; ".$this->hobbit->date_debut_cumul_hobbit;
		}
		$this->view->user->info_prochaine_position = $info;
		Bral_Util_Log::tour()->trace(get_class($this)." calculInfoTour - exit -");
	}
	
	private function calculDLA() {
		Bral_Util_Log::tour()->trace(get_class($this)." calculDLA - enter -");
		$this->hobbit->duree_courant_tour_hobbit = $this->hobbit->duree_prochain_tour_hobbit;
		// Ajouter la prise en compte du niveau de sagesse
		//Durée DLA (en minutes) = 1440 – 10 * Niveau SAG

		Bral_Util_Log::tour()->debug(get_class($this)." this->hobbit->duree_prochain_tour_hobbit=".$this->hobbit->duree_prochain_tour_hobbit);			
		
		$minutesCourant = Bral_Util_ConvertDate::getMinuteFromHeure($this->hobbit->duree_prochain_tour_hobbit);// - 10 * $this->hobbit->sagesse_base_hobbit;
		Bral_Util_Log::tour()->debug(get_class($this)." minutesCourant=".$minutesCourant);			
		// Ajouter les blessures : pour chaque PV : Arrondi inférieur [durée DLA (+BM) / (4*max PV du Hobbit)]. 
		
		$minutesAAjouter = 0;
		if ($this->hobbit->pv_restant_hobbit > $this->hobbit->pv_max_hobbit) {
			$this->hobbit->pv_restant_hobbit = $this->hobbit->pv_max_hobbit;
		}
		if ($this->hobbit->pv_max_hobbit - $this->hobbit->pv_restant_hobbit > 0) {
			$minutesAAjouter = floor($minutesCourant / (4 * $this->hobbit->pv_max_hobbit)) * ($this->hobbit->pv_max_hobbit - $this->hobbit->pv_restant_hobbit);
		}
		
		Bral_Util_Log::tour()->debug(get_class($this)." minutesAAjouter=".$minutesAAjouter);
		
		$this->hobbit->duree_courant_tour_hobbit = Bral_Util_ConvertDate::getHeureFromMinute($minutesCourant + $minutesAAjouter);
		Bral_Util_Log::tour()->debug(get_class($this)." this->hobbit->duree_courant_tour_hobbit=".$this->hobbit->duree_courant_tour_hobbit);			
		
		$minutesProchain = Bral_Util_ConvertDate::getMinuteFromHeure($this->view->config->game->tour->duree_base);
		Bral_Util_Log::tour()->debug(get_class($this)." minutesProchain base=".$minutesProchain);	
		$minutesProchain = $minutesProchain - (10 * $this->hobbit->sagesse_base_hobbit);
		Bral_Util_Log::tour()->debug(get_class($this)." minutesProchain en comptant la sag=".$minutesProchain);	
		
		$this->hobbit->duree_prochain_tour_hobbit =  Bral_Util_ConvertDate::getHeureFromMinute($minutesProchain); // TODO Rajouter les BM
		Bral_Util_Log::tour()->debug(get_class($this)." this->hobbit->duree_prochain_tour_hobbit=".$this->hobbit->duree_prochain_tour_hobbit);			
		
		$this->hobbit->date_debut_tour_hobbit = $this->hobbit->date_fin_tour_hobbit;
		$this->hobbit->date_fin_tour_hobbit = Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_fin_tour_hobbit, $this->hobbit->duree_courant_tour_hobbit);
		
		$time_latence = Bral_Util_ConvertDate::get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_latence);
		$time_cumul = Bral_Util_ConvertDate::get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_cumul);

		$this->hobbit->date_fin_latence_hobbit =  Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_latence);
		$this->hobbit->date_debut_cumul_hobbit =  Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_cumul);
		
		Bral_Util_Log::tour()->debug(get_class($this)." this->hobbit->date_fin_tour_hobbit=".$this->hobbit->date_fin_tour_hobbit);
		Bral_Util_Log::tour()->trace(get_class($this)." calculDLA - exit -");
	}
	
	private function calculPv() {
		Bral_Util_Log::tour()->trace(get_class($this)." calculPv - enter -");
		if ($this->hobbit->pv_restant_hobbit < $this->hobbit->pv_max_hobbit) {
			for ($i=1; $i <= $this->hobbit->regeneration_hobbit; $i++) {
				$this->view->jetRegeneration = $this->view->jetRegeneration + Bral_Util_De::get_1d6();
			}	
			if ($this->view->jetRegeneration < 0) { // pas de regénération négative (même si le malus est important)
				$this->view->jetRegeneration = 0;
			}
			$this->hobbit->pv_restant_hobbit = $this->hobbit->pv_restant_hobbit + $this->view->jetRegeneration;
			Bral_Util_Log::tour()->trace(get_class($this)." activer - jet Regeneration=".$this->view->jetRegeneration);
			if ($this->hobbit->pv_restant_hobbit > $this->hobbit->pv_max_hobbit) {
				$this->view->jetRegeneration = $this->hobbit->pv_max_hobbit - $this->hobbit->pv_restant_hobbit;
				$this->hobbit->pv_restant_hobbit = $this->hobbit->pv_max_hobbit;
			}
			Bral_Util_Log::tour()->trace(get_class($this)." activer - jet Regeneration ajuste=".$this->view->jetRegeneration);
			Bral_Util_Log::tour()->trace(get_class($this)." activer - pv_restant_hobbit=".$this->hobbit->pv_restant_hobbit);
		}
		
		if ($this->hobbit->pv_restant_hobbit > $this->hobbit->pv_max_hobbit) {
			$this->hobbit->pv_restant_hobbit = $this->hobbit->pv_max_hobbit;
		}
		Bral_Util_Log::tour()->trace(get_class($this)." calculPv - exit -");
	}
	
	private function updateDb() {
		Bral_Util_Log::tour()->trace(get_class($this)." updateDb - enter -");
		
		// Mise a jour du joueur dans la base de donnees
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->hobbit->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$this->view->user->x_hobbit = $this->hobbit->x_hobbit;
		$this->view->user->y_hobbit  = $this->hobbit->y_hobbit;
		$this->view->user->date_debut_tour_hobbit = $this->hobbit->date_debut_tour_hobbit;
		$this->view->user->date_fin_tour_hobbit = $this->hobbit->date_fin_tour_hobbit;
		$this->view->user->duree_courant_tour_hobbit = $this->hobbit->duree_courant_tour_hobbit;
		$this->view->user->duree_prochain_tour_hobbit = $this->hobbit->duree_prochain_tour_hobbit;
		$this->view->user->tour_position_hobbit = $this->hobbit->tour_position_hobbit;
		$this->view->user->pa_hobbit = $this->hobbit->pa_hobbit;
		$this->view->user->armure_naturelle_hobbit = $this->hobbit->armure_naturelle_hobbit;
		$this->view->user->est_mort_hobbit = $this->hobbit->est_mort_hobbit;
		$this->view->user->px_commun_hobbit = $this->hobbit->px_commun_hobbit;
		$this->view->user->px_perso_hobbit = $this->hobbit->px_perso_hobbit;
		$this->view->user->pv_max_hobbit = $this->hobbit->pv_max_hobbit;
		$this->view->user->pv_restant_hobbit = $this->hobbit->pv_restant_hobbit;
		$this->view->user->pv_max_bm_hobbit = $this->hobbit->pv_max_bm_hobbit;
		$this->view->user->balance_faim_hobbit = $this->hobbit->balance_faim_hobbit;
		
		$this->view->user->force_bm_hobbit = $this->hobbit->force_bm_hobbit;
		$this->view->user->agilite_bm_hobbit = $this->hobbit->agilite_bm_hobbit;
		$this->view->user->vigueur_bm_hobbit = $this->hobbit->vigueur_bm_hobbit;
		$this->view->user->sagesse_bm_hobbit = $this->hobbit->sagesse_bm_hobbit;
		$this->view->user->vue_bm_hobbit = $this->hobbit->vue_bm_hobbit;
		$this->view->user->poids_transportable_hobbit = $this->hobbit->poids_transportable_hobbit;
		$this->view->user->poids_transporte_hobbit = $this->hobbit->poids_transporte_hobbit;
		
		$this->view->user->bm_attaque_hobbit = $this->hobbit->bm_attaque_hobbit;
		$this->view->user->bm_degat_hobbit = $this->hobbit->bm_degat_hobbit;
		$this->view->user->bm_defense_hobbit = $this->hobbit->bm_defense_hobbit;
		
		$this->view->user->regeneration_malus_hobbit = $this->hobbit->regeneration_malus_hobbit;
		
		$data = array(
			'x_hobbit' => $this->hobbit->x_hobbit,
			'y_hobbit'  => $this->hobbit->y_hobbit,
			'date_debut_tour_hobbit' => $this->hobbit->date_debut_tour_hobbit,
			'date_fin_tour_hobbit' => $this->hobbit->date_fin_tour_hobbit,
			'date_fin_latence_hobbit' => $this->hobbit->date_fin_latence_hobbit,
			'date_debut_cumul_hobbit' => $this->hobbit->date_debut_cumul_hobbit,
			'duree_courant_tour_hobbit' => $this->hobbit->duree_courant_tour_hobbit,
			'duree_prochain_tour_hobbit' => $this->hobbit->duree_prochain_tour_hobbit,
			'tour_position_hobbit' => $this->hobbit->tour_position_hobbit,
			'pa_hobbit' => $this->hobbit->pa_hobbit,
			'armure_naturelle_hobbit' => $this->hobbit->armure_naturelle_hobbit,
			'armure_equipement_hobbit' => $this->hobbit->armure_equipement_hobbit,
			'est_mort_hobbit' => $this->hobbit->est_mort_hobbit,
			'px_commun_hobbit' => $this->hobbit->px_commun_hobbit,
			'px_perso_hobbit' => $this->hobbit->px_perso_hobbit,
			'pv_max_hobbit' => $this->hobbit->pv_max_hobbit,
			'pv_restant_hobbit' => $this->hobbit->pv_restant_hobbit,
			'pv_max_bm_hobbit' => $this->hobbit->pv_max_bm_hobbit,
			'balance_faim_hobbit' => $this->hobbit->balance_faim_hobbit,
			'force_bm_hobbit' => $this->hobbit->force_bm_hobbit,
			'force_bbdf_hobbit' => $this->hobbit->force_bbdf_hobbit,
			'agilite_bm_hobbit' => $this->hobbit->agilite_bm_hobbit,
			'agilite_bbdf_hobbit' => $this->hobbit->agilite_bbdf_hobbit,
			'vigueur_bm_hobbit' => $this->hobbit->vigueur_bm_hobbit,
			'vigueur_bbdf_hobbit' => $this->hobbit->vigueur_bbdf_hobbit,
			'sagesse_bm_hobbit' => $this->hobbit->sagesse_bm_hobbit,
			'sagesse_bbdf_hobbit' => $this->hobbit->sagesse_bbdf_hobbit,
			'vue_bm_hobbit' => $this->hobbit->vue_bm_hobbit,
			'poids_transportable_hobbit' => $this->hobbit->poids_transportable_hobbit,
			'poids_transporte_hobbit' => $this->hobbit->poids_transporte_hobbit,
			'regeneration_hobbit' => $this->hobbit->regeneration_hobbit,
			'regeneration_malus_hobbit' => $this->hobbit->regeneration_malus_hobbit,
			'bm_attaque_hobbit' => $this->hobbit->bm_attaque_hobbit,
			'bm_degat_hobbit' => $this->hobbit->bm_degat_hobbit,
			'bm_defense_hobbit' => $this->hobbit->bm_defense_hobbit,
		);
		$where = "id_hobbit=".$this->hobbit->id_hobbit;
		$hobbitTable->update($data, $where);
		Bral_Util_Log::tour()->debug(get_class($this)." activer() - update hobbit ".$this->hobbit->id_hobbit." en base");
		Bral_Util_Log::tour()->trace(get_class($this)." updateDb - exit -");
	}
}

