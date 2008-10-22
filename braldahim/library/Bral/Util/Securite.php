<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Util_Securite {

	private function __construct() {}

	public static function controlAdmin() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			throw new Zend_Exception("Securite : session invalide");
		}
		
		if (Zend_Auth::getInstance()->getIdentity()->sysgroupe_hobbit != "admin") {
			throw new Zend_Exception("Securite : role invalide");
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
}