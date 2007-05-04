<?php

class Bral_Box_CompetencesCommunes {
	
	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
		
		// chargement des competences
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id);
		$hobbit = $hobbitRowset->current();
		$this->competences = $hobbit->findCompetenceViaHobbitsCompetences();
		
		
		print_r($hobbit->findHobbitsCompetences());
		
		echo "YVO AA";
		$competences = Zend_Registry::get('competences');
		print_r($competences);
		echo "YVO FIN";
	}
	
	function getTitreOnglet() {
		return "Communes";
	}
	
	function getNomInterne() {
		return "competences_communes";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$tabCompetence = null;
		$this->view->nom_interne = $this->getNomInterne();
		
		foreach($this->competences as $competence) {
			$c = array("id" => $competence->id, "nom" => $competence->nom_competence);
			$tabCompetence[] = $c;
			
		}

		print_r($tabCompetence);

		return $this->view->render("interface/competences_communes.phtml");
	}
}
?>