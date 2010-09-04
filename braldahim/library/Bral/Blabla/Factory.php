<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Factory.php 2737 2010-06-15 17:19:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-06-15 19:19:50 +0200 (mar., 15 juin 2010) $
 * $LastChangedRevision: 2737 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Blabla_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Blabla_Blabla");
		$matches = null;
		preg_match('/(.*)_blabla_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		$construct = "Bral_Blabla_".Bral_Util_String::firstToUpper($nomSystemeAction);

		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Blabla_Factory construct invalide (classe): ".$nomSystemeAction);
		}
			
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Blabla_Factory action invalide: ".$nomSystemeAction);
		}
	}

}