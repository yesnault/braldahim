<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Competences_Debusquer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("TailleMonstre");
		Zend_Loader::loadClass("Bral_Util_Quete");
		Zend_Loader::loadClass("Monstre");
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculDebusquer();
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculDebusquer() {

		Zend_Loader::loadClass("TypeMonstre");
		$typeMonstreTable = new TypeMonstre();

		$typesMonstre = $typeMonstreTable->fetchAllByTypeGroupe($this->view->config->game->groupe_monstre->type->gibier);
		if ($typesMonstre == null || count($typesMonstre) < 1) {
			throw new Zend_Exception(get_class($this)." Type Monstre Gibier invalide");
		}
		$deType = Bral_Util_De::get_de_specifique(1, count($typesMonstre)) - 1;
		$typeGibier = $typesMonstre[$deType];

		$this->initVariablesVue();
		$coefH = $this->calculCoefH();
		$coefM = $this->calculCoefM();
		$coefD = $this->calculCoefD();
		$coefT = $this->calculCoefT();

		$nbGibier = floor($this->view->user->agilite_base_hobbit / 4) + 1 + $coefH + $coefM + $coefD + $coefT;
		if ($nbGibier < 1) {
			$nbGibier = 1;	
		}
		
		for ($i = 1; $i <= $nbGibier; $i++) {
			$idTaille = $this->calculTaille();
			$this->creationGibier($typeGibier["id_type_monstre"], $idTaille);
		} 

		$this->view->nbGibier = $nbGibier;
	}
	
	private function creationGibier($id_fk_type_monstre, $id_fk_taille_monstre) {
		
		$niveau_monstre = 0;
		$niveau_force = 0;
		$niveau_sagesse = 0;
		$niveau_agilite = 0;
		$niveau_vigueur = 0;
		
		$aleaX = Bral_Util_De::get_1d4();
		$aleaY = Bral_Util_De::get_1d4();
		if (Bral_Util_De::get_1d2() == 1) {
			$aleaX = -$aleaX;
		}
		if (Bral_Util_De::get_1d2() == 1) {
			$aleaY = -$aleaY;
		}
		
		$x_monstre = $this->view->user->x_hobbit + $aleaX;
		$y_monstre = $this->view->user->y_hobbit + $aleaY;
		
		$force_base_monstre = $this->view->config->game->inscription->force_base + $niveau_force;
		$sagesse_base_monstre = $this->view->config->game->inscription->sagesse_base + $niveau_sagesse;
		$agilite_base_monstre = $this->view->config->game->inscription->agilite_base + $niveau_agilite;
		$vigueur_base_monstre = $this->view->config->game->inscription->vigueur_base + $niveau_vigueur;
		
		//REG
		$regeneration_monstre = 1;

		//ARMNAT
		$armure_naturelle_monstre = 0;

		//DLA
		$dla_monstre = Bral_Util_ConvertDate::get_time_from_minutes(720 - 10 * $niveau_sagesse);
		$date_fin_tour_monstre = date("Y-m-d H:i:s");
		
		//Le gibier reste visible 1+1D3 jour
		$dateSuppressionGibier = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), 1 + Bral_Util_De::get_1d3());

		//PV
		$pv_restant_monstre = 1;

		//Vue
		$vue_monstre = 1;

		$data = array(
			"id_fk_type_monstre" => $id_fk_type_monstre,
			"id_fk_taille_monstre" => $id_fk_taille_monstre,
			"id_fk_groupe_monstre" => null,
			"x_monstre" => $x_monstre,
			"y_monstre" => $y_monstre,
			"x_direction_monstre" => $x_monstre,
			"y_direction_monstre" => $y_monstre,
			"id_fk_hobbit_cible_monstre" => null,
			"pv_restant_monstre" => $pv_restant_monstre,
			"pv_max_monstre" => $pv_restant_monstre,
			"niveau_monstre" => $niveau_monstre,
			"vue_monstre" => $vue_monstre,
			"force_base_monstre" => $force_base_monstre,
			"force_bm_monstre" => 0,
			"agilite_base_monstre" => $agilite_base_monstre,
			"agilite_bm_monstre" => 0,
			"sagesse_base_monstre" => $sagesse_base_monstre,
			"sagesse_bm_monstre" => 0,
			"vigueur_base_monstre" => $vigueur_base_monstre,
			"vigueur_bm_monstre" => 0,
			"regeneration_monstre" => $regeneration_monstre,
			"armure_naturelle_monstre" => $armure_naturelle_monstre,
			"date_fin_tour_monstre" => $date_fin_tour_monstre,
			"duree_base_tour_monstre" => $dla_monstre,
			"nb_kill_monstre" => 0,
			"date_creation_monstre" => date("Y-m-d H:i:s"),
			"est_mort_monstre" => 'non',
			"pa_monstre" => 0, // pas de PA à la creation.
			"date_suppression_monstre" => $dateSuppressionGibier,
		);
		
		$monstreTable = new Monstre();
		$id_monstre = $monstreTable->insert($data);
	}

	/*
	 * Petit : -0,5625 * % comp Débusquer + 55,625
	 * Normal : 0,125 * % comp Débusquer + 38,75
	 * Grand : 0,3125 * % comp Débusquer + 1,875
	 * Gigantesque : 0,125 * % comp Débusquer + 3,75
	 */
	private function calculTaille() {
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"] / 100;

		$chance_a = -0.5625 * $maitrise + 55.625 ;
		$chance_b = 0.125 * $maitrise + 38.75 ;
		$chance_c = 0.3125 * $maitrise + 1.875 ;
		$chance_d = 0.125 * $maitrise + 3.75 ;

		/*
		 * Seul le meilleur des n jets est gardé. n=(BM AGI/2)+1.
		 */
		$n = (($this->view->user->agilite_bm_hobbit + $this->view->user->agilite_bbdf_hobbit) / 2 ) + 1;

		if ($n < 1) $n = 1;
		$tirage = 0;

		for ($i = 1; $i <= $n; $i ++) {
			$tirageTemp = Bral_Util_De::get_1d100();
			if ($tirageTemp > $tirage) {
				$tirage = $tirageTemp;
			}
		}

		$tailleMonstre = -1;
		if ($tirage > 0 && $tirage <= $chance_a) {
			$tailleMonstre = TailleMonstre::ID_TAILLE_PETIT;
		} elseif ($tirage > $chance_a && $tirage <= $chance_a + $chance_b) {
			$tailleMonstre = TailleMonstre::ID_TAILLE_NORMAL;
		} elseif ($tirage > $chance_a + $chance_b && $tirage <= $chance_a + $chance_b + $chance_c) {
			$tailleMonstre = TailleMonstre::ID_TAILLE_GRAND;
		} else {
			$tailleMonstre = TailleMonstre::ID_TAILLE_GIGANTESQUE;
		}
		
		return $tailleMonstre;
	}
	
	private function initVariablesVue() {
		$this->view->vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		$this->view->x_min = $this->view->user->x_hobbit - $this->view->vue_nb_cases;
		$this->view->x_max = $this->view->user->x_hobbit + $this->view->vue_nb_cases;
		$this->view->y_min = $this->view->user->y_hobbit - $this->view->vue_nb_cases;
		$this->view->y_max = $this->view->user->y_hobbit + $this->view->vue_nb_cases;
	}

	/*
	 * CoefH : nb Hobbit dans sa vue :
	 * < 2 : 1
	 * de 2 à 5 : -1
	 * de 6 à 10 : -3
	 * > 10 : -5
	 */
	private function calculCoefH() {
		$retour = 0;

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);

		$nbHobbits = count($hobbits);
		if ($nbHobbits < 2) {
			$retour = 1;
		} else if ($nbHobbits <= 5) {
			$retour = -1;
		} else if ($nbHobbits <= 10) {
			$retour = -3;
		} else if ($nbHobbits > 10) {
			$retour = -5;
		}
		return $retour;
	}

	/*
	 * CoefM : nb monstres dans sa vue :
	 * < 2 : 1
	 * de 2 à 5 : -1
	 * de 6 à 10 : -3
	 * > 10 : -5
	 */
	private function calculCoefM() {
		$retour = 0;
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);

		$nbMonstres = count($monstres);
		if ($nbMonstres < 2) {
			$retour = 1;
		} else if ($nbMonstres <= 5) {
			$retour = -1;
		} else if ($nbMonstres <= 10) {
			$retour = -3;
		} else if ($nbMonstres > 10) {
			$retour = -5;
		}
		return $retour;
	}

	/*
	 * CoefD : distance du batiment le plus proche :
	 * < 10 cases : -5
	 * de 11 à 20 : -2
	 * > 20 : 1
	 */
	private function calculCoefD() {
		$retour = 0;
		
		Zend_Loader::loadClass("Lieu");
		$lieu = new Lieu();
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->findByPositionMax($this->view->user->x_hobbit, $this->view->user->y_hobbit, 20);
		
		if ($lieux == null || count($lieux) <= 0) {
			$retour = 1;
		} else {
			$lieu = $lieux[0];
			if ($lieu["distance"] <= 10) {
				$retour = -5;
			} else if ($lieu["distance"] < 20) {
				$retour = -2;
			} else {
				$retour = 1;
			}
		}

		return $retour;
	}

	/*
	 * CoefT : fonction du type de terrain :
	 * Plaine, Gazon : 0
	 * Marais, Montagne : -1
	 * Forêt : 1
	 */
	private function calculCoefT() {
		$retour = 0;

		Zend_Loader::loadClass("Zone");

		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($zoneTable);
		$zone = $zones[0];
		unset($zones);

		switch($zone["nom_systeme_environnement"]) {
			case "marais":
			case "montagne":
				$retour = -1;
				break;
			case "caverne":
			case "plaine" :
			case "gazon" :
				$retour = 0;
				break;
			case "foret" :
				$retour = 1;
				break;
			default :
				throw new Exception("Debusquer Environnement invalide:".$zone["nom_systeme_environnement"]. " x=".$this->view->user->x_hobbit." y=".$this->view->user->y_hobbit);
		}

		return $retour;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue"));
	}
}
