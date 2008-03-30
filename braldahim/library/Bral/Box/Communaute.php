<?php

class Bral_Box_Communaute {

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
		Zend_Loader::loadClass("HobbitCommunaute");
		
		$tabCommunaute = null;
		$hobbitCommunauteTable = new HobbitCommunaute();
		$communauteRowset = $hobbitCommunauteTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($communauteRowset) > 0) {
			foreach ($communauteRowset as $c) {
				$tabCommunaute = $c["nom_communaute"];
				break;
			}
		}
		$this->view->communaute = $tabCommunaute;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute.phtml");
	}

}