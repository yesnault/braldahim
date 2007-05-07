<?php

class Bral_Box_Tour {

	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}

	function getNomInterne() {
		return "box_tour";
	}

	function render() {
		return $this->view->render("interface/tour.phtml");
	}

	public function activer() {

		if ($this->view->user->activation === false) {
			return false;
		}

		/* on verifie d'abord que le tour est bien nouveau */
		if ($this->calcul_debut_nouveau() == false) {
			return false;
		}

		// Calcul de la nouvelle date de fin
		$convert_date = new Bral_Util_ConvertDate();
		$date_courante = date("Y-m-d H:i:s");

		if ($this->view->user->date_fin_tour_hobbit > date("Y-m-d H:i:s")) { // mort
			// date_fin_tour = duree_prochain_tour + date_courante
			$convert_date->get_date_add_time_to_date($date_courante, $this->view->user->duree_prochain_tour_hobbit);
		} else {
			// date_fin_tour = duree_prochain_tour + date_fin_tour
			$this->date_fin = $convert_date->get_date_add_time_to_date($this->view->user->date_fin_tour_hobbit, $this->view->user->duree_prochain_tour_hobbit);
		}

		/* Si des DLA ont ete manquees, on prend comme date de debut la date courante
		 * et la date de fin, la date courante + 6 heures
		 * Surement a modifier plus tard avec les bonus / malus
		 */
		if ($this->date_fin < $date_courante) {
			$this->date_fin = $convert_date->get_date_add_time_to_date($date_courante, "6:0:0");
		}

		// Mise a jour en cas de mort
//		$this->calcul_mort();

		// Mise a jour des dates
		$this->view->user->date_debut_tour_hobbit = $date_courante;
		$this->view->user->date_fin_tour_hobbit = $this->date_fin;

		// Mise a jour de la duree du prochain tour
		$this->update_duree_prochain();

		// Mise a jour du nombre de PA
		$this->view->user->pa_hobbit = $this->view->config->game->pa_max;

		// Mise a jour de la balance faim
//		$this->joueur->diminuer_balance_faim ($this->view->config->game->tour_faim);

		// Mise a jour de l'armure naturelle
		/* valeur entiere((Des de Force + Des de Vigueur)/5)=Armure naturelle
		exemple :
		niveau de VIG + niveau de FOR = 5 -> 1 ARM NAT
		niveau de VIG + niveau de FOR = 10 -> 2 ARM NAT
		niveau de VIG + niveau de FOR = 15 -> 3 ARM NAT
		*/

		$this->view->user->armure_naturelle_hobbit = intval(($this->view->user->force_base_hobbit + $this->view->user->vigueur_base_hobbit) / 5);

		// Mise a jour du jouer dans la base de donnees
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id);
		$hobbit = $hobbitRowset->current();

		$data = array( 
			'x_hobbit' => $this->view->user->x_hobbit,
			'y_hobbit'  => $this->view->user->y_hobbit,
			'date_debut_tour_hobbit' => $this->view->user->date_debut_tour_hobbit,
			'date_fin_tour_hobbit' => $this->view->user->date_fin_tour_hobbit,
			'pa_hobbit' => $this->view->user->pa_hobbit,
			'armure_naturelle_hobbit' => $this->view->user->armure_naturelle_hobbit,
			'duree_prochain_tour_hobbit' => $this->view->user->duree_prochain_tour_hobbit,
			'est_mort_hobbit' => $this->view->user->est_mort_hobbit,
		); 
		$where = "id=".$this->view->user->id;
		$hobbitTable->update($data, $where);

		return true;
	}
	/* Verification que c'est bien le debut d'un
	 * nouveau tour pour le joueur
	 * @return false si non
	 * @return true si oui
	 */
	public function calcul_debut_nouveau() {
		if ($this->view->user->date_fin_tour_hobbit < date("Y-m-d H:i:s") || $this->view->user->est_mort_hobbit == 'oui') {
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
		$duree = $this->view->user->duree_base_tour_hobbit;
		$this->view->user->duree_prochain_tour_hobbit = $duree;
	}

}
?>