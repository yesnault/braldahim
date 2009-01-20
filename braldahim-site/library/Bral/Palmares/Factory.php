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
		
		$matches = null;
		preg_match('/(.*)_palmares_(.*)_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2]; // classe
		$filtre = (int)$matches[3]; // filtre

		$construct = "Bral_Palmares_".Bral_Util_String::firstToUpper($section);
		try {
			Zend_Loader::loadClass($construct);
		} catch (Zend_Exception $e) {
			throw new Zend_Exception("Bral_Palmares_Factory classe invalide 1: ".$construct);
		}
		
		// verification que la classe existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($request, $view, $interne, $filtre);
		} else {
			throw new Zend_Exception("Bral_Palmares_Factory classe invalide 2: ".$construct);
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
	
	public static function getBoxesCombattantspve($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspvetop10");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspvefamille");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspveniveau");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspvesexe");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Combattantspvetop10($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspvefamille($request, $view, true);
		$retour[] = new Bral_Palmares_Combattantspveniveau($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspvesexe($request, $view, false);
		return $retour;
	}
	
	public static function getBoxesCombattantspvp($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspvptop10");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspvpfamille");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspvpniveau");
		Zend_Loader::loadClass("Bral_Palmares_Combattantspvpsexe");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Combattantspvptop10($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspvpfamille($request, $view, true);
		$retour[] = new Bral_Palmares_Combattantspvpniveau($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspvpsexe($request, $view, false);
		return $retour;
	}
	
	public static function getBoxesMorts($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Mortstop10");
		Zend_Loader::loadClass("Bral_Palmares_Mortsfamille");
		Zend_Loader::loadClass("Bral_Palmares_Mortsniveau");
		Zend_Loader::loadClass("Bral_Palmares_Mortssexe");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Mortstop10($request, $view, false);
		$retour[] = new Bral_Palmares_Mortsfamille($request, $view, true);
		$retour[] = new Bral_Palmares_Mortsniveau($request, $view, false);
		$retour[] = new Bral_Palmares_Mortssexe($request, $view, false);
		return $retour;
	}
}