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
class Bral_Util_Charrette {

	function __construct() {
	}

	public static function calculCourrirChargerPossible($idHobbit) {
		$retour = false;

		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharretteMaterielAssemble");
		
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($idHobbit);
		$nb = count($charrettes);

		if ($nb == 1) {
			$charrette = $charrettes[0];
			$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
			$materielsAssembles = $charretteMaterielAssembleTable->findByIdCharrette($charrette["id_charrette"]);
			
			//Si on a : Lanière en cuir, Bache en fourrure, 
			//cerclage en fer et 
			// Essieu en métal alors il est possible de courir et charger avec sa charrette.												
			$possedeLaniere = false;
			$possedeCerclage = false;
			$possedeBache = false;
			$possedeEssieu = false;
			
			if (count($materielsAssembles) > 0) {
				foreach($materielsAssembles as $m) {
					if ($m["nom_systeme_type_materiel"] == "lanieres_cuir") {
						$possedeLaniere = true;
					} else if ($m["nom_systeme_type_materiel"] == "cerclage_fer") {
						$possedeCerclage = true;
					} else if ($m["nom_systeme_type_materiel"] == "bache_fourrure") {
						$possedeBache = true;
					} else if ($m["nom_systeme_type_materiel"] == "essieu_metal") {
						$possedeEssieu = true;
					}
				}
			}
			
			if ($possedeLaniere && $possedeCerclage && $possedeBache && $possedeEssieu) {
				$retour = true;
			}
		} else if ($nb > 1) {
			throw new Zend_Exception("Bral_Util_Charrette::calculCourrirChargerPossible idh:".$this->view->user->id_hobbit);
		} else {
			$retour = true;
		}

		return $retour;
	}
	
	public static function possedeCaleFrein($idCharrette) {
		return self::possedeElement($idCharrette, "cale_frein");
	}

	public static function possedePanneauAmovible($idCharrette) {
		return self::possedeElement($idCharrette, "panneau_amovible");
	}
	
	private static function possedeElement($idCharrette, $nomSystemeElement) {
		$retour = false;
		Zend_Loader::loadClass("CharretteMaterielAssemble");
		$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
		
		$materielsAssembles = $charretteMaterielAssembleTable->findByIdCharrette($idCharrette);
		
		if ($materielsAssembles != null && count($materielsAssembles) > 0) {
			foreach($materielsAssembles as $m) {
				if ($m["nom_systeme_type_materiel"] == $nomSystemeElement) {
					$retour = true;
					break;
				}
			}	
		}
		return $retour;
	}
}