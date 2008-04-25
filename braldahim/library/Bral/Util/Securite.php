<?php

class Bral_Util_Securite {

	private function __construct(){}

	public static function controlAdmin() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			throw new Zend_Exception("Securite : session invalide");
		}
		
		if (Zend_Auth::getInstance()->getIdentity()->sysgroupe_hobbit != "admin") {
			throw new Zend_Exception("Securite : role invalide");
		}
	}
}