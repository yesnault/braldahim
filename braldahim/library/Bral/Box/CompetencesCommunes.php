<?php

class Bral_Box_CompetencesCommunes {
	
	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
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
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/competences_communes.phtml");
	}
}
?>