<?php

class Bral_Box_Tour {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Util_Log");
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$this->hobbit = $hobbitRowset->current();
		//$this->view->user = $this->hobbit;

		$this->nomsTour = Zend_Registry::get('nomsTour');
		$this->view->user->nom_tour = $this->nomsTour[$this->view->user->tour_position_hobbit];
		$this->calculInfoTour();
	}

	function getNomInterne() {
		return "box_tour";
	}

	function render() {
		$this->view->user->nom_tour = $this->nomsTour[$this->view->user->tour_position_hobbit];
		return $this->view->render("interface/tour.phtml");
	}

	public function modificationTour() {

		$this->is_update_tour = false;
		$this->is_nouveau_tour = false;

		if ($this->view->user->activation === false) {
			return false;
		}

		$this->calcul_debut_nouveau();

		// Calcul de la nouvelle date de fin
		$date_courante = date("Y-m-d H:i:s");

		// En cas de mort : la date de fin de tour doit être positionnée à la mort
		if ($this->is_nouveau_tour) {
			$this->hobbit->duree_courant_tour_hobbit = $this->hobbit->duree_prochain_tour_hobbit;
			$this->hobbit->date_debut_tour_hobbit = $this->hobbit->date_fin_tour_hobbit;
			$this->hobbit->date_fin_tour_hobbit = Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_fin_tour_hobbit, $this->hobbit->duree_courant_tour_hobbit);
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_latence;
			$this->is_update_tour = true;
		}

		/* Si des DLA ont ete manquees, on prend comme date de debut la date courante
		 * et la date de fin, la date courante + 6 heures, le joueur se trouve
		 * directement en position de cumul
		 */

		$time_latence = Bral_Util_ConvertDate::get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_latence);
		$time_cumul = Bral_Util_ConvertDate::get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_cumul);

		$date_fin_latence =  Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_latence);
		$date_debut_cumul =  Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_cumul);

		Bral_Util_Log::tech()->debug(get_class($this)." time_latence=".$time_latence);
		Bral_Util_Log::tech()->debug(get_class($this)." time_cumul=".$time_cumul);
		Bral_Util_Log::tech()->debug(get_class($this)."	date_fin_latence=".$date_fin_latence);
		Bral_Util_Log::tech()->debug(get_class($this)."	date_debut_cumul".$date_debut_cumul);
		Bral_Util_Log::tech()->debug(get_class($this)."	date_courante=".$date_courante);
		Bral_Util_Log::tech()->debug(get_class($this)."	date fin tour=".$this->hobbit->date_fin_tour_hobbit);

		$this->is_tour_manque = false;
		// Mise a jour du nombre de PA + position tour
		if ($date_courante > $this->hobbit->date_fin_tour_hobbit) { // Perte d'un tour
			Bral_Util_Log::tech()->debug(get_class($this)." Perte d'un tour");
			$this->hobbit->date_fin_tour_hobbit = Bral_Util_ConvertDate::get_date_add_time_to_date($date_courante, $this->view->config->game->tour->duree_tour_manque);
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_cumul;
			$this->hobbit->pa_hobbit = $this->view->config->game->pa_max_cumul;
			$this->is_tour_manque  = true;
			$this->is_update_tour = true;
		} elseif(($date_courante < $date_fin_latence) // Latence
		&& $this->is_nouveau_tour) {
			Bral_Util_Log::tech()->debug(get_class($this)." Latence Tour");
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_latence;
			$this->hobbit->pa_hobbit = 0;
			$this->is_update_tour = true;
		} elseif(($date_courante >= $date_fin_latence && $date_courante < $date_debut_cumul) // Milieu
		&& ( (!$this->is_nouveau_tour && ($this->hobbit->tour_position_hobbit != $this->view->config->game->tour->position_milieu))
		|| ($this->is_nouveau_tour))) {
			Bral_Util_Log::tech()->debug(get_class($this)." Milieu Tour");
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_milieu;
			$this->hobbit->pa_hobbit = $this->view->config->game->pa_max;
			$this->is_update_tour = true;
		} elseif(($date_courante >= $date_debut_cumul && $date_courante < $this->hobbit->date_fin_tour_hobbit)  // Cumul
		&& ( (!$this->is_nouveau_tour && ($this->hobbit->tour_position_hobbit != $this->view->config->game->tour->position_cumul))
		|| ($this->is_nouveau_tour))) {
			Bral_Util_Log::tech()->debug(get_class($this)." Cumul tour");
			// Si le joueur a déjà eu des PA
			if ($this->hobbit->tour_position_hobbit == $this->view->config->game->tour->position_milieu) {
				Bral_Util_Log::tech()->debug(get_class($this)." Le joueur a deja eu des PA");
				$this->hobbit->pa_hobbit = $this->hobbit->pa_hobbit + $this->view->config->game->pa_max;
			} else { // S'il vient d'activer et qu'il n'a jamais eu de PA dans ce tour
				Bral_Util_Log::tech()->debug(get_class($this)." Le joueur n'a pas encore eu de PA");
				$this->hobbit->pa_hobbit = $this->view->config->game->pa_max_cumul;
			}
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_cumul;
			$this->is_update_tour = true;
		}
		
		if (($this->is_update_tour) || ($this->is_nouveau_tour) || ($this->hobbit->est_mort_hobbit == "oui")) {
			Bral_Util_Log::tech()->debug(get_class($this)." modificationTour true");
			return true;
		} else {
			Bral_Util_Log::tech()->debug(get_class($this)." modificationTour false");
			return false;
		}
	}

	public function activer() {
		$this->modificationTour();
		// Mise a jour de la balance faim
		//		$this->joueur->diminuer_balance_faim ($this->view->config->game->tour_faim);

		// Mise a jour en cas de mort
		$this->calcul_mort();

		// Mise a jour en cas d'update
		if ($this->is_update_tour) {
			// Mise a jour de la duree du prochain tour
			$this->update_duree_prochain();
		}

		// Mise a jour de l'armure naturelle
		/* valeur entiere((Des de Force + Des de Vigueur)/5)=Armure naturelle
		exemple :
		niveau de VIG + niveau de FOR = 5 -> 1 ARM NAT
		niveau de VIG + niveau de FOR = 10 -> 2 ARM NAT
		niveau de VIG + niveau de FOR = 15 -> 3 ARM NAT
		*/

		if ($this->is_nouveau_tour) {
			$this->hobbit->armure_naturelle_hobbit = intval(($this->hobbit->force_base_hobbit + $this->hobbit->vigueur_base_hobbit) / 5);
		}

		if ($this->is_update_tour) {
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
			$this->view->user->pv_restant_hobbit = $this->hobbit->pv_restant_hobbit;
			$this->view->user->balance_faim_hobbit = $this->hobbit->balance_faim_hobbit;

			$data = array(
			'x_hobbit' => $this->hobbit->x_hobbit,
			'y_hobbit'  => $this->hobbit->y_hobbit,
			'date_debut_tour_hobbit' => $this->hobbit->date_debut_tour_hobbit,
			'date_fin_tour_hobbit' => $this->hobbit->date_fin_tour_hobbit,
			'duree_courant_tour_hobbit' => $this->hobbit->duree_courant_tour_hobbit,
			'duree_prochain_tour_hobbit' => $this->hobbit->duree_prochain_tour_hobbit,
			'tour_position_hobbit' => $this->hobbit->tour_position_hobbit,
			'pa_hobbit' => $this->hobbit->pa_hobbit,
			'armure_naturelle_hobbit' => $this->hobbit->armure_naturelle_hobbit,
			'est_mort_hobbit' => $this->hobbit->est_mort_hobbit,
			'px_commun_hobbit' => $this->hobbit->px_commun_hobbit,
			'px_perso_hobbit' => $this->hobbit->px_perso_hobbit,
			'pv_restant_hobbit' => $this->hobbit->pv_restant_hobbit,
			'balance_faim_hobbit' => $this->hobbit->balance_faim_hobbit,
			);
			$where = "id_hobbit=".$this->hobbit->id_hobbit;
			$hobbitTable->update($data, $where);
		}

		$this->view->is_update_tour = $this->is_update_tour;
		$this->view->is_nouveau_tour = $this->is_nouveau_tour;
		$this->view->is_tour_manque = $this->is_tour_manque;
		$this->view->is_mort = $this->est_mort;

		if (($this->is_update_tour) || ($this->is_nouveau_tour)) {
			$this->calculInfoTour();
			return true;
		} else {
			return false;
		}
	}
	/* Verification que c'est bien le debut d'un
	 * nouveau tour pour le joueur
	 * @return false si non
	 * @return true si oui
	 */
	private function calcul_debut_nouveau() {
		if ($this->hobbit->date_fin_tour_hobbit < date("Y-m-d H:i:s") || $this->hobbit->est_mort_hobbit == 'oui') {
			$this->is_nouveau_tour = true;
			return true;
		} else {
			$this->is_nouveau_tour = false;
			return false;
		}
	}

	private function calcul_mort() {
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
	}

	/* Mise a jour de la duree du prochain tour
	 * Prise en compte des bonus/malus
	 * Prise en compte des blessures suivant les PV
	 * ... A definir
	 */
	public function update_duree_prochain() {
		//TODO
		$duree = $this->hobbit->duree_base_tour_hobbit;
		$this->hobbit->duree_prochain_tour_hobbit = $duree;
		$this->hobbit->pv_restant_hobbit = $this->view->config->game->pv_base + $this->hobbit->vigueur_base_hobbit*$this->view->config->game->pv_max_coef;
	}

	private function calculInfoTour() {
		$info = "";
		if ($this->view->user->tour_position_hobbit == $this->view->config->game->tour->position_latence) {
			$time_latence = Bral_Util_ConvertDate::get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_latence);
			$date_fin_latence =  Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_latence);
			$info = "Fin latence &agrave; ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',$date_fin_latence);
		} else if ($this->view->user->tour_position_hobbit == $this->view->config->game->tour->position_milieu) {
			$time_cumul = Bral_Util_ConvertDate::get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_cumul);
			$date_debut_cumul =  Bral_Util_ConvertDate::get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_cumul);
			$info = "D&eacute;but cumul &agrave; ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',$date_debut_cumul);
		}
		$this->view->user->info_prochaine_position = $info;
	}
}

