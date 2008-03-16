<?php

abstract class Bral_Competences_Competence {
	
	protected $view;
	
	function __construct($competence, $hobbitCompetence, $request, $view, $action) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $competence["nom_systeme"];
		$this->competence = $competence;
		$this->view->jetUtilise = false;
		$this->view->balanceFaimUtilisee = false;
		
		// recuperation de hobbit competence
		$this->hobbit_competence = $hobbitCompetence;

		$this->prepareCommun();
		$this->calculNbPa();

		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();

	public function getIdEchoppeCourante() {
		return false;
	}
	
	public function calculNbPa() {
		if ($this->view->user->pa_hobbit - $this->competence["pa_utilisation"] < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
		$this->view->nb_pa = $this->competence["pa_utilisation"];
	}
	
	public function ameliorationCompetenceMetier() {
		Zend_Loader::loadClass("HobbitsMetiers");
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		$ameliorationCompetence = false;
		foreach($hobbitsMetierRowset as $m) {
			if ($this->competence["id_fk_metier_competence"] == $m["id_metier"]) {
				if ($m["est_actif_hmetier"] == "oui") {
					$ameliorationCompetence = true;
				}
				break;
			}
		}
		return $ameliorationCompetence;
	}
	
	public function getIdMetier() {
		if ($this->competence["type_competence"] == "metier") {
			return $this->competence["id_fk_metier_competence"];
		} else {
			return null;
		}
	}
	
	public function calculPx() {
		$this->view->nb_px_commun = 0;
		$this->view->calcul_px_generique = true;
		if ($this->view->okJet1 === true) {
			$this->view->nb_px_perso = $this->competence["px_gain"];
		} else {
			$this->view->nb_px_perso = 0;
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}

	public function calculBalanceFaim() {
		$this->view->balanceFaimUtilisee = true;
		$this->view->balance_faim = $this->competence["balance_faim"];
	}

	public function calculJets() {
		Zend_Loader::loadClass("Bral_Util_De");
		$this->view->jetUtilise = true;
		$this->view->okJet1 = false; // jet de compétence
		$this->view->okJet2 = false; // jet amélioration de la compétence
		$this->view->okJet3 = false; // jet du % d'amélioration
		$this->calculJets1();
		$this->calculJets2et3();
		$this->majSuiteJets();
	}

	public function calculJets1() {
		// 1er Jet : réussite ou non de la compétence
		$this->view->jet1 = Bral_Util_De::get_1d100();
		if ($this->view->jet1 <= $this->hobbit_competence["pourcentage_hcomp"]) {
			$this->view->okJet1 = true;
		} else { // si le jet est manquee, on recalcule le cout en PA
			$this->view->nb_pa = $this->competence["pa_manquee"];
		}
	}

	public function calculJets2et3() {
		$this->view->jet2Possible = false;
		
		
		$this->view->estCompetenceMetier = false;
		if ($this->competence["type_competence"] == "metier") {
			$this->view->estCompetenceMetier = true;
			$this->view->ameliorationCompetenceMetierCourant = $this->ameliorationCompetenceMetier();
		}
		
		// a t-on le droit d'améliorer la compétence métier
		if ($this->view->estCompetenceMetier === true && $this->view->ameliorationCompetenceMetierCourant === false) { 
			$this->view->okJet2 = false;
			
		}  else if ($this->view->okJet1 === true || $this->hobbit_competence["pourcentage_hcomp"] < 50) {
			// 2nd Jet : réussite ou non de l'amélioration de la compétence
			// seulement si la maitrise de la compétence est < 50 ou si le jet1 est réussi
			$this->view->jet2 = Bral_Util_De::get_1d100();
			$this->view->jet2Possible = true;
			if ($this->view->jet2 > $this->hobbit_competence["pourcentage_hcomp"]) {
				$this->view->okJet2 = true;
			}
		}

		// 3ème Jet : % d'amélioration de la compétence
		if ($this->view->okJet2 === true) {
			if ($this->hobbit_competence["pourcentage_hcomp"] >= 90) { // pas d'amélioration au delà de 90 %
				$this->view->okJet3 = false;
			} else {
				$this->view->okJet3 = true;
				if ($this->hobbit_competence["pourcentage_hcomp"] < 50) {
					if ($this->view->okJet1 === true) {
						$this->view->jet3 = Bral_Util_De::get_1d6();
					} else {
						$this->view->jet3 = Bral_Util_De::get_1d3();
					}
				} else if ($this->hobbit_competence["pourcentage_hcomp"] < 75) {
					$this->view->jet3 = Bral_Util_De::get_1d3();
				} else if ($this->hobbit_competence["pourcentage_hcomp"] < 90) {
					$this->view->jet3 = Bral_Util_De::get_1d1();
				}
			}
		}
	}

	// mise à jour de la table hobbit competence
	public function majSuiteJets() {
		if ($this->view->okJet3 === true) { // uniquement dans le cas de réussite du jet3
			$hobbitsCompetencesTable = new HobbitsCompetences();
			$pourcentage = $this->hobbit_competence["pourcentage_hcomp"] + $this->view->jet3;
			if ($pourcentage > 90) { // 90% maximum
				$pourcentage = 90;
			}
			$data = array('pourcentage_hcomp' => $pourcentage);
			$where = array("id_fk_competence_hcomp = ".$this->hobbit_competence["id_fk_competence_hcomp"]." AND id_fk_hobbit_hcomp = ".$this->view->user->id_hobbit);
			$hobbitsCompetencesTable->update($data, $where);
		}
	}

	/*
	 * Mise à jour des évènements du hobbit / du monstre.
	 */
	public function majEvenements($id_concerne, $id_type_evenement, $details, $type="hobbit") {
		Zend_Loader::loadClass('Evenement');

		$evenementTable = new Evenement();
		
		if ($type == "hobbit") {
			$data = array(
			'id_fk_hobbit_evenement' => $id_concerne,
			'date_evenement' => date("Y-m-d H:i:s"),
			'id_fk_type_evenement' => $id_type_evenement,
			'details_evenement' => $details,
			);
		} else {
			$data = array(
			'id_fk_monstre_evenement' => $id_concerne,
			'date_evenement' => date("Y-m-d H:i:s"),
			'id_fk_type_evenement' => $id_type_evenement,
			'details_evenement' => $details,
			);
		}
		$evenementTable->insert($data);
	}
	
	/*
	 * Mise à jour des évènements du hobbit : type : compétence.
	 */
	public function majEvenementsStandard() {
		$id_type = $this->view->config->game->evenements->type->competence;
		$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a réussi l'utilisation d'une compétence";
		$this->majEvenements($this->view->user->id_hobbit, $id_type, $details);
	}
	
	/*
	 * Mise à jour des PA, des PX et de la balance de faim.
	 */
	public function majHobbit() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa;
		$this->view->user->px_perso_hobbit = $this->view->user->px_perso_hobbit + $this->view->nb_px_perso;
		$this->view->user->px_commun_hobbit = $this->view->user->px_commun_hobbit + $this->view->nb_px_commun;
		$this->view->user->balance_faim_hobbit = $this->view->user->balance_faim_hobbit + $this->view->balance_faim;

		if ($this->view->user->balance_faim_hobbit < 0) {
			$this->view->user->balance_faim_hobbit = 0;
		}

		$this->view->changeNiveau = false;
		$this->calculNiveau();

		$data = array(
			'pa_hobbit' => $this->view->user->pa_hobbit,
			'px_perso_hobbit' => $this->view->user->px_perso_hobbit,
			'px_commun_hobbit' => $this->view->user->px_commun_hobbit,
			'pi_hobbit' => $this->view->user->pi_hobbit,
			'niveau_hobbit' => $this->view->user->niveau_hobbit,
			'px_base_niveau_hobbit' => $this->view->user->px_base_niveau_hobbit,
			'balance_faim_hobbit' => $this->view->user->balance_faim_hobbit,
			'nb_kill_hobbit' => $this->view->user->nb_kill_hobbit,
			'x_hobbit' => $this->view->user->x_hobbit,
			'y_hobbit'  => $this->view->user->y_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}

	public function getNomInterne() {
		return "box_action";
	}

	public function render() {
		switch($this->action) {
			case "ask":
				return $this->view->render("competences/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				return $this->view->render("competences/".$this->nom_systeme."_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}


	/**
	 * Le niveau suivant est calculé à partir d'un certain nombre de px perso
	 * qui doit être >= à :
	 * NiveauSuivantPX = NiveauSuivant x 3 + debutNiveauPrecedentPx
	 */
	private function calculNiveau() {

		$niveauSuivantPx = ($this->view->user->niveau_hobbit + 1) * 3 + $this->view->user->px_base_niveau_hobbit;
		if ($this->view->user->px_perso_hobbit >= $niveauSuivantPx) {
			$this->view->user->px_perso_hobbit = $this->view->user->px_perso_hobbit - $niveauSuivantPx;
			$this->view->user->niveau_hobbit = $this->view->user->niveau_hobbit + 1;
			$this->view->user->px_base_niveau_hobbit = $niveauSuivantPx;
			$this->view->user->pi_hobbit = $this->view->user->pi_hobbit + $niveauSuivantPx;
			$this->view->changeNiveau = true;
		}

		$niveauSuivantPx = ($this->view->user->niveau_hobbit + 1) * 3 + $this->view->user->px_base_niveau_hobbit;
		if ($this->view->user->px_perso_hobbit >= $niveauSuivantPx) {
			$this->calculNiveau();
		}
	}
	
	public function dropHobbitCastars(&$cible) {
		//Lorqu'un Hobbit meurt il perd une partie de ces castars : 1/3 arr inférieur.
		
		if ($cible["castars_hobbit"] > 0) {
			if (Bral_Util_De::get_1d1() == 1) { 
				$nbCastars = floor($cible["castars_hobbit"] / 3) + Bral_Util_De::get_1d5();
			} else {
				$nbCastars = floor($cible["castars_hobbit"] / 3) - Bral_Util_De::get_1d5() ;
			}
			
			$cible["castars_hobbit"] = $cible["castars_hobbit"] - $nbCastars;
			
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
	}
	
	protected function attaqueHobbit($idHobbit) {
		$attaqueReussie = false;
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
			$commun = new Bral_Util_Commun();
			
			$this->view->critique = false;
			$this->view->fragilisee = false;
			$attaqueReussie = true;
			
			if ($this->view->jetAttaquant / 2 > $this->view->jetCible ) {
				if ($commun->getEffetMotX($hobbit->id_hobbit) == true) {
					$this->view->critique = false;
				} else {
					$this->view->critique = true;
				}
			}
			$this->calculDegat($this->view->critique);
			
			$this->view->jetDegat = $commun->getEffetMotA($hobbit->id_hobbit, $this->view->jetDegat);
			$hobbit->regeneration_malus_hobbit = $commun->getEffetMotI($this->view->user->id_hobbit, $hobbit->regeneration_malus_hobbit);
			$hobbit->vue_malus_hobbit = $commun->getEffetMotJ($this->view->user->id_hobbit);
			$hobbit->vue_bm_hobbit = $hobbit->vue_bm_hobbit + $hobbit->vue_malus_hobbit;
			$hobbit->agilite_malus_hobbit = $commun->getEffetMotQ($this->view->user->id_hobbit);
			$hobbit->agilite_bm_hobbit = $hobbit->agilite_bm_hobbit + $hobbit->agilite_malus_hobbit;
			
			$pv = ($hobbit->pv_restant_hobbit + $hobbit->bm_defense_hobbit) - $this->view->jetDegat;
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
				'regeneration_malus_hobbit' => $hobbit->regeneration_malus_hobbit,
				'vue_bm_hobbit' => $hobbit->vue_bm_hobbit,
				'vue_malus_hobbit' => $hobbit->vue_malus_hobbit,
				'agilite_bm_hobbit' => $hobbit->agilite_bm_hobbit,
				'agilite_malus_hobbit' => $hobbit->agilite_malus_hobbit,
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
		
		return $attaqueReussie;
	}
	
	protected function attaqueMonstre($idMonstre) {
		$this->calculJetAttaque();
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
		$this->view->jetCible = $jetCible + $monstre["agilite_bm_monstre"];
		
		$cible = array('nom_cible' => $monstre["nom_type_monstre"]." ".$m_taille, 'id_cible' => $monstre["id_monstre"], 'niveau_cible' => $monstre["niveau_monstre"],  'x_cible' => $monstre["x_monstre"], 'y_cible' => $monstre["y_monstre"]);
		$this->view->cible = $cible;

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($this->view->jetAttaquant > $this->view->jetCible) {
			$commun = new Bral_Util_Commun();
			
			$this->view->critique = false;
			$this->view->fragilisee = false;
			$attaqueReussie = true;
			
			if ($this->view->jetAttaquant / 2 > $this->view->jetCible ) {
				$this->view->critique = true;
			}
			$this->calculDegat($this->view->critique);
			
			$monstre["regeneration_malus_monstre"] = $commun->getEffetMotI($this->view->user->id_hobbit, $monstre["regeneration_malus_monstre"]);
			$monstre["vue_malus_monstre"] = $commun->getEffetMotJ($this->view->user->id_hobbit);
			$monstre["agilite_malus_monstre"] = $commun->getEffetMotQ($this->view->user->id_hobbit);
			$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] + $monstre["agilite_malus_monstre"];
			
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
					'agilite_bm_monstre' => $agilite_bm_monstre,
					'regeneration_malus_monstre' => $monstre["regeneration_malus_monstre"],
					'vue_malus_monstre' => $monstre["vue_malus_monstre"],
					'agilite_bm_monstre' => $monstre["agilite_bm_monstre"],
					'agilite_malus_monstre' => $monstre["agilite_malus_monstre"],
				);
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
		
		return $attaqueReussie;
	}
}
