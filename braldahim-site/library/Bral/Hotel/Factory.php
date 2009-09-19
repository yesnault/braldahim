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
class Bral_Hotel_Factory {
	static function getBox($request, $view) {
		Zend_Loader::loadClass("Bral_Hotel_Box");
		Zend_Loader::loadClass("Bral_Util_String");

		if ($request->get("caction") != null) {
			$matches = null;
			preg_match('/(.*)_hotel_(.*)/', $request->get("caction"), $matches);
			$nomSystemeAction = $matches[2];
			$construct = null;
		} else {	
			$nomSystemeAction = "Voir";
		}

		$construct = "Bral_Hotel_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Hotel_Factory construct invalide (classe): ".$nomSystemeAction);
		}
			
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($request, $view);
		} else {
			throw new Zend_Exception("Bral_Hotel_Factory action invalide: ".$nomSystemeAction);
		}
	}
}