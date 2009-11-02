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

		foreach($equipementRunes as $e) {
			$tabEquipements[$e["id_equipement_rune"]]["runes"][] = array(
				"id_rune_equipement_rune" => $e["id_rune_equipement_rune"],
				"id_fk_type_rune" => $e["id_fk_type_rune"],
				"nom_type_rune" => $e["nom_type_rune"],
				"image_type_rune" => $e["image_type_rune"],
				"effet_type_rune" => $e["effet_type_rune"],
			);
		}
		unset($equipementRunes);
	}

	public static function populateBonus(&$tabEquipements, $tabWhere) {
		Zend_Loader::loadClass("EquipementBonus");
		$equipementBonusTable = new EquipementBonus();
		$equipementBonus = $equipementBonusTable->findByIdsEquipement($tabWhere);
		unset($equipementBonusTable);

		foreach($equipementBonus as $b) {
			$tabEquipements[$b["id_equipement_bonus"]]["bonus"] = $b;
		}
		unset($equipementBonus);
	}

	public static function getTabEmplacementsEquipement($idHobbit) {

		Zend_Loader::loadClass("TypeEmplacement");
		Zend_Loader::loadClass("HobbitEquipement");
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
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementPorteRowset = $hobbitEquipementTable->findByIdHobbit($idHobbit);
		unset($hobbitEquipementTable);

		$equipementPorte = null;

		if (count($equipementPorteRowset) > 0) {
			$tabWhere = null;
			$equipementRuneTable = new EquipementRune();
			$equipementBonusTable = new EquipementBonus();
			$equipements = null;

			$idEquipements = null;

			foreach ($equipementPorteRowset as $e) {
				$idEquipements[] = $e["id_equipement_hequipement"];
			}

			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);
			unset($equipementRuneTable);
			$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
			unset($equipementBonusTable);

			foreach ($equipementPorteRowset as $e) {
				$runes = null;
				if (count($equipementRunes) > 0) {
					foreach($equipementRunes as $r) {
						if ($r["id_equipement_rune"] == $e["id_equipement_hequipement"]) {
							$runes[] = array(
							"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
							"id_fk_type_rune" => $r["id_fk_type_rune"],
							"nom_type_rune" => $r["nom_type_rune"],
							"image_type_rune" => $r["image_type_rune"],
							"effet_type_rune" => $r["effet_type_rune"],
							);
						}
					}
				}

				$bonus = null;
				if (count($equipementBonus) > 0) {
					foreach($equipementBonus as $b) {
						if ($b["id_equipement_bonus"] == $e["id_equipement_hequipement"]) {
							$bonus = $b;
							break;
						}
					}
				}
					
				$equipement = array(
						"id_equipement" => $e["id_equipement_hequipement"],
						"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
						"nom_standard" => $e["nom_type_equipement"],
						"qualite" => $e["nom_type_qualite"],
						"emplacement" => $e["nom_type_emplacement"],
						"niveau" => $e["niveau_recette_equipement"],
						"id_type_equipement" => $e["id_type_equipement"],
						"id_type_emplacement" => $e["id_type_emplacement"],
						"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
						"nb_runes" => $e["nb_runes_equipement"],
						"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
						"armure" => $e["armure_equipement"],
						"force" => $e["force_equipement"],
						"agilite" => $e["agilite_equipement"],
						"vigueur" => $e["vigueur_equipement"],
						"sagesse" => $e["sagesse_equipement"],
						"vue" => $e["vue_recette_equipement"],
						"attaque" => $e["attaque_equipement"],
						"degat" => $e["degat_equipement"],
						"defense" => $e["defense_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"poids" => $e["poids_equipement"],
						"etat_courant" => $e["etat_courant_equipement"],
						"etat_initial" => $e["etat_initial_equipement"],
						"ingredient" => $e["nom_type_ingredient"],
						"runes" => $runes,
						"bonus" => $bonus,
				);
				$equipementPorte[] = $equipement;
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["affiche"] = "oui";
				$tabTypesEmplacement[$e["nom_systeme_type_emplacement"]]["equipementPorte"][] = $equipement;
			}
			unset($equipementPorteRowset);
		}
		return array ("equipementPorte" => $equipementPorte ,
					"tabTypesEmplacement" => $tabTypesEmplacement);
	}


	public static function calculNouvelleDlaEquipement($idHobbit, $x, $y) {
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("Equipement");

		$hobbitEquipementTable = new HobbitEquipement();
		$equipements = $hobbitEquipementTable->findByIdHobbit($idHobbit);

		$equipementTable = new Equipement();

		$retour = null;

		$nbAbime = 0;
		$texte = "";
		$texteDetruit = "";
		foreach($equipements as $e) {
			$etat = $e["etat_courant_equipement"] - 15;
			if ($etat <= 0) {
				$where = "id_equipement_hequipement =".$e["id_equipement_hequipement"];
				$hobbitEquipementTable->delete($where);
				self::destructionEquipement($e["id_equipement_hequipement"]);

				$texteDetruit .= "Votre équipement ".Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]);
				$texteDetruit .= " n&deg;".$e["id_equipement_hequipement"]." est détruit.<br>";
				$retour["detruit"] = $texteDetruit;

				$details = "[h".$idHobbit."] n'a pas réparé la pièce d'équipement n°".$id_equipement. ". Elle est détruite.";
				self::insertHistorique(self::HISTORIQUE_DESTRUCTION_ID, $e["id_equipement_hequipement"], $details);
			} else {
				$data = array("etat_courant_equipement" => $etat);
				$where = "id_equipement = ".$e["id_equipement_hequipement"];
				$equipementTable->update($data, $where);

				if ($etat <= 500) {
					$nbAbime = $nbAbime +1;
					$retour["abime"]["nb"] =  $nbAbime;
					$texte .= "Votre équipement ".Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]);
					$texte .= " n&deg;".$e["id_equipement_hequipement"]." est très abimé, état : ".$etat."/".$e["etat_initial_equipement"].".<br>";
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
}
