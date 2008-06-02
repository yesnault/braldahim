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
		$this->view->vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		return $this->view->render("interface/profil.phtml");
	}
}
