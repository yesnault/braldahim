<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Blabla extends Bral_Box_Box
{

	const NB_TOUR_MESSAGE_MAX = 5;
	const NB_CASES_MAX = 3;

	function getTitreOnglet()
	{
		return "Le Blabla";
	}

	function getNomInterne()
	{
		return "box_blabla";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		Zend_Loader::loadClass('Bral_Util_Blabla');
		$this->view->nom_interne = $this->getNomInterne();
		Bral_Util_Blabla::render($this->view);
		return $this->view->render("interface/blabla.phtml");
	}

}
