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
class Bral_Scripts_Factory {
	static function calculScript($nomSystemeAction, $view) {
		Zend_Loader::loadClass("Bral_Scripts_Script");
		Zend_Loader::loadClass("Bral_Util_Log");

		$construct = null;
		$construct = "Bral_Scripts_".$nomSystemeAction;
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Scripts_Factory construct invalide (classe): ".$nomSystemeAction);
		}
		 
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			$batchClasse = new $construct ($nomSystemeAction, $view);
			return $batchClasse->calculScript();
		} else {
			throw new Zend_Exception("Bral_Boutique_Script action invalide: ".$nomSystemeAction);
		}
	}
}