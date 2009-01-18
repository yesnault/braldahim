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
class Bral_Palmares_Factory {
	
	static function getBox($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Naissancecomte");
		Zend_Loader::loadClass("Bral_Palmares_Naissancesexe");
		Zend_Loader::loadClass("Bral_Palmares_Naissancefamille");
		
		$matches = null;
		preg_match('/(.*)_palmares_(.*)_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2]; // classe
		$filtre = (int)$matches[3]; // filtre

		$construct = "Bral_Palmares_".Bral_Util_String::firstToUpper($section);
		// verification que la classe existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($request, $view, $interne, $filtre);
		} else {
			throw new Zend_Exception("Bral_Palmares_Factory classe invalide: ".$construct);
		}
	}
	
	public static function getBoxesNaissance($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Naissancecomte");
		Zend_Loader::loadClass("Bral_Palmares_Naissancesexe");
		Zend_Loader::loadClass("Bral_Palmares_Naissancefamille");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Naissancefamille($request, $view, false);
		$retour[] = new Bral_Palmares_Naissancecomte($request, $view, true);
		$retour[] = new Bral_Palmares_Naissancesexe($request, $view, false);
		return $retour;
	}
}