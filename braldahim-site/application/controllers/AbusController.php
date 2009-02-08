<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: IndexController.php 1049 2009-01-24 15:31:36Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-24 16:31:36 +0100 (Sat, 24 Jan 2009) $
 * $LastChangedRevision: 1049 $
 * $LastChangedBy: yvonnickesnault $
 */
class AbusController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
				
		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
		$email = $filter->filter($this->_request->getPost('email_abus'));
		$password = $filter->filter($this->_request->getPost('password_abus'));
		$texte = $filter->filter($this->_request->getPost('texte_abus'));
		
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && $email != "" && $password != "" && $texte != "") {
			Zend_Loader::loadClass('Zend_Auth');
			Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
			
			$dbAdapter = Zend_Registry::get('dbAdapter');
			// Suppression de la sessions courante
			Zend_Auth::getInstance()->clearIdentity();
			$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
			$authAdapter->setTableName('hobbit');

			$authAdapter->setIdentityColumn('email_hobbit');
			$authAdapter->setCredentialColumn('password_hobbit');

			// Set the input credential values to authenticate against
			$authAdapter->setIdentity($email);
			$authAdapter->setCredential(md5($password));

			// do the authentication
			$auth = Zend_Auth::getInstance();
			$result = $auth->authenticate($authAdapter);
			if ($result->isValid()) {
				Zend_Loader::loadClass('Abus');
				Zend_Loader::loadClass('Zend_Filter');
				
				$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit');

				$abusTable = new Abus();
				
				$data = array(
					'date_abus' => date("Y-m-d H:i:s"),
				 	'id_fk_hobbit_abus' => $hobbit->id_hobbit,
					'texte_abus' => $texte,
				);
				$abusTable->insert($data);
				
				$this->_redirect('/abus/fin');
			} else {
				$this->view->message = "Echec d'authentification";
			}
		} else {
			$this->view->message = "Tous les champs sont obligatoires";
		}
		
		$this->render();
	}
	
	public function finAction() {
		$this->render();
	}
}