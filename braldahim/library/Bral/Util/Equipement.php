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
class Bral_Util_Equipement {

	const HISTORIQUE_CREATION_ID = 1;
	const HISTORIQUE_SERTIR_ID = 2;
	const HISTORIQUE_EQUIPER_ID = 3;
	const HISTORIQUE_DESTRUCTION_ID = 4;
	const HISTORIQUE_VERNIR_ID = 5;
	const HISTORIQUE_REPARER_ID = 6;
	const HISTORIQUE_ACHETER_ID = 7;
	const HISTORIQUE_VENDRE_ID = 8;
	const HISTORIQUE_TRANSBAHUTER_ID = 9;

	public static function getNomByIdRegion($typeEquipement, $idRegion) {
		$template = "";
		if (isset($typeEquipement["vernis_template_equipement"])) {
			$template = " [".$typeEquipement["vernis_template_equipement"]."]";
		}

		switch($idRegion) {
			case 1:
				return $typeEquipement["region_1_nom_type_equipement"].$template;
				break;
			case 2:
				return $typeEquipement["region_2_nom_type_equipement"].$template;
				break;
			case 3:
				return $typeEquipement["region_3_nom_type_equipement"].$template;
				break;
			case 4:
				return $typeEquipement["region_4_nom_type_equipement"].$template;
				break;
			case 5:
				return $typeEquipement["region_5_nom_type_equipement"].$template;
				break;
			default:
				throw new Zend_Exception("Bral_Util_Equipement::getNomByIdRegion Region invalide id:".$idRegion);
				break;
		}
	}

	public static function insertEquipementBonus($idEquipement, $niveauEquipement, $idRegion) {

		$data["id_equipement_bonus"] = $idEquipement;
		$retour = "";

		switch($idRegion) {
			case 1:
				$data["sagesse_equipement_bonus"] = floor(Bral_Util_De::getLanceDeSpecifique($niveauEquipement+1, 1, 2));
				if ($data["sagesse_equipement_bonus"] < 1) $data["sagesse_equipement_bonus"] = 1;
				$retour = " Sagesse + ".$data["sagesse_equipement_bonus"];
				break;
			case 2:
				$data["agilite_equipement_bonus"] = floor(Bral_Util_De::getLanceDeSpecifique($niveauEquipement+1, 1, 2));
				if ($data["agilite_equipement_bonus"] < 1) $data["agilite_equipement_bonus"] = 1;
				$retour = " Agilité + ".$data["agilite_equipement_bonus"];
				break;
			case 3:
				$data["force_equipement_bonus"] = floor(Bral_Util_De::getLanceDeSpecifique($niveauEquipement+1, 1, 2));
				if ($data["force_equipement_bonus"] < 1) $data["force_equipement_bonus"] = 1;
				$retour = " Force + ".$data["force_equipement_bonus"];
				break;
			case 4:
				$data["armure_equipement_bonus"] = floor(Bral_Util_De::getLanceDeSpecifique($niveauEquipement+1, 1, 2) / 2);
				if ($data["armure_equipement_bonus"] < 1) $data["armure_equipement_bonus"] = 1;
				$retour = " Armure + ".$data["armure_equipement_bonus"];
				break;
			case 5:
				$data["vigueur_equipement_bonus"] = floor(Bral_Util_De::getLanceDeSpecifique($niveauEquipement+1, 1, 2));
				if ($data["vigueur_equipement_bonus"] < 1) $data["vigueur_equipement_bonus"] = 1;
				$retour = " Vigueur + ".$data["vigueur_equipement_bonus"];
				break;
			case -1 : // donjon
				break;
			default:
				throw new Zend_Exception("Bral_Util_Equipement::getNomByIdRegion Region invalide id:".$idRegion);
				break;
		}

		Zend_Loader::loadClass("EquipementBonus");
		$equipementBonusTable = new EquipementBonus();
		$equipementBonusTable->insert($data);
		return $retour;
	}

