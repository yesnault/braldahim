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

class Bral_Competences_Tirer extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass("Bral_Util_Commun");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("LabanMunition");
		
		//on verifie que le hobbit porte une arme de tir
		$armeTirPortee = false;
		$munitionPortee = false;
		$hobbitEquipement = new HobbitEquipement();
		$equipementPorteRowset = $hobbitEquipement->findByTypePiece($this->view->user->id_hobbit,"arme_tir");
		
		if (count($equipementPorteRowset) > 0){
			$armeTirPortee = true;
			//on verifie qu'il a des munitions et que ce sont les bonnes
			$labanMunition = new LabanMunition();
			$munitionPorteRowset = 	$labanMunition->findByIdHobbit($this->view->user->id_hobbit);
			if (count ($munitionPorteRowset) > 0) {
				foreach ($equipementPorteRowset as $eq){
					foreach ($munitionPorteRowset as $mun){
						if ($mun['id_fk_type_laban_munition'] == $eq['id_fk_type_munition_type_equipement']){
							$munitionPortee = true;
							break;
						}
					}
				}
			}
		}
		
		if ($armeTirPortee == true && $munitionPortee == true){
		
			//On ne peut tirer qu'à 4 cases maxi.
			$this->view->tir_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
			if ($this->view->tir_nb_cases > 4) {
				$this->view->tir_nb_cases = 4;
			}
			
			//On calcule les cases où on peut tirer.
			$x_min = $this->view->user->x_hobbit - $this->view->tir_nb_cases;
			$x_max = $this->view->user->x_hobbit + $this->view->tir_nb_cases;
			$y_min = $this->view->user->y_hobbit - $this->view->tir_nb_cases;
			$y_max = $this->view->user->y_hobbit + $this->view->tir_nb_cases;
			
			for ($i = 0 ; $i <= $this->view->tir_nb_cases ; $i++) {
				$xdiagonale_bas_haut = $x_min + $i;
				$xdiagonale_haut_bas = $x_max - $i;
				$ydiagonale_bas_haut = $y_min + $i;
				$ydiagonale_haut_bas = $y_max - $i;
				$tabValide[$xdiagonale_bas_haut][$ydiagonale_bas_haut] = true;
				$tabValide[$xdiagonale_haut_bas][$ydiagonale_haut_bas] = true;
				$tabValide[$xdiagonale_bas_haut][$ydiagonale_haut_bas] = true;
				$tabValide[$xdiagonale_haut_bas][$ydiagonale_bas_haut] = true;
			}
			
			$tabHobbits = null;
			$tabMonstres = null;
	
			$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_hobbit, $this->view->user->y_hobbit);
			
			if ($estRegionPvp) {
				// recuperation des hobbits qui sont presents sur la vue
				$hobbitTable = new Hobbit();
				$hobbits = $hobbitTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->id_hobbit);
				
				foreach($hobbits as $h) {
					$tab = array(
						'id_hobbit' => $h["id_hobbit"],
						'nom_hobbit' => $h["nom_hobbit"],
						'prenom_hobbit' => $h["prenom_hobbit"],
						'x_hobbit' => $h["x_hobbit"],
						'y_hobbit' => $h["y_hobbit"],
						'dist_hobbit' => max(abs($h["x_hobbit"] - $this->view->user->x_hobbit), abs($h["y_hobbit"] - $this->view->user->y_hobbit))
					);
					$tabHobbits[] = $tab;
				}
			}
			
			// recuperation des monstres qui sont presents sur la vue
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
					'niveau_monstre' => $m["niveau_monstre"],
					'x_monstre' => $m["x_monstre"],
					'y_monstre' => $m["y_monstre"],
					'dist_monstre' => max(abs($m["x_monstre"] - $this->view->user->x_hobbit), abs($m["y_monstre"]-$this->view->user->y_hobbit))
				);
			}
			$this->view->tabHobbits = $tabHobbits;
			$this->view->nHobbits = count($tabHobbits);
			$this->view->tabMonstres = $tabMonstres;
			$this->view->nMonstres = count($tabMonstres);
			$this->view->estRegionPvp = $estRegionPvp;
		}
		$this->view->armeTirPortee = $armeTirPortee;
		$this->view->munitionPortee = $munitionPortee;
	}
	
	function prepareFormulaire() {
		//on trie suivant la distance
		if ($this->view->nMonstres > 0) {
			foreach ($this->view->tabMonstres as $key => $row) {
    			$dist[$key] = $row['dist_monstre'];
			}
			array_multisort($dist, SORT_ASC, $this->view->tabMonstres);
		}
		
		if ($this->view->nHobbits > 0) {
			foreach ($this->view->tabHobbits as $key => $row) {
    			$dist[$key] = $row['dist_hobbit'];
			}
			array_multisort($dist, SORT_ASC, $this->view->tabHobbits);
		}
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
			throw new Zend_Exception(get_class($this)." Monstre ou Hobbit invalide (!=-1)");
		}
		
		//$attaqueMonstre = false;
		//$attaqueHobbit = false;
		if ($idHobbit != -1) {
			if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
				foreach ($this->view->tabHobbits as $h) {
					if ($h["id_hobbit"] == $idHobbit) {
						//$attaqueHobbit = true;
						$this->view->retourAttaque = $this->attaqueHobbit($this->view->user, $idHobbit);
						//Gérer uniquement pour l'ARM NAT
						//Décompter une munition
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
						//$attaqueMonstre = true;
						$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre);
						//Gérer uniquement pour l'ARM NAT
						//Décompter une munition
						break;
					}
				}
			}
			if ($attaqueMonstre === false) {
				throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
			}
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 * Le jet d'attaque d'un tir est différent : JA = (Jet d'AGI + BM) * coeff 
	 */
	protected function calculJetAttaque($hobbit) {
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->view->config->game->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$coef=1;
		
		//calcul du coef suivant palissade (attention aux diagonales ?)
		
		
		$jetAttaquant = $coef * ($jetAttaquant + $hobbit->agilite_bm_hobbit + $hobbit->agilite_bbdf_hobbit + $hobbit->bm_attaque_hobbit);
		return $jetAttaquant;
	}

	/*
	 * Le jet de dégats diffère aussi : 
	 * moyenne(jet AGI+BM;jet SAG +BM)
	 * cas du critique :
	 * 1,5*moyenne(jet AGI+BM;jet SAG +BM)
	 */
	protected function calculDegat($hobbit) {
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;
		
		for ($i=1; $i<= ($this->view->config->game->base_force + $hobbit->force_base_hobbit) * $coefCritique; $i++) {
			$jetDegat["critique"] = $jetDegat["critique"] + Bral_Util_De::get_1d6();
		}
		$jetDegat["critique"] = $jetDegat["critique"] + $this->view->user->force_bm_hobbit + $this->view->user->force_bbdf_hobbit;
		
		for ($i=1; $i<= ($this->view->config->game->base_force + $hobbit->force_base_hobbit); $i++) {
			$jetDegat["noncritique"] = $jetDegat["noncritique"] + Bral_Util_De::get_1d6();
		}
		$jetDegat["noncritique"] = $jetDegat["noncritique"] + $this->view->user->force_bm_hobbit + $this->view->user->force_bbdf_hobbit;
		
		for ($i=1; $i<= $this->view->config->game->base_vigueur + $hobbit->vigueur_base_hobbit; $i++) {
			$jetDegat["critique"] = $jetDegat["critique"] + Bral_Util_De::get_1d6();
			$jetDegat["noncritique"] = $jetDegat["noncritique"] + Bral_Util_De::get_1d6();
		}
		
		$jetDegat["critique"] = $jetDegat["critique"] + $hobbit->vigueur_bm_hobbit + $hobbit->vigueur_bbdf_hobbit + $hobbit->bm_degat_hobbit;
		$jetDegat["noncritique"] = $jetDegat["noncritique"] + $hobbit->vigueur_bm_hobbit + $hobbit->vigueur_bbdf_hobbit + $hobbit->bm_degat_hobbit;

		return $jetDegat;
	}
	
	function calculTirer(){
		Zend_Loader::loadClass("Palissade");
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_laban", "box_lieu"));
	}
	
}