<?php

class Bral_Box_Erreur extends Bral_Box_Box {
	
	function __construct($request, $view, $interne, $message) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->messageErreur = $message ;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "Erreur";
	}
	
	function getNomInterne() {
		return "erreur";
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		return $this->view->render("interface/erreur.phtml");
	}
}