<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Communaute_Gestion extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return "Gestion";
	}

	function getNomInterne()
	{
		return "box_communaute_gestion";
	}

	function getChargementInBoxes()
	{
		return false;
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function getListBoxRefresh()
	{
	}

	function prepareCommun()
	{
	}

	function prepareFormulaire()
	{
	}

	function prepareResultat()
	{
	}

	function render()
	{
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("RangCommunaute");
		Zend_Loader::loadClass("Bral_Util_Communaute");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Bral_Helper_Communaute");

		if ($this->view->affichageInterne) {
			$this->prepareData();
		}
		$this->view->nom_interne = $this->getNomInterne();

		$this->view->niveauTribune = Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_TRIBUNE);

		return $this->view->render("interface/communaute/gestion.phtml");
	}

	function prepareData()
	{
	}
}
