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
class Bral_Lieux_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Lieux_Lieu");
		
		$matches = null;
		preg_match('/(.*)_lieu_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeLieu = $matches[2];
		$construct = null;
		
		$construct = "Bral_Lieux_".Bral_Util_String::firstToUpper($nomSystemeLieu);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Lieux_Factory construct invalide (classe): ".$nomSystemeLieu);
		}
		
 		$construct = "Bral_Lieux_".$nomSystemeLieu;
	    // verification que la classe du lieu existe.            
		if (($construct != null) && (class_exists($construct))) {                
			return new $construct ($nomSystemeLieu, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Lieux_Factory Lieu invalide: ".$nomSystemeLieu);
		}
	}
}