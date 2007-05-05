<?php

class Bral_Box_Competences {
	
	function __construct($request, $view, $type) {
		$this->_request = $request;
		$this->view = $view;
		$this->type = $type;
		
		// chargement des competences
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id);
		$hobbit = $hobbitRowset->current();
		$this->hobbitCompetences = $hobbit->findCompetenceViaHobbitsCompetences();
		$this->competences = Zend_Registry::get('competences');
	}
	
	function getTitreOnglet() {
		switch($this->type) {
			case "basic":
				$r = "Basiques";
				break;
			case "commun":
				$r = "Communes";
				break;
			case "metier":
				$r = "M&eacute;iers";
				break;	
		}
		return $r;	
	}
	
	function getNomInterne() {
		switch($this->type) {
			case "basic":
				$r = "competences_basiques";
				break;
			case "commun":
				$r = "competences_communes";
				break;
			case "metier":
				$r = "competences_metiers";
				break;	
		}
		return $r;		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$tabCompetences = null;
		$this->view->nom_interne = $this->getNomInterne();
		
		foreach($this->hobbitCompetences as $c) {
			if ($this->competences[$c->id]["type"] == $this->type) {
				$t = array("id" => $c->id, "nom" => $this->competences[$c->id]["nom"], "pa_utilisation" => $this->competences[$c->id]["pa_utilisation"]);
				$tabCompetences[] = $t;
			}
		}
		$this->view->competences = $tabCompetences;
		return $this->view->render("interface/competences_communes.phtml");
	}
}
?>