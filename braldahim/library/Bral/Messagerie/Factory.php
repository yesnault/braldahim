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
class Bral_Messagerie_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Messagerie_Messagerie");
		Zend_Loader::loadClass("Bral_Messagerie_Message");
		Zend_Loader::loadClass("Bral_Messagerie_Contacts");
		
		$matches = null;
		preg_match('/(.*)_messagerie_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2]; // messages ou message
		$construct = null;

		$construct = "Bral_Messagerie_".Bral_Util_String::firstToUpper($section);
		// verification que la classe existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Messagerie_Factory classe invalide: ".$construct);
		}
	}

	static function getMessage($request, $view) {
		Zend_Loader::loadClass("Bral_Messagerie_Message");
		$matches = null;
		preg_match('/(.*)_messagerie_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		return new Bral_Messagerie_Message($request, $view, $action);
	}
}
