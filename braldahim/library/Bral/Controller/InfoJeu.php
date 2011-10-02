<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Controller_InfoJeu extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexCommunAction($type)
	{

		$this->prepareAnnees();

		Zend_Loader::loadClass("Bral_Util_InfoJeu");
		$infoJeu = Bral_Util_InfoJeu::prepareInfosJeu($type, $this->view->anneeSelect);
		$this->view->annonces = $infoJeu["annonces"];
		$this->view->histoires = $infoJeu["histoires"];
	}

	private function prepareAnnees()
	{
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');

		$f = new Zend_Filter_StripTags();
		$anneeCourante = date("Y");
		$anneeSelect = intval($f->filter($this->_request->get("anneeselect")));
		if ($anneeSelect <= 0 || $anneeSelect == null) {
			$anneeSelect = $anneeCourante;
		}
		$this->view->anneeSelect = $anneeSelect;

		$anneeDebut = 2008;
		$anneeCourante = date("Y");

		for ($i = $anneeDebut; $i <= $anneeCourante; $i++) {
			$annees[] = $i;
		}
		$this->view->annees = $annees;
	}

}
