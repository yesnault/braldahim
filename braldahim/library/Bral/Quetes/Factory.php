<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Factory.php 2037 2009-09-25 17:36:39Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-25 19:36:39 +0200 (Ven, 25 sep 2009) $
 * $LastChangedRevision: 2037 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Quetes_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Quetes_Quetes");
		$matches = null;
		preg_match('/(.*)_quetes_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		if ($view->user->activation == false && $nomSystemeAction != 'voir') {
			throw new Zend_Exception("Tour non activé");
		}

		$construct = "Bral_Quetes_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Quetes_Factory construct invalide (classe): ".$nomSystemeAction);
		}
			
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Quetes_Factory action invalide: ".$nomSystemeAction);
		}
	}

	static function getListe($request, $view) {
		Zend_Loader::loadClass("Bral_Quetes_Quetes");
		Zend_Loader::loadClass("Bral_Quetes_Liste");
		return new Bral_Quetes_Liste("voir", $request, $view, "ask");
	}
}