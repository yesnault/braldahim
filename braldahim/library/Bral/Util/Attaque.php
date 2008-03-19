<?php

class Bral_Util_Attaque {

	public static function attaqueHobbit(&$hobbitAttaquant, $idHobbitCible, $jetAttaquant, $jetsDegat, $effetMotSPossible = true) {
		$config = Zend_Registry::get('config');
		$attaqueReussie = false;
		
		$retourAttaque = null;
		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($idHobbitCible);
		$hobbitCible = $hobbitRowset->current();

		$jetCible = 0;
		for ($i=1; $i<=$config->base_agilite + $hobbitCible->agilite_base_hobbit; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$retourAttaque["jetCible"] = $jetCible + $hobbitCible->agilite_bm_hobbit;

		$cible = array('nom_cible' => $hobbitCible->prenom_hobbit ." ". $hobbitCible->nom_hobbit, 'id_cible' => $hobbitCible->id_hobbit, 'x_cible' => $hobbitCible->x_hobbit, 'y_cible' => $hobbitCible->y_hobbit,'niveau_cible' => $hobbitCible->niveau_hobbit);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) {
			$commun = new Bral_Util_Commun();
			
			$retourAttaque["critique"]  = false;
			$retourAttaque["fragilisee"] = false;
			$attaqueReussie = true;
			
			if ($retourAttaque["jetAttaquant"] / 2 > $retourAttaque["jetCible"]) {
				if ($commun->getEffetMotX($hobbitCible->id_hobbit) == true) {
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
			
			$retourAttaque["jetDegat"] = $commun->getEffetMotA($hobbitCible->id_hobbit, $retourAttaque["jetDegat"]);
			
			$effetMotE = $commun->getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null) {
				if ($effetMotSPossible == false) $this->view->effetMotE = true;
				$gainPv = ($retourAttaque["jetDegat"] / 2);
				if ($gainPv > $effetMotE * 3) {
					$gainPv = $effetMotE * 3;
				}
				$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_restant_hobbit + $gainPv;
				if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit) {
					$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit;
				}
			}
			
			$effetMotG = $commun->getEffetMotG($hobbitAttaquant->id_hobbit);
			if ($effetMotG != null) {
				if ($effetMotSPossible == false) $this->view->effetMotG = true;
				$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
			}
			
			$effetMotI = $commun->getEffetMotI($hobbitAttaquant->id_hobbit);
			if ($effetMotI != null) {
				if ($effetMotSPossible == false) $this->view->effetMotI = true;
				$hobbitCible->regeneration_malus_hobbit = $hobbitCible->regeneration_malus_hobbit + $effetMotI;
			}
			
			$effetMotJ = $commun->getEffetMotJ($hobbitAttaquant->id_hobbit);
			if ($effetMotJ != null) {
				if ($effetMotSPossible == false) $this->view->effetMotJ = true;
				$hobbitCible->vue_malus_hobbit = $hobbitCible->vue_malus_hobbit+ $effetMotJ;
			}
			
			$hobbitCible->vue_bm_hobbit = $hobbitCible->vue_bm_hobbit + $hobbitCible->vue_malus_hobbit;
			
			$effetMotQ = $commun->getEffetMotQ($hobbitAttaquant->id_hobbit);
			if ($effetMotQ != null) {
				if ($effetMotSPossible == false) $this->view->effetMotQ = true;
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
				
				$effetH = $commun->getEffetMotH($hobbitAttaquant->id_hobbit);
				if ($effetH == true) {					
					if ($effetMotSPossible == false) $this->view->effetMotH = true;
				}
				
				if ($commun->getEffetMotL($hobbitAttaquant->id_hobbit) == true) {
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					if ($effetMotSPossible == false) $this->view->effetMotL = true;
				}
				
				$retourAttaque["mort"] = true;
				$nbCastars = $commun->dropHobbitCastars($hobbitCible, $effetH);
				$hobbitCible->castars_hobbit = $hobbitCible->castars_hobbit - $nbCastars;
				if ($hobbitCible->castars_hobbit < 0) {
					$hobbitCible->castars_hobbit = 0;
				}
			} else {
				if ($effetMotSPossible == true) {
					$effetMotS = $commun->getEffetMotS($hobbitAttaquant->id_hobbit);
					if ($effetMotS != null) {
						$this->view->effetMotS = true;
						$retourAttaque["retourAttaqueEffetMotS"] = $this->attaqueHobbit($hobbitCible, $hobbitAttaquant->id_hobbit, false);
					}
				}
				
				$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit - $hobbitCible->niveau_hobbit;
				$mort = "non";
				$retourAttaque["mort"] = false;
				$retourAttaque["fragilisee"] = true;
			}
			$data = array(
				'castars_hobbit' => $cible["castars_hobbit"],
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
			$hobbitTable->update($data, $where);
		} else if ($this->view->jetCible/2 < $retourAttaque["jetAttaquant"]) {
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
		
		$retourAttaque["attaqueReussie"] = $attaqueReussie;
		return $retourAttaque;
	}
	
	public static function attaqueMonstre(&$hobbitAttaquant, $idMonstre, $jetAttaquant, $jetsDegat) {
		$config = Zend_Registry::get('config');
		$retourAttaque = null;
		$retourAttaque["jetAttaquant"] = $jetAttaquant;
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		
		$attaqueReussie = false;
		
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($idMonstre);
		$monstre = $monstreRowset;

		if ($monstre["genre_type_monstre"] == 'feminin') {
			$m_taille = $monstre["nom_taille_f_monstre"];
		} else {
			$m_taille = $monstre["nom_taille_m_monstre"];
		}
			
		$jetCible = 0;
		for ($i=1; $i <= $monstre["agilite_base_monstre"]; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$retourAttaque["jetCible"] = $jetCible + $monstre["agilite_bm_monstre"];
		
		$cible = array('nom_cible' => $monstre["nom_type_monstre"]." ".$m_taille, 'id_cible' => $monstre["id_monstre"], 'niveau_cible' => $monstre["niveau_monstre"],  'x_cible' => $monstre["x_monstre"], 'y_cible' => $monstre["y_monstre"]);
		$retourAttaque["cible"] = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($retourAttaque["jetAttaquant"] > $retourAttaque["jetCible"]) {
			$commun = new Bral_Util_Commun();
			
			$retourAttaque["critique"] = false;
			$retourAttaque["fragilisee"] = false;
			$attaqueReussie = true;
			
			if ($retourAttaque["jetAttaquant"] / 2 > $retourAttaque["jetCible"]) {
				$retourAttaque["critique"]  = true;
			}
			
			if ($retourAttaque["critique"] == true) {
				$retourAttaque["jetDegat"] = $jetsDegat["critique"];
			} else {
				$retourAttaque["jetDegat"] = $jetsDegat["noncritique"];
			}
			
			$effetMotE = $commun->getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null) {
				if ($effetMotSPossible == false) $this->view->effetMotE = true;
				$gainPv = ($retourAttaque["jetDegat"] / 2);
				if ($gainPv > $effetMotE * 3) {
					$gainPv = $effetMotE * 3;
				}
				
				$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_restant_hobbit	+ $hobbitAttaquant->pv_max_hobbit;
				if ($hobbitAttaquant->pv_restant_hobbit > $hobbitAttaquant->pv_max_hobbit) {
					$hobbitAttaquant->pv_restant_hobbit = $hobbitAttaquant->pv_max_hobbit;
				}
			}
			
			$effetMotG = $commun->getEffetMotG($hobbitAttaquant->id_hobbit);
			if ($effetMotG != null) {
				if ($effetMotSPossible == false) $this->view->effetMotG = true;
				$retourAttaque["jetDegat"] = $this->view->jetDegat + $effetMotG;
			}
			
			$effetMotI = $commun->getEffetMotI($hobbitAttaquant->id_hobbit);
			if ($effetMotI != null) {
				if ($effetMotSPossible == false) $this->view->effetMotI = true;
				$monstre["regeneration_malus_monstre"] = $monstre["regeneration_malus_monstre"] + $effetMotI;
			}
			
			$effetMotJ = $commun->getEffetMotJ($hobbitAttaquant->id_hobbit);
			if ($effetMotJ != null) {
				if ($effetMotSPossible == false) $this->view->effetMotJ = true;
				$monstre["vue_malus_monstre"] = $monstre["vue_malus_monstre"] + $effetMotJ;
			}
			
			$effetMotQ = $commun->getEffetMotQ($hobbitAttaquant->id_hobbit);
			if ($effetMotQ != null) {
				if ($effetMotSPossible == false) $this->view->effetMotQ = true;
				$monstre["agilite_malus_monstre"] = $monstre["agilite_malus_monstre"] + $effetMotQ;
			}
			
			$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] + $monstre["agilite_malus_monstre"];
			
			$pv = $monstre["pv_restant_monstre"] - $retourAttaque["jetDegat"];
			
			if ($pv <= 0) {
				$effetD = null;
				$effetH = null;
				
				$effetD = $commun->getEffetMotD($hobbitAttaquant->id_hobbit);
				if ($effetD != 0) {					
					if ($effetMotSPossible == false) $this->view->effetMotD = true;
				}
				
				$effetH = $commun->getEffetMotH($hobbitAttaquant->id_hobbit);
				if ($effetH == true) {					
					if ($effetMotSPossible == false) $this->view->effetMotH = true;
				}
				
				if ($commun->getEffetMotL($hobbitAttaquant->id_hobbit) == true) {
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					if ($effetMotSPossible == false) $this->view->effetMotL = true;
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
		
		$retourAttaque["attaqueReussie"] = $attaqueReussie;
		return $retourAttaque;
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
}


?>