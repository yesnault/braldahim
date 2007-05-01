<?php

class Bral_Box_CompetencesBasiques {
	
	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
	}
	
	function getTitreOnglet() {
		return "Basiques";
	}
	
	function getNomInterne() {
		return "basiques";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		return $this->view->render("interface/competences_basiques.phtml");
	}
}
?>