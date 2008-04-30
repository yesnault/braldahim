<?php

class Bral_Competences_Deposer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Castar");
		Zend_Loader::loadClass("Laban");
		
		$this->view->deposerOk = true;
		
		$id_type_courant = $this->request->get("type_element");
		
		$typesElements[] = array("id_type_element" => 1, "selected" => $id_type_courant, "nom_element" => "Castars");
		$typesElements[] = array("id_type_element" => 2, "selected" => $id_type_courant, "nom_element" => "Equipements");
		$typesElements[] = array("id_type_element" => 3, "selected" => $id_type_courant, "nom_element" => "Minerais");
		$typesElements[] = array("id_type_element" => 4, "selected" => $id_type_courant, "nom_element" => "Parties de Plantes");
		$typesElements[] = array("id_type_element" => 5, "selected" => $id_type_courant, "nom_element" => "Potions");
		$typesElements[] = array("id_type_element" => 6, "selected" => $id_type_courant, "nom_element" => "Runes");
		$typesElements[] = array("id_type_element" => 7, "selected" => $id_type_courant, "nom_element" => "Autres Elements");
		
		$this->view->typeElements = $typesElements;
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
		if ($this->view->deposerOk == false) {
			throw new Zend_Exception(get_class($this)." Deposer interdit ");
		}
		
		$this->calculRamasser();
		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_laban", "box_evenements");
	}
}
