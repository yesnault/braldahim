<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: IndexController.php 1946 2009-08-27 20:51:10Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-08-27 22:51:10 +0200 (jeu., 27 août 2009) $
 * $LastChangedRevision: 1946 $
 * $LastChangedBy: yvonnickesnault $
 */
class IndexController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}
}