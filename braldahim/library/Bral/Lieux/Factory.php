<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Lieux_Lieu");

		$matches = null;
		preg_match('/(.*)_lieu_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeLieu = $matches[2];
		$construct = null;

		if ($view->user->activation == false) {
			throw new Zend_Exception("Tour non activé");
		}

		$construct = "Bral_Lieux_".Bral_Util_String::firstToUpper($nomSystemeLieu);

		if ($nomSystemeLieu == "puits") {
			Zend_Loader::loadClass("Bral_Lieux_Mine");
		}

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