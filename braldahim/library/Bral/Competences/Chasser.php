<?php

class Bral_Competences_Chasser extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Ville");
		Zend_Loader::loadClass("Zone");
		
		$this->view->chasserOk = false;
		
		$this->preCalculPoids();
		if ($this->view->poidsPlaceDisponible !== true) {
			return;
		}
		
		// On regarde si le hobbit n'est pas dans une ville
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($villeTable);
		
		if (count($villes) == 0) {
			$this->view->chasserOk = true;
		}
		
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($zoneTable);
		$zone = $zones[0];
		unset($zones);

		$r = 0;
		/**
		 * Plaine : 60 %
		 * Forêt : 80 %
		 * Marais : 30 %
		 * Montagneux : 30 %
		 */
		switch($zone["nom_systeme_environnement"]) {
			case "marais":
				$r = 30;
				break;
			case "montagne":
				$r = 30;
				break;
			case "caverne":
				$r = 0;
				break;
			case "plaine" :
				$r = 60;
				break;
			case "foret" :
				$r = 80;
				break;
			default :
				throw new Exception("Chasser Environnement invalide:".$zone["nom_systeme_environnement"]. " x=".$x." y=".$y);
		}
		$this->view->tauxReussite = $r;
		
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

		// Verification chasse
		if ($this->view->chasserOk == false) {
			throw new Zend_Exception(get_class($this)." Chasse interdite ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->view->jetChasse = Bral_Util_De::get_1d100();
			if ((int)$this->view->jetChasse <= (int)$this->view->tauxReussite) {
				$this->view->jetChasseOk = true;
				$this->calculChasse();
			}
		}
		
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 * La quantité de viande et de peau trouvée est fonction du niveau d'AGILITE du chasseur.
	 * de 0 à 4 : 1D3 + BM AGI/2 unité de viande + 1D3 + BM AGI/2 unité de peau
	 * de 5 à 9 : 2D3 + BM AGI/2 unité de viande + 2D3 + BM AGI/2 unité de peau
	 * de 10 à 14 :3D3 + BM AGI/2 unité de viande + 3D3 + BM AGI/2 unité de peau
	 * de 15 à 19 : 4D3 + BM AGI/2 unité de viande + 4D3 + BM AGI/2 unité de peau
	 */
	private function calculChasse() {
		$this->view->nbViande = 0;
		$this->view->nbPeau = 0;
		
		$nb = floor($this->view->user->agilite_base_hobbit / 5) + 1;
		$this->view->nbViande = Bral_Util_De::getLanceDeSpecifique($nb, 1, 3);
		$this->view->nbViande  = $this->view->nbViande  + ($this->view->user->agilite_bm_hobbit + $this->view->user->agilite_bbdf_hobbit) / 2 ;
		$this->view->nbViande  = intval($this->view->nbViande);
		if ($this->view->nbViande < 0) {
			$this->view->nbViande  = 0;
		}
		
		$nb = floor($this->view->user->agilite_base_hobbit / 5) + 1;
		$this->view->nbPeau = Bral_Util_De::getLanceDeSpecifique($nb, 1, 3);
		
		$this->view->nbPeau  = $this->view->nbPeau  + ($this->view->user->agilite_bm_hobbit + $this->view->user->agilite_bbdf_hobbit) / 2 ;
		$this->view->nbPeau  = intval($this->view->nbPeau);
		if ($this->view->nbPeau < 0) {
			$this->view->nbPeau  = 0;
		}
		
		$this->controlePoids();
		
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_laban' => $this->view->nbViande,
			'quantite_peau_laban' => $this->view->nbPeau,
		);

		$labanTable->insertOrUpdate($data);
		unset($labanTable);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_laban", "box_evenements");
	}
	
	public function calculPx() {
		$this->view->nb_px_commun = 0;
		$this->view->calcul_px_generique = true;
		if ($this->view->okJet1 === true) {
			if ($this->view->jetChasseOk === true) {
				$this->view->nb_px_perso = $this->competence["px_gain"] + 1;
			} else {
				$this->view->nb_px_perso = $this->competence["px_gain"];
			}
		} else {
			$this->view->nb_px_perso = 0;
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
	
	private function preCalculPoids() {
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		
		// on regarde le poids de la peau, plus légère que la viande
		$nbElementPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PEAU);
		
		if ($nbElementPossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
	}
	
	private function controlePoids() {
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		while ($this->view->nbViande * Bral_Util_Poids::POIDS_VIANDE + $this->view->nbPeau * Bral_Util_Poids::POIDS_PEAU > $poidsRestant) {
			$this->view->nbViande = $this->view->nbViande - 1;
			$this->view->nbPeau = $this->view->nbPeau - 1;
			if ($this->view->nbPeau <= 0 && $this->view->nbViande <= 0) {
				break;
			}
		}
		if ($this->view->nbPeau <= 0) {
			$this->view->nbPeau = 0;
		}
		
		if ($this->view->nbViande <= 0) {
			$this->view->nbViande = 0;
		}
		
		if ($this->view->nbPeau <= 0 && $this->view->nbViande <= 0) {
			$this->view->nbPeau = 1;
		}
	}
}
