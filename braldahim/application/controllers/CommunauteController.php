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
class CommunauteController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		if (!Zend_Auth::getInstance()->hasIdentity() || $this->_request->get("dateAuth") != $this->view->user->dateAuth ) {
			$this->_redirect('/auth/logoutajax');
		}

		$this->view->config = Zend_Registry::get('config');
		$this->view->controleur = $this->_request->controller;

		Zend_Loader::loadClass('Bral_Communaute_Factory');
	}

	function askactionAction() {
		$this->doactionAction();
	}

	function doactionAction() {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_response = new Bral_Xml_Response();
		try {
			$communaute = Bral_Communaute_Factory::getAction($this->_request, $this->view);
			$xml_entry->set_valeur($communaute->getNomInterne());
			$xml_entry->set_data($communaute->render());
			$xml_response->add_entry($xml_entry);
			
			if ($communaute->anotherXmlEntry() != null) {
				$xml_response->add_entry($communaute->anotherXmlEntry());
			}
			Bral_Util_JoomlaUser::setXmlResponseMessagerie($xml_response, $this->view->user->id_fk_jos_users_hobbit);
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$xml_response->add_entry($xml_entry);
		}
		$xml_response->render();
	}
}
