<?php

class Bral_Util_Attaque {

	public static function attaqueHobbit(&$hobbitAttaquant, $hobbitCible, $jetAttaquant, $jetCible, $jetsDegat, $effetMotSPossible = true) {
		$config = Zend_Registry::get('config');
		$attaqueReussie = false;
		
		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		$retourAttaque["critique"]  = false;
		
		$retourAttaque["effetMotD"] = false;
		$retourAttaque["effetMotE"] = false;
		$retourAttaque["effetMotG"] = false;
		$retourAttaque["effetMotH"] = false;
		$retourAttaque["effetMotI"] = false;
		$retourAttaque["effetMotJ"] = false;
		$retourAttaque["effetMotL"] = false;
		$retourAttaque["effetMotQ"] = false;
		$retourAttaque["effetMotS"] = false;
		
		$cible = array('nom_cible' => $hobbitCible->prenom_hobbit ." ". $hobbitCible->nom_hobbit, 'id_cible' => $hobbitCible->id_hobbit, 'x_cible' => $hobbitCible->x_hobbit, 'y_cible' => $hobbitCible->y_hobbit,'niveau_cible' => $hobbitCible->niveau_hobbit);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) {
			$retourAttaque["attaqueReussie"] = true;
			
			if ($retourAttaque["jetAttaquant"] / 2 > $retourAttaque["jetCible"]) {
				if (Bral_Util_Commun::getEffetMotX($hobbitCible->id_hobbit) == true) {
					$retourAttaque["critique"]  = false;
				} else {
					$retourAttaque["critique"]  = true;
				}
			}
			
			if ($retourAttaque["critique"] == true) {
				$retourAttaque["jetDegat"] = $jetsDegat["critique"];
			} else {
				$retourAttaque["jetDegat"] = $jetsDegat["noncritique"];
			}
			
			$retourAttaque["jetDegat"] = Bral_Util_Commun::getEffetMotA($hobbitCible->id_hobbit, $retourAttaque["jetDegat"]);
			
			$effetMotE = Bral_Util_Commun::getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotE"] = true;
				$gainPv = ($retourAttaque["jetDegat"] / 2);
				if ($gainPv > $effetMotE * 3) {
					$gainPv = $effetMotE * 3;
				}
				$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_restant_hobbit + $gainPv;
				if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit) {
					$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit;
				}
			}
			
			$effetMotG = Bral_Util_Commun::getEffetMotG($hobbitAttaquant->id_hobbit);
			if ($effetMotG != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotG"] = true;
				$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
			}
			
			$effetMotI = Bral_Util_Commun::getEffetMotI($hobbitAttaquant->id_hobbit);
			if ($effetMotI != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotI"] = true;
				$hobbitCible->regeneration_malus_hobbit = $hobbitCible->regeneration_malus_hobbit + $effetMotI;
			}
			
			$effetMotJ = Bral_Util_Commun::getEffetMotJ($hobbitAttaquant->id_hobbit);
			if ($effetMotJ != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotJ"] = true;
				$hobbitCible->vue_malus_hobbit = $hobbitCible->vue_malus_hobbit+ $effetMotJ;
			}
			
			$hobbitCible->vue_bm_hobbit = $hobbitCible->vue_bm_hobbit + $hobbitCible->vue_malus_hobbit;
			
			$effetMotQ = Bral_Util_Commun::getEffetMotQ($hobbitAttaquant->id_hobbit);
			if ($effetMotQ != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotQ"]= true;
				$hobbitCible->agilite_malus_hobbit = $hobbitCible->agilite_malus_hobbit + $effetMotQ;
			}
			
			$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit + $hobbitCible->agilite_malus_hobbit;
			
			$pv = ($hobbitCible->pv_restant_hobbit + $hobbitCible->bm_defense_hobbit) - $retourAttaque["jetDegat"];
			$nb_mort = $hobbitCible->nb_mort_hobbit;
			if ($pv <= 0) {
				$pv = 0;
				$mort = "oui";
				$nb_mort = $nb_mort + 1;
				$hobbitAttaquant->nb_kill_hobbit = $hobbitAttaquant->nb_kill_hobbit + 1;
				
				$effetH = Bral_Util_Commun::getEffetMotH($hobbitAttaquant->id_hobbit);
				if ($effetH == true) {					
					if ($effetMotSPossible == false) $retourAttaque["effetMotH"] = true;
				}
				
				if (Bral_Util_Commun::getEffetMotL($hobbitAttaquant->id_hobbit) == true) {
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					if ($effetMotSPossible == false) $retourAttaque["effetMotL"] = true;
				}
				
				$retourAttaque["mort"] = true;
				$nbCastars = Bral_Util_Commun::dropHobbitCastars($hobbitCible, $effetH);
				$hobbitCible->castars_hobbit = $hobbitCible->castars_hobbit - $nbCastars;
				if ($hobbitCible->castars_hobbit < 0) {
					$hobbitCible->castars_hobbit = 0;
				}
			} else {
				if ($effetMotSPossible == true) {
					$effetMotS = Bral_Util_Commun::getEffetMotS($hobbitAttaquant->id_hobbit);
					if ($effetMotS != null) {
						$retourAttaque["effetMotS"] = true;
						$retourAttaque["retourAttaqueEffetMotS"] = $this->attaqueHobbit($hobbitCible, $hobbitAttaquant->id_hobbit, false);
					}
				}
				
				$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit - $hobbitCible->niveau_hobbit;
				$mort = "non";
				$retourAttaque["mort"] = false;
				$retourAttaque["fragilisee"] = true;
			}
			$data = array(
				'castars_hobbit' => $hobbitCible->castars_hobbit,
				'pv_restant_hobbit' => $pv,
				'est_mort_hobbit' => $mort,
				'nb_mort_hobbit' => $nb_mort,
				'date_fin_tour_hobbit' => date("Y-m-d H:i:s"),
				'regeneration_malus_hobbit' => $hobbitCible->regeneration_malus_hobbit,
				'vue_bm_hobbit' => $hobbitCible->vue_bm_hobbit,
				'vue_malus_hobbit' => $hobbitCible->vue_malus_hobbit,
				'agilite_bm_hobbit' => $hobbitCible->agilite_bm_hobbit,
				'agilite_malus_hobbit' => $hobbitCible->agilite_malus_hobbit,
			);
			$where = "id_hobbit=".$hobbitCible->id_hobbit;
			$hobbitTable = new Hobbit();
			$hobbitTable->update($data, $where);
		} else if ($retourAttaque["jetCible"] / 2 < $retourAttaque["jetAttaquant"]) {
			$cible["agilite_bm_hobbit"] = $cible["agilite_bm_hobbit"] - ( floor($cible["niveau_hobbit"] / 10) + 1 );
			$data = array('agilite_bm_hobbit' => $cible["agilite_bm_hobbit"]);
			$where = "id_hobbit=".$cible["id_cible"];
			$hobbitTable->update($data, $where);
			$retourAttaque["mort"] = false;
			$retourAttaque["fragilisee"] = true;
		}

		$id_type = $config->game->evenements->type->attaquer;
		$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a attaqué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"]."";
		Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
		Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details);

		if ($retourAttaque["mort"] === true) {
			$id_type = $config->game->evenements->type->kill;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a tué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
			$id_type = $config->game->evenements->type->mort;
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details);
		}
		return $retourAttaque;
	}
	
	public static function attaqueMonstre(&$hobbitAttaquant, $idMonstre, $jetAttaquant, $jetCible, $jetsDegat) {
		$config = Zend_Registry::get('config');

		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["jetCible"] = $jetCible;
		
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		$retourAttaque["critique"] = false;
		
		$retourAttaque["effetMotD"] = false;
		$retourAttaque["effetMotE"] = false;
		$retourAttaque["effetMotG"] = false;
		$retourAttaque["effetMotH"] = false;
		$retourAttaque["effetMotI"] = false;
		$retourAttaque["effetMotJ"] = false;
		$retourAttaque["effetMotL"] = false;
		$retourAttaque["effetMotQ"] = false;
		$retourAttaque["effetMotS"] = false;
		
		$retourAttaque["attaqueReussie"] = false;
		
		if ($monstre["genre_type_monstre"] == 'feminin') {
			$m_taille = $monstre["nom_taille_f_monstre"];
		} else {
			$m_taille = $monstre["nom_taille_m_monstre"];
		}
		
		$cible = array('nom_cible' => $monstre["nom_type_monstre"]." ".$m_taille, 'id_cible' => $monstre["id_monstre"], 'niveau_cible' => $monstre["niveau_monstre"],  'x_cible' => $monstre["x_monstre"], 'y_cible' => $monstre["y_monstre"]);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) {
			$retourAttaque["attaqueReussie"] = true;
			
			if ($retourAttaque["jetAttaquant"] / 2 > $retourAttaque["jetCible"]) {
				$retourAttaque["critique"]  = true;
			}
			
			if ($retourAttaque["critique"] == true) {
				$retourAttaque["jetDegat"] = $jetsDegat["critique"];
			} else {
				$retourAttaque["jetDegat"] = $jetsDegat["noncritique"];
			}
			
			$effetMotE = Bral_Util_Commun::getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotE"] = true;
				$gainPv = ($retourAttaque["jetDegat"] / 2);
				if ($gainPv > $effetMotE * 3) {
					$gainPv = $effetMotE * 3;
				}
				
				$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_restant_hobbit	+ $hobbitAttaquant->pv_max_hobbit;
				if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit) {
					$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit;
				}
			}
			
			$effetMotG = Bral_Util_Commun::getEffetMotG($hobbitAttaquant->id_hobbit);
			if ($effetMotG != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotG"] = true;
				$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
			}
			
			$effetMotI = Bral_Util_Commun::getEffetMotI($hobbitAttaquant->id_hobbit);
			if ($effetMotI != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotI"] = true;
				$monstre["regeneration_malus_monstre"] = $monstre["regeneration_malus_monstre"] + $effetMotI;
			}
			
			$effetMotJ = Bral_Util_Commun::getEffetMotJ($hobbitAttaquant->id_hobbit);
			if ($effetMotJ != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotJ"] = true;
				$monstre["vue_malus_monstre"] = $monstre["vue_malus_monstre"] + $effetMotJ;
			}
			
			$effetMotQ = Bral_Util_Commun::getEffetMotQ($hobbitAttaquant->id_hobbit);
			if ($effetMotQ != null) {
				if ($effetMotSPossible == false) $retourAttaque["effetMotQ"] = true;
				$monstre["agilite_malus_monstre"] = $monstre["agilite_malus_monstre"] + $effetMotQ;
			}
			
			$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] + $monstre["agilite_malus_monstre"];
			
			$pv = $monstre["pv_restant_monstre"] - $retourAttaque["jetDegat"];
			
			if ($pv <= 0) {
				$effetD = null;
				$effetH = null;
				
				$effetD = Bral_Util_Commun::getEffetMotD($hobbitAttaquant->id_hobbit);
				if ($effetD != 0) {					
					if ($effetMotSPossible == false) $retourAttaque["effetMotD"]= true;
				}
				
				$effetH = Bral_Util_Commun::getEffetMotH($hobbitAttaquant->id_hobbit);
				if ($effetH == true) {					
					if ($effetMotSPossible == false) $retourAttaque["effetMotH"] = true;
				}
				
				if (Bral_Util_Commun::getEffetMotL($hobbitAttaquant->id_hobbit) == true) {
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					if ($effetMotSPossible == false) $retourAttaque["effetMotL"] = true;
				}

				$retourAttaque["mort"] = true;
				$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
				$vieMonstre->mortMonstreDb($cible["id_cible"], $effetD, $effetH);
			} else {
				$agilite_bm_monstre = $monstre["agilite_bm_monstre"] - $monstre["niveau_monstre"];
				$retourAttaque["fragilisee"] = true;
				
				$retourAttaque["mort"] = false;
				$data = array(
					'pv_restant_monstre' => $pv,
					'agilite_bm_monstre' => $agilite_bm_monstre,
					'regeneration_malus_monstre' => $monstre["regeneration_malus_monstre"],
					'vue_malus_monstre' => $monstre["vue_malus_monstre"],
					'agilite_bm_monstre' => $monstre["agilite_bm_monstre"],
					'agilite_malus_monstre' => $monstre["agilite_malus_monstre"],
				);
				$where = "id_monstre=".$cible["id_cible"];
				$monstreTable = new Monstre();
				$monstreTable->update($data, $where);
			}
		} else if ($retourAttaque["jetCible"] / 2 < $retourAttaque["jetAttaquant"]) {
			$agilite_bm_monstre = $monstre["agilite_bm_monstre"] - ( floor($monstre["niveau_monstre"] / 10) + 1 );
			$retourAttaque["mort"] = false;
			$data = array('agilite_bm_monstre' => $agilite_bm_monstre);
			$where = "id_monstre=".$cible["id_cible"];
			$monstreTable->update($data, $where);
			$retourAttaque["fragilisee"] = true;
		}

		$id_type = $config->game->evenements->type->attaquer;
		$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a attaqué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
		Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
		Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details, "monstre");
		
		if ($retourAttaque["mort"] === true) {
			$id_type = $config->game->evenements->type->kill;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a tué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			Bral_Util_Evenement::majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
			$id_type = $config->game->evenements->type->mort;
			Bral_Util_Evenement::majEvenements($cible["id_cible"], $id_type, $details, "monstre");
		}
		return $retourAttaque;
	}
	
	public static function calculJetCibleHobbit($hobbitCible) {
		$config = Zend_Registry::get('config');
		$jetCible = 0;
		for ($i=1; $i<=$config->base_agilite + $hobbitCible->agilite_base_hobbit; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$jetCible = $jetCible + $hobbitCible->agilite_bm_hobbit;
		return $jetCible;
	}
	
	public static function calculJetCibleMonstre($monstre) {
		$config = Zend_Registry::get('config');
		$jetCible = 0;
		for ($i=1; $i <= $monstre["agilite_base_monstre"]; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$jetCible = $jetCible + $monstre["agilite_bm_monstre"];
		return $jetCible;
	}
	
	public static function calculJetAttaqueNormale($hobbit) {
		$config = Zend_Registry::get('config');
		$jetAttaquant = 0;
		for ($i=1; $i<=$config->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = $jetAttaquant + $hobbit->agilite_bm_hobbit;
		return $jetAttaquant;
	}
	
	public static function calculDegatAttaqueNormale($hobbit) {
		$config = Zend_Registry::get('config');
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;
		
		for ($i=1; $i<= ($config->game->base_force + $hobbit->force_base_hobbit) * $coefCritique; $i++) {
			$jetDegat["critique"] = $jetDegat["critique"] + Bral_Util_De::get_1d6();
		}
		
		for ($i=1; $i<= ($config->game->base_force + $hobbit->force_base_hobbit); $i++) {
			$jetDegat["noncritique"] = $jetDegat["noncritique"] + Bral_Util_De::get_1d6();
		}
		
		$jetDegat["critique"] = $jetDegat["critique"] + $hobbit->force_bm_hobbit + $hobbit->bm_degat_hobbit;
		$jetDegat["noncritique"] = $jetDegat["noncritique"] + $hobbit->force_bm_hobbit + $hobbit->bm_degat_hobbit;
		return $jetDegat;
	}
	
	public static function calculDegatCase($config, $hobbit, $degats) {
		Zend_Loader::loadClass("Monstre");
		$retour["hobbitMorts"] = null;
		$retour["hobbitTouches"] = null;
		$retour["monstreMorts"] = null;
		$retour["monstreTouches"] = null;
		$retour["n_cible"] = 0;
		$this->calculDegatCaseHobbit($config, $hobbit, $degats, $retour);
		$this->calculDegatCaseMonstre($config, $hobbit, $degats, $retour);
		$retour["n_cible"] = count($retour["hobbitTouches"]) + count($retour["monstreTouches"]);
		return $retour;
	}
	
	public static function calculDegatCaseHobbit($config, $hobbit, $degats, &$retour) {
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->id_hobbit);
		
		$jetsDegat["critique"] = $jetsDegat;
		$jetsDegat["noncritique"] = $jetsDegat;
		$jetAttaquant = 1;
		$jetCible = 0;
		
		$i = 0;
		foreach($hobbits as $h) {
			/*$hobbitRowset = $hobbitTable->find($idHobbitCible);
			$hobbitCible = $hobbitRowset->current();*/
			$retour["hobbitTouches"][$i]["hobbit"] = $h;
			$retour["hobbitTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueHobbit($hobbit, $h, $jetAttaquant, $jetCible, $jetsDegat);
			$i++;
		}
		return $retour;
	}
	
	public static function calculDegatCaseMonstre($config, $hobbit, $degats, &$retour) {
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit);
		
		$jetsDegat["critique"] = $jetsDegat;
		$jetsDegat["noncritique"] = $jetsDegat;
		$jetAttaquant = 1;
		$jetCible = 0;
		
		$i = 0;
		foreach($monstres as $m) {
			$retour["monstreTouches"][$i]["monstre"] = $m;
			$retour["monstreTouches"][$i]["retourAttaque"] = Bral_Util_Attaque::attaqueMonstre($hobbit, $m, $jetAttaquant, $jetCible, $jetsDegat);
			$i++;
		}
		return $retour;
	}
	
	public static function calculSoinCase($config, $hobbit, $soins) {
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->id_hobbit);
		$retour["hobbitTouches"] = null;
		$i = 0;
		foreach($hobbits as $h) {
			$retour["hobbitTouches"][$i]["hobbit"] = $h;
			$retour["hobbitTouches"][$i]["retourAttaque"] = null;
			$i++;
			if ($h["pv_max_hobbit"] >  $h["pv_restant_hobbit"]) {
				$h["pv_restant_hobbit"] = $h["pv_restant_hobbit"] + $soins;
				if ($h["pv_restant_hobbit"] > $h["pv_max_hobbit"]) {
					$h["pv_restant_hobbit"] = $h["pv_max_hobbit"];
				}
				$data = array("pv_restant_hobbit" => $h["pv_restant_hobbit"]);
					
				$where = "id_hobbit = ".$h["id_hobbit"];
				$hobbitTable->update($data, $where);
					
				$id_type = $config->game->evenements->type->effet;
				$details = $hobbit->prenom_hobbit ." ". $hobbit->nom_hobbit ." (".$hobbit->id_hobbit.") N".$hobbit->niveau_hobbit." a soign&eacute; le hobbit ".$h["prenom_hobbit"] ." ". $h["nom_hobbit"] ." (".$h["id_hobbit"].") N".$h["niveau_hobbit"];  
				$this->majEvenements($hobbit->id_hobbit, $id_type, $details);
				$this->majEvenements($h["id_hobbit"], $id_type, $details);
			}
		}
		return $retour;
	}
}

?>