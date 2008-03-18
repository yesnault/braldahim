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
		
		$this->view->effetMotD = false;
		$this->view->effetMotE = false;
		$this->view->effetMotG = false;
		$this->view->effetMotH = false;
		$this->view->effetMotI = false;
		$this->view->effetMotJ = false;
		$this->view->effetMotL = false;
		$this->view->effetMotQ = false;
		
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
			'pv_restant_hobbit' => $this->view->user->pv_restant_hobbit,
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
	
	protected function attaqueHobbit(&$hobbitAttaquant, $idHobbitCible, $effetMotSPossible = true) {
		$attaqueReussie = false;
		
		$retourAttaque = null;
		$retourAttaque["jetAttaquant"] = $this->calculJetAttaque($hobbitAttaquant);
		$retourAttaque["mort"] = false;
		$retourAttaque["fragilisee"] = false;
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($idHobbitCible);
		$hobbitCible = $hobbitRowset->current();

		$jetCible = 0;
		for ($i=1; $i<=$this->view->config->base_agilite + $hobbitCible->agilite_base_hobbit; $i++) {
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
			
			$retourAttaque["jetDegat"] = $this->calculDegat($retourAttaque["critique"], $hobbitAttaquant);
			$retourAttaque["jetDegat"] = $commun->getEffetMotA($hobbitCible->id_hobbit, $retourAttaque["jetDegat"]);
			
			$effetMotE = $commun->getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null) {
				$this->view->effetMotE = true;
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
				$this->view->effetMotG = true;
				$retourAttaque["jetDegat"] = $retourAttaque["jetDegat"] + $effetMotG;
			}
			
			$effetMotI = $commun->getEffetMotI($hobbitAttaquant->id_hobbit);
			if ($effetMotI != null) {
				$this->view->effetMotI = true;
				$hobbitCible->regeneration_malus_hobbit = $hobbitCible->regeneration_malus_hobbit + $effetMotI;
			}
			
			$effetMotJ = $commun->getEffetMotJ($hobbitAttaquant->id_hobbit);
			if ($effetMotJ != null) {
				$this->view->effetMotJ = true;
				$hobbitCible->vue_malus_hobbit = $hobbitCible->vue_malus_hobbit+ $effetMotJ;
			}
			
			$hobbitCible->vue_bm_hobbit = $hobbitCible->vue_bm_hobbit + $hobbitCible->vue_malus_hobbit;
			
			$effetMotQ = $commun->getEffetMotQ($hobbitAttaquant->id_hobbit);
			if ($effetMotQ != null) {
				$this->view->effetMotQ = true;
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
					$this->view->effetMotH = true;
				}
				
				if ($commun->getEffetMotL($hobbitAttaquant->id_hobbit) == true) {
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					$this->view->effetMotL = true;
				}
				
				$retourAttaque["mort"] = true;
				$nbCastars = $commun->dropHobbitCastars($hobbitCible, $effetH);
				$hobbitCible->castars_hobbit = $hobbitCible->castars_hobbit - $nbCastars;
				if ($hobbitCible->castars_hobbit < 0) {
					$hobbitCible->castars_hobbit = 0;
				}
			} else {
				if ($effetMotSPossible) {
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

		$id_type = $this->view->config->game->evenements->type->attaquer;
		$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a attaqué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"]."";
		$this->majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
		$this->majEvenements($cible["id_cible"], $id_type, $details);

		if ($retourAttaque["mort"] === true) {
			$id_type = $this->view->config->game->evenements->type->kill;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a tué le hobbit ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			$this->majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
			$id_type = $this->view->config->game->evenements->type->mort;
			$this->majEvenements($cible["id_cible"], $id_type, $details);
		}
		
		$retourAttaque["attaqueReussie"] = $attaqueReussie;
		return $retourAttaque;
	}
	
	protected function attaqueMonstre(&$hobbitAttaquant, $idMonstre) {
		$retourAttaque = null;
		$retourAttaque["jetAttaquant"] = $this->calculJetAttaque($hobbitAttaquant);
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
			
			$retourAttaque["jetDegat"] = $this->calculDegat($retourAttaque["critique"], $hobbitAttaquant);
			
			$effetMotE = $commun->getEffetMotE($hobbitAttaquant->id_hobbit);
			if ($effetMotE != null) {
				$this->view->effetMotE = true;
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
				$this->view->effetMotG = true;
				$retourAttaque["jetDegat"] = $this->view->jetDegat + $effetMotG;
			}
			
			$effetMotI = $commun->getEffetMotI($hobbitAttaquant->id_hobbit);
			if ($effetMotI != null) {
				$this->view->effetMotI = true;
				$monstre["regeneration_malus_monstre"] = $monstre["regeneration_malus_monstre"] + $effetMotI;
			}
			
			$effetMotJ = $commun->getEffetMotJ($hobbitAttaquant->id_hobbit);
			if ($effetMotJ != null) {
				$this->view->effetMotJ = true;
				$monstre["vue_malus_monstre"] = $monstre["vue_malus_monstre"] + $effetMotJ;
			}
			
			$effetMotQ = $commun->getEffetMotQ($hobbitAttaquant->id_hobbit);
			if ($effetMotQ != null) {
				$this->view->effetMotQ = true;
				$monstre["agilite_malus_monstre"] = $monstre["agilite_malus_monstre"] + $effetMotQ;
			}
			
			$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] + $monstre["agilite_malus_monstre"];
			
			$pv = $monstre["pv_restant_monstre"] - $retourAttaque["jetDegat"];
			
			if ($pv <= 0) {
				$effetD = null;
				$effetH = null;
				
				$effetD = $commun->getEffetMotD($hobbitAttaquant->id_hobbit);
				if ($effetD != 0) {					
					$this->view->effetMotD = true;
				}
				
				$effetH = $commun->getEffetMotH($hobbitAttaquant->id_hobbit);
				if ($effetH == true) {					
					$this->view->effetMotH = true;
				}
				
				if ($commun->getEffetMotL($hobbitAttaquant->id_hobbit) == true) {
					$hobbitAttaquant->pa_hobbit = $hobbitAttaquant->pa_hobbit + 4;
					$this->view->effetMotL = true;
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

		$id_type = $this->view->config->game->evenements->type->attaquer;
		$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a attaqué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
		$this->majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
		$this->majEvenements($cible["id_cible"], $id_type, $details, "monstre");
		
		if ($retourAttaque["mort"] === true) {
			$id_type = $this->view->config->game->evenements->type->kill;
			$details = $hobbitAttaquant->prenom_hobbit ." ". $hobbitAttaquant->nom_hobbit ." (".$hobbitAttaquant->id_hobbit.") N".$hobbitAttaquant->niveau_hobbit." a tué le monstre ".$cible["nom_cible"]." (".$cible["id_cible"] . ") N".$cible["niveau_cible"];
			$this->majEvenements($hobbitAttaquant->id_hobbit, $id_type, $details);
			$id_type = $this->view->config->game->evenements->type->mort;
			$this->majEvenements($cible["id_cible"], $id_type, $details, "monstre");
		}
		
		$retourAttaque["attaqueReussie"] = $attaqueReussie;
		return $retourAttaque;
	}
	
	protected function setEffetMotG($effet) {
		$this->view->effetMotG = $effet;
	}
}
