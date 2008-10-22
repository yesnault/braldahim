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
/*
 * La distance de charge est basé sur la vigueur avec une réduction suivant le terrain de départ :
 * En plaine :
 * VIG 0-2 -> 1 case
 * 3-5 -> 2 cases
 * 6-8 -> 3 cases
 * 9-11 -> 4 cases
 * 12-14 -> 5 cases
 * 15+  -> 6 cases
 * En forêt un malus de -1, ne marais et montagne un malus de -2 sur la distance est apliqué (minimum 1).
 * La distance de charge est borné par la vue.
 * 
 * Le jet d'attaque d'une charge est différent : (0.5 jet AGI) + BM + bonus arme
 * 
 * Le jet de dégats diffère aussi : jet FOR + BM FOR + bonus arme + jet VIG + BM VIG
 * cas du critique :
 * 1.5(jet FOR) + BM FOR + bonus arme + jet VIG + BM VIG
 * 
 * On ne peut pas charger sur une cible qui est sur sa propre case.
 * 
 * On ne peut pas charger si l'une des cases entre le chargeur et le charger est une palissade.
 * 
 * Ne peut pas être utilisé en ville.
 */
class Bral_Competences_Charger extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass("Ville"); 
		
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->chargerVilleOk = true;
		
		if (count($villes) > 0) {
			$this->view->chargerVilleOk = false;
			return;
		}	
		
		$this->view->charge_nb_cases = floor($this->view->user->vigueur_base_hobbit / 3) + 1;
		if ($this->view->charge_nb_cases > 6) {
			$this->view->charge_nb_cases = 6;
		}
		
		//En forêt un malus de -1 en distance, en marais et montagne un malus de -2 sur la distance est appliqué
		$environnement = Bral_Util_Commun::getEnvironnement($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		if ($environnement == "montage" || $environnement == "marais") {
			$this->view->charge_nb_cases = $this->view->charge_nb_cases  - 2;
		} elseif ($environnement == "foret") {
			$this->view->charge_nb_cases = $this->view->charge_nb_cases  - 1;
		}
		
		//minimum de distance de charge à 1 case dans tous les cas
		if ($this->view->charge_nb_cases < 1) {
			$this->view->charge_nb_cases = 1;
		}
		
		//La distance de charge est bornée par la VUE
		$vue = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		if ($vue < $this->view->charge_nb_cases) {
			$this->view->charge_nb_cases = $vue;
		}
		
		$x_min = $this->view->user->x_hobbit - $this->view->charge_nb_cases;
		$x_max = $this->view->user->x_hobbit + $this->view->charge_nb_cases;
		$y_min = $this->view->user->y_hobbit - $this->view->charge_nb_cases;
		$y_max = $this->view->user->y_hobbit + $this->view->charge_nb_cases;
		
		$tabValide = null;
		for ($i = $x_min ; $i <= $x_max ; $i++) {
			$tabValide[$i][$this->view->user->y_hobbit] = true;
			for ($j = $y_min ; $j <= $y_max ; $j++) {
				if (!isset($tabValide[$i][$j])) {
					$tabValide[$i][$j] = false;
				}
				$tabValide[$this->view->user->x_hobbit][$j] = true;
			}
		}
		
		for ($i = 0 ; $i <= $this->view->charge_nb_cases ; $i++) {
			$xdiagonale_bas_haut = $x_min + $i;
			$xdiagonale_haut_bas = $x_max - $i;
			$ydiagonale_bas_haut = $y_min + $i;
			$ydiagonale_haut_bas = $y_max - $i;
			$tabValide[$xdiagonale_bas_haut][$ydiagonale_bas_haut] = true;
			$tabValide[$xdiagonale_haut_bas][$ydiagonale_haut_bas] = true;
			$tabValide[$xdiagonale_bas_haut][$ydiagonale_haut_bas] = true;
			$tabValide[$xdiagonale_haut_bas][$ydiagonale_bas_haut] = true;
		}
		
		// On ne peut pas charger sur une cible qui est sur sa propre case.
		$tabValide[$this->view->user->x_hobbit][$this->view->user->y_hobbit] = false;
		
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($x_min, $y_min, $x_max, $y_max);
		
		foreach($palissades as $p) {
			$tabValide[$p["x_palissade"]][$p["y_palissade"]] = false;
			if ($p["x_palissade"] == $this->view->user->x_hobbit) {
				if ($p["x_palissade"] < $this->view->user->x_hobbit) {
					for ($i = $y_min; $i<= $p["y_palissade"]; $i++) {
						$tabValide[$p["x_palissade"]][$i] = false;
					}
				} else {
					for ($i = $y_max; $i>= $p["y_palissade"]; $i--) {
						$tabValide[$p["x_palissade"]][$i] = false;
					}
				}
			}
			if ($p["y_palissade"] == $this->view->user->y_hobbit) {
				if ($p["y_palissade"] < $this->view->user->x_hobbit) {
					for ($i = $x_min; $i<= $p["x_palissade"]; $i++) {
						$tabValide[$i][$p["x_palissade"]] = false;
					}
				} else {
					for ($i = $x_max; $i>= $p["x_palissade"]; $i--) {
						$tabValide[$i][$p["y_palissade"]] = false;
					}
				}
			}
			if ($p["x_palissade"] <= $this->view->user->x_hobbit && 
				$p["y_palissade"] >= $this->view->user->y_hobbit) {
				for ($i = $x_min; $i<= $p["x_palissade"]; $i++) {
					for ($j = $y_max; $j>= $p["y_palissade"]; $j--) {
							$tabValide[$i][$j] = false;
					}
				}
			} elseif ($p["x_palissade"] >= $this->view->user->x_hobbit && 
					  $p["y_palissade"] >= $this->view->user->y_hobbit) {
  				for ($i = $x_max; $i>= $p["x_palissade"]; $i--) {
					for ($j = $y_max; $j>= $p["y_palissade"]; $j--) {
							$tabValide[$i][$j] = false;
					}
				}
			} elseif ($p["x_palissade"] <= $this->view->user->x_hobbit && 
					  $p["y_palissade"] <= $this->view->user->y_hobbit) {
  				for ($i = $x_min; $i<= $p["x_palissade"]; $i++) {
					for ($j = $y_min; $j<= $p["y_palissade"]; $j++) {
							$tabValide[$i][$j] = false;
					}
				}
			} elseif ($p["x_palissade"] >= $this->view->user->x_hobbit && 
					  $p["y_palissade"] <= $this->view->user->y_hobbit) {
  				for ($i = $x_max; $i>= $p["x_palissade"]; $i--) {
					for ($j = $y_min; $j<= $p["y_palissade"]; $j++) {
							$tabValide[$i][$j] = false;
					}
				}
			}
		}
		
		$tabHobbits = null;
		$tabMonstres = null;

		// recuperation des hobbits qui sont presents sur la vue
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->id_hobbit);
		
		foreach($hobbits as $h) {
			if ($tabValide[$h["x_hobbit"]][$h["y_hobbit"]] === true) {
				$tab = array(
					'id_hobbit' => $h["id_hobbit"],
					'nom_hobbit' => $h["nom_hobbit"],
					'prenom_hobbit' => $h["prenom_hobbit"],
					'x_hobbit' => $h["x_hobbit"],
					'y_hobbit' => $h["y_hobbit"],
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
			if ($tabValide[$m["x_monstre"]][$m["y_monstre"]] === true) {
				$tabMonstres[] = array(
					'id_monstre' => $m["id_monstre"], 
					'nom_monstre' => $m["nom_type_monstre"], 
					'taille_monstre' => $m_taille, 
					'niveau_monstre' => $m["niveau_monstre"],
					'x_monstre' => $m["x_monstre"],
					'y_monstre' => $m["y_monstre"],
				);
			}
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
		
		if ($this->view->chargerVilleOk == false) {
			throw new Zend_Exception(get_class($this)." Charger interdit ville");
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
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			if ($attaqueHobbit === true) {
				$this->view->retourAttaque = $this->attaqueHobbit($this->view->user, $idHobbit);
			} elseif ($attaqueMonstre === true) {
				$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre);
			} else {
				throw new Zend_Exception(get_class($this)." Erreur inconnue");
			}
			/* on va à la position de la cible. */
			$this->view->user->x_hobbit = $this->view->retourAttaque["cible"]["x_cible"];
			$this->view->user->y_hobbit = $this->view->retourAttaque["cible"]["y_cible"];
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_vue", "box_lieu", "box_evenements");
	}

	/*
	 * Le jet d'attaque d'une charge est différent : (0.5 jet AGI) + BM + bonus arme
	 */
	protected function calculJetAttaque($hobbit) {
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->view->config->game->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = 0.5 * $jetAttaquant + $hobbit->agilite_bm_hobbit + $hobbit->agilite_bbdf_hobbit + $hobbit->bm_attaque_hobbit;
		return $jetAttaquant;
	}
	
	/*
	 * Le jet de dégats diffère aussi : 
	 * jet FOR + BM FOR + bonus arme + jet VIG + BM VIG
	 * cas du critique :
	 * 1.5(jet FOR) + BM FOR + bonus arme + jet VIG + BM VIG
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

	public function calculPx() {
		parent::calculPx();
		$this->view->calcul_px_generique = false;

		if ($this->view->retourAttaque["attaqueReussie"] === true) {
			$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
		}

		if ($this->view->retourAttaque["mort"] === true) {
			// [10+2*(diff de niveau) + Niveau Cible ]
			$this->view->nb_px_commun = 10+2*($this->view->retourAttaque["cible"]["niveau_cible"] - $this->view->user->niveau_hobbit) + $this->view->retourAttaque["cible"]["niveau_cible"];
			if ($this->view->nb_px_commun < $this->view->nb_px_perso ) {
				$this->view->nb_px_commun = $this->view->nb_px_perso;
			}
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
}