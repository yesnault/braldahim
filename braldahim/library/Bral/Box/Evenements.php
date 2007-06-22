<?php

class Bral_Box_Evenements {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "&Eacute;v&egrave;nements";
	}
	
	function getNomInterne() {
		return "box_evenements";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/evenements.phtml");
	}
}
?>