	public static function populateRune(&$tabEquipements, $tabWhere) {
		Zend_Loader::loadClass("EquipementRune");
		$equipementRuneTable = new EquipementRune();
		$equipementRunes = $equipementRuneTable->findByIdsEquipement($tabWhere);
		unset($equipementRuneTable);

		if ($equipementRunes != null) {
			foreach($equipementRunes as $e) {
				$tabEquipements[$e["id_equipement_rune"]]["runes"][] = array(
				"id_rune_equipement_rune" => $e["id_rune_equipement_rune"],
				"id_fk_type_rune" => $e["id_fk_type_rune"],
				"nom_type_rune" => $e["nom_type_rune"],
				"image_type_rune" => $e["image_type_rune"],
				"effet_type_rune" => $e["effet_type_rune"],
				);
			}
		}
		unset($equipementRunes);
	}

	public static function populateBonus(&$tabEquipements, $tabWhere) {
		Zend_Loader::loadClass("EquipementBonus");
		$equipementBonusTable = new EquipementBonus();
		$equipementBonus = $equipementBonusTable->findByIdsEquipement($tabWhere);
		unset($equipementBonusTable);

		if ($equipementBonus != null) {
			foreach($equipementBonus as $b) {
				$tabEquipements[$b["id_equipement_bonus"]]["bonus"] = $b;
			}
		}
		unset($equipementBonus);
	}

	public static function getTabEmplacementsEquipement($idBraldun, $niveauBraldun) {

		Zend_Loader::loadClass("TypeEmplacement");
		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("EquipementRune");
		Zend_Loader::loadClass("EquipementBonus");

		// on va chercher les emplacements
		$tabTypesEmplacement = null;
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacement = $typeEmplacementTable->fetchAll(null, "ordre_emplacement");
		unset($typeEmplacementTable);
		$typesEmplacement = $typesEmplacement->toArray();

		foreach ($typesEmplacement as $t) {

			if ($t["est_equipable_type_emplacement"] == "oui") {
				$affiche = "oui";
				$position = "gauche";
				if ($t["nom_systeme_type_emplacement"] == "deuxmains" ||
				$t["nom_systeme_type_emplacement"] == "mains" ||
				$t["nom_systeme_type_emplacement"] == "maingauche" ||
				$t["nom_systeme_type_emplacement"] == "maindroite") {
					$affiche = "non";
					$position = "droite";
				}
					
				$tabTypesEmplacement[$t["nom_systeme_type_emplacement"]] = array(
						"id_type_emplacement" => $t["id_type_emplacement"],
						"nom_type_emplacement" => $t["nom_type_emplacement"],
						"ordre_emplacement" => $t["ordre_emplacement"],
						"equipementPorte" => null,
						"affiche" => $affiche,
						"position" => $position,
				);
			}
		}
		unset($typesEmplacement);

		// on va chercher l'équipement porté
		$tabEquipementPorte = null;
		$braldunEquipementTable = new BraldunEquipement();
		$equipementPorteRowset = $braldunEquipementTable->findByIdBraldun($idBraldun);
		$tabEquipementPorte = Bral_Util_Equipement::prepareTabEquipements($equipementPorteRowset, false, $niveauBraldun);
		unset($braldunEquipementTable);

		$equipementPorte = null;

		if (count($tabEquipementPorte) > 0) {
			$equipements = null;

			foreach ($tabEquipementPorte as $e) {
				$equipementPorte[] = $e;
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["affiche"] = "oui";
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementPorte"][] = $e;
			}
			unset($equipementPorteRowset);
		}
		return array ("equipementPorte" => $equipementPorte ,
					"tabTypesEmplacement" => $tabTypesEmplacement);
	}

