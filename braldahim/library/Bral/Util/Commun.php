<?php

class Bral_Util_Commun {

	private function __construct() {
	}
	
	public static function getVueBase($x, $y) {
		Zend_Loader::loadClass('Zone');
		
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($x, $y);
		$zone = $zones[0];

		$r = 0;
		switch($zone["nom_systeme_environnement"]) {
			case "marais":
				$r = 5;
				break;
			case "montagne":
				$r = 5;
				break;
			case "caverne":
				$r = 2;
				break;
			case "plaine" :
				$r = 6;
				break;
			case "foret" :
				$r = 4;
				break;
			default :
				throw new Exception("getVueBase Environnement invalide:".$zone["nom_systeme_environnement"]. " x=".$x." y=".$y);
		}
		return $r;
	}
	
	public static function getEnvironnement($x, $y) {
		Zend_Loader::loadClass('Zone');
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($x, $y);
		$zone = $zones[0];
		return $zone["nom_systeme_environnement"];
	}
	
	/*
	 * Mise à jour des évènements du hobbit.
	 */
	public static function majEvenements($id_hobbit, $id_type_evenement, $details) {
		Zend_Loader::loadClass('Evenement');

		$evenementTable = new Evenement();

		$data = array(
			'id_fk_hobbit_evenement' => $id_hobbit,
			'date_evenement' => date("Y-m-d H:i:s"),
			'id_fk_type_evenement' => $id_type_evenement,
			'details_evenement' => $details,
		);
		$evenementTable->insert($data);
	}

	/*
	 * Regarde si la rune de @param est portée
	 */
	public static function isRunePortee($idHobbit, $nomTypeRune) {
		$retour = false;
		Zend_Loader::loadClass("HobbitEquipement");
		$hobbitEquipementTable = new HobbitEquipement();
		$runesRowset = $hobbitEquipementTable->findRunesOnly($idHobbit);
		
		if ($runesRowset != null && count($runesRowset) > 0) {
			foreach ($runesRowset as $r) {
				if ($r["nom_type_rune"] == $nomTypeRune) {
					$retour = true;
					break;
				}
			}
		}
		return $retour;
	}
	
	public static function getEquipementByNomSystemeMot($idHobbit, $nomSystemeMot) {
		$retour = null;
		Zend_Loader::loadClass("HobbitEquipement");
		$hobbitEquipementTable = new HobbitEquipement();
		$equipementRowset = $hobbitEquipementTable->findByNomSystemeMot($idHobbit, $nomSystemeMot);
		
		if ($equipementRowset != null && count($equipementRowset) > 0) {
			foreach ($equipementRowset as $e) {
				$retour = $e;
				break;
			}
		}
		return $retour;
	}
	
	public static function calculPvMaxSansEffetMotE($config, $vigueur_base_hobbit, $pv_max_bm_hobbit) {
		// calcul des pvs restants avec la regeneration
		$pvMax = ($config->game->pv_base + $vigueur_base_hobbit * $config->game->pv_max_coef) + $pv_max_bm_hobbit;
		
		return $pvMax;
	}
	
	public static function ajouteEffetMotR($idHobbit) {
		Zend_Loader::loadClass("HobbitsCompetences");
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($idHobbit);
		foreach($hobbitCompetences as $c) {
			if ($c["type_competence"] == "metier") {
				$data = array("pourcentage_hcomp" => $c["pourcentage_hcomp"] + 2);
				$where = array("id_fk_hobbit_hcomp = ".intval($idHobbit). " AND id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]);
				$hobbitsCompetencesTables->update($data, $where);
			}
		}
	}
	
	public static function retireEffetMotR($idHobbit) {
		Zend_Loader::loadClass("HobbitsCompetences");
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($idHobbit);
		foreach($hobbitCompetences as $c) {
			if ($c["type_competence"] == "metier") {
				$data = array("pourcentage_hcomp" => $c["pourcentage_hcomp"] - 2);
				$where = array("id_fk_hobbit_hcomp = ".intval($idHobbit). " AND id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]);
				$hobbitsCompetencesTables->update($data, $where);
			}
		}
	}
	
	public static function getEffetMotA($idHobbit, $jetDegat) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_a");
		if ($equipement != null) {
			if ($jetDegat > $equipement["niveau_recette_equipement"]) {
				$jetDegat = $equipement["niveau_recette_equipement"];
			}
		}
		return $jetDegat;
	}
	
	public static function getEffetMotD($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_d");
		$retour = 0;
		if ($equipement != null) {
			$retour = $equipementCible["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public static function getEffetMotE($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_e");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipementCible["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public static function getEffetMotF($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_f");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public static function getEffetMotG($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_g");
		$retour = null;
		if ($equipement != null) {
			if ($equipement["bm_degat_recette_equipement"] < 0) {
				$retour = (- $equipement["bm_degat_recette_equipement"]) / 2; // le malus est divise par deux : on enleve la moitie
			} else {
				$retour = $equipement["bm_degat_recette_equipement"]; // double
			}
		}
		return $retour;
	}
	
	public static function getEffetMotH($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_h");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}
	
	public static function getEffetMotI($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_i");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public static function getEffetMotJ($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_j");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public static function getEffetMotL($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_l");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}

	public static function getEffetMotN($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_n");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"] * 2;
		}
		return $retour;
	}
	
	public static function getEffetMotO($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_o");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"] * 2;
		}
		return $retour;
	}
	
	public static function getEffetMotQ($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_q");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public static function getEffetMotS($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_s");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public static function getEffetMotX($idHobbit) {
		$equipement = self::getEquipementByNomSystemeMot($idHobbit, "mot_x");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}
	
	/*
	 * Lorqu'un Hobbit meurt il perd une partie de ces castars : 1/3 arr inférieur.
	 */
	public static function dropHobbitCastars($cible, $effetH = null) {
		if ($cible->castars_hobbit > 0) {
			$nbCastars = floor($cible->castars_hobbit / 3) + Bral_Util_De::get_1d5();
			
			if ($effetH != null && $effetH == true) { 
				$nbCastars = $nbCastars * 2;
			}
			
			Zend_Loader::loadClass("Castar");
			$castarTable = new Castar();
			$data = array(
				"x_castar"  => $cible->x_hobbit,
				"y_castar" => $cible->y_hobbit,
				"nb_castar" => $nbCastars,
			);
			$castarTable = new Castar();
			$castarTable->insertOrUpdate($data);
		}
		
		return $nbCastars;
	}
}