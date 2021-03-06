<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Echoppe extends Bral_Lieux_Lieu
{

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun()
	{
		Zend_Loader::loadClass("Lieu");
		$this->_coutCastars = 0;
		$this->_utilisationPossible = true;
	}

	function prepareFormulaire()
	{
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat()
	{
	}

	function getNomInterne()
	{
		return "box_lieu";
	}

	function getListBoxRefresh()
	{
		return array();
	}

}