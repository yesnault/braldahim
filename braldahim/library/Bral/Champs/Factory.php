<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Champs_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Champs_Champ");

		$matches = null;
		preg_match('/(.*)_champs_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		if ($view->user->activation == false && $nomSystemeAction != "liste" && $nomSystemeAction != "voir") {
			throw new Zend_Exception("Tour non activé");
		}
		
		$construct = "Bral_Champs_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Champs_Factory construct invalide (classe): ".$nomSystemeAction);
		}
		 
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Champs_Factory action invalide: ".$nomSystemeAction);
		}
	}

	static function getVoir($request, $view, $id_champ) {
		Zend_Loader::loadClass("Bral_Champs_Voir");
		Zend_Loader::loadClass("Bral_Champs_Champ");
		return new Bral_Champs_Voir("voir", $request, $view, "ask", $id_champ);
	}
}