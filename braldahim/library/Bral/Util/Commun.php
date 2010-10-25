<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Commun {

	private function __construct() {
	}

	public static function getVueBase($x, $y, $z) {
		Zend_Loader::loadClass('Zone');
		
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($x, $y, $z);
		unset($zoneTable);
		$zone = $zones[0];
		unset($zones);
		
		Zend_Loader::loadClass("Bosquet");
		$bosquetTable = new Bosquet();
		$nombreBosquets = $bosquetTable->countByCase($x, $y, $z);

		if ($nombreBosquets >= 1) {
			$environnement = "bosquet";
		} else {
			$environnement = $zone["nom_systeme_environnement"];
		}
		
		$r = 0;
		switch($environnement) {
			case "marais":
				$r = 5;
				break;
			case "montagne":
				$r = 5;
				break;
			case "caverne":
				$r = 2;
				break;
			case "mine":
				$r = 2;
				break;
			case "plaine" :
				$r = 6;
				break;
			case "bosquet" :
				$r = 4;
				break;
			case "gazon" :
				$r = 6;
				break;
			default :
				throw new Exception("getVueBase Environnement invalide:".$zone["nom_systeme_environnement"]. " x=".$x." y=".$y);
		}
		unset($zone);
		return $r;
	}

	public static function getEnvironnement($x, $y, $z) {
		Zend_Loader::loadClass('Zone');
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($x, $y, $z);
		unset($zoneTable);
		$zone = $zones[0];
		unset($zones);
		return $zone["nom_systeme_environnement"];
	}

	/*
	 * Regarde si la rune de @param est portée
	 */
	public static function isRunePortee($idBraldun, $nomTypeRune) {
		$retour = false;
		Zend_Loader::loadClass("BraldunEquipement");
		$braldunEquipementTable = new BraldunEquipement();
		$runesRowset = $braldunEquipementTable->findRunesOnly($idBraldun);
		unset($braldunEquipementTable);

		if ($runesRowset != null && count($runesRowset) > 0) {
			foreach ($runesRowset as $r) {
				if ($r["nom_type_rune"] == $nomTypeRune) {
					$retour = true;
					break;
				}
			}
			unset($runesRowset);
		}
		return $retour;
	}

	public static function getEquipementByNomSystemeMot($idBraldun, $nomSystemeMot) {
		$retour = null;
		Zend_Loader::loadClass("BraldunEquipement");
		$braldunEquipementTable = new BraldunEquipement();
		$equipementRowset = $braldunEquipementTable->findByNomSystemeMot($idBraldun, $nomSystemeMot);
		unset($braldunEquipementTable);

		if ($equipementRowset != null && count($equipementRowset) > 0) {
			foreach ($equipementRowset as $e) {
				$retour = $e;
				break;
			}
			unset($equipementRowset);
		}
		return $retour;
	}

	public static function calculPvMaxBaseSansEffetMotE($config, $vigueur_base_braldun) {
		// calcul des pvs restants avec la regeneration
		return ($config->game->pv_base + $vigueur_base_braldun * $config->game->pv_max_coef);
	}

	public static function calculArmureNaturelle($forceBase, $vigueurBase) {
		return intval(($forceBase + $vigueurBase) / 5) + 2;
	}

	public static function ajouteEffetMotR($idBraldun) {
		Zend_Loader::loadClass("BraldunsCompetences");
		$braldunsCompetencesTables = new BraldunsCompetences();
		$braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($idBraldun);
		unset($braldunsCompetencesTables);

		foreach($braldunCompetences as $c) {
			if ($c["type_competence"] == "metier") {
				$data = array("pourcentage_hcomp" => $c["pourcentage_hcomp"] + 2);
				$where = array("id_fk_braldun_hcomp = ".intval($idBraldun). " AND id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]);
				$braldunsCompetencesTables->update($data, $where);
			}
		}
		unset($braldunCompetences);
	}

	public static function retireEffetMotR($idBraldun) {
		Zend_Loader::loadClass("BraldunsCompetences");
		$braldunsCompetencesTables = new BraldunsCompetences();
		$braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($idBraldun);
		foreach($braldunCompetences as $c) {
			if ($c["type_competence"] == "metier") {
				$data = array("pourcentage_hcomp" => $c["pourcentage_hcomp"] - 2);
				$where = array("id_fk_braldun_hcomp = ".intval($idBraldun). " AND id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]);
				$braldunsCompetencesTables->update($data, $where);
			}
		}
	}

	public static function getEffetMotA($idBraldun, $jetDegat) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_a");
		if ($equipement != null) {
			if ($jetDegat > 5 * $equipement["niveau_recette_equipement"]) {
				$jetDegat = 5 * $equipement["niveau_recette_equipement"];
			}
		}
		return $jetDegat;
	}

	public static function getEffetMotD($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_d");
		$retour = 0;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}

	public static function getEffetMotE($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_e");
		$retour = null;
		if ($equipement != null) {
			$retour = 10 * $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}

	public static function getEffetMotF($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_f");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}

	public static function getEffetMotG($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_g");
		$retour = null;
		if ($equipement != null) {
			if ($equipement["degat_equipement"] < 0) {
				$retour = (- $equipement["degat_equipement"]) / 2; // le malus est divise par deux : on enleve la moitie
			} else {
				$retour = $equipement["degat_equipement"]; // double
			}
		}
		return $retour;
	}

	public static function getEffetMotH($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_h");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}

	public static function getEffetMotI($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_i");
		$retour = null;
		if ($equipement != null) {
			$retour = - (3 * $equipement["niveau_recette_equipement"]);
		}
		return $retour;
	}

	public static function getEffetMotJ($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_j");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}

	public static function getEffetMotL($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_l");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}

	public static function getEffetMotN($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_n");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"] * 2;
		}
		return $retour;
	}

	public static function getEffetMotO($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_o");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"] * 2;
		}
		return $retour;
	}

	public static function getEffetMotQ($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_q");
		$retour = null;
		if ($equipement != null) {
			$retour = - (5 * $equipement["niveau_recette_equipement"]);
		}
		return $retour;
	}

	public static function getEffetMotS($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_s");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}

	public static function getEffetMotX($idBraldun) {
		$equipement = self::getEquipementByNomSystemeMot($idBraldun, "mot_x");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}

	/*
	 * Lorqu'un Braldûn meurt il perd une partie de ces castars : 1/3 arr inférieur.
	 */
	public static function dropBraldunCastars($cible, $effetH, $idButin) {
		$nbCastars = 0;

		if ($cible->castars_braldun > 0) {
			$nbCastars = floor($cible->castars_braldun / 3);
				
			if ($effetH != null && $effetH == true) {
				$nbCastars = $nbCastars * 2;
			}
				
			if ($nbCastars > 0 && $cible->castars_braldun >= $nbCastars) {
				Zend_Loader::loadClass("Element");
				$elementTable = new Element();
				$data = array(
					"quantite_castar_element" => $nbCastars,
					"x_element" => $cible->x_braldun,
					"y_element" => $cible->y_braldun,
					"id_fk_butin_element" => $idButin,
				);
				$elementTable->insertOrUpdate($data);

			}
		}

		return $nbCastars;
	}

	public static function getPourcentage($competence, $config) {
		if ($competence["nb_tour_restant_bonus_tabac_hcomp"] > 0) {
			$pourcentage = $competence["pourcentage_hcomp"]."% + ".$config->game->tabac->bonus." (tabac)";
		} else if ($competence["nb_tour_restant_malus_tabac_hcomp"] > 0) {
			$pourcentage = $competence["pourcentage_hcomp"]."% - ".$config->game->tabac->malus." (tabac)";
		} else {
			$pourcentage = $competence["pourcentage_hcomp"];
		}
		if ($pourcentage > 100) {
			$pourcentage = 100;
		}
		return $pourcentage;
	}
}