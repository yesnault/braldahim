<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
abstract class Bral_Brasserie_Box {
	
	protected $loadWithBoxes = true;
	
	function __construct($request, $view, $interne) {
		$this->request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	abstract function getTitreOnglet();
	abstract function getNomInterne();
	
	public function getChargementInBoxes() {
		return $this->loadWithBoxes;		
	}
	
	abstract function setDisplay($display) ;
	abstract function render() ;
	
}