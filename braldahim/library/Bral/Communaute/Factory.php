<?php

class Bral_Communaute_Factory {
	static function getAction($request, $view) {
		
		Zend_Loader::loadClass("Bral_Communaute_Communaute");  
	
		$matches = null;
		preg_match('/(.*)_communaute_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2];
		$construct = null;

		$construct = "Bral_Communaute_".Bral_Util_String::firstToUpper($section);
		// verification que la classe existe.
		try {
			Zend_Loader::loadClass($construct);  
	    } catch(Exception $e) {
	  	  	throw new Zend_Exception("Bral_Communaute_Factory Action invalide (classe): ".$construct);
	    }
	    
		if (class_exists($construct)) {
			return new $construct ($request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Communaute_Factory Classe invalide: ".$construct);
		}
	}
}
