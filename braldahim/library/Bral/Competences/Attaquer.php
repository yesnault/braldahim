<?php

class Bral_Competences_Attaquer extends Bral_Competences_Competence {

	function prepareCommun() {
		$tabHobbits = null;
		$tabMonstres = null;

		// récupération des hobbits qui sont présents sur la case
		$hobbitTable = new Hobbit();

		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->id_hobbit);
		foreach($hobbits as $h) {
			$tab = array(
			'id_hobbit' => $h["id_hobbit"],
			'nom_hobbit' => $h["nom_hobbit"],
			);
			$tabHobbits[] = $tab;
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
			foreach ($this->view->tabHobbits as $h) {
				if ($h["id_hobbit"] == $idHobbit) {
					$attaqueHobbit = true;
					break;
				}
			}
			if ($attaqueHobbit === false) {
				throw new Zend_Exception(get_class($this)." Hobbit invalide (".$idHobbit.")");
			}
		} else {
			foreach ($this->view->tabMonstres as $m) {
				if ($m["id_monstre"] == $idMonstre) {
					$attaqueMonstre = true;
					break;
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
				$this->attaqueHobbit($idHobbit);
			} elseif ($attaqueMonstre === true) {
				$this->attaqueMonstre($idMonstre);
			} else {
				throw new Zend_Exception(get_class($this)." Erreur inconnue");
			}
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
		
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_lieu");
	}


	private function attaqueHobbit($idHobbit) {
		Zend_Loader::loadClass("Bral_Util_De");

		$this->view->attaqueReussie = false;
		$this->calculJetAttaque();

		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($idHobbit);
		$hobbit = $hobbitRowset->current();

		$jetCible = 0;
		for ($i=1; $i<=$hobbit->agilite_base_hobbit; $i++) {
			$jetCible = $jetAttaque + Bral_Util_De::get_1d6();
		}
		$this->view->jetCible = $jetCible + $hobbit->agilite_bm_hobbit;

		$cible = array('nom_cible' => $hobbit->nom_hobbit, 'id_cible' => $hobbit->id_hobbit, 'niveau_cible' =>$hobbit->niveau_hobbit);
		$this->view->cible = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($this->view->jetAttaquant > $this->view->jetCible) {
			$this->view->attaqueReussie = true;
			$this->calculDegat();

			$pv = $hobbit->pv_restant_hobbit - $this->view->jetDegat;
			$nb_mort = $hobbit->nb_mort_hobbit;
			if ($pv <= 0) {
				$pv = 0;
				$mort = "oui";
				$nb_mort = $nb_mort + 1;
				$this->view->user->nb_kill_hobbit = $this->view->user->nb_kill_hobbit + 1;
				$this->view->mort = true;
			} else {
				$mort = "non";
				$this->view->mort = false;
			}
			$data = array(
			'pv_restant_hobbit' => $pv,
			'est_mort_hobbit' => $mort,
			'nb_mort_hobbit' => $nb_mort,
			'date_fin_tour_hobbit' => date("Y-m-d H:i:s"),
			);
			$where = "id_hobbit=".$hobbit->id_hobbit;
			$hobbitTable->update($data, $where);
		}
	}

	private function attaqueMonstre($idMonstre) {
		$this->calculJetAttaque();

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($this->view->jetAttaquant > $this->view->jetCible) {
			$attaqueReussie = true;
			$this->calculDegat();
		}

		$this->calculDegat();
	}

	private function calculJetAttaque() {
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->view->user->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = $jetAttaquant + $this->view->user->agilite_bm_hobbit;
		$this->view->jetAttaquant = $jetAttaquant;
	}

	private function calculDegat() {
		$jetDegat = 0;
		for ($i=1; $i<=$this->view->user->force_base_hobbit; $i++) {
			$jetDegat = $jetDegat + Bral_Util_De::get_1d3();
		}
		$jetDegat = $jetDegat + $this->view->user->force_bm_hobbit;
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
			$this->view->nb_px_commun = 10+2*($this->view->user->niveau_hobbit - $this->view->cible["niveau_cible"]) + $this->view->cible["niveau_cible"];
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun; 
	}
}