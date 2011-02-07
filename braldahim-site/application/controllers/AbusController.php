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
class AbusController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
		Zend_Loader::loadClass('Bral_Util_Mail');
				
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
			$authAdapter->setTableName('braldun');

			$authAdapter->setIdentityColumn('email_braldun');
			$authAdapter->setCredentialColumn('password_hash_braldun');

			$braldunTable = new Braldun();
			$salt = $braldunTable->getSaltByEmail($email);
			$salt = $salt["password_salt_braldun"];
				
			// Set the input credential values to authenticate against
			$authAdapter->setIdentity($email);
			$passwordHash = Bral_Util_Hash::getHashString($salt, md5($password));
			$authAdapter->setCredential($passwordHash);

			// do the authentication
			$auth = Zend_Auth::getInstance();
			$result = $auth->authenticate($authAdapter);
			if ($result->isValid()) {
				Zend_Loader::loadClass('Abus');
				Zend_Loader::loadClass('Zend_Filter');
				
				$braldun = $authAdapter->getResultRowObject(null, array('password_hash_braldun', 'password_salt_braldun'));

				$abusTable = new Abus();
				
				$data = array(
					'date_abus' => date("Y-m-d H:i:s"),
				 	'id_fk_braldun_abus' => $braldun->id_braldun,
					'texte_abus' => $texte,
				);
				$idAbus = $abusTable->insert($data);
				
				$mail = Bral_Util_Mail::getNewZendMail();
				
				$mail->setFrom($this->view->config->general->mail->abus->from, $this->view->config->general->mail->abus->nom);
				$mail->addTo($this->view->config->general->mail->abus->from, $this->view->config->general->mail->abus->nom);
				$mail->setSubject("[Braldahim-Abus] Abus n°".$idAbus ." signalé par Braldûn n°".$braldun->id_braldun." (".$email.")");
				$mail->setBodyText($texte);
				$mail->send();
				
				$this->_redirect('/abus/fin');
			} else {
				$this->view->message = "Email ou mot de passe incorrect";
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