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
class Bral_Contrats_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Contrats_Contrats");
		$matches = null;
		preg_match('/(.*)_contrats_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		if ($view->user->activation == false && $nomSystemeAction != 'voir') {
			throw new Zend_Exception("Tour non activé");
		}

		$construct = "Bral_Contrats_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Contrats_Factory construct invalide (classe): ".$nomSystemeAction);
		}
			
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Contrats_Factory action invalide: ".$nomSystemeAction);
		}
	}

	static function getListe($request, $view) {
		Zend_Loader::loadClass("Bral_Contrats_Quete");
		Zend_Loader::loadClass("Bral_Contrats_Liste");
		return new Bral_Contrats_Liste("voir", $request, $view, "ask");
	}

	static function getVoirFilature($request, $view, $idFilature) {
		Zend_Loader::loadClass("Bral_Contrats_Quete");
		Zend_Loader::loadClass("Bral_Contrats_Liste");
		return new Bral_Contrats_Voirfilature("voir", $request, $view, "ask", $idFilature);
	}

}