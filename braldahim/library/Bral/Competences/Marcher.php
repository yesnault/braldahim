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
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
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
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
	
	function prepareFormulaire() {
		Zend_Loader::loadClass('zone'); 
		$zoneTable = new Zone();
		$zone = $zoneTable->selectCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		// La requete ne doit renvoyer qu'une seule case
		if (count($zone) == 1) {
			$case = $zone[0];
		} else {
			throw new Zend_Exception(get_class($this)."::prepareFormulaire : Nombre de case invalide");
		}
		
		$this->view->environnement = $case["nom_environnement"];
		$this->nom_systeme_environnement = $case["nom_systeme_environnement"];
		$this->calculPaNbCases();
		
		if ($this->view->assezDePa) {
			$this->prepareFormulaireTableau();
		}
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
			default:
				throw new Zend_Exception(get_class($this)."::environnement invalide :".$this->nom_systeme_environnement);
		}
		
		if ($this->view->user->pa_hobbit - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}
	
	private function prepareFormulaireTableau() {
		for ($j = $this->view->nb_cases; $j >= -$this->view->nb_cases; $j --) {
			 $change_level = true;
			 for ($i = -$this->view->nb_cases; $i <= $this->view->nb_cases; $i ++) {
			 	if ($i == -1 && $j == 1) {
					$default = "checked";
			 	} else {
			 		$default = "";
			 	}
			 	
			 	$display = $this->view->user->x_hobbit + $i;
			 	$display .= " ; ";
			 	$display .= $this->view->user->y_hobbit + $j;
			 	
			 	if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
			 	} else {
			 		$valid = false;
			 	}
			 	
			 	$tab[] = array ("x_offset" => $i,
			 	"y_offset" => $j,
			 	"default" => $default,
			 	"display" => $display,
			 	"change_level" => $change_level, // nouvelle ligne dans le tableau
				"valid" => $valid);	
				
				if ($change_level) {
					$change_level = false;
				}
			 }
		}
		$this->view->tableau = $tab;
	}
}