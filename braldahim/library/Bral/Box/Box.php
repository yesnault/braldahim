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
abstract class Bral_Box_Box {

	protected $loadWithBoxes = true;
	protected $tablesHtmlTri = false;

	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$this->tablesHtmlTri = false;
	}

	abstract function getTitreOnglet();
	abstract function getNomInterne();

	function getChargementInBoxes() {
		return $this->loadWithBoxes;
	}

	public function getTablesHtmlTri() {
		return $this->tablesHtmlTri;
	}

	abstract function setDisplay($display) ;
	abstract function render() ;
}
