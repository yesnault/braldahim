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
class RechercheController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		if (!Zend_Auth::getInstance()->hasIdentity() 
			&& $this->_request->action != 'logoutajax') {
			$this->_redirect('/Recherche/logoutajax');
		}
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}
	
	function logoutajaxAction() {
		$this->render();
	}
	
	function hobbitAction() {
		if (Bral_Util_String::isChaineValide($this->_request->get("valeur"))) {
			$tabHobbits = null;
			$hobbitTable = new Hobbit();
			
			
			$hobbitRowset = $hobbitTable->findHobbitsParPrenomIdJoomlaOnly($this->_request->get("valeur").'%');
			$this->view->champ = $this->_request->get("champ");
			
			foreach ($hobbitRowset as $h) {
				$tabHobbits[] = array(
					"id_hobbit" => $h["id_hobbit"],
					"id_fk_jos_users_hobbit" => $h["id_fk_jos_users_hobbit"],
					"nom" => $h["nom_hobbit"],
					"prenom" => $h["prenom_hobbit"],
				);
			}
			$this->view->pattern = $this->_request->get("valeur");
			$this->view->tabHobbits = $tabHobbits;
		}
		$this->render();
	}
}
