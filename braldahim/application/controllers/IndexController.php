<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class IndexController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction()
	{
		$this->render();
	}

	function preDispatch()
	{
		if ($this->view->config->general->actif == 1) {
			$auth = Zend_Auth::getInstance();
			if (!$auth->hasIdentity() || !isset($this->view->user) || !isset($this->view->user->email_braldun)) {
				$this->_redirect('auth/login');
			} else {
				$this->_redirect('interface/');
			}
		} else {
			$this->_redirect('debraye');
		}
	}
}
