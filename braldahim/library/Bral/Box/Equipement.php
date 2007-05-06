<?php

class Bral_Box_Equipement {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "Equipement";
	}
	
	function getNomInterne() {
		return "box_equipement";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/equipement.phtml");
	}
}
?>