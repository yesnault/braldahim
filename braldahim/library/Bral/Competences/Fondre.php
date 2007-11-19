<?php

class Bral_Competences_Fondre extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		// On regarde si le hobbit n'est pas dans une ville
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		/*if (count($villes) == 0) {
			$this->view->fondreOk = true;
		}*/
		
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
		if ($this->view->fondreOk == false) {
			throw new Zend_Exception(get_class($this)." Chasse interdite ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculFondre();
			
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculFondre() {
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
