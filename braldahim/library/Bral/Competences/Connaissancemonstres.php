<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Competences_Connaissancemonstres extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Commun");
		Zend_Loader::loadClass("Monstre");
		
		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}
		
		$vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		$this->view->distance = $vue_nb_cases;
		
		$x_min = $this->view->user->x_hobbit - $this->view->distance;
		$x_max = $this->view->user->x_hobbit + $this->view->distance;
		$y_min = $this->view->user->y_hobbit - $this->view->distance;
		$y_max = $this->view->user->y_hobbit + $this->view->distance;
		
		// recuperation des monstres qui sont presents sur la vue
		$tabMonstres = null;
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max);
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
			);
		}
		
		$this->view->tabMonstres = $tabMonstres;
		$this->view->nMonstres = count($tabMonstres);
		
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
					break;
				}
			}
		}
		if ($cdmMonstre === false) {
			throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculCDM($idMonstre);
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculCDM($idMonstre) {
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($idMonstre);
		$monstre = $monstreRowset;
		$tabCDM["id_monstre"] = $monstre["id_monstre"];
		$tabCDM["nom_monstre"] = $monstre["nom_type_monstre"];
		if ($monstre["genre_type_monstre"] == "feminin")
			$tabCDM["taille_monstre"] = $monstre["nom_taille_f_monstre"];
		else
			$tabCDM["taille_monstre"] = $monstre["nom_taille_m_monstre"];

		//Distance du monstre
		$dist = max(abs($monstre["x_monstre"])-abs($this->view->user->x_hobbit),abs($monstre["y_monstre"])-abs($this->view->user->y_hobbit));
		
		/*
		 * 0 case : +/- [0,5;5] %
		 * 1 case : +/- [1;10] %
		 * 2 cases : +/- [1,5;15] %
		 * 3 cases  : +/- [2;20] %
		 * 4 cases et plus : +/- [2,5;25] %
		 */
		
		switch ($dist) {
			case 0:
				$rand_min = 5;
				$rand_max = 50;
				$val_zero = 1;
				break;
			case 1:
				$rand_min = 10;
				$rand_max = 100;
				$val_zero = 2;
				break;
			case 2:
				$rand_min = 15;
				$rand_max = 150;
				$val_zero = 3;
				break;
			case 3:
				$rand_min = 20;
				$rand_max = 200;
				$val_zero = 4;
				break;
			default:
				$rand_min = 25;
				$rand_max = 250;
				$val_zero = 5;
				break;	
		}
		
		$tabCDM["min_niveau_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["niveau_monstre"]);
		$tabCDM["max_niveau_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["niveau_monstre"]);
		
		$tabCDM["min_vue_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["vue_monstre"]);
		$tabCDM["max_vue_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["vue_monstre"]);
		
		$tabCDM["min_for_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["force_base_monstre"]);
		$tabCDM["max_for_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["force_base_monstre"]);
		
		$tabCDM["min_agi_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["agilite_base_monstre"]);
		$tabCDM["max_agi_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["agilite_base_monstre"]);
		
		$tabCDM["min_sag_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["sagesse_base_monstre"]);
		$tabCDM["max_sag_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["sagesse_base_monstre"]);
		
		$tabCDM["min_vig_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["vigueur_base_monstre"]);
		$tabCDM["max_vig_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["vigueur_base_monstre"]);
		
		$tabCDM["min_reg_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["regeneration_monstre"]);
		$tabCDM["max_reg_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["regeneration_monstre"]);
		
		$tabCDM["min_arm_monstre"] = $this->calculMin ($rand_min,$rand_max,$monstre["armure_naturelle_monstre"]);
		$tabCDM["max_arm_monstre"] = $this->calculMax ($val_zero,$rand_min,$rand_max,$monstre["armure_naturelle_monstre"]);
		
		//TODO
		//PV
		//ordre affichage dans formulaire
		//Gestion des PX
		
		$this->view->tabCDM = $tabCDM;
		$this->view->dist = $dist;
		
	}
	
	private function calculMin($alea_min,$alea_max,$valeur) {
		return floor($valeur * (1 - rand($alea_min,$alea_max)/1000));
	}
	
	private function calculMax($val_nulle,$alea_min,$alea_max,$valeur) {
		if ($valeur != 0 )
			return ceil($valeur * (1 + rand($alea_min,$alea_max)/1000));
		else
			return $val_nulle;
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_evenements", "box_competences_communes");
	}
}
?>