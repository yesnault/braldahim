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
class EtalsController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->config = Zend_Registry::get('config');

		$f = new Zend_Filter_StripTags();

		$regionSelect = intval($f->filter($this->_request->get('regionselect')));
		if ($regionSelect <= 0) {
			$regionSelect = -1;
		}
		$this->view->regionSelect = $regionSelect;
	}

	function indexAction()
	{
		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$rowset = $regionTable->fetchAll(null, 'nom_region');
		$regions[-1] = 'Toutes';
		foreach ($rowset as $r) {
			$regions[$r['id_region']] = $r['nom_region'];
		}
		$this->view->regions = $regions;

		Zend_Loader::loadClass('Bral_Util_Lot');
		Zend_Loader::loadClass('Bral_Util_Poids');
		Zend_Loader::loadClass('Bral_Util_String');
		$this->view->lots = Bral_Util_Lot::getLotsByEtals($this->view->regionSelect);

		$this->render();
	}


}