<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_BralSession
{

	private function __construct()
	{
	}

	static function refreshSession()
	{
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
		$rangCommunaute = $user->rangCommunaute;

		$dbAdapter = Zend_Registry::get('dbAdapter');

		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$authAdapter->setTableName('braldun');
		$authAdapter->setIdentityColumn('id_braldun');
		$authAdapter->setCredentialColumn('email_braldun');
		$authAdapter->setIdentity($user->id_braldun);
		$authAdapter->setCredential($user->email_braldun);
		$result = $auth->authenticate($authAdapter);
		$braldun = $authAdapter->getResultRowObject(null, array('password_braldun', 'password_salt_braldun'));

		$sessionTable = new Session();
		$sessionTable->purge();
		$nombre = $sessionTable->countByIdBraldunAndIdSession($user->id_braldun, session_id());

		if ($braldun != null && ($nombre == 1 || $administrateur)) {
			$auth->getStorage()->write($braldun);
			Zend_Auth::getInstance()->getIdentity()->initialCall = false;
			Zend_Auth::getInstance()->getIdentity()->dateAuth = $dateAuth;
			Zend_Auth::getInstance()->getIdentity()->activation = $activation;
			Zend_Auth::getInstance()->getIdentity()->gardiennage = $gardiennage;
			Zend_Auth::getInstance()->getIdentity()->gardeEnCours = $gardeEnCours;
			Zend_Auth::getInstance()->getIdentity()->administrateur = $administrateur;
			Zend_Auth::getInstance()->getIdentity()->gestion = $gestion;
			Zend_Auth::getInstance()->getIdentity()->administrationvue = $administrationvue;
			Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees = $administrationvueDonnees;
			Zend_Auth::getInstance()->getIdentity()->rangCommunaute = $rangCommunaute;

			$data = array("id_fk_braldun_session" => $braldun->id_braldun, "id_php_session" => session_id(), "ip_session" => $_SERVER['REMOTE_ADDR'], "date_derniere_action_session" => date("Y-m-d H:i:s"));
			$sessionTable->insertOrUpdate($data);

			return true;
		} else {
			if ($nombre != 1) {
				$where = "id_fk_braldun_session = " . $user->id_braldun;
				$sessionTable->delete($where);
				Bral_Util_Log::tech()->warn("Bral_Util_BralSession - session sur 2 navigateurs nb.session:(" . $nombre . ") - ID Braldun:" . $user->id_braldun . " IP:" . $_SERVER['REMOTE_ADDR']);
			}
			return false;
		}
	}
}