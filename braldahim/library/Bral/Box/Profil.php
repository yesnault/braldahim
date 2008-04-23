<?php

class Bral_Box_Profil extends Bral_Box_Box {
	
	function getTitreOnglet() {
		return "Profil";
	}
	
	function getNomInterne() {
		return "box_profil";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/profil.phtml");
	}
}
