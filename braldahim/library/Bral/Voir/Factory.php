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
class Bral_Voir_Factory {

	static function getAction($request, $view) {
		$matches = null;
		preg_match('/(.*)_voir_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$section = $matches[2];
		$construct = null;

		$construct = "Bral_Voir_".Bral_Util_String::firstToUpper($section);
		// verification que la classe existe.
		try {
			Zend_Loader::loadClass($construct);  
	    } catch(Exception $e) {
	  	  	throw new Zend_Exception("Bral_Voir_Factory Action invalide (classe): ".$construct);
	    }
	    
		if (class_exists($construct)) {
			return new $construct ($request, $view);
		} else {
			throw new Zend_Exception("Bral_Voir_Factory Classe invalide: ".$construct);
		}
	}
	
	static function getCommunaute($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Communaute");
		return new Bral_Voir_Communaute($request, $view);
	}

	static function getMateriel($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Materiel");
		return new Bral_Voir_Materiel($request, $view);
	}
	
	static function getEquipement($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Equipement");
		return new Bral_Voir_Equipement($request, $view);
	}

	static function getPotion($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Potion");
		return new Bral_Voir_Potion($request, $view);
	}
	
	static function getRune($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Rune");
		return new Bral_Voir_Rune($request, $view);
	}
	
	static function getHobbit($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Hobbit");
		return new Bral_Voir_Hobbit($request, $view);
	}
	
	static function getHobbits($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Hobbits");
		return new Bral_Voir_Hobbits($request, $view);
	}
	
	static function getCommunautes($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Communautes");
		return new Bral_Voir_Communautes($request, $view);
	}

	static function getMonstre($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Monstre");
		return new Bral_Voir_Monstre($request, $view);
	}
	
	static function getVue($request, $view) {
		Zend_Loader::loadClass("Bral_Voir_Vue");
		return new Bral_Voir_Vue($request, $view);
	}
}