	/*
	 * 33% chance d'avoir une usure prématurée quand on prend un coup sur l'une de ses pièce équipée.
	 * - On tire au hasard une des pièce que le Braldun a déquipée.
	 * - Elle s'use de 1D10+5 immédiatement.
	 *
	 * return le nom de la pièce d'équipement abimée ou null sinon
	 */
	public static function usureAttaquePiece($idBraldun) {

		$chance = Bral_Util_De::get_1D100();
		if ($chance > 34) {
			return null;
		}

		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("Equipement");

		$braldunEquipementTable = new BraldunEquipement();
		$equipements = $braldunEquipementTable->findByIdBraldun($idBraldun);

		if(count($equipements) > 0) {
			shuffle($equipements);

			$e = $equipements[0];

			$usure = Bral_Util_De::get_1D10() + 5;
			$etat = $e["etat_courant_equipement"] - $usure;

			if ($etat < 1) {
				$etat = 1;
			}
			$data = array("etat_courant_equipement" => $etat);
			$where = "id_equipement = ".$e["id_equipement_hequipement"];
			$equipementTable = new Equipement();
			$equipementTable->update($data, $where);

			$nom = Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]);
			$nom .= " n&deg;".$e["id_equipement_hequipement"];
			$nom .= " usure choc:-".$usure. " etat:".$etat."/".$e["etat_initial_equipement"];
			return $nom;
		} else {
			return null;
		}
	}

	public static function calculNouvelleDlaEquipement($idBraldun, $x, $y) {
		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("Equipement");

		$braldunEquipementTable = new BraldunEquipement();
		$equipements = $braldunEquipementTable->findByIdBraldun($idBraldun);

		$equipementTable = new Equipement();

		$retour = null;

		$nbAbime = 0;
		$texte = "";
		$texteDetruit = "";
		foreach($equipements as $e) {
			$etat = $e["etat_courant_equipement"] - 15;
			if ($etat <= 0) {
				$where = "id_equipement_hequipement =".$e["id_equipement_hequipement"];
				$braldunEquipementTable->delete($where);
				self::destructionEquipement($e["id_equipement_hequipement"]);

				$texteDetruit .= "Votre équipement ".Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]);
				$texteDetruit .= " n&deg;".$e["id_equipement_hequipement"]." est détruit.<br />";
				$retour["detruit"] = $texteDetruit;

				$details = "[b".$idBraldun."] n'a pas réparé la pièce d'équipement n°".$e["id_equipement_hequipement"]. ". Elle est détruite.";
				self::insertHistorique(self::HISTORIQUE_DESTRUCTION_ID, $e["id_equipement_hequipement"], $details);
			} else {
				$data = array("etat_courant_equipement" => $etat);
				$where = "id_equipement = ".$e["id_equipement_hequipement"];
				$equipementTable->update($data, $where);

				if ($etat <= 500) {
					$nbAbime = $nbAbime +1;
					$retour["abime"]["nb"] =  $nbAbime;
					$texte .= "Votre équipement ".Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]);
					$texte .= " n&deg;".$e["id_equipement_hequipement"]." est très abimé, état : ".$etat."/".$e["etat_initial_equipement"].".<br />";
					$retour["abime"]["texte"] = $texte;
				}
			}
		}

		return $retour;
	}

	public static function destructionEquipement($idEquipement) {
		Zend_Loader::loadClass("EquipementBonus");
		$equipementBonusTable = new EquipementBonus();
		$where = "id_equipement_bonus=".$idEquipement;
		$equipementBonusTable->delete($where);

		Zend_Loader::loadClass("EquipementRune");
		$equipementRuneTable = new EquipementRune();
		$where = "id_equipement_rune=".$idEquipement;
		$equipementRuneTable->delete($where);

		Zend_Loader::loadClass("Equipement");
		$equipementTable = new Equipement();
		$where = "id_equipement =".$idEquipement;
		$equipementTable->delete($where);

	}

	public static function insertHistorique($idTypeHistoriqueEquipement, $idEquipement, $details) {
		Zend_Loader::loadClass("Bral_Util_Lien");
		$detailsTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($details);

		Zend_Loader::loadClass('HistoriqueEquipement');
		$historiqueEquipementTable = new HistoriqueEquipement();

		$data = array(
			'date_historique_equipement' => date("Y-m-d H:i:s"),
			'id_fk_type_historique_equipement' => $idTypeHistoriqueEquipement,
			'id_fk_historique_equipement' => $idEquipement,
			'details_historique_equipement' => $detailsTransforme,
		);
		$historiqueEquipementTable->insert($data);
	}

	public static function prepareTabEquipements($equipementsRowset, $filtreEquipable = false, $niveauBraldun = null) {

		$filtreEquipableAFaire = false;
		if ($filtreEquipable == true && $niveauBraldun != null) {
			$filtreEquipableAFaire = true;
		}

		$idEquipements = null;
		$tabEquipements = null;

		foreach ($equipementsRowset as $e) {

			if ($filtreEquipableAFaire == false ||
			($filtreEquipableAFaire == true && $e["est_equipable_type_emplacement"] == "oui" && floor($niveauBraldun / 10) >= $e["niveau_recette_equipement"])) {
					
				$equipement = array(
					"id_equipement" => $e["id_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"id_type_equipement" => $e["id_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"emplacement" => $e["nom_type_emplacement"],
					"niveau" => $e["niveau_recette_equipement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"nom_systeme_type_piece" => $e["nom_systeme_type_piece"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nb_runes" => $e["nb_runes_equipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
					"armure" => self::calculBmSet($e, 'armure_equipement', $niveauBraldun),
					"force" => self::calculBmSet($e, 'force_equipement', $niveauBraldun),
					"agilite" => self::calculBmSet($e, 'agilite_equipement', $niveauBraldun),
					"vigueur" => self::calculBmSet($e, 'vigueur_equipement', $niveauBraldun),
					"sagesse" => self::calculBmSet($e, 'sagesse_equipement', $niveauBraldun),
					"vue" => $e["vue_recette_equipement"],
					"attaque" => self::calculBmSet($e, 'attaque_equipement', $niveauBraldun),
					"degat" => self::calculBmSet($e, 'degat_equipement', $niveauBraldun),
					"defense" => self::calculBmSet($e, 'defense_equipement', $niveauBraldun),
					"suffixe" => $e["suffixe_mot_runique"],
					"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"],
					"id_fk_region" => $e["id_fk_region_equipement"],
					"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
					"etat_courant" => $e["etat_courant_equipement"],
					"etat_initial" => $e["etat_initial_equipement"],
					"ingredient" => $e["nom_type_ingredient"],
					"poids" => $e["poids_equipement"],
					"runes" => array(),
					"bonus" => array(),
				);

				$idEquipements[] = $e["id_equipement"];
				$tabEquipements[$e["id_equipement"]] = $equipement;
			}
		}

		if ($idEquipements != null) {
			Bral_Util_Equipement::populateRune($tabEquipements, $idEquipements);
			Bral_Util_Equipement::populateBonus($tabEquipements, $idEquipements);
		}

		return $tabEquipements;
	}

	private static function calculBmSet($equipement, $key, $niveauBraldun) {
		if ($equipement["id_fk_donjon_type_equipement"] != null && $niveauBraldun != null) {
			return $equipement[$key] * intval($niveauBraldun / 10);
		} else {
			return $equipement[$key];
		}
	}

	public static function possedeEquipement($idBraldun, $idEquipement) {
		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("CharretteEquipement");
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("LabanEquipement");

		$table = new BraldunEquipement();
		$equipement = $table->findByIdBraldun($idBraldun, $idEquipement);
		if ($equipement != null) {
			return true;
		}

		$table = new LabanEquipement();
		$equipement = $table->findByIdBraldun($idBraldun, $idEquipement);
		if ($equipement != null) {
			return true;
		}

		$table = new CharretteEquipement();
		$equipement = $table->findByIdBraldun($idBraldun, $idEquipement);
		if ($equipement != null) {
			return true;
		}

		$table = new EchoppeEquipement();
		$equipement = $table->findByIdEchoppe($idBraldun, null, $idEquipement);
		if ($equipement != null) {
			return true;
		}

		return false;
	}
}
