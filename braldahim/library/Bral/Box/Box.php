<?php

abstract class Bral_Box_Box {
	
	protected $loadWithBoxes = true;
	
	abstract function getTitreOnglet();
	abstract function getNomInterne();
	
	function getChargementInBoxes() {
		return $this->loadWithBoxes;		
	}
	
	abstract function setDisplay($display) ;
	abstract function render() ;
	
}
