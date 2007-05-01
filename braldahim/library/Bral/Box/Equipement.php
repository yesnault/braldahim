<?php

class Bral_Box_Equipement {
	
	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
	}
	
	function getTitreOnglet() {
		return "Profil";
	}
	
	function getNomInterne() {
		return "profil";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		return $this->view->render("interface/profil.phtml");
	}
}
?>