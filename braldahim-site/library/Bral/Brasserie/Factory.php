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
class Bral_Brasserie_Factory
{

	static function getBox($request, $view, $interne)
	{
		Zend_Loader::loadClass("Bral_Brasserie_Box");

		$matches = null;
		preg_match('/(.*)_brasserie_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2]; // classe

		$construct = "Bral_Brasserie_" . Bral_Util_String::firstToUpper($section);
		try {
			Zend_Loader::loadClass($construct);
		} catch (Zend_Exception $e) {
			throw new Zend_Exception("Bral_Brasserie_Factory classe invalide 1: " . $construct);
		}

		// verification que la classe existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($request, $view, $interne);
		} else {
			throw new Zend_Exception("Bral_Brasserie_Factory classe invalide 2: " . $construct);
		}
	}

}