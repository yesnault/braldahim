<?php

class Bral_Messagerie_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Messagerie_Message");

		$matches = null;
		preg_match('/(.*)_messagerie_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2]; // messages ou message
		$construct = null;

		$construct = "Bral_Messagerie_".Bral_Util_String::firstToUpper($section);
		// verification que la classe existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Messagerie_Factory section invalide: ".$section);
		}
	}

	static function getMessage($request, $view) {
		Zend_Loader::loadClass("Bral_Messagerie_Message");
		$matches = null;
		preg_match('/(.*)_messagerie_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		return new Bral_Messagerie_Message($request, $view, $action);
	}
}
