<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Charrette {

	function __construct() {
	}

	public static function calculAttraperPossible($charrette, $braldun, $estMenuisierOuBucheron) {

		$tabRetour["possible"] = false;
		$tabRetour["detail"] = "";

		if (($braldun->force_base_braldun >= $charrette["force_base_min_type_materiel"] &&
		$braldun->agilite_base_braldun >= $charrette["agilite_base_min_type_materiel"] &&
		$braldun->vigueur_base_braldun >= $charrette["vigueur_base_min_type_materiel"] &&
		$braldun->sagesse_base_braldun >= $charrette["sagesse_base_min_type_materiel"]) ||
		($charrette["nom_systeme_type_materiel"] == "charrette_legere" && $estMenuisierOuBucheron)
		) {
			$tabRetour["possible"] = true;
		} else {
			if ($braldun->force_base_braldun < $charrette["force_base_min_type_materiel"]) {
				$tabRetour["detail"] .= " Niv. requis FOR:".$charrette["force_base_min_type_materiel"];
			}
			if ($braldun->agilite_base_braldun < $charrette["agilite_base_min_type_materiel"]) {
				$tabRetour["detail"] .= " Niv. requis AGI:".$charrette["agilite_base_min_type_materiel"];
			}
			if ($braldun->sagesse_base_braldun < $charrette["sagesse_base_min_type_materiel"]) {
				$tabRetour["detail"] .= " Niv. requis SAG:".$charrette["sagesse_base_min_type_materiel"];
			}
			if ($braldun->vigueur_base_braldun < $charrette["vigueur_base_min_type_materiel"]) {
				$tabRetour["detail"] .= " Niv. requis VIG:".$charrette["vigueur_base_min_type_materiel"];
			}
		}

		return $tabRetour;
	}

	public static function calculCourrirChargerPossible($idBraldun) {
		$retour = false;

		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharretteMaterielAssemble");

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($idBraldun);
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
			throw new Zend_Exception("Bral_Util_Charrette::calculCourrirChargerPossible idh:".$idBraldun);
		} else {
			$retour = true;
		}

		return $retour;
	}

	public static function possedeCaleFrein($idCharrette) {
		return self::possedeElement($idCharrette, "cale_frein");
	}
	
	public static function possedeSabot($idCharrette) {
		return self::possedeElement($idCharrette, "sabot");
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

	public static function calculAmeliorationsCharrette($idBraldun) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharretteMaterielAssemble");

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($idBraldun);
		$nb = count($charrettes);

		if ($nb == 1) {
			$charrette = $charrettes[0];
			$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
			$materielsAssembles = $charretteMaterielAssembleTable->findByIdCharrette($charrette["id_charrette"]);

			$durabiliteMaxCharrette = $charrette["durabilite_type_materiel"];
			$poidsTransportable = $charrette["capacite_type_materiel"];

			if ($materielsAssembles != null && count($materielsAssembles) > 0) {
				foreach($materielsAssembles as $m) {
					$durabiliteMaxCharrette = $durabiliteMaxCharrette + $m["durabilite_type_materiel"];
					$poidsTransportable = $poidsTransportable + $m["capacite_type_materiel"];
				}
			}
				
			$data = array(
					"durabilite_max_charrette" => $durabiliteMaxCharrette,
					"poids_transportable_charrette" => $poidsTransportable,
			);

			$where = "id_charrette = ".$charrette["id_charrette"];
			$charretteTable->update($data, $where);

			// mise à jour du poids en base
			Zend_Loader::loadClass("Bral_Util_Poids");
			Bral_Util_Poids::calculPoidsCharrette($idBraldun, true);
		} else if ($nb > 1) {
			throw new Zend_Exception("Bral_Util_Charrette::calculAmeliorationsCharrette idh:".$idBraldun);
		}
	}

	public static function calculNouvelleDlaCharrette($idBraldun, $niveauBraldun, $x, $y, $z) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharretteMaterielAssemble");
		Zend_Loader::loadClass("Bral_Util_Poids");

		self::calculAmeliorationsCharrette($idBraldun);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($idBraldun);
		$nb = count($charrettes);

		$estDetruite = false;

		if ($nb == 1) {
			$charrette = $charrettes[0];
			$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
			$materielsAssembles = $charretteMaterielAssembleTable->findByIdCharrette($charrette["id_charrette"]);

			$durabiliteActuelle = $charrette["durabilite_actuelle_charrette"] - $charrette["usure_type_materiel"];

			if ($materielsAssembles != null && count($materielsAssembles) > 0) {
				foreach($materielsAssembles as $m) {
					$durabiliteActuelle = $durabiliteActuelle - $m["usure_type_materiel"];
				}
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
				self::destructionCharrette($idBraldun, $niveauBraldun, $charrette, $x, $y, $z);
				$estDetruite = true;
			}
		} else if ($nb > 1) {
			throw new Zend_Exception("Bral_Util_Charrette::calculNouvelleDlaCharrette idh:".$idBraldun);
		}

		return $estDetruite;
	}

	private static function destructionCharrette($idBraldun, $niveauBraldun, $charrette, $x, $y, $z) {
		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

		self::destructionCharretteElement($charrette, $x, $y, $z, $dateFin);
		self::destructionCharretteAliment($charrette, $x, $y, $z, $dateFin);
		self::destructionCharretteMateriel($charrette, $x, $y, $z, $dateFin);
		self::destructionCharretteMinerai($charrette, $x, $y, $z, $dateFin);
		self::destructionCharretteMunition($charrette, $x, $y, $z, $dateFin);
		self::destructionCharrettePartieplante($charrette, $x, $y, $z, $dateFin);
		self::destructionCharrettePotion($charrette, $x, $y, $z, $dateFin);
		self::destructionCharretteRune($charrette, $x, $y, $z, $dateFin);
		self::destructionCharretteTabac($charrette, $x, $y, $z, $dateFin);

		Zend_Loader::loadClass("Charrette");
		$where = "id_charrette = ".$charrette["id_charrette"];
		$charretteTable = new Charrette();
		$charretteTable->delete($where);
		
		$config = Zend_Registry::get('config');
		
		$detailEvenement = "[b".$idBraldun."] a détruit la [t".$charrette["id_charrette"]."]";
		$detailBotCible = "Vous avez détruit la charrette n°".$charrette["id_charrette"].PHP_EOL;
		$detailBotCible .= "Tout ce qu'elle pouvait contenir est tombé au sol".

		Zend_Loader::loadClass("Bral_Util_Evenement");
		Bral_Util_Evenement::majEvenements($idBraldun, $config->game->evenements->type->special, $detailEvenement, $detailBotCible, $niveauBraldun);
		
		Zend_Loader::loadClass("Bral_Util_Materiel");
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_DETRUIRE_ID, $charrette["id_charrette"], $detailEvenement);
	}

	private static function destructionCharretteElement($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("Element");
		$data = array(
			"quantite_peau_element" => $charrette["quantite_peau_charrette"],
			"quantite_cuir_element" => $charrette["quantite_cuir_charrette"],
			"quantite_castar_element" => $charrette["quantite_castar_charrette"],
			"quantite_fourrure_element" => $charrette["quantite_fourrure_charrette"],
			"quantite_planche_element" => $charrette["quantite_planche_charrette"],
			"quantite_rondin_element" => $charrette["quantite_rondin_charrette"],
			"x_element" => $x,
			"y_element" => $y,
			"z_element" => $z,
		);
		$elementTable = new Element();
		$elementTable->insertOrUpdate($data);
	}

	private static function destructionCharretteAliment($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharretteAliment");
		Zend_Loader::loadClass("ElementAliment");

		$charretteAlimentTable = new CharretteAliment();
		$elementAlimentTable = new ElementAliment();

		$charretteAliments = $charretteAlimentTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charretteAliments as $a) {
			$data = array(
				"x_element_aliment" => $x,
				"y_element_aliment" => $y,
				"z_element_aliment" => $z,
				"id_element_aliment" => $a["id_charrette_aliment"],
				"date_fin_element_aliment" => $dateFin,
			);
			$elementAlimentTable->insert($data);
		}
	}

	private static function destructionCharretteEquipement($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharretteEquipement");
		Zend_Loader::loadClass("ElementEquipement");

		$charretteEquipementTable = new CharretteEquipement();
		$elementEquipementTable = new ElementEquipement();

		$charretteEquipements = $charretteEquipementTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charretteEquipements as $a) {
			$data = array(
				"x_element_equipement" => $x,
				"y_element_equipement" => $y,
				"z_element_equipement" => $z,
				"id_element_equipement" => $a["id_charrette_equipement"],
				"date_fin_element_equipement" => $dateFin,
			);
			$elementEquipementTable->insert($data);
		}
	}

	private static function destructionCharretteMateriel($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharretteMateriel");
		Zend_Loader::loadClass("ElementMateriel");

		$charretteMaterielTable = new CharretteMateriel();
		$elementMaterielTable = new ElementMateriel();

		$charretteMateriels = $charretteMaterielTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charretteMateriels as $a) {
			$data = array(
				"x_element_materiel" => $x,
				"y_element_materiel" => $y,
				"z_element_materiel" => $z,
				"id_element_materiel" => $a["id_charrette_materiel"],
				"date_fin_element_materiel" => $dateFin,
			);
			$elementMaterielTable->insert($data);
		}
	}

	private static function destructionCharretteMinerai($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharretteMinerai");
		Zend_Loader::loadClass("ElementMinerai");

		$charretteMineraiTable = new CharretteMinerai();
		$elementMineraiTable = new ElementMinerai();

		$charretteMinerais = $charretteMineraiTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charretteMinerais as $a) {
			$data = array(
				"x_element_minerai" => $x,
				"y_element_minerai" => $y,
				"z_element_minerai" => $z,
				"id_fk_type_element_minerai" => $a["id_fk_type_charrette_minerai"],
				"quantite_brut_element_minerai" => $a["quantite_brut_charrette_minerai"],
				"quantite_lingots_element_minerai" => $a["quantite_lingots_charrette_minerai"],
				"date_fin_element_minerai" => $dateFin,
			);
			$elementMineraiTable->insert($data);
		}
	}

	private static function destructionCharretteMunition($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharretteMunition");
		Zend_Loader::loadClass("ElementMunition");

		$charretteMunitionTable = new CharretteMunition();
		$elementMunitionTable = new ElementMunition();

		$charretteMunitions = $charretteMunitionTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charretteMunitions as $a) {
			$data = array(
				"x_element_munition" => $x,
				"y_element_munition" => $y,
				"z_element_munition" => $z,
				"id_fk_type_element_munition" => $a["id_fk_type_charrette_munition"],
				"quantite_feuille_element_munition" => $a["quantite_feuille_charrette_munition"],
				"date_fin_element_munition" => $dateFin,
			);
			$elementMunitionTable->insert($data);
		}
	}

	private static function destructionCharrettePartieplante($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharrettePartieplante");
		Zend_Loader::loadClass("ElementPartieplante");

		$charrettePartieplanteTable = new CharrettePartieplante();
		$elementPartieplanteTable = new ElementPartieplante();

		$charrettePartieplantes = $charrettePartieplanteTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charrettePartieplantes as $a) {
			$data = array(
				"x_element_partieplante" => $x,
				"y_element_partieplante" => $y,
				"z_element_partieplante" => $z,
				"id_fk_type_element_partieplante" => $a["id_fk_type_charrette_partieplante"],
				"id_fk_type_plante_element_partieplante" => $a["id_fk_type_plante_charrette_partieplante"],
				"quantite_element_partieplante" => $a["quantite_charrette_partieplante"],
				"quantite_preparee_element_partieplante" => $a["quantite_preparee_charrette_partieplante"],
				"date_fin_element_partieplante" => $dateFin,
			);
			$elementPartieplanteTable->insert($data);
		}
	}

	private static function destructionCharrettePotion($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharrettePotion");
		Zend_Loader::loadClass("ElementPotion");

		$charrettePotionTable = new CharrettePotion();
		$elementPotionTable = new ElementPotion();

		$charrettePotions = $charrettePotionTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charrettePotions as $a) {
			$data = array(
				"x_element_potion" => $x,
				"y_element_potion" => $y,
				"z_element_potion" => $z,
				"id_element_potion" => $a["id_charrette_potion"],
				"date_fin_element_potion" => $dateFin,
			);
			$elementPotionTable->insert($data);
		}
	}

	private static function destructionCharretteRune($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharretteRune");
		Zend_Loader::loadClass("ElementRune");

		$charretteRuneTable = new CharretteRune();
		$elementRuneTable = new ElementRune();

		$charretteRunes = $charretteRuneTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charretteRunes as $a) {
			$data = array(
				"x_element_rune" => $x,
				"y_element_rune" => $y,
				"z_element_rune" => $z,
				"id_rune_element_rune" => $a["id_rune_charrette_rune"],
				"date_fin_element_rune" => $dateFin,
			);
			$elementRuneTable->insert($data);
		}
	}

	private static function destructionCharretteTabac($charrette, $x, $y, $z, $dateFin) {
		Zend_Loader::loadClass("CharretteTabac");
		Zend_Loader::loadClass("ElementTabac");

		$charretteTabacTable = new CharretteTabac();
		$elementTabacTable = new ElementTabac();

		$charretteTabacs = $charretteTabacTable->findByIdCharrette($charrette["id_charrette"]);

		foreach($charretteTabacs as $a) {
			$data = array(
				"x_element_tabac" => $x,
				"y_element_tabac" => $y,
				"z_element_tabac" => $z,
				"id_fk_type_element_tabac" => $a["id_fk_type_charrette_tabac"],
				"quantite_feuille_element_tabac" => $a["quantite_feuille_charrette_tabac"],
				"date_fin_element_tabac" => $dateFin,
			);
			$elementTabacTable->insert($data);
		}
	}

}