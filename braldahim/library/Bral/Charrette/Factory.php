<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Charrette_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Charrette_Charrette");

		$matches = null;
		preg_match('/(.*)_charrette_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		if ($view->user->activation == false) {
			throw new Zend_Exception("Tour non activé");
		}
		
		$construct = "Bral_Charrette_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Charrette_Factory construct invalide (classe): ".$nomSystemeAction);
		}
		 
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Charrette_Factory action invalide: ".$nomSystemeAction);
		}
	}

}