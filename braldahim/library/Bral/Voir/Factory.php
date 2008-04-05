<?php

class Bral_Voir_Factory {
	static function getAction($request, $view) {

		$matches = null;
		preg_match('/(.*)_voir_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2];
		$construct = null;

		$construct = "Bral_Voir_".$section;
		// verification que la classe existe.
		try {
			Zend_Loader::loadClass($construct);  
	    } catch(Exception $e) {
	  	  	throw new Zend_Exception("Bral_Voir_Factory Action invalide (classe): ".$construct);
	    }
	    
		if (class_exists($construct)) {
			return new $construct ($request, $view);
		} else {
			throw new Zend_Exception("Bral_Voir_Factory Classe invalide: ".$construct);
		}
	}
	
	static function getCommunaute($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Communaute");
		return new Bral_Voir_Communaute($request, $view);
	}
}
