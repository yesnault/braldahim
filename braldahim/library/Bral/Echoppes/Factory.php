<?php

class Bral_Echoppes_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Echoppes_Echoppe");
		Zend_Loader::loadClass("Bral_Echoppes_Construire");
		
		$matches = null;
		preg_match('/(.*)_echoppes_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;
		
		// verification que le joueur est sur le lieu
		// TODO

 		$construct = "Bral_Echoppes_".$nomSystemeAction;
	    // verification que la classe de l'action existe.            
		if (($construct != null) && (class_exists($construct))) {                
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Echoppes_Factory action invalide: ".$nomSystemeAction);
		}
	}
}