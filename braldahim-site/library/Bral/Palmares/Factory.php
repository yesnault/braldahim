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
class Bral_Palmares_Factory {
	
	static function getBox($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		
		$matches = null;
		preg_match('/(.*)_palmares_(.*)_(.*)_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2]; // classe
		$filtre = (int)$matches[3]; // filtre
		$type = $matches[4]; // type

		$construct = "Bral_Palmares_".Bral_Util_String::firstToUpper($section);
		try {
			Zend_Loader::loadClass($construct);
		} catch (Zend_Exception $e) {
			throw new Zend_Exception("Bral_Palmares_Factory classe invalide 1: ".$construct);
		}
		
		// verification que la classe existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($request, $view, $interne, $filtre, $type);
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
		$retour[] = new Bral_Palmares_Naissancecomte($request, $view, false);
		$retour[] = new Bral_Palmares_Naissancesexe($request, $view, false);
		$view->titre = "Naissance";
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
		$retour[] = new Bral_Palmares_Combattantspvefamille($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspveniveau($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspvesexe($request, $view, false);
		$view->titre = "Combattants PVE";
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
		$retour[] = new Bral_Palmares_Combattantspvpfamille($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspvpniveau($request, $view, false);
		$retour[] = new Bral_Palmares_Combattantspvpsexe($request, $view, false);
		$view->titre = "Combattants PVP";
		return $retour;
	}
	
	public static function getBoxesKo($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Kotop10");
		Zend_Loader::loadClass("Bral_Palmares_Kofamille");
		Zend_Loader::loadClass("Bral_Palmares_Koniveau");
		Zend_Loader::loadClass("Bral_Palmares_Kosexe");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Kotop10($request, $view, false);
		$retour[] = new Bral_Palmares_Kofamille($request, $view, false);
		$retour[] = new Bral_Palmares_Koniveau($request, $view, false);
		$retour[] = new Bral_Palmares_Kosexe($request, $view, false);
		$view->titre = "KO";
		return $retour;
	}
	
	public static function getBoxesExperience($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Experiencetop10");
		Zend_Loader::loadClass("Bral_Palmares_Experiencefamille");
		Zend_Loader::loadClass("Bral_Palmares_Experienceniveau");
		Zend_Loader::loadClass("Bral_Palmares_Experiencesexe");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Experiencetop10($request, $view, false);
		$retour[] = new Bral_Palmares_Experiencefamille($request, $view, false);
		$retour[] = new Bral_Palmares_Experienceniveau($request, $view, false);
		$retour[] = new Bral_Palmares_Experiencesexe($request, $view, false);
		$view->titre = "Expérience";
		return $retour;
	}
	
	public static function getBoxesMonstres($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Monstrestop10");
		Zend_Loader::loadClass("Bral_Palmares_Monstrestype");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Monstrestop10($request, $view, false);
		$retour[] = new Bral_Palmares_Monstrestype($request, $view, false);
		$view->titre = "Montres";
		return $retour;
	}
	
	public static function getBoxesSuperhobbits($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Superhobbits");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Superhobbits($request, $view, false, -1);
		$view->titre = "Super Hobbits";
		return $retour;
	}
	
	public static function getBoxesRecolteurs($request, $view, $type) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Recolteurstop10");
		Zend_Loader::loadClass("Bral_Palmares_Recolteursfamille");
		Zend_Loader::loadClass("Bral_Palmares_Recolteursniveau");
		Zend_Loader::loadClass("Bral_Palmares_Recolteurssexe");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Recolteurstop10($request, $view, false, 1, $type);
		$retour[] = new Bral_Palmares_Recolteursfamille($request, $view, true, 1, $type);
		$retour[] = new Bral_Palmares_Recolteursniveau($request, $view, false, 1, $type);
		$retour[] = new Bral_Palmares_Recolteurssexe($request, $view, false, 1, $type);
		$view->titre = "Récolteurs";
		
		switch($type) {
			case "mineurs":
				$view->titre .= " - Mineurs";
				break;
			case "herboristes":
				$view->titre .= " - Herboristes";
				break;
			case "bucherons":
				$view->titre .= " - Bûcherons";
				break;
			case "chasseurs":
				$view->titre .= " - Chasseurs";
				break;
		}
		
		return $retour;
	}
	
	public static function getBoxesFabricants($request, $view, $type) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Fabricantstop10");
		Zend_Loader::loadClass("Bral_Palmares_Fabricantsfamille");
		Zend_Loader::loadClass("Bral_Palmares_Fabricantsniveau");
		Zend_Loader::loadClass("Bral_Palmares_Fabricantssexe");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Fabricantstop10($request, $view, false, 1, $type);
		$retour[] = new Bral_Palmares_Fabricantsfamille($request, $view, true, 1, $type);
		$retour[] = new Bral_Palmares_Fabricantsniveau($request, $view, false, 1, $type);
		$retour[] = new Bral_Palmares_Fabricantssexe($request, $view, false, 1, $type);
		$view->titre = "Fabricants";
		
		switch($type) {
			case "apothicaires":
				$view->titre .= " - Apothicaires";
				break;
			case "menuisiers":
				$view->titre .= " - Menuisiers";
				break;
			case "forgerons":
				$view->titre .= " - Forgerons";
				break;
			case "tanneurs":
				$view->titre .= " - Tanneurs";
				break;
			case "bucherons":
				$view->titre .= " - Bûcherons";
				break;
			case "terrassiers":
				$view->titre .= " - Terrassier";
				break;
			case "cuisiniers":
				$view->titre .= " - Cuisinier";
				break;
		}
		
		
		return $retour;
	}
	
	public static function getBoxesRunes($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Runestype");
		Zend_Loader::loadClass("Bral_Palmares_Runescategorie");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Runestype($request, $view, false, 2);
		$retour[] = new Bral_Palmares_Runescategorie($request, $view, false, 2);
		$view->titre = "Runes";
		return $retour;
	}
	
	public static function getBoxesMotsRuniques($request, $view) {
		Zend_Loader::loadClass("Bral_Palmares_Box");
		Zend_Loader::loadClass("Bral_Palmares_Motsruniquesmot");
		Zend_Loader::loadClass("Bral_Palmares_Motsruniquestypepiece");
		Zend_Loader::loadClass("Bral_Palmares_Motsruniquesniveaupiece");
		
		$retour = null;
		$retour[] = new Bral_Palmares_Motsruniquesmot($request, $view, false, 1);
		$retour[] = new Bral_Palmares_Motsruniquestypepiece($request, $view, false, 1);
		$retour[] = new Bral_Palmares_Motsruniquesniveaupiece($request, $view, false, 1);
		$view->titre = "Mots Runiques";
		return $retour;
	}
}