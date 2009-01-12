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
		Zend_Loader::loadClass("Palissade");
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
			
			//on charge les palissades présentes dans la vue.
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
	
			$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_hobbit, $this->view->user->y_hobbit);
			
			if ($estRegionPvp) {
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
			$this->view->estRegionPvp = $estRegionPvp;
		}
		$this->view->armeTirPortee = $armeTirPortee;
		$this->view->munitionPortee = $munitionPortee;
	}
	
	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {
		
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_laban", "box_lieu"));
	}
	
}