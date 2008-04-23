<?php

abstract class Bral_Box_Box {
	
	protected $loadWithBoxes = true;
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	abstract function getTitreOnglet();
	abstract function getNomInterne();
	
	function getChargementInBoxes() {
		return $this->loadWithBoxes;		
	}
	
	abstract function setDisplay($display) ;
	abstract function render() ;
	
}
