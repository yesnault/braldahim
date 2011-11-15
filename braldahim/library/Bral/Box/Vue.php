<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Vue extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return 'Vue';
	}

	function getNomInterne()
	{
		return 'box_vue';
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		if ($this->view->user->administrationvue === true) {
			$this->prepareAdministrationVue();
		}

		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render('interface/vue.phtml');

	}

	private function prepareAdministrationVue()
	{
		Zend_Loader::loadClass('Ville');
		$villeTable = new Ville();
		$villes = $villeTable->fetchAll();
		$this->view->administrationVilles = $villes;

		Zend_Loader::loadClass('Lieu');
		$lieuTable = new Lieu();
		$lieux = $lieuTable->fetchAll();
		$this->view->administrationLieux = $lieux;
	}
}
