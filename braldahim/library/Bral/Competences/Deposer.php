<?php

class Bral_Competences_Deposer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Castar");
		Zend_Loader::loadClass("Laban");
		
		$this->view->deposerOk = false;
		
		if ($this->request->get("valeur_1") != "") {
			$id_type_courant = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
			if ($id_type_courant < 1 && $id_type_courant > 7) {
				throw new Zend_Exception("Bral_Competences_Deposer Valeur invalide : id_type_courant=".$id_type_courant);
			}
		} else {
			$id_type_courant = -1;
		}
		
		$typesElements[1] = array("id_type_element" => 1, "selected" => $id_type_courant, "nom_systeme" => "castars", "nom_element" => "Castars");
		$typesElements[2] = array("id_type_element" => 2, "selected" => $id_type_courant, "nom_systeme" => "equipements", "nom_element" => "Equipements");
		$typesElements[3] = array("id_type_element" => 3, "selected" => $id_type_courant, "nom_systeme" => "minerais", "nom_element" => "Minerais");
		$typesElements[4] = array("id_type_element" => 4, "selected" => $id_type_courant, "nom_systeme" => "partiesplantes", "nom_element" => "Parties de Plantes");
		$typesElements[5] = array("id_type_element" => 5, "selected" => $id_type_courant, "nom_systeme" => "potions", "nom_element" => "Potions");
		$typesElements[6] = array("id_type_element" => 6, "selected" => $id_type_courant, "nom_systeme" => "runes", "nom_element" => "Runes");
		$typesElements[7] = array("id_type_element" => 7, "selected" => $id_type_courant, "nom_systeme" => "autres", "nom_element" => "Autres Elements");
		
		$this->view->typeElements = $typesElements;
		$this->view->type = null;
		
		if ($id_type_courant != -1) {
			$this->view->type = $typesElements[$id_type_courant]["nom_systeme"];
			$this->prepareDeposer();
		}
	}

	private function prepareDeposer() {
		switch($this->view->type) {
			case "castars" :
				$this->prepareTypeCastars();
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Deposer prepareType invalide : type=".$this->view->type);
		}
	}
	
	private function calculDeposer() {
		switch($this->view->type) {
			case "castars" :
				$this->deposeTypeCastars();
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Deposer prepareType invalide : type=".$this->view->type);
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

		// Verification deposer
		if ($this->view->deposerOk == false) {
			throw new Zend_Exception(get_class($this)." Deposer interdit ");
		}
		
		$this->calculDeposer();
		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_laban", "box_evenements");
	}
	
	private function prepareTypeCastars() {
		$this->view->castars = $this->view->user->castars_hobbit;
		
		if ($this->view->castars > 0) {
			$this->view->deposerOk = true;
		}
	}
	
	private function deposeTypeCastars() {
		$nbCastars = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		
		if ($nbCastars > $this->view->user->castars_hobbit) {
			throw new Zend_Exception(get_class($this)." NB Castars invalide : ".$nbcastars);
		} 
		
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $nbCastars;
		
		$castarsTable = new Castar();
		$data = array(
			"nb_castar" => $nbCastars,
			"x_castar" => $this->view->user->x_hobbit,
			"y_castar" => $this->view->user->y_hobbit,
		);
		$castarsTable->insertOrUpdate($data);
	}
}
