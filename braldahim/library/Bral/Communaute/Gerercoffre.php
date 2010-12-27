<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Gerercoffre extends Bral_Communaute_Communaute {

	function __construct($request, $view, $interne) {

		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		
		$this->view->message = null;
	}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	private function prepareRender() {
	}
	
	public function render() {
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/gerer/coffre.phtml");
	}
	
}
