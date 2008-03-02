<?php

class Bral_Competences_Chasser extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Ville");
		Zend_Loader::loadClass("Zone");
		
		// On regarde si le hobbit n'est pas dans une ville
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		if (count($villes) == 0) {
			$this->view->chasserOk = true;
		}
		
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$zone = $zones[0];

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
		Zend_Loader::loadClass("Bral_Util_De");
		Zend_Loader::loadClass('Hobbit');

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
			
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculChasse() {
	/*
	 * La quantité de viande et de peau trouvée est fonction du niveau d'AGILITE du chasseur.
	 * de 0 à 4 : 1D3 unité de viande + 1D2 unité de peau
	 * de 5 à 9 : 1D3+1 unité de viande + 1D2+1 unité de peau
	 * de 10 à 14 :1D3+2 unité de viande + 1D2+2 unité de peau
	 * de 15 à 19 : 1D3+3 unité de viande + 1D2+3 unité de peau
	 */
		$this->view->nbViande = 0;
		$this->view->nbPeau = 0;
		$this->view->nbFourrure = 0;
		
		$n = Bral_Util_De::get_1d3();
		$this->view->nbViande = $n + floor($this->view->user->agilite_base_hobbit / 5);
		
		$n = Bral_Util_De::get_1d2();
		$this->view->nbPeau = $n + floor($this->view->user->agilite_base_hobbit / 5);
		
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_laban' => $this->view->nbViande,
			'quantite_peau_laban' => $this->view->nbPeau,
		);

		$labanTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
