<?php

class Bral_Competences_Cuisiner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Ville");
		
		// On regarde si le hobbit n'est pas dans une ville
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		if (count($villes) == 0) {
			$this->view->cuisinerLieuOk = true;
		}
		
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabLaban = null;
		foreach ($laban as $p) {
			$tabLaban = array(
			"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
			"nb_ration" => $p["quantite_ration_laban"],
			);
		}
		if (isset($tabLaban) && $tabLaban["nb_viande_preparee"] > 0) {
			$this->view->cuisinerNbViandeOk = true;
		}
		
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

		// Verification cuisiner
		if ($this->view->cuisinerNbViandeOk == false || $this->view->cuisinerLieuOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdite ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculCuisiner();
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 * Transforme 1 unité de viande préparée en 1D2+1 rations (conservation illimitée) 
	 * 1 ration fait 1 repas complet. 
	 * Un repas complet fait +80% dans la balance de faim.
	 * Peut être utilisé partout sauf en ville
	 */	
	private function calculCuisiner() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Bral_Util_De");
		
		$this->view->nbRation = Bral_Util_De::get_1d2() + 1;
		
		$labanTable = new Laban();
		$data = array(
			'id_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_ration_laban' => $this->view->nbRation,
			'quantite_viande_preparee_laban' => -1,
		);
		$labanTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
