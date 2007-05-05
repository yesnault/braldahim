<?php

class Bral_Competences_Marcher extends Bral_Competences_Competence {
	
	function __construct($request, $view, $action) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
	}
	
	function render() {
		switch($this->action) {
			case "ask":
				return $this->view->render("competences/marcher_formulaire.phtml");
				break;
			case "do":
				return $this->view->render("competences/marcher_resultat.phtml");
				break;
		}
	}
}