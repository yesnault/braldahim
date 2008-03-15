<?php

/*
 * Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
 * dégats : 0.5*(jet FOR)+BM FOR+ bonus arme dégats
 * dégats critiques : (1.5*(0.5*FOR))+BM FOR+bonus arme dégats
 * Ne peut pas être utilisé en ville.
 */
class Bral_Competences_Frenesie extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		
		$tabHobbits = null;
		$tabMonstres = null;

		// recuperation des hobbits qui sont presents sur la vue
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->id_hobbit);
		foreach($hobbits as $h) {
			$tab = array(
			'id_hobbit' => $h["id_hobbit"],
			'nom_hobbit' => $h["nom_hobbit"],
			'prenom_hobbit' => $h["prenom_hobbit"],
			);
			$tabHobbits[] = $tab;
		}
		
		// recuperation des monstres qui sont presents sur la vue
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		foreach($monstres as $m) {
			if ($m["genre_type_monstre"] == 'feminin') {
				$m_taille = $m["nom_taille_f_monstre"];
			} else {
				$m_taille = $m["nom_taille_m_monstre"];
			}
			$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"]);
		}

		$this->view->tabHobbits = $tabHobbits;
		$this->view->nHobbits = count($tabHobbits);
		$this->view->tabMonstres = $tabMonstres;
		$this->view->nMonstres = count($tabMonstres);
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_De");
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_1");
		}
		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide : ".$this->request->get("valeur_2"));
		} else {
			$idHobbit = (int)$this->request->get("valeur_2");
		}

		if ($idMonstre != -1 && $idHobbit != -1) {
			throw new Zend_Exception(get_class($this)." Montre ou Hobbit invalide (!=-1)");
		}
		if ($idMonstre == -1 && $idHobbit == -1) {
			throw new Zend_Exception(get_class($this)." Montre ou Hobbit invalide (==-1)");
		}

		$attaqueMonstre = false;
		$attaqueHobbit = false;
		if ($idHobbit != -1) {
			if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
				foreach ($this->view->tabHobbits as $h) {
					if ($h["id_hobbit"] == $idHobbit) {
						$attaqueHobbit = true;
						break;
					}
				}
			}
			if ($attaqueHobbit === false) {
				throw new Zend_Exception(get_class($this)." Hobbit invalide (".$idHobbit.")");
			}
		} else {
			if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
				foreach ($this->view->tabMonstres as $m) {
					if ($m["id_monstre"] == $idMonstre) {
						$attaqueMonstre = true;
						break;
					}
				}
			}
			if ($attaqueMonstre === false) {
				throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
			}
		}

		if ($attaqueHobbit === true) {
			$this->attaqueHobbit($idHobbit);
		} elseif ($attaqueMonstre === true) {
			$this->attaqueMonstre($idMonstre);
		} else {
			throw new Zend_Exception(get_class($this)." Erreur inconnue");
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_vue", "box_lieu", "box_evenements");
	}

	private function attaqueHobbit($idHobbit) {
		Zend_Loader::loadClass("Bral_Util_De");

		$this->view->frenesieReussie = false;
		$this->calculJetAttaque();

		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($idHobbit);
		$hobbit = $hobbitRowset->current();

		$jetCible = 0;
		for ($i=1; $i<=$this->view->config->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$this->view->jetCible = $jetCible + $hobbit->agilite_bm_hobbit;

		$cible = array('nom_cible' => $hobbit->prenom_hobbit ." ". $hobbit->nom_hobbit, 'id_cible' => $hobbit->id_hobbit, 'x_cible' => $hobbit->x_hobbit, 'y_cible' => $hobbit->y_hobbit,'niveau_cible' =>$hobbit->niveau_hobbit, 'castars_hobbit' => $hobbit->castars_hobbit, 'agilite_bm_hobbit' => $hobbit->agilite_bm_hobbit);
		$this->view->cible = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($this->view->jetAttaquant > $this->view->jetCible) {
			$this->view->critique = false;
			$this->view->fragilisee = false;
			$this->view->frenesieReussie = true;
			
			if ($this->view->jetAttaquant / 2 > $this->view->jetCible ) {
				$this->view->critique = true;
			}
			$this->calculDegat($this->view->critique);

			$pv = $hobbit->pv_restant_hobbit - $this->view->jetDegat;
			$nb_mort = $hobbit->nb_mort_hobbit;
			if ($pv <= 0) {
				$pv = 0;
				$mort = "oui";
				$nb_mort = $nb_mort + 1;
				$this->view->user->nb_kill_hobbit = $this->view->user->nb_kill_hobbit + 1;
				$this->view->mort = true;
				$this->dropHobbitCastars($cible);
			} else {
				$cible["agilite_bm_hobbit"]  = $cible["agilite_bm_hobbit"] - $cible["niveau_hobbit"];
				$mort = "non";
				$this->view->mort = false;
				$this->view->fragilisee = true;
			}
			$data = array(
			'castars_hobbit' => $cible["castars_hobbit"],
			'pv_restant_hobbit' => $pv,
			'est_mort_hobbit' => $mort,
			'nb_mort_hobbit' => $nb_mort,
			'date_fin_tour_hobbit' => date("Y-m-d H:i:s"),
			'agilite_bm_hobbit' => $cible["agilite_bm_hobbit"],
			);
			$where = "id_hobbit=".$hobbit->id_hobbit;
			$hobbitTable->update($data, $where);
		} else if ($this->view->jetCible/2 < $this->view->jetAttaquant) {
			$cible["agilite_bm_hobbit"] = $cible["agilite_bm_hobbit"] - ( floor($cible["niveau_hobbit"] / 10) + 1 );
			$data = array('agilite_bm_hobbit' => $cible["agilite_bm_hobbit"]);
			$where = "id_hobbit=".$cible["id_cible"];
			$hobbitTable->update($data, $where);
			$this->view->mort = false;
			$this->view->fragilisee = true;
		}

		$id_type = $this->view->config->game->evenements->type->attaquer;
		$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") N".$this->view->user->niveau_hobbit." a attaqué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"]."";
		$this->majEvenements($this->view->user->id_hobbit, $id_type, $details);
		$this->majEvenements($cible["id_cible"], $id_type, $details);

		if ($this->view->mort === true) {
			$id_type = $this->view->config->game->evenements->type->kill;
			$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") N".$this->view->user->niveau_hobbit." a tué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			$this->majEvenements($this->view->user->id_hobbit, $id_type, $details);
			$id_type = $this->view->config->game->evenements->type->mort;
			$this->majEvenements($cible["id_cible"], $id_type, $details);
		}
	}

	private function attaqueMonstre($idMonstre) {
		$this->calculJetAttaque();
		$this->view->frenesieReussie = false;
		
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
		$this->view->jetCible = $jetCible + $monstre["agilite_bm_monstre"];
		
		$cible = array('nom_cible' => $monstre["nom_type_monstre"]." ".$m_taille, 'id_cible' => $monstre["id_monstre"], 'niveau_cible' => $monstre["niveau_monstre"]);
		$this->view->cible = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($this->view->jetAttaquant > $this->view->jetCible) {
			$this->view->critique = false;
			$this->view->fragilisee = false;
			$this->view->frenesieReussie = true;
			
			if ($this->view->jetAttaquant / 2 > $this->view->jetCible ) {
				$this->view->critique = true;
			}
			$this->calculDegat($this->view->critique);
			
			$pv = $monstre["pv_restant_monstre"] - $this->view->jetDegat;
			
			if ($pv <= 0) {
				$this->view->mort = true;
				$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
				$vieMonstre->mortMonstreDb($cible["id_cible"]);
			} else {
				$agilite_bm_monstre = $monstre["agilite_bm_monstre"] - $monstre["niveau_monstre"];
				$this->view->fragilisee = true;
				
				$this->view->mort = false;
				$data = array(
				'pv_restant_monstre' => $pv,
				'agilite_bm_monstre' => $agilite_bm_monstre);
				$where = "id_monstre=".$cible["id_cible"];
				$monstreTable->update($data, $where);
			}
		} else if ($this->view->jetCible/2 < $this->view->jetAttaquant) {
			$agilite_bm_monstre = $monstre["agilite_bm_monstre"] - ( floor($monstre["niveau_monstre"] / 10) + 1 );
			$this->view->mort = false;
			$data = array('agilite_bm_monstre' => $agilite_bm_monstre);
			$where = "id_monstre=".$cible["id_cible"];
			$monstreTable->update($data, $where);
			$this->view->fragilisee = true;
		}

		$id_type = $this->view->config->game->evenements->type->attaquer;
		$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") N".$this->view->user->niveau_hobbit." a attaqué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
		$this->majEvenements($this->view->user->id_hobbit, $id_type, $details);
		$this->majEvenements($cible["id_cible"], $id_type, $details, "monstre");
		
		if ($this->view->mort === true) {
			$id_type = $this->view->config->game->evenements->type->kill;
			$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") N".$this->view->user->niveau_hobbit." a tué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			$this->majEvenements($this->view->user->id_hobbit, $id_type, $details);
			$id_type = $this->view->config->game->evenements->type->mort;
			$this->majEvenements($cible["id_cible"], $id_type, $details, "monstre");
		}
	}

	private function calculJetAttaque() {
		//Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->view->config->base_agilite + $this->view->user->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		// TODO ajouter bonus arme att
		$jetAttaquant = (0.5 * $jetAttaquant) + $this->view->user->agilite_bm_hobbit;
		$this->view->jetAttaquant = $jetAttaquant;
	}

	// * dégats : 0.5*(jet FOR)+BM FOR+ bonus arme dégats
 	// * dégats critiques : (1.5*(0.5*FOR))+BM FOR+bonus arme dégats
	private function calculDegat($estCritique) {
		$jetDegat = 0;
		$coefCritique = 1;
		if ($estCritique === true) {
			$coefCritique = 1.5;
		}
		
		for ($i=1; $i<= ($this->view->config->game->base_force + $this->view->user->force_base_hobbit); $i++) {
			$jetDegat = $jetDegat + Bral_Util_De::get_1d6();
		}
		//TODO Rajouter le bonus de degat de l'arme s'il y en a
		$jetDegat = $coefCritique * (0.5 * $jetDegat) + $this->view->user->force_bm_hobbit;
		$this->view->jetDegat = $jetDegat;
	}

	public function calculPx() {
		parent::calculPx();
		$this->view->calcul_px_generique = false;

		if ($this->view->attaqueReussie === true) {
			$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
		}

		if ($this->view->mort === true) {
			// [10+2*(diff de niveau) + Niveau Cible ]
			$this->view->nb_px_commun = 10+2*($this->view->cible["niveau_cible"] - $this->view->user->niveau_hobbit) + $this->view->cible["niveau_cible"];
			if ($this->view->nb_px_commun < $this->view->nb_px_perso ) {
				$this->view->nb_px_commun = $this->view->nb_px_perso;
			}
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
}