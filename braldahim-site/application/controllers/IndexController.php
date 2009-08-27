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
class IndexController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->prepareInfosJeu();
		$this->render();
	}

	private function prepareInfosJeu() {
		Zend_Loader::loadClass("Bral_Util_InfoJeu");
		$infoJeu = Bral_Util_InfoJeu::prepareInfosJeu();
		$this->view->annonces = $infoJeu["annonces"];
		$this->view->histoires = $infoJeu["histoires"];
	}
}