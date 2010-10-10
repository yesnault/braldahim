<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Securite {

	const ROLE_AUTEUR_LIEU = "auteurlieu";
	const ROLE_BETA_TESTEUR = "testeur";
	const ROLE_AUTEUR_BOUGRIE = "auteurbougrie";

	private function __construct() {}

	public static function controlAdmin() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			throw new Zend_Exception("Securite : session invalide");
		}

		if (Zend_Auth::getInstance()->getIdentity()->administrateur != true) {
			throw new Zend_Exception("Securite : role invalide");
		}
	}

	public static function controlRole($nomController) {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			throw new Zend_Exception("Securite : session invalide");
		}

		if (Zend_Auth::getInstance()->getIdentity()->administrateur == true) {
			return ;
		} else {

			if (Zend_Auth::getInstance()->getIdentity()->gestion == false) {
				throw new Zend_Exception("Securite : gestionnaire invalide");
			}
				
			Zend_Loader::loadClass("Zend_Acl");
			Zend_Loader::loadClass("Zend_Acl_Role");
			$acl = new Zend_Acl();

			$acl->addRole(new Zend_Acl_Role(self::ROLE_AUTEUR_LIEU));
			$acl->addRole(new Zend_Acl_Role(self::ROLE_AUTEUR_BOUGRIE));
			$acl->addRole(new Zend_Acl_Role(self::ROLE_BETA_TESTEUR));
				
			$acl->allow(self::ROLE_AUTEUR_LIEU, null, 'AdministrationlieuController');
			$acl->allow(self::ROLE_AUTEUR_BOUGRIE, null, 'AdministrationbougrieController');
			$acl->allow(self::ROLE_BETA_TESTEUR, null, 'AdministrationbraldunController');
				
			$acl->allow(self::ROLE_AUTEUR_LIEU, null, 'GestionController');
			$acl->allow(self::ROLE_AUTEUR_BOUGRIE, null, 'GestionController');
			$acl->allow(self::ROLE_BETA_TESTEUR, null, 'GestionController');

			Zend_Loader::loadClass("BraldunsRoles");
			$braldunsRoles = new BraldunsRoles();
			$roles = $braldunsRoles->findByIdBraldun(Zend_Auth::getInstance()->getIdentity()->id_braldun);

			if ($roles == null || count($roles) == 0) {
				throw new Zend_Exception("Securite : role invalide A");
			}
				
			$roleOk = false;
			foreach($roles as $r) {
				if ($acl->isAllowed($r["nom_systeme_role"], null, $nomController)) {
					$roleOk = true;
					break;
				}
			}

			if ($roleOk == false) {
				throw new Zend_Exception("Securite : role invalide B");
			}
		}
	}

	public static function controlBatchsOrAdmin($request) {
		$passe = $request->get("batchspassword");
		$config = Zend_Registry::get('config');
		if ($passe == md5($config->batchs->password)) { // mot de passe Ok
			return true;
		} else {
			self::controlAdmin();
		}
	}

	public static function controlFeedAdmin($request) {
		$passe = $request->get("feedspassword");
		$config = Zend_Registry::get('config');
		if ($passe == md5($config->feeds->password)) { // mot de passe Ok
			return true;
		} else {
			self::controlAdmin();
		}
	}
}