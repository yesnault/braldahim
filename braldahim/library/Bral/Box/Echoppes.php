<?php

class Bral_Box_Echoppes {
	
	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass('Echoppe');
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "Echoppes";
	}
	
	function getNomInterne() {
		return "box_echoppe";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		
		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdHobbit($this->view->user->id_hobbit);
		$this->view->estLieuCourant = false;
		
		$tabEchoppes = null;
		foreach($echoppesRowset as $e) {
//			if ($e["nom_metier"]{0} == 'A' ) {
//				$nom = "Echoppe d'".$e["nom_metier"];
//			} else {
//				$nom = "Echoppe de".$e["nom_metier"];
//			}
			$tabEchoppes[] = array(
			"id_echoppe" => $e["id_echoppe"],
			"x_echoppe" => $e["x_echoppe"],
			"y_echoppe" => $e["y_echoppe"],
			"nom_metier" => $e["nom_metier"]
			);
		}
		$this->view->echoppes = $tabEchoppes;
		$this->view->nEchoppes = count($tabEchoppes);
		
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/echoppes.phtml");
	}
	
}
