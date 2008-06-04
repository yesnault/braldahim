<?php

class Bral_Competences_Ramasser extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Castar");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("ElementRune");
		
		$this->view->ramasserOk = false;
		

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

		// Verification depiauter
		if ($this->view->ramasserOk == false) {
			throw new Zend_Exception(get_class($this)." Ramasser interdit ");
		}
		
		$this->calculRamasser();
		$this->setEvenementQueSurOkJet1(false);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculRamasser() {
	
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_laban", "box_evenements");
	}
}
