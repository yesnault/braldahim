<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AdministrationsessionsController extends Zend_Controller_Action
{

	function init()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction()
	{
		$this->render();
	}

	function sessionsAction()
	{
		Zend_Loader::loadClass('Session');

		$session = new Session();
		$sessionsRowset = $session->findAll();

		$sessions = null;
		foreach ($sessionsRowset as $s) {
			$sessions[] = array(
				"nom" => $s["prenom_braldun"] . " " . $s["nom_braldun"],
				"id_fk_braldun_session" => $s["id_fk_braldun_session"],
				"id_php_session" => $s["id_php_session"],
				"ip_session" => $s["ip_session"],
				"date_derniere_action_session" => $s["date_derniere_action_session"],
			);
		}

		$this->view->sessions = $sessions;

		$this->render();
	}
}

