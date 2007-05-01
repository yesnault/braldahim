<?php

class Bral_Box_Profil {
	
	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
	}
	
	function getTitreOnglet() {
		return "Equipement";
	}
	
	function getNomInterne() {
		return "equipement";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		return $this->view->render("interface/equipement.phtml");
	}
}
?>