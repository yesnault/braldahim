<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Box_Box
{

	protected $loadWithBoxes = true;
	protected $tablesHtmlTri = false;

	function __construct($request, $view, $interne)
	{
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$this->loadWithBoxes = $interne;
		$this->tablesHtmlTri = false;
	}

	abstract function getTitreOnglet();

	abstract function setDisplay($display);

	abstract function getNomInterne();

	function getChargementInBoxes()
	{
		return false;
	}

	public function getTablesHtmlTri()
	{
		return $this->tablesHtmlTri;
	}

	abstract function render();
}
