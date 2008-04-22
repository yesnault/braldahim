<?php

class Bral_Box_Communaute extends Bral_Box_Box {

	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}

	function getTitreOnglet() {
		return "Communaut&eacute;";
	}

	function getNomInterne() {
		return "box_communaute";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$estGestionnaire = false;
		$communaute = null;
		
		Zend_Loader::loadClass("RangCommunaute");
		$rangCommunauteTable = new RangCommunaute();
		$rang = $rangCommunauteTable->findRangCreateur($this->view->user->id_fk_communaute_hobbit);

		if ($this->view->user->id_fk_rang_communaute_hobbit == $rang["id_rang_communaute"]) { // rang 1 : Gestionnaire
			$estGestionnaire = true;
		}
		if ($this->view->user->id_fk_communaute_hobbit != null) {
			Zend_Loader::loadClass("Communaute");
			$communauteTable = new Communaute();
			$communaute = $communauteTable->findById($this->view->user->id_fk_communaute_hobbit);
			if (count($communaute) == 1) {
				$communaute = $communaute[0];
			} else {
				$communaute = null;
			}
			$estDansCommunaute = true;
		}
		
		$this->view->estGestionnaire = $estGestionnaire;
		$this->view->communaute = $communaute;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute.phtml");
	}
}
