<?php

class Bral_Box_Tour {

	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id);
		$this->hobbit = $hobbitRowset->current();
	}

	function getNomInterne() {
		return "box_tour";
	}

	function render() {
		switch($this->hobbit->tour_position_hobbit) {
			case 1:
				$this->view->nom_tour = "Latence";
				break;
			case 2:
				$this->view->nom_tour = "Milieu";
				break;
			case 3:
				$this->view->nom_tour = "Cumul";
				break;
		}
		return $this->view->render("interface/tour.phtml");
	}

	public function activer() {
		
		$this->is_update_tour = false;
		$this->is_nouveau_tour = false;
		
		if ($this->view->user->activation === false) {
			return false;
		}

		$this->calcul_debut_nouveau();

		// Calcul de la nouvelle date de fin
		$convert_date = new Bral_Util_ConvertDate();
		$date_courante = date("Y-m-d H:i:s");

		//if ($this->hobbit->date_fin_tour_hobbit > date("Y-m-d H:i:s")) { // mort
		// En cas de mort : la date de fin de tour doit être positionnée à la mort 
		if ($this->is_nouveau_tour) {
			$this->hobbit->duree_courant_tour_hobbit = $this->hobbit->duree_prochain_tour_hobbit;
			$this->hobbit->date_debut_tour_hobbit = $this->hobbit->date_fin_tour_hobbit;
			$this->hobbit->date_fin_tour_hobbit = $convert_date->get_date_add_time_to_date($this->hobbit->date_fin_tour_hobbit, $this->hobbit->duree_courant_tour_hobbit);
			$this->is_update_tour = true;
		}
		
		/* Si des DLA ont ete manquees, on prend comme date de debut la date courante
		 * et la date de fin, la date courante + 6 heures, le joueur se trouve
		 * directement en position de cumul
		 */

		$time_latence = $convert_date->get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_latence);
		$time_cumul = $convert_date->get_divise_time_to_time($this->hobbit->duree_courant_tour_hobbit, $this->view->config->game->tour->diviseur_cumul);
		
		$date_fin_latence =  $convert_date->get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_latence);
		$date_debut_cumul =  $convert_date->get_date_add_time_to_date($this->hobbit->date_debut_tour_hobbit, $time_cumul);
		
//		echo " time_latence=".$time_latence;
//		echo " time_cumul=".$time_cumul;
//		echo " date_fin_latence=".$date_fin_latence;
//		echo " date_debut_cumul".$date_debut_cumul;
//		echo " date_fin".$this->date_fin;
//		echo " date_courante=".$date_courante;
		
		$is_tour_manque = false;
		// Mise a jour du nombre de PA + position tour
		if ($date_courante > $this->hobbit->date_fin_tour_hobbit) { // Perte d'un tour
			$this->hobbit->date_fin_tour_hobbit = $convert_date->get_date_add_time_to_date($date_courante, "6:0:0");
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_cumul;
			$this->hobbit->pa_hobbit = $this->view->config->game->pa_max;
			$is_tour_manque = true;
			$this->is_update_tour = true;
		} elseif(($date_courante < $date_fin_latence) // Latence
				&& $this->is_nouveau_tour) {
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_latence;
			$this->hobbit->pa_hobbit = 0;
			$this->is_update_tour = true;
		} elseif(($date_courante >= $date_fin_latence && $date_courante < $date_debut_cumul) // Milieu
				&& ( (!$this->is_nouveau_tour && ($this->hobbit->tour_position_hobbit != $this->view->config->game->tour->position_milieu)) 
					|| ($this->is_nouveau_tour))) {
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_milieu;
			$this->hobbit->pa_hobbit = $this->view->config->game->pa_max;
			$this->is_update_tour = true;
		} elseif(($date_courante >= $date_debut_cumul && $date_courante < $this->hobbit->date_fin_tour_hobbit)  // Cumul
				&& ( (!$this->is_nouveau_tour && ($this->hobbit->tour_position_hobbit != $this->view->config->game->tour->position_cumul)) 
					|| ($this->is_nouveau_tour))) {
			// Si le joueur a déjà eu des PA
			if ($this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_milieu) {
				$this->hobbit->pa_hobbit = $this->hobbit->pa_hobbit + $this->view->config->game->pa_max;
			} else { // S'il vient d'activer et qu'il n'a jamais eu de PA dans ce tour
				$this->hobbit->pa_hobbit = $this->view->config->game->pa_max_cumul;
			}
			$this->hobbit->tour_position_hobbit = $this->view->config->game->tour->position_cumul;
			$this->is_update_tour = true;
		}
		
		// Mise a jour en cas de mort
//		$this->calcul_mort();

		// Mise a jour en cas d'update
		if ($this->is_update_tour) {
			// Mise a jour de la duree du prochain tour
			$this->update_duree_prochain();
		}

		// Mise a jour de la balance faim
//		$this->joueur->diminuer_balance_faim ($this->view->config->game->tour_faim);

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
			// Mise a jour du jouer dans la base de donnees
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($this->hobbit->id);
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
				$this->hobbit->est_mort_hobbit = $this->hobbit->est_mort_hobbit;
				
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
			); 
			$where = "id=".$this->hobbit->id;
			$hobbitTable->update($data, $where);
		}
		
		$this->view->user->is_update_tour = $this->is_update_tour;
		$this->view->user->is_nouveau_tour = $this->is_nouveau_tour;
		$this->view->user->is_tour_manque = $is_tour_manque;
		
		if (($this->is_update_tour) || ($this->is_nouveau_tour)) {
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

//	private function calcul_mort() {
//		$this->est_mort = $this->joueur->is_mort();
//
//		if ($this->est_mort) {
//
//			// remise en vu
//			$this->joueur->set_est_vu(true);
//			$this->joueur->set_est_mort(false);
//
//			// perte des PX
//			$this->joueur->set_px_personnels(0);
//			$this->joueur->set_px_redistribuables(0);
//
//			// balance de faim, 
//			// si la balance est positive : 1 d� 5 en dessous de 1
//			// si la balance est deja negative : 1 d� 5 en dessous du niveau actuel
//			$de = new de();
//
//			if ($this->joueur->get_balance_faim() > 0)
//				$this->joueur->set_balance_faim(0 - ($de->get_1d5()));
//			else
//				$this->joueur->diminuer_balance_faim($de->get_1d5());
//
//			// points de vie : pv totaux / 3, 4 ou 5
//			$pv = intval($this->joueur->get_pv_base() / ($de->get_de_specifique(3, 5)));
//			$this->joueur->set_pv_actif($pv);
//
//			// recalcul de la position
//			$this->joueur->set_x(0);
//			$this->joueur->set_y(0);
//		}
//
//	}

	/* Mise a jour de la duree du prochain tour
	 * Prise en compte des bonus/malus
	 * Prise en compte des blessures suivant les PV
	 * ... A definir
	 */
	public function update_duree_prochain() {
		//TODO
		$duree = $this->hobbit->duree_base_tour_hobbit;
		$this->hobbit->duree_prochain_tour_hobbit = $duree;
	}

}
?>