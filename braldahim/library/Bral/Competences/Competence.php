<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Competences_Competence {

	protected $view;
	protected $reloadInterface = false;
	private $estEvenementAuto = true;
	private $evenementQueSurOkJet1 = true;
	private $detailEvenement = null;
	private $idTypeEvenement = null;
	private $idCible = null;
	private $typeCible = null;
	private $nbPaSurcharge = null;

	function __construct($competence, $braldunCompetence, $request, $view, $action) {
		Zend_Loader::loadClass("Bral_Util_Evenement");
		Zend_Loader::loadClass("Bral_Util_Niveau");
		Zend_Loader::loadClass("StatsExperience");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $competence["nom_systeme"];
		$this->competence = $competence;

		$this->view->jetUtilise = false;
		$this->view->balanceFaimUtilisee = false;

		$this->view->nb_px_commun = 0;

		$this->view->effetMotD = false;
		$this->view->effetMotE = false;
		$this->view->effetMotG = false;
		$this->view->effetMotH = false;
		$this->view->effetMotI = false;
		$this->view->effetMotJ = false;
		$this->view->effetMotL = false;
		$this->view->effetMotQ = false;

		$this->view->finMatchSoule = false;
		$this->idMatchSoule = null;

		$this->view->estQueteEvenement = false;
		$this->view->estSurEchoppe = false;
		$this->view->possedeCharrette = false;

		// recuperation de Braldûn competence
		$this->braldun_competence = $braldunCompetence;

		// si c'est une competence metier, on verifie que ce n'est pas utilise plus de 2 fois par DLA
		$this->view->nbActionMetierParDlaOk = $this->calculNbActionMetierParDlaOk();

		// si c'est une competence commune avec un jet de dé, on verifie qu'on ne peut gagner de PX plus de 2 fois par DLA
		$this->view->nbGainCommunParDlaOk = $this->calculNbGainCommunParDlaOk();

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

	protected function constructListBoxRefresh($tab = null) {
		if ($this->view->user->niveau_braldun > 0 && $this->view->changeNiveau == true) {
			$tab[] = "box_titres";
		}
		$tab[] = "box_evenements";
		if ($this->view->finMatchSoule) {
			$tab[] = "box_soule";
			$tab[] = "box_coffre";
		} else if ($this->idMatchSoule != null) {
			$tab[] = "box_soule";
		}
		if ($this->view->estQueteEvenement) {
			$tab[] = "box_quetes";
			$tab[] = "box_coffre";
			$tab[] = "box_laban";
		}

		if ($this->view->user->pa_braldun < 1) {
			Zend_Loader::loadClass("Bral_Util_Box");
			Bral_Util_Box::calculBoxToRefresh0PA($tab);
		} else if ($this->view->user->pa_braldun < 2) {
			Zend_Loader::loadClass("Bral_Util_Box");
			Bral_Util_Box::calculBoxToRefresh1PA($tab);
		}
		$tab[] = "box_profil";
		return $tab;
	}

	public function getIdEchoppeCourante() {
		return false;
	}

	public function getIdChampCourant() {
		return false;
	}

	protected function setNbPaSurcharge($pa) {
		$this->view->nb_pa = $pa;
	}

	protected function calculNbPa() {
		if ($this->view->user->pa_braldun - $this->competence["pa_utilisation"] < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
		$this->view->nb_pa = $this->competence["pa_utilisation"];
	}

	protected function ameliorationCompetenceMetier() {
		Zend_Loader::loadClass("BraldunsMetiers");

		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);
		$ameliorationCompetence = false;
		foreach($braldunsMetierRowset as $m) {
			if ($this->competence["id_fk_metier_competence"] == $m["id_metier"]) {
				if ($m["est_actif_hmetier"] == "oui") {
					$ameliorationCompetence = true;
				}
				break;
			}
		}
		return $ameliorationCompetence;
	}

	protected function getIdMetier() {
		if ($this->competence["type_competence"] == "metier") {
			return $this->competence["id_fk_metier_competence"];
		} else {
			return null;
		}
	}

	protected function calculPx() {
		$this->view->calcul_px_generique = true;
		if ($this->view->okJet1 === true && $this->view->nbGainCommunParDlaOk === true) {
			$this->view->nb_px_perso = $this->competence["px_gain"];
		} else {
			$this->view->nb_px_perso = 0;
		}
		$this->view->nb_px = floor($this->view->nb_px_perso + $this->view->nb_px_commun);
	}

	protected function calculBalanceFaim($coef = 1) {
		$this->view->balanceFaimUtilisee = true;

		$this->view->balance_faim = floor($this->competence["balance_faim"] * $coef);
		if ($this->view->okJet1 == false) {
			$this->view->balance_faim = floor($this->view->balance_faim / 2);
		}
		Zend_Loader::loadClass("Bral_Util_Faim");
		$this->view->balanceFaimPvPerdus = Bral_Util_Faim::calculBalanceFaim($this->view->user, $this->view->balance_faim);
	}

	protected function calculPoids() {
		$this->view->user->poids_transporte_braldun = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_braldun, $this->view->user->castars_braldun);
	}

	protected function calculJets($bmJet1 = false) {
		$this->view->jetUtilise = true;
		$this->view->okJet1 = false; // jet de compétence
		$this->view->okJet2 = false; // jet amélioration de la compétence
		$this->view->bmJet1 = $bmJet1; // BM sur le jet 1
		$this->calculJets1($bmJet1);
		$this->calculJets2et3();
		$this->majSuiteJets();
		$this->updateCompetenceNbAction();
		$this->updateCompetenceNbGain();
	}

	private function calculJets1($bmJet1) {
		// 1er Jet : réussite ou non de la compétence
		if ($this->view->bmJet1 != false) {
			$this->view->jet1 = Bral_Util_De::get_1d100() + $this->view->bmJet1;
		} else {
			$this->view->jet1 = Bral_Util_De::get_1d100();
		}

		if ($this->braldun_competence["nb_tour_restant_bonus_tabac_hcomp"] > 0) {
			$pourcentage = $this->braldun_competence["pourcentage_hcomp"] + $this->view->config->game->tabac->bonus;
		} else if ($this->braldun_competence["nb_tour_restant_malus_tabac_hcomp"] > 0) {
			$pourcentage = $this->braldun_competence["pourcentage_hcomp"] - $this->view->config->game->tabac->malus;
		} else {
			$pourcentage = $this->braldun_competence["pourcentage_hcomp"];
		}

		if ($pourcentage > 100) {
			$pourcentage = 100;
		}

		if ($this->view->jet1 <= $pourcentage) {
			$this->view->okJet1 = true;
		} else { // si le jet est manquee, on recalcule le cout en PA
			$this->view->nb_pa = $this->competence["pa_manquee"];
		}
	}

	private function calculJets2et3() {
		$this->view->jet2Possible = false;
		$this->view->estCompetenceMetier = false;
		if ($this->competence["type_competence"] == "metier") {
			$this->view->estCompetenceMetier = true;
			$this->view->ameliorationCompetenceMetierCourant = $this->ameliorationCompetenceMetier();
		}

		// a t-on le droit d'améliorer la compétence métier
		if ($this->view->estCompetenceMetier === true && $this->view->ameliorationCompetenceMetierCourant === false) {
			$this->view->okJet2 = false;

		}  else if (($this->view->okJet1 === true || $this->braldun_competence["pourcentage_hcomp"] < 50) && $this->braldun_competence["pourcentage_hcomp"] < $this->competence["pourcentage_max"]) {
			// 2nd Jet : réussite ou non de l'amélioration de la compétence
			// seulement si la maitrise de la compétence est < 50 ou si le jet1 est réussi
			// et qu'on n'a pas le max de la compétence
			$this->view->jet2 = Bral_Util_De::get_1d100();
			$this->view->jet2Possible = true;
			if ($this->view->jet2 > $this->braldun_competence["pourcentage_hcomp"]) {
				$this->view->okJet2 = true;
			}
		}

		// 3ème Jet : % d'amélioration de la compétence
		if ($this->view->okJet2 === true) {
			if ($this->braldun_competence["pourcentage_hcomp"] < 50) {
				if ($this->view->okJet1 === true) {
					$this->view->jet3 = Bral_Util_De::get_1d6();
				} else {
					$this->view->jet3 = Bral_Util_De::get_1d3();
				}
			} else if ($this->braldun_competence["pourcentage_hcomp"] < 75) {
				$this->view->jet3 = Bral_Util_De::get_1d3();
			} else if ($this->braldun_competence["pourcentage_hcomp"] < 90) {
				$this->view->jet3 = Bral_Util_De::get_1d1();
			}
		}
	}

	// mise à jour de la table braldun competence
	private function majSuiteJets() {
		if ($this->view->okJet2 === true) { // uniquement dans le cas de réussite du jet2
			$braldunsCompetencesTable = new BraldunsCompetences();
			$pourcentage = $this->braldun_competence["pourcentage_hcomp"] + $this->view->jet3;
			if ($pourcentage > $this->competence["pourcentage_max"]) { // % comp maximum
				$pourcentage = $this->competence["pourcentage_max"];
			}
			$data = array('pourcentage_hcomp' => $pourcentage);
			$where = array("id_fk_competence_hcomp = ".$this->braldun_competence["id_fk_competence_hcomp"]." AND id_fk_braldun_hcomp = ".$this->view->user->id_braldun);
			$braldunsCompetencesTable->update($data, $where);
		}
	}

	/*
	 * Mise à jour des événements du Braldûn / du monstre.
	 */
	protected function setDetailsEvenement($details, $idType) {
		$this->detailEvenement = $details;
		$this->idTypeEvenement = $idType;
	}

	/*
	 * Mise à jour des événements de la cible.
	 */
	protected function setDetailsEvenementCible($idCible, $typeCible, $niveauCible, $detailBotCible="?") {
		$this->idCible = $idCible;
		$this->niveauCible = $niveauCible;
		$this->typeCible = $typeCible;
		$this->detailBotCible = $detailBotCible;
	}

	/*
	 * Mise à jour des événements du Braldûn / du monstre.
	 */
	protected function setEstEvenementAuto($flag) {
		$this->estEvenementAuto = $flag;
	}

	/*
	 * Mise à jour des événements du Braldûn / du monstre.
	 */
	protected function setEvenementQueSurOkJet1($flag) {
		$this->evenementQueSurOkJet1 = $flag;
	}

	/*
	 * Mise à jour des événements du Braldûn : type : compétence.
	 */
	private function majEvenementsStandard($detailsBot) {
		if ($this->estEvenementAuto === true) {
			if ($this->idTypeEvenement == null) {
				$this->idTypeEvenement = $this->view->config->game->evenements->type->competence;
			}
			if ($this->detailEvenement == null) {
				if ($this->view->okJet1 == true) {
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a réussi l'utilisation d'une compétence";
				} elseif ($this->view->okJet1 == false) {
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a raté l'utilisation d'une compétence";
				}
			}
			if ($this->view->okJet1 === true || $this->evenementQueSurOkJet1 == false) {
				Bral_Util_Evenement::majEvenements($this->view->user->id_braldun, $this->idTypeEvenement, $this->detailEvenement, $detailsBot, $this->view->user->niveau_braldun, "braldun", false, null, $this->idMatchSoule);
				if ($this->idCible != null && $this->typeCible != null){
					Bral_Util_Evenement::majEvenements($this->idCible, $this->idTypeEvenement, $this->detailEvenement, $this->detailBotCible, $this->niveauCible, $this->typeCible);
				}
			}
		}
	}

	/*
	 * Mise à jour des PA, des PX et de la balance de faim.
	 */
	protected function majBraldun() {
		Zend_Loader::loadClass("Bral_Util_Faim");
		Bral_Util_Faim::calculBalanceFaim($this->view->user);
		$this->view->user->pa_braldun = $this->view->user->pa_braldun - $this->view->nb_pa;
		$this->view->user->px_perso_braldun = $this->view->user->px_perso_braldun + $this->view->nb_px_perso;

		if ($this->view->user->est_soule_braldun == "oui") {
			Zend_Loader::loadClass("Bral_Util_Soule");
			Bral_Util_Soule::updateCagnotteDb($this->view->user, $this->view->nb_px_commun);
		} else {
			$this->view->user->px_commun_braldun = $this->view->user->px_commun_braldun + $this->view->nb_px_commun;
		}

		$data["nb_px_perso_gagnes_stats_experience"] = $this->view->nb_px_perso;
		$data["nb_px_commun_gagnes_stats_experience"] = $this->view->nb_px_commun;
		$data["id_fk_braldun_stats_experience"] = $this->view->user->id_braldun;
		$data["niveau_braldun_stats_experience"] = $this->view->user->niveau_braldun;
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$data["mois_stats_experience"] = date("Y-m-d", $moisEnCours);

		$statsExperience = new StatsExperience();
		$statsExperience->insertOrUpdate($data);

		if ($this->view->user->balance_faim_braldun < 0) {
			$this->view->user->balance_faim_braldun = 0;
		}

		if ($this->view->user->pa_braldun  < 0) { // verif au cas où...
			$this->view->user->pa_braldun = 0;
		}

		$this->view->changeNiveau = Bral_Util_Niveau::calculNiveau(&$this->view->user);

		$data = array(
			'pa_braldun' => $this->view->user->pa_braldun,
			'px_perso_braldun' => $this->view->user->px_perso_braldun,
			'px_commun_braldun' => $this->view->user->px_commun_braldun,
			'pi_braldun' => $this->view->user->pi_braldun,
			'niveau_braldun' => $this->view->user->niveau_braldun,
			'pi_cumul_braldun' => $this->view->user->pi_cumul_braldun,
			'balance_faim_braldun' => $this->view->user->balance_faim_braldun,
			'nb_braldun_ko_braldun' => $this->view->user->nb_braldun_ko_braldun,
			'nb_monstre_kill_braldun' => $this->view->user->nb_monstre_kill_braldun,
			'x_braldun' => $this->view->user->x_braldun,
			'y_braldun'  => $this->view->user->y_braldun,
			'z_braldun'  => $this->view->user->z_braldun,
			'pv_restant_braldun' => $this->view->user->pv_restant_braldun,
			'pv_max_braldun' => $this->view->user->pv_max_braldun,
			'pv_max_bm_braldun' => $this->view->user->pv_max_bm_braldun,
			'poids_transporte_braldun' => $this->view->user->poids_transporte_braldun,
			'poids_transportable_braldun' => $this->view->user->poids_transportable_braldun,
			'castars_braldun' => $this->view->user->castars_braldun,
			'force_bbdf_braldun' => $this->view->user->force_bbdf_braldun,
			'agilite_bbdf_braldun' => $this->view->user->agilite_bbdf_braldun,
			'vigueur_bbdf_braldun' => $this->view->user->vigueur_bbdf_braldun,
			'sagesse_bbdf_braldun' => $this->view->user->sagesse_bbdf_braldun,
			'agilite_bm_braldun' => $this->view->user->agilite_bm_braldun,
			'force_bm_braldun' => $this->view->user->force_bm_braldun,
			'vigueur_bm_braldun' => $this->view->user->vigueur_bm_braldun,
			'sagesse_bm_braldun' => $this->view->user->sagesse_bm_braldun,
			'agilite_base_braldun' => $this->view->user->agilite_base_braldun,
			'force_base_braldun' => $this->view->user->force_base_braldun,
			'vigueur_base_braldun' => $this->view->user->vigueur_base_braldun,
			'sagesse_base_braldun' => $this->view->user->sagesse_base_braldun,
			'duree_prochain_tour_braldun' => $this->view->user->duree_prochain_tour_braldun ,
			'armure_naturelle_braldun' => $this->view->user->armure_naturelle_braldun,
			'titre_courant_braldun' => $this->view->user->titre_courant_braldun,
			'est_engage_braldun' => $this->view->user->est_engage_braldun,
			'est_engage_next_dla_braldun' => $this->view->user->est_engage_next_dla_braldun,
			'nb_braldun_plaquage_braldun' => $this->view->user->nb_braldun_plaquage_braldun,
			'nb_plaque_braldun' => $this->view->user->nb_plaque_braldun,
			'est_soule_braldun' => $this->view->user->est_soule_braldun,
			'soule_camp_braldun' => $this->view->user->soule_camp_braldun,
			'id_fk_soule_match_braldun' => $this->view->user->id_fk_soule_match_braldun,
			'est_quete_braldun' => $this->view->user->est_quete_braldun,
			'points_gredin_braldun' => $this->view->user->points_gredin_braldun,
			'points_redresseur_braldun' => $this->view->user->points_redresseur_braldun,
			'nb_ko_redresseurs_suite_braldun' => $this->view->user->nb_ko_redresseurs_suite_braldun,
			'nb_ko_gredins_suite_braldun' => $this->view->user->nb_ko_gredins_suite_braldun,
			'nb_ko_redresseur_braldun' => $this->view->user->nb_ko_redresseur_braldun,
			'nb_ko_gredin_braldun' => $this->view->user->nb_ko_gredin_braldun,
			'nb_ko_neutre_braldun' => $this->view->user->nb_ko_neutre_braldun,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;

		$braldunTable = new Braldun();
		$braldunTable->getAdapter()->beginTransaction();
		$braldunTable->update($data, $where);
		$braldunTable->getAdapter()->commit();
		unset($braldunTable);
		unset($data);
	}

	public function getNomInterne() {
		return "box_action";
	}

	public function render() {
		$this->view->competence = $this->competence;
		switch($this->action) {
			case "ask":
				$texte = $this->view->render("competences/".$this->nom_systeme."_formulaire.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));

				return $this->view->render("competences/commun_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				$texte = $this->view->render("competences/".$this->nom_systeme."_resultat.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));

				$this->majEvenementsStandard(Bral_Helper_Affiche::copie($this->view->texte));
				if ($this->view->finMatchSoule === true) {
					Bral_Util_Soule::calculFinMatch($this->view->user, $this->view, true);
				}
				return $this->view->render("competences/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	protected function attaqueBraldun(&$braldunAttaquant, $idBraldunCible, $effetMotSPossible = true, $tir = false, $enregistreEvenementDansAttaque = false) {
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$jetAttaquant = $this->calculJetAttaque($braldunAttaquant);
		$jetsDegat = $this->calculDegat($braldunAttaquant);
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($idBraldunCible);
		$braldunCible = $braldunRowset->current();
		$jetCible = Bral_Util_Attaque::calculJetCibleBraldun($braldunCible);
		$retourAttaque = Bral_Util_Attaque::attaqueBraldun(&$braldunAttaquant, $braldunCible, $jetAttaquant, $jetCible, $jetsDegat, $this->view, false, $effetMotSPossible, $tir, $enregistreEvenementDansAttaque);
		if ($enregistreEvenementDansAttaque == true) {
			$this->idTypeEvenement = $this->idTypeEvenement = $this->view->config->game->evenements->type->attaquer;
		} else {
			$this->detailEvenement = $retourAttaque["details"];
			$this->idTypeEvenement = $retourAttaque["typeEvenement"];
		}
		$this->idMatchSoule = $retourAttaque["idMatchSoule"];
		return $retourAttaque;
	}

	protected function attaqueMonstre(&$braldunAttaquant, $idMonstre, $tir = false, $enregistreEvenementDansAttaque = false) {
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$jetAttaquant = $this->calculJetAttaque($braldunAttaquant);
		$jetsDegat = $this->calculDegat($braldunAttaquant);
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($idMonstre);
		$monstre = $monstreRowset;
		$jetCible = Bral_Util_Attaque::calculJetCibleMonstre($monstre);
		$retourAttaque = Bral_Util_Attaque::attaqueMonstre(&$braldunAttaquant, $monstre, $jetAttaquant, $jetCible, $jetsDegat, $this->view, false, $tir, false, $enregistreEvenementDansAttaque);
		if ($enregistreEvenementDansAttaque == true) {
			$this->idTypeEvenement = $this->view->config->game->evenements->type->attaquer;
		} else {
			$this->detailEvenement = $retourAttaque["details"];
			$this->idTypeEvenement = $retourAttaque["typeEvenement"];
		}

		$this->view->estQueteEvenement = $retourAttaque["etape"];
		return $retourAttaque;
	}

	private function updateCompetenceNbAction() {
		if ($this->view->okJet1 === true && $this->competence["type_competence"] == "metier") { // uniquement dans le cas de réussite du jet3
			$braldunsCompetencesTable = new BraldunsCompetences();
			$data = array(
				'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_braldun,
				'nb_action_tour_hcomp' => ($this->braldun_competence["nb_action_tour_hcomp"] + 1),
			);
			$where = array("id_fk_competence_hcomp = ".$this->braldun_competence["id_fk_competence_hcomp"]." AND id_fk_braldun_hcomp = ".$this->view->user->id_braldun);
			$braldunsCompetencesTable->update($data, $where);
		}
	}

	private function updateCompetenceNbGain() {
		if ($this->view->okJet1 === true && $this->competence["type_competence"] == "commun") { // uniquement dans le cas de réussite du jet3 et une compétence commune
			$braldunsCompetencesTable = new BraldunsCompetences();
			$data = array(
				'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_braldun,
				'nb_gain_tour_hcomp' => ($this->braldun_competence["nb_gain_tour_hcomp"] + 1),
			);
			$where = array("id_fk_competence_hcomp = ".$this->braldun_competence["id_fk_competence_hcomp"]." AND id_fk_braldun_hcomp = ".$this->view->user->id_braldun);
			$braldunsCompetencesTable->update($data, $where);
		}
	}

	private function calculNbActionMetierParDlaOk() {
		$retour = false;
		if ($this->competence["id_fk_metier_competence"] != null && $this->competence["id_fk_metier_competence"] > 0) {
			if ($this->view->user->date_debut_tour_braldun == $this->braldun_competence["date_debut_tour_hcomp"]) {
				if ($this->braldun_competence["nb_action_tour_hcomp"] >= 2) {
					$retour = false;
				} else { // < 2
					$retour = true;
				}
			} else { // premiere utilisation de la competence dans ce tour
				$retour = true;

				$braldunsCompetencesTable = new BraldunsCompetences();
				$data = array(
					'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_braldun,
					'nb_action_tour_hcomp' => 0,
				);
				$where = array("id_fk_competence_hcomp = ".$this->braldun_competence["id_fk_competence_hcomp"]." AND id_fk_braldun_hcomp = ".$this->view->user->id_braldun);
				$braldunsCompetencesTable->update($data, $where);
			}
		} else { // competence non metier
			$retour = true;
		}
		return $retour;
	}

	private function calculNbGainCommunParDlaOk() {
		$retour = false;
		if ($this->competence["type_competence"] == "commun" && $this->competence["pourcentage_max"] < 100) {
			if ($this->view->user->date_debut_tour_braldun == $this->braldun_competence["date_debut_tour_hcomp"]) {
				if ($this->braldun_competence["nb_gain_tour_hcomp"] >= 2) {
					$retour = false;
				} else { // < 2
					$retour = true;
				}
			} else { // premiere utilisation de la competence dans ce tour
				$retour = true;

				$braldunsCompetencesTable = new BraldunsCompetences();
				$data = array(
					'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_braldun,
					'nb_gain_tour_hcomp' => 0,
				);
				$where = array("id_fk_competence_hcomp = ".$this->braldun_competence["id_fk_competence_hcomp"]." AND id_fk_braldun_hcomp = ".$this->view->user->id_braldun);
				$braldunsCompetencesTable->update($data, $where);
			}
		} else { // competence non commune et soumise à un jet
			$retour = true;
		}
		return $retour;
	}

	protected function calculFinMatchSoule() {
		if ($this->view->user->est_soule_braldun == "oui") {
			Zend_Loader::loadClass("Bral_Util_Soule");
			$this->view->finMatchSoule = Bral_Util_Soule::calculFinMatch($this->view->user, $this->view, false);
		}
	}

	protected function calculEchoppe($metier) {
		// On regarde si le Braldûn est dans une de ses echopppes
		$this->view->estSurEchoppe = false;
		Zend_Loader::loadClass("Echoppe");
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$idEchoppe = null;
		foreach($echoppes as $e) {
			if ($e["id_fk_braldun_echoppe"] == $this->view->user->id_braldun &&
			$e["nom_systeme_metier"] == $metier &&
			$e["x_echoppe"] == $this->view->user->x_braldun &&
			$e["y_echoppe"] == $this->view->user->y_braldun &&
			$e["z_echoppe"] == $this->view->user->z_braldun) {
				$this->view->estSurEchoppe = true;
				$idEchoppe = $e["id_echoppe"];
				break;
			}
		}
		$this->view->idEchoppe = $idEchoppe;
		if ($idEchoppe != null) {
			return true;
		} else {
			return false;
		}
	}

	protected function calculCharrette() {
		// On regarde si le Braldûn possède une charrette
		$this->view->possedeCharrette = false;
		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdBraldun($this->view->user->id_braldun);
		if ($charrette != null && count($charrette) == 1) {
			$this->view->possedeCharrette = true;
			$this->view->idCharrette = $charrette[0]["id_charrette"];
			$this->view->poidsRestantCharrette = $charrette[0]["poids_transportable_charrette"] - $charrette[0]["poids_transporte_charrette"];
		}
	}
}
