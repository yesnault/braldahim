<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class GestionController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlRole(get_class($this));

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {

		Zend_Loader::loadClass("HobbitsRoles");
		$hobbitsRoles = new HobbitsRoles();
		$roles = $hobbitsRoles->findByIdHobbit(Zend_Auth::getInstance()->getIdentity()->id_hobbit);
		
		$tabRoles = null;
		foreach($roles as $r) {
			$tabRoles[] = $r["nom_systeme_role"];
		}
		
		$this->view->roles = $tabRoles;
		
		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->fetchAll();
		$this->view->administrationLieux = $lieux;
		
		$this->render();
	}
}
