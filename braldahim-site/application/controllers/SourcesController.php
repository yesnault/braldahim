<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: ProjetController.php 1049 2009-01-24 15:31:36Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-24 16:31:36 +0100 (sam., 24 janv. 2009) $
 * $LastChangedRevision: 1049 $
 * $LastChangedBy: yvonnickesnault $
 */
class SourcesController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}
}