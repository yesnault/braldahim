<?php

class Bral_Box_Echoppe {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "&Eacute;choppe";
	}
	
	function getNomInterne() {
		return "box_lieu";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		return $this->view->render("interface/echoppe.phtml");
	}
}
