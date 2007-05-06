<?php

class Bral_Box_Erreur {
	
	function __construct($request, $view, $interne, $message) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->messageErreur = $message ;
		$this->view->affichageInterne = $interne;
	}
	
	function getNomInterne() {
		return "erreur";
	}
	
	function render() {
		return $this->view->render("interface/erreur.phtml");
	}
}