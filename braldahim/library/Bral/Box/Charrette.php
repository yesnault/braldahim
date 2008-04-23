<?php

class Bral_Box_Charrette extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Charrette";
	}

	function getNomInterne() {
		return "box_charrette";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		Zend_Loader::loadClass('Charrette');
		
		$tabCharrette = null;
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		foreach ($charrette as $c) {
			$tabCharrette = array(
			"nb_rondin" => $c["quantite_rondin_charrette"],
			);
		}
		
		$this->view->charrette = $tabCharrette;
		$this->view->nom_interne = $this->getNomInterne();
		
		return $this->view->render("interface/charrette.phtml");
	}
}
