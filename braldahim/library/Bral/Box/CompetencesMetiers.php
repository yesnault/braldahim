<?php

class Bral_Box_CompetencesMetiers {
	
	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
	}
	
	function getTitreOnglet() {
		return "M&eacute;tiers";
	}
	
	function getNomInterne() {
		return "competences_metiers";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/competences_metiers.phtml");
	}
}
?>