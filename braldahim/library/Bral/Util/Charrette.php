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
			throw new Zend_Exception("Bral_Util_Charrette::calculCourrirChargerPossible idh:".$idHobbit);
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

	public static function calculAmeliorationsCharrette($idHobbit) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharretteMaterielAssemble");

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($idHobbit);
		$nb = count($charrettes);

		if ($nb == 1) {
			$charrette = $charrettes[0];
			$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
			$materielsAssembles = $charretteMaterielAssembleTable->findByIdCharrette($charrette["id_charrette"]);

			if ($materielsAssembles != null && count($materielsAssembles) > 0) {

				$durabiliteMaxCharrette = $charrette["durabilite_type_materiel"];
				$poidsTransportable = $charrette["capacite_type_materiel"];

				foreach($materielsAssembles as $m) {
					$durabiliteMaxCharrette = $durabiliteMaxCharrette + $m["durabilite_type_materiel"];
					$poidsTransportable = $poidsTransportable + $m["capacite_type_materiel"] - $m["poids_type_materiel"];
				}

				$data = array(
					"durabilite_max_charrette" => $durabiliteMaxCharrette,
					"poids_transportable_charrette" => $poidsTransportable,
				);
				$where = "id_charrette = ".$charrette["id_charrette"];
				$charretteTable->update($data, $where);
			}
		} else if ($nb > 1) {
			throw new Zend_Exception("Bral_Util_Charrette::calculAmeliorationsCharrette idh:".$idHobbit);
		}
	}

	public static function calculNouvelleDlaCharrette($idHobbit, $x, $y) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharretteMaterielAssemble");

		self::calculAmeliorationsCharrette($idHobbit);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($idHobbit);
		$nb = count($charrettes);

		if ($nb == 1) {
			$charrette = $charrettes[0];
			$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
			$materielsAssembles = $charretteMaterielAssembleTable->findByIdCharrette($charrette["id_charrette"]);

			if ($materielsAssembles != null && count($materielsAssembles) > 0) {

				$durabiliteActuelle = $charrette["durabilite_actuelle_charrette"] - $charrette["usure_type_materiel"];

				foreach($materielsAssembles as $m) {
					$durabiliteActuelle = $durabiliteActuelle - $m["usure_type_materiel"];
				}

				if ($durabiliteActuelle > $charrette["durabilite_max_charrette"]) {
					$durabiliteActuelle = $charrette["durabilite_max_charrette"];
				}

				$data = array(
					"durabilite_actuelle_charrette" => $durabiliteActuelle,
				);
				$where = "id_charrette = ".$charrette["id_charrette"];
				$charretteTable->update($data, $where);

				if ($durabiliteActuelle <= 0) {
					self::destructionCharrette($charrette, $x, $y);
				}
			}
		} else if ($nb > 1) {
			throw new Zend_Exception("Bral_Util_Charrette::calculNouvelleDlaCharrette idh:".$idHobbit);
		}
	}

	private static function destructionCharrette($charrette, $x, $y) {
		self::destructionCharretteElement($charrette, $x, $y);
		self::destructionCharretteAliment($charrette, $x, $y);
		self::destructionCharretteMateriel($charrette, $x, $y);
		self::destructionCharretteMinerai($charrette, $x, $y);
		self::destructionCharretteMunition($charrette, $x, $y);
		self::destructionCharrettePartieplante($charrette, $x, $y);
		self::destructionCharrettePotion($charrette, $x, $y);
		self::destructionCharretteRune($charrette, $x, $y);
		self::destructionCharretteTabac($charrette, $x, $y);

		Zend_Loader::loadClass("Charrette");
		$where = "id_charrette = ".$charrette["id_charrette"];
		$charretteTable = new Charrette();
		$charretteTable->delete($where);
	}

	private static function destructionCharretteElement($charrette, $x, $y) {
		Zend_Loader::loadClass("Element");
		$data = array(
			"quantite_viande_element" => $charrette["quantite_viande_charrette"],
			"quantite_peau_element" => $charrette["quantite_peau_charrette"],
			"quantite_viande_preparee_element" => $charrette["quantite_viande_preparee_charrette"],
			"quantite_cuir_element" => $charrette["quantite_cuir_charrette"],
			"quantite_castar_element" => $charrette["quantite_castar_charrette"],
			"quantite_fourrure_element" => $charrette["quantite_fourrure_charrette"],
			"quantite_planche_element" => $charrette["quantite_planche_charrette"],
			"quantite_rondin_element" => $charrette["quantite_rondin_charrette"],
			"x_element" => $x,
			"y_element" => $y,
		);
		$elementTable = new Element();
		$elementTable->insertOrUpdate($data);
	}

	private static function destructionCharretteAliment($charrette, $x, $y) {
		Zend_Loader::loadClass("CharretteAliment");
		Zend_Loader::loadClass("ElementAliment");
		
		$sourceTable = new CharretteAliment();
		$destinationTable = new ElementAliment();
		
		$sourceTable->findByIdHobbit($charrette["id_charrette"]);
		
	/*	$data = array(
			"id_element_aliment" => ,
			""
			);
				
			$destinationTable->insert($data);*/
	}

	private static function destructionCharretteEquipement($charrette, $x, $y) {
	}
	private static function destructionCharretteMateriel($charrette, $x, $y) {
	}
	private static function destructionCharretteMinerai($charrette, $x, $y) {
	}
	private static function destructionCharretteMunition($charrette, $x, $y) {
	}
	private static function destructionCharrettePartieplante($charrette, $x, $y) {
	}
	private static function destructionCharrettePotion($charrette, $x, $y) {
	}
	private static function destructionCharretteRune($charrette, $x, $y) {
	}
	private static function destructionCharretteTabac($charrette, $x, $y) {
	}

}