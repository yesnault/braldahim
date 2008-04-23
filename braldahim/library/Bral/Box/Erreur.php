<?php

class Bral_Box_Erreur extends Bral_Box_Box {
	
	function getTitreOnglet() {
		return null;
	}
	
	function getNomInterne() {
		return "erreur";
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function setMessage($message) {
		$this->view->messageErreur = $message ;
	}
	
	function render() {
		return $this->view->render("interface/erreur.phtml");
	}
}