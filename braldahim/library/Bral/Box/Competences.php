<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Competences extends Bral_Box_Box
{

	function __construct($request, $view, $interne)
	{
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;

		$this->chargementInBoxes = false;
		$this->nomInterne = "box_competences";
		$this->render = "interface/competences.phtml";

		$this->titreOnglet = '<span class="titrea textalic titreasized">Action !</span>';
	}

	function getTitreOnglet()
	{
		return $this->titreOnglet;
	}

	function getChargementInBoxes()
	{
		return $this->chargementInBoxes;
	}

	function getNomInterne()
	{
		return $this->nomInterne;
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render($this->render);
	}

	public function getTablesHtmlTri()
	{
		return null;
	}
}
