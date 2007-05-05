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
		
		switch($this->type) {
			case "basic":
				$this->titreOnglet = "Basiques";
				$this->nomInterne = "competences_basiques";
				$this->render = "interface/competences_basiques.phtml";
				break;
			case "commun":
				$this->titreOnglet = "Communes";
				$this->nomInterne = "competences_communes";
				$this->render = "interface/competences_communes.phtml";
				break;
			case "metier":
				$this->titreOnglet = "M&eacute;tiers";
				$this->nomInterne = "competences_metiers";
				$this->render = "interface/competences_metiers.phtml";
				break;	
		}
	}
	
	function getTitreOnglet() {
		return $this->titreOnglet;	
	}
	
	function getNomInterne() {
		return $this->nomInterne;		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$tabCompetences = null;
		$this->view->nom_interne = $this->getNomInterne();
		
		foreach($this->hobbitCompetences as $c) {
			if ($this->competences[$c->id]["type"] == $this->type) {
				$t = array("id" => $c->id, 
				"nom" => $this->competences[$c->id]["nom"], 
				"pa_utilisation" => $this->competences[$c->id]["pa_utilisation"],
				"nom_systeme" => $this->competences[$c->id]["nom_systeme"]);
				$tabCompetences[] = $t;
			}
		}
		$this->view->competences = $tabCompetences;
		return $this->view->render($this->render);
	}
	
}
?>