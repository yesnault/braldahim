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
		Zend_Loader::loadClass("Ville"); 
		Zend_Loader::loadClass('Bral_Util_Commun');
		
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->frenesieVilleOk = true;
		
		if (count($villes) > 0) {
			$this->view->frenesieVilleOk = false;
			return;
		}	
		
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
		
		if ($this->view->frenesieVilleOk == false) {
			throw new Zend_Exception(get_class($this)." Frenesie interdit ville");
		}
		
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
	
		// calcul des jets
		$this->calculJets();
		
		if ($this->view->okJet1 === true) {
			if ($attaqueHobbit === true) {
				$this->view->retourAttaque = $this->attaqueHobbit($this->view->user, $idHobbit);
			} elseif ($attaqueMonstre === true) {
				$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre);
			} else {
				throw new Zend_Exception(get_class($this)." Erreur inconnue");
			}
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_vue", "box_lieu", "box_evenements");
	}

	protected function calculJetAttaque($hobbit) {
		//Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->view->config->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = (0.5 * $jetAttaquant) + $hobbit->agilite_bm_hobbit + $hobbit->bm_attaque_hobbit;
		return $jetAttaquant;
	}

	protected function calculDegat($hobbit) {
		$commun = new Bral_Util_Commun();
		$this->view->effetRune = false;
		
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$jetDegat = 0;
		$coefCritique = 1.5;
			
		for ($i=1; $i<= ($this->view->config->game->base_force + $hobbit->force_base_hobbit); $i++) {
			$jetDegat = $jetDegat + Bral_Util_De::get_1d6();
		}
		
		if ($commun->isRunePortee($hobbit->id_hobbit, "EM")) { 
			$this->view->effetRune = true;
			// dégats : Jet FOR + BM + Bonus de dégat de l'arme
			// dégats critiques : Jet FOR *1,5 + BM + Bonus de l'arme
			$jetDegat["critique"] = $coefCritique * $jetDegat;
			$jetDegat["noncritique"] = $jetDegat;
		} else {
			// * dégats : 0.5*(jet FOR)+BM FOR+ bonus arme dégats
 			// * dégats critiques : (1.5*(0.5*FOR))+BM FOR+bonus arme dégats
			$jetDegat["critique"] = $coefCritique * (0.5 * $jetDegat);
			$jetDegat["noncritique"] = 0.5 * $jetDegat;
		}
		
		$jetDegat["critique"] = $jetDegat["critique"] + $hobbit->force_bm_hobbit + $hobbit->bm_degat_hobbit;
		$jetDegat["noncritique"] = $jetDegat["noncritique"] + $hobbit->force_bm_hobbit + $hobbit->bm_degat_hobbit;
		
		return $jetDegat;
	}

	public function calculPx() {
		parent::calculPx();
			
		$this->view->nb_px_commun = 0;
		$this->view->calcul_px_generique = false;

		if ($this->view->retourAttaque["attaqueReussie"] === true) {
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