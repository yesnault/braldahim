<?php

class Bral_Box_Competences {
	
	function __construct($request, $view, $interne, $type) {
		$this->_request = $request;
		$this->view = $view;
		$this->type = $type;
		$this->view->affichageInterne = $interne;
		
		// chargement des competences
		switch($this->type) {
			case "basic":
				$this->titreOnglet = "Basiques";
				$this->nomInterne = "box_competences_basiques";
				$this->render = "interface/competences_basiques.phtml";
				break;
			case "commun":
				$this->titreOnglet = "Communes";
				$this->nomInterne = "box_competences_communes";
				$this->render = "interface/competences_communes.phtml";
				break;
			case "metier":
				$this->titreOnglet = "M&eacute;tiers";
				$this->nomInterne = "box_competences_metiers";
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
		
		if ($this->type == 'basic') {
			$tabCompetences = Zend_Registry::get('competencesBasiques');
		} else {
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
			$hobbit = $hobbitRowset->current();
			$hobbitCompetences = $hobbit->findCompetenceViaHobbitsCompetences();
			$competences = Zend_Registry::get('competences');
			
			foreach($hobbitCompetences as $c) {
				if ($competences[$c->id_competence]["type"] == $this->type) {
					$t = array("id_competence" => $c->id_competence, 
					"nom" => $competences[$c->id_competence]["nom"], 
					"pa_utilisation" => $competences[$c->id_competence]["pa_utilisation"],
					"nom_systeme" => $competences[$c->id_competence]["nom_systeme"]);
					$tabCompetences[] = $t;
				}
			}
		}
		
		$this->view->competences = $tabCompetences;
		return $this->view->render($this->render);
	}
	
}
