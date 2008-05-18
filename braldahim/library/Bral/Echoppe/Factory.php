<?php

class Bral_Echoppe_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Echoppe_Echoppe");

		$matches = null;
		preg_match('/(.*)_echoppe_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		$construct = "Bral_Echoppe_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Echoppe_Factory construct invalide (classe): ".$nomSystemeAction);
		}
		 
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Echoppe_Factory action invalide: ".$nomSystemeAction);
		}
	}

}