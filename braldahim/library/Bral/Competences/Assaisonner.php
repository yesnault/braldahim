<?php


class Bral_Competences_Assaisonner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabLaban = null;
		foreach ($laban as $p) {
			$tabLaban = array(
				"nb_viande" => $p["quantite_viande_laban"],
				"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
			);
		}
		if (isset($tabLaban) && $tabLaban["nb_viande"] > 1) {
			$this->view->assaisonnerNbViandeOk = true;
		}
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
		
		// Verification assaisonner
		if ($this->view->assaisonnerNbViandeOk == false) {
			throw new Zend_Exception(get_class($this)." Assaisonnement interdit ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculAssaisonner();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 *  Transforme 2 unités de viande en 1D2 unité de viande préparée
	 */
	private function calculAssaisonner() {
		Zend_Loader::loadClass("Laban");
		
		$this->view->nbViandePreparee = Bral_Util_De::get_1d2();
		
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_laban' => -2,
			'quantite_viande_preparee_laban' => $this->view->nbViandePreparee,
		);
		$labanTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_vue", "box_laban", "box_evenements");
	}
}
