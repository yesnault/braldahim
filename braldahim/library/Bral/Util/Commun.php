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
	
	public function calculPvMaxSansEffetMotE($config, $vigueur_base_hobbit, $pv_max_bm_hobbit) {
		// calcul des pvs restants avec la regeneration
		$pvMax = ($config->game->pv_base + $vigueur_base_hobbit * $config->game->pv_max_coef) + $pv_max_bm_hobbit;
		
		return $pvMax;
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
	
	public function getEffetMotE($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_e");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipementCible["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public function getEffetMotF($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_f");
		$retour = null;
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

	public function getEffetMotN($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_n");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipementCible["niveau_recette_equipement"] * 2;
		}
		return $retour;
	}
	
	public function getEffetMotO($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_o");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipementCible["niveau_recette_equipement"] * 2;
		}
		return $retour;
	}
	
	public function getEffetMotQ($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_q");
		$retour = null;
		if ($equipement != null) {
			$retour = - $equipementCible["niveau_recette_equipement"];
		}
		return $retour;
	}
	
	public function getEffetMotS($idHobbit) {
		$equipement = $this->getEquipementByNomSystemeMot($idHobbit, "mot_s");
		$retour = null;
		if ($equipement != null) {
			$retour = $equipementCible["niveau_recette_equipement"];
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
	
	public function calculDegatCase($config, $hobbit, $degats) {
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit);
		
		$retour["hobbitMorts"] = null;
		$retour["hobbitTouches"] = null;
		
		foreach($hobbits as $h) {
			$retour["hobbitTouches"][] = $h;
			
			$id_type = $config->game->evenements->type->effet;
			$details = $hobbit->prenom_hobbit ." ". $hobbit->nom_hobbit ." (".$hobbit->id_hobbit.") N".$hobbit->niveau_hobbit." a attaqu&eacute; le hobbit ".$h["prenom_hobbit"] ." ". $h["nom_hobbit"] ." (".$h["id_hobbit"].") N".$h["niveau_hobbit"];  
			$this->majEvenements($hobbit->id_hobbit, $id_type, $details);
			$this->majEvenements($h["id_hobbit"], $id_type, $details);
			
			$h["pv_restant_hobbit"] = $h["pv_restant_hobbit"] - $soins;
			if ($h["pv_restant_hobbit"] > 0) {
				$data = array("pv_restant_hobbit" => $h["pv_restant_hobbit"]);
				$where = "id_hobbit = ".$h["id_hobbit"];
				$hobbitTable->update($data, $where);
			} else { // mort
				$retour["hobbitMorts"][] = $h;
				
				$hobbit->nb_kill_hobbit = $hobbit->nb_kill_hobbit + 1;
				$data = array("nb_kill_hobbit" => $hobbit->nb_kill_hobbit);
				$where = "id_hobbit = ".$hobbit->id_hobbit;
				$hobbitTable->update($data, $where);
				
				$effetH = $commun->getEffetMotH($hobbit->id_hobbit);
				if ($effetH == true) {					
					$this->view->effetMotH = true;
				}
				$nbCastars = $this->dropHobbitCastars($h, $effetH);
				
				$h["est_mort_hobbit"] = "oui";
				$h["castars_hobbit"] = $h["castars_hobbit"] - $nbCastars;
				if ($h["castars_hobbit"] < 0) {
					$h["castars_hobbit"] = 0;
				}
				
				$data = array(
					'castars_hobbit' => $h["castars_hobbit"],
					'pv_restant_hobbit' => 0,
					'est_mort_hobbit' => "oui",
					'nb_mort_hobbit' => $h["nb_mort_hobbit"] + 1,
					'date_fin_tour_hobbit' => date("Y-m-d H:i:s"),
				);
				$where = "id_hobbit=".$hobbit->id_hobbit;
				$hobbitTable->update($data, $where);
				
				$id_type = $config->game->evenements->type->kill;
				$details = $hobbit->prenom_hobbit ." ". $hobbit->nom_hobbit ." (".$hobbit->id_hobbit.") N".$hobbit->niveau_hobbit." a tué le hobbit ".$h["prenom_hobbit"] ." ". $h["nom_hobbit"] ." (".$h["id_hobbit"].") N".$h["niveau_hobbit"]; 
				$this->majEvenements($this->view->user->id_hobbit, $id_type, $details);
				$id_type = $config->evenements->type->mort;
				$this->majEvenements($h["id_hobbit"], $id_type, $details);
			}
		}
		
		return $retour;
	}
	
	public function calculSoinCase($config, $hobbit, $soins) {
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit);
		
		$retour["hobbitsSoignes"] = null;
		foreach($hobbits as $h) {
			$retour["hobbitsSoignes"][] = $h;
			if ($h["pv_max_hobbit"] >  $h["pv_restant_hobbit"]) {
				$h["pv_restant_hobbit"] = $h["pv_restant_hobbit"] + $soins;
				if ($h["pv_restant_hobbit"] > $h["pv_max_hobbit"]) {
					$h["pv_restant_hobbit"] = $h["pv_max_hobbit"];
					
					$data = array("pv_restant_hobbit" => $h["pv_restant_hobbit"]);
					
					$where = "id_hobbit = ".$h["id_hobbit"];
					$hobbitTable->update($data, $where);
					
					$id_type = $config->game->evenements->type->effet;
					$details = $hobbit->prenom_hobbit ." ". $hobbit->nom_hobbit ." (".$hobbit->id_hobbit.") N".$hobbit->niveau_hobbit." a soign&eacute; le hobbit ".$h["prenom_hobbit"] ." ". $h["nom_hobbit"] ." (".$h["id_hobbit"].") N".$h["niveau_hobbit"];  
					$this->majEvenements($hobbit->id_hobbit, $id_type, $details);
					$this->majEvenements($h["id_hobbit"], $id_type, $details);
				}
			}
		}
		return $retour;
	}
	
	public function dropHobbitCastars($cible, $effetH = null) {
		//Lorqu'un Hobbit meurt il perd une partie de ces castars : 1/3 arr inférieur.
		if ($cible["castars_hobbit"] > 0) {
			$nbCastars = floor($cible["castars_hobbit"] / 3) + Bral_Util_De::get_1d5();
			
			if ($effetH != null && $effetH == true) { 
				$nbCastars = $nbCastars * 2;
			}
			
			Zend_Loader::loadClass("Castar");
			$castarTable = new Castar();
			$data = array(
				"x_castar"  => $cible["x_cible"],
				"y_castar" => $cible["y_cible"],
				"nb_castar" => $nbCastars,
			);
			$castarTable = new Castar();
			$castarTable->insertOrUpdate($data);
		}
		
		return $nbCastars;
	}
}