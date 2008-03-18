<?php

class Bral_Util_Commun {

	function __construct() {
	}
	
	public function getVueBase($x, $y) {
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
	
	public function getEnvironnement($x, $y) {
		Zend_Loader::loadClass('Zone');
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($x, $y);
		$zone = $zones[0];
		return $zone["nom_systeme_environnement"];
	}
	
	/*
	 * Mise à jour des évènements du hobbit.
	 */
	public function majEvenements($id_hobbit, $id_type_evenement, $details) {
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
	public function isRunePortee($idHobbit, $nomTypeRune) {
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
	
	public function getEquipementByNomSystemeMot($idHobbit, $nomSystemeMot) {
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
	
	public function ajouteEffetMotR($idHobbit) {
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
	
	public function retireEffetMotR($idHobbit) {
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
	
	public function getEffetMotA($idHobbit, $jetDegat) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_a");
		if ($equipement != null) {
			if ($jetDegat > $equipementCible["niveau_recette_equipement"]) {
				$jetDegat = $equipementCible["niveau_recette_equipement"];
			}
		}
		return $jetDegat;
	}
	
	public function getEffetMotD($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_d");
		$retour = 0;
		if ($equipement != null) {
			$retour = $equipementCible["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public function getEffetMotG($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_g");
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
	
	public function getEffetMotH($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_h");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}
	
	public function getEffetMotI($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_i");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipement["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public function getEffetMotJ($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_j");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipementCible["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public function getEffetMotL($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_l");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}
	
	public function getEffetMotQ($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_j");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipementCible["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public function getEffetMotX($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_x");
		$retour = false;
		if ($equipement != null) {
			$retour = true;
		}
		return $retour;
	}
}