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
class Bral_Util_BralSession {

	private function __construct() {}

	static function refreshSession() {
		Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
		$auth = Zend_Auth::getInstance();
		$user = Zend_Auth::getInstance()->getIdentity();

		$dateAuth = $user->dateAuth;
		$activation = $user->activation;
		$gardiennage = $user->gardiennage;
		$gardeEnCours = $user->gardeEnCours;
		$administrateur = $user->administrateur;
		$gestion = $user->gestion;
		$administrationvue = $user->administrationvue;
		$administrationvueDonnees = $user->administrationvueDonnees;

		$dbAdapter = Zend_Registry::get('dbAdapter');
			
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$authAdapter->setTableName('hobbit');
		$authAdapter->setIdentityColumn('id_hobbit');
		$authAdapter->setCredentialColumn('email_hobbit');
		$authAdapter->setIdentity($user->id_hobbit);
		$authAdapter->setCredential($user->email_hobbit);
		$result = $auth->authenticate($authAdapter);
		$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit');
		
		$sessionTable = new Session();
		$sessionTable->purge();
		$nombre = $sessionTable->countByIdHobbitAndIdSession($user->id_hobbit, session_id());
		
		if ($hobbit != null && ($nombre == 1 || $administrateur)) {
			$auth->getStorage()->write($hobbit);
			Zend_Auth::getInstance()->getIdentity()->initialCall = false;
			Zend_Auth::getInstance()->getIdentity()->dateAuth = $dateAuth;
			Zend_Auth::getInstance()->getIdentity()->activation = $activation;
			Zend_Auth::getInstance()->getIdentity()->gardiennage = $gardiennage;
			Zend_Auth::getInstance()->getIdentity()->gardeEnCours = $gardeEnCours;
			Zend_Auth::getInstance()->getIdentity()->administrateur = $administrateur;
			Zend_Auth::getInstance()->getIdentity()->gestion = $gestion;
			Zend_Auth::getInstance()->getIdentity()->administrationvue = $administrationvue;
			Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees = $administrationvueDonnees;
			
			$data = array("id_fk_hobbit_session" => $hobbit->id_hobbit, "id_php_session" => session_id(), "ip_session" => $_SERVER['REMOTE_ADDR'], "date_derniere_action_session" => date("Y-m-d H:i:s")); 
			$sessionTable->insertOrUpdate($data);
			
			return true;
		} else {
			if ($nombre != 1) {
				$where = "id_fk_hobbit_session = ".$user->id_hobbit; 
				$sessionTable->delete($where);
				Bral_Util_Log::tech()->warn("Bral_Util_BralSession - session sur 2 navigateurs nb.session:(".$nombre.") - ID Hobbit:".$user->id_hobbit." IP:".$_SERVER['REMOTE_ADDR']);
			}
			return false;
		}
	}
}