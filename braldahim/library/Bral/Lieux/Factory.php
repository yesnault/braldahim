<?php

class Bral_Lieux_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Lieux_Lieu");
		Zend_Loader::loadClass("Bral_Lieux_Ahennepeheux");
		Zend_Loader::loadClass("Bral_Lieux_Behennepee");
		Zend_Loader::loadClass("Bral_Lieux_Essenecehef");
		Zend_Loader::loadClass("Bral_Lieux_Laffaque");
		Zend_Loader::loadClass("Bral_Lieux_Eujimenasiumme");
		
		$matches = null;
		preg_match('/(.*)_lieu_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeLieu = $matches[2];
		$construct = null;
		
		// verification que le joueur est sur le lieu
		// TODO

 		$construct = "Bral_Lieux_".$nomSystemeLieu;
	    // verification que la classe du lieu existe.            
		if (($construct != null) && (class_exists($construct))) {                
			return new $construct ($nomSystemeLieu, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Lieux_Factory Lieu invalide: ".$nomSystemeLieu);
		}
	}
}