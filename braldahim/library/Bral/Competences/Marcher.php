<?php

class Bral_Competences_Marcher extends Bral_Competences_Competence {
	
	function __construct($request, $view, $action) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		
		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
		}
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
	
	function prepareFormulaire() {
		Zend_Loader::loadClass('zone'); 
		$zoneTable = new Zone();
		$zone = $zoneTable->selectCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$case = $zone[0];
		$this->view->environnement = $case["nom_environnement"];
		$this->nom_systeme_environnement = $case["nom_systeme_environnement"];
		$this->calculPaNbCases();
	}
	
	function prepareResultat() {
		
	}
	
	/* Pour marcher, le nombre de PA utilise est variable suivant l'environnement'
	* sur lequel le hobbit marche :
	* Plaine : 1 PA jusqu'? 2 case
	* Foret : 1 PA pour 1 case
	* Marais : 2 PA pour 1 case
	* Montagneux : 2 PA pour 1 case
	* Caverneux : 1 PA pour 1 case
	*/
	private function calculPaNbCases() {
		switch($this->nom_systeme_environnement) {
			case "plaine" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 1;
				break;
			case "marais" :
				$this->view->nb_cases = 2;
				$this->view->nb_pa = 1; 
				break;
			case "montagne" :
				$this->view->nb_cases = 2;
				$this->view->nb_pa = 1;
				break;
			case "foret" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 1;
				break;
			case "caverne" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 1;
				break;
		}
		
		if ($this->view->user->pa_hobbit - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}
}