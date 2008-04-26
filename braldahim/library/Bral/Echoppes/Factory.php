<?php

class Bral_Echoppes_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Echoppes_Echoppe");

		$matches = null;
		preg_match('/(.*)_echoppes_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		$construct = "Bral_Echoppes_".$nomSystemeAction;
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Echoppes_Factory construct invalide (classe): ".$nomSystemeAction);
		}
		 
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Echoppes_Factory action invalide: ".$nomSystemeAction);
		}
	}

	static function getVoir($request, $view, $id_echoppe) {
		Zend_Loader::loadClass("Bral_Echoppes_Voir");
		Zend_Loader::loadClass("Bral_Echoppes_Echoppe");
		
		return new Bral_Echoppes_Voir("voir", $request, $view, "ask", $id_echoppe);
	}
}