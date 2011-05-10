<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Connaissancemonstres extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Commun");
		Zend_Loader::loadClass("Monstre");

		/*
		 * Si le Braldûn n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}

		$vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun;
		$this->view->distance = $vue_nb_cases;
		
		if ($this->view->distance < 0) {
			$this->view->distance = 0;
		}

		$x_min = $this->view->user->x_braldun - $this->view->distance;
		$x_max = $this->view->user->x_braldun + $this->view->distance;
		$y_min = $this->view->user->y_braldun - $this->view->distance;
		$y_max = $this->view->user->y_braldun + $this->view->distance;
		
		// recuperation des monstres qui sont presents sur la vue
		$tabMonstres = null;
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun);
		foreach($monstres as $m) {
			if ($m["genre_type_monstre"] == 'feminin') {
				$m_taille = $m["nom_taille_f_monstre"];
			} else {
				$m_taille = $m["nom_taille_m_monstre"];
			}
			$tabMonstres[] = array(
				'id_monstre' => $m["id_monstre"], 
				'nom_monstre' => $m["nom_type_monstre"], 
				'taille_monstre' => $m_taille,
				'x_monstre' => $m["x_monstre"],
				'y_monstre' => $m["y_monstre"],
				'dist_monstre' => max(abs($m["x_monstre"] - $this->view->user->x_braldun), abs($m["y_monstre"]-$this->view->user->y_braldun))
			);
		}

		$this->view->tabMonstres = $tabMonstres;
		$this->view->nMonstres = count($tabMonstres);
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
		if ($this->view->nMonstres > 0) {
			foreach ($this->view->tabMonstres as $key => $row) {
				$dist[$key] = $row['dist_monstre'];
			}
			array_multisort($dist, SORT_ASC, $this->view->tabMonstres);
		}
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_1");
		}

		$cdmMonstre = false;
		if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
			foreach ($this->view->tabMonstres as $m) {
				if ($m["id_monstre"] == $idMonstre) {
					$cdmMonstre = true;
					$dist = $m["dist_monstre"];
					$this->view->distance = $dist;
					break;
				}
			}
		}
		
		$this->view->monstreVisible = true;

		if ($cdmMonstre === false) {
			$this->view->monstreVisible = false;
			$this->setNbPaSurcharge(0);
		} else {

			$this->calculJets();
			if ($this->view->okJet1 === true) {
				$this->calculCDM($idMonstre, $dist);
			}
			$this->calculPx();
			$this->calculBalanceFaim();
			$this->majBraldun();
		}
	}

	private function calculCDM($idMonstre, $dist_monstre) {
		Zend_Loader::loadClass("BraldunsCdm");
		Zend_Loader::loadClass("BraldunsCompetences");

		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($idMonstre);
		$monstre = $monstreRowset;
		$tabCDM["id_monstre"] = $monstre["id_monstre"];
		$tabCDM["nom_monstre"] = $monstre["nom_type_monstre"];
		$tabCDM["id_taille_monstre"] = $monstre["id_fk_taille_monstre"];

		if ($monstre["genre_type_monstre"] == "feminin") {
			$tabCDM["taille_monstre"] = $monstre["nom_taille_f_monstre"];
			$article = "une";
		} else {
			$tabCDM["taille_monstre"] = $monstre["nom_taille_m_monstre"];
			$article = "un";
		}

		$tabCDM["min_niveau_monstre"] = $monstre["niveau_monstre"] - (Bral_Util_De::get_1d3());
		$tabCDM["max_niveau_monstre"] = $monstre["niveau_monstre"] + (Bral_Util_De::get_1d3());
		if ( $tabCDM["min_niveau_monstre"] < 0 ){
			$tabCDM["min_niveau_monstre"] = 0;
		}

		$tabCDM["max_vue_monstre"] = $monstre["vue_monstre"] + $monstre["vue_malus_monstre"];

		$tabCDM["max_deg_monstre"] = ($monstre["force_base_monstre"] + $this->view->config->game->base_force)*6 + $monstre["force_bm_monstre"] + $monstre["bm_degat_monstre"];

		$tabCDM["max_att_monstre"] = ($monstre["agilite_base_monstre"] + $this->view->config->game->base_agilite)*6 + $monstre["agilite_bm_monstre"] + $monstre["bm_attaque_monstre"];
		
		$tabCDM["max_def_monstre"] = ($monstre["agilite_base_monstre"] + $this->view->config->game->base_agilite)*6 + $monstre["agilite_bm_monstre"] + $monstre["bm_defense_monstre"];
		
		$tabCDM["max_sag_monstre"] = ($monstre["sagesse_base_monstre"] + $this->view->config->game->base_sagesse)*6 + $monstre["sagesse_bm_monstre"];

		$tabCDM["max_vig_monstre"] = ($monstre["vigueur_base_monstre"] + $this->view->config->game->base_vigueur)*6 + $monstre["vigueur_bm_monstre"];

		$tabCDM["max_reg_monstre"] = $monstre["regeneration_monstre"] * 10 + $monstre["regeneration_malus_monstre"];

		$tabCDM["min_arm_monstre"] = floor($monstre["armure_naturelle_monstre"] - $monstre["armure_naturelle_monstre"] * (Bral_Util_De::get_1D10())/100);
		$tabCDM["max_arm_monstre"] = ceil($monstre["armure_naturelle_monstre"] + $monstre["armure_naturelle_monstre"] * (Bral_Util_De::get_1D10())/100);
		if ( $tabCDM["max_arm_monstre"] == 0 ){
			$tabCDM["max_arm_monstre"] = 1;
		}

		$tabCDM["min_pvmax_monstre"] = floor($monstre["pv_max_monstre"] - $monstre["pv_max_monstre"] * (Bral_Util_De::get_1D10())/100);
		$tabCDM["max_pvmax_monstre"] = ceil($monstre["pv_max_monstre"] + $monstre["pv_max_monstre"] * (Bral_Util_De::get_1D10())/100);

		$tabCDM["min_pvact_monstre"] = floor($monstre["pv_restant_monstre"] - $monstre["pv_restant_monstre"] * (Bral_Util_De::get_1D10())/100);
		$tabCDM["max_pvact_monstre"] = ceil($monstre["pv_restant_monstre"] + $monstre["pv_restant_monstre"] * (Bral_Util_De::get_1D10())/100);
		if ($tabCDM["max_pvact_monstre"] > $tabCDM["max_pvmax_monstre"]) {
			$tabCDM["max_pvact_monstre"] = $tabCDM["max_pvmax_monstre"];
		}
		if ($tabCDM["min_pvact_monstre"] > $tabCDM["min_pvmax_monstre"]) {
			$tabCDM["min_pvact_monstre"] = $tabCDM["min_pvmax_monstre"];
		}

		$duree_tour_minute = Bral_Util_ConvertDate::getMinuteFromHeure($monstre["duree_prochain_tour_monstre"]);
		$tabCDM["min_dla_monstre"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_tour_minute - floor($duree_tour_minute * (Bral_Util_De::get_1D10())/100));
		$tabCDM["max_dla_monstre"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_tour_minute + ceil($duree_tour_minute * (Bral_Util_De::get_1D10())/100));

		$this->view->tabCDM = $tabCDM;

		$id_type = $this->view->config->game->evenements->type->competence;
		$details = "[b".$this->view->user->id_braldun."] a réussi l'utilisation d'une compétence sur ".$article." [m".$monstre["id_monstre"]."]";
		$this->setDetailsEvenement($details, $id_type);
		$this->setDetailsEvenementCible($monstre["id_monstre"], "monstre", $monstre["niveau_monstre"]);

		$data = array(
			'id_fk_braldun_hcdm' => $this->view->user->id_braldun,
			'id_fk_monstre_hcdm'  => $idMonstre,
			'id_fk_type_monstre_hcdm'  => $monstre["id_type_monstre"],
			'id_fk_taille_monstre_hcdm'  => $monstre["id_taille_monstre"],
		);

		$braldunCdmTable = new BraldunsCdm();
		$braldunCdmTable->insertOrUpdate($data);

		Zend_Loader::loadClass("TailleMonstre");

		$pister = null;
		if ($tabCDM["id_taille_monstre"] != TailleMonstre::ID_TAILLE_BOSS) {
			$pister = $braldunCdmTable->findByIdBraldunAndIdTypeMonstre($this->view->user->id_braldun,$monstre["id_type_monstre"]);
		}
		$braldunCompetence = new BraldunsCompetences();
		$braldunPister = $braldunCompetence->findByIdBraldunAndNomSysteme($this->view->user->id_braldun,'pister');

		$this->view->pister = $pister;
		$this->view->possedePister = (count($braldunPister) == 1);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences", "box_laban"));
	}
}
