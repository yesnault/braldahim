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
class IndexController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction()
	{
		$this->render();
	}
}