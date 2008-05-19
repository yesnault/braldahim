<?php

class Bral_Util_BralSession {

	private function __construct() {}

	static function refreshSession() {
		Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
		$auth = Zend_Auth::getInstance();
		$user = Zend_Auth::getInstance()->getIdentity();

		$activation = $user->activation;
		$gardiennage = $user->gardiennage;
		$gardeEnCours = $user->gardeEnCours;
		$administrateur = $user->administrateur;

		$dbAdapter = Zend_Registry::get('dbAdapter');
			
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$authAdapter->setTableName('hobbit');
		$authAdapter->setIdentityColumn('id_hobbit');
		$authAdapter->setCredentialColumn('email_hobbit');
		$authAdapter->setIdentity($user->id_hobbit);
		$authAdapter->setCredential($user->email_hobbit);
		$result = $auth->authenticate($authAdapter);
		$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit');
		
		if ($hobbit != null) {
			$auth->getStorage()->write($hobbit);
			Zend_Auth::getInstance()->getIdentity()->activation = $activation;
			Zend_Auth::getInstance()->getIdentity()->gardiennage = $gardiennage;
			Zend_Auth::getInstance()->getIdentity()->gardeEnCours = $gardeEnCours;
			Zend_Auth::getInstance()->getIdentity()->administrateur = $administrateur;
			return true;
		} else {
			return false;
		}
	}
}