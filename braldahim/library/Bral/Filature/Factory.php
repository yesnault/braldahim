<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Filature_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Filature_Filature");
		$matches = null;
		preg_match('/(.*)_filature_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		if ($view->user->activation == false && $nomSystemeAction != 'voir') {
			throw new Zend_Exception("Tour non activé");
		}

		$construct = "Bral_Filature_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Filature_Factory construct invalide (classe): ".$nomSystemeAction);
		}
			
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Filature_Factory action invalide: ".$nomSystemeAction);
		}
	}

	static function getVoir($request, $view, $idFilature) {
		Zend_Loader::loadClass("Bral_Filature_Filature");
		Zend_Loader::loadClass("Bral_Filature_Voir");
		return new Bral_Filature_Voir("voir", $request, $view, "ask", $idFilature);
	}

}