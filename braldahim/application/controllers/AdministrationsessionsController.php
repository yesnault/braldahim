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
class AdministrationsessionsController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}
	
	function indexAction() {
		$this->render();
	}
	
	function sessionsAction() {
		Zend_Loader::loadClass('Session');
		
		$session = new Session();
		$sessionsRowset = $session->findAll();
		
		$sessions = null;
		foreach($sessionsRowset as $s) {
			$sessions[] = array(
				"nom" => $s["prenom_hobbit"]. " ".$s["nom_hobbit"],
				"id_fk_hobbit_session" => $s["id_fk_hobbit_session"],
				"id_php_session" => $s["id_php_session"],
				"ip_session" => $s["ip_session"],
				"date_derniere_action_session" => $s["date_derniere_action_session"],
				);
		}
		
		$this->view->sessions = $sessions;
		
		$this->render();
	}
}

