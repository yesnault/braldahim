<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Erreur extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return null;
	}

	function getNomInterne()
	{
		return "erreur";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function setMessage($message)
	{
		$this->view->messageErreur = $message;
	}

	function render()
	{
		return $this->view->render("interface/erreur.phtml");
	}
}