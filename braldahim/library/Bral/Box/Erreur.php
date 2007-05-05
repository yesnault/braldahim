<?php

class Bral_Box_Erreur {
	
	function __construct($request, $view, $message) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->messageErreur = $message;
	}
	
	function getIdBox() {
		return "erreur";
	}
	
	function render() {
		return $this->view->render("interface/erreur.phtml");
	}
}