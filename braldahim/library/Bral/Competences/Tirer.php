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
		$idMunitionPortee = null;
		$hobbitEquipement = new HobbitEquipement();
		$equipementPorteRowset = $hobbitEquipement->findByTypePiece($this->view->user->id_hobbit,"arme_tir");

		if (count($equipementPorteRowset) > 0) {
			$armeTirPortee = true;
			//on verifie qu'il a des munitions et que ce sont les bonnes
			$labanMunition = new LabanMunition();
			$munitionPorteRowset = 	$labanMunition->findByIdHobbit($this->view->user->id_hobbit);
			if (count ($munitionPorteRowset) > 0) {
				foreach ($equipementPorteRowset as $eq){
					foreach ($munitionPorteRowset as $mun){
						if ($mun['id_fk_type_laban_munition'] == $eq['id_fk_type_munition_type_equipement']){
							$munitionPortee = true;
							$idMunitionPortee = $eq['id_fk_type_munition_type_equipement'];
							break;
						}
					}
				}
			}
		}

		if ($armeTirPortee == true && $munitionPortee == true && $this->view->user->est_intangible_hobbit == "non") {

			//On ne peut tirer qu'à 4 cases maxi.
			$this->view->tir_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit) + $this->view->user->vue_bm_hobbit;
			if ($this->view->tir_nb_cases > 4) {
				$this->view->tir_nb_cases = 4;
			}

			//On calcule les cases où on peut tirer.
			$x_min = $this->view->user->x_hobbit - $this->view->tir_nb_cases;
			$x_max = $this->view->user->x_hobbit + $this->view->tir_nb_cases;
			$y_min = $this->view->user->y_hobbit - $this->view->tir_nb_cases;
			$y_max = $this->view->user->y_hobbit + $this->view->tir_nb_cases;

			$tabHobbits = null;
			$tabMonstres = null;

			$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_hobbit, $this->view->user->y_hobbit);

			if ($estRegionPvp) {
				// recuperation des hobbits qui sont presents sur la vue
				$hobbitTable = new Hobbit();
				$hobbits = $hobbitTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_hobbit, $this->view->user->id_hobbit, false);

				foreach($hobbits as $h) {
					if ($h["x_hobbit"] != $this->view->user->x_hobbit || $h["y_hobbit"] != $this->view->user->y_hobbit) { // on ne prend pas la case courante
						$tabHobbits[] = array(
							'id_hobbit' => $h["id_hobbit"],
							'nom_hobbit' => $h["nom_hobbit"],
							'prenom_hobbit' => $h["prenom_hobbit"],
							'x_hobbit' => $h["x_hobbit"],
							'y_hobbit' => $h["y_hobbit"],
							'dist_hobbit' => max(abs($h["x_hobbit"] - $this->view->user->x_hobbit), abs($h["y_hobbit"] - $this->view->user->y_hobbit))
						);
					}
				}
			}

			// recuperation des monstres qui sont presents sur la vue
			$monstreTable = new Monstre();
			$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_hobbit);
			foreach($monstres as $m) {
				if ($m["x_monstre"] != $this->view->user->x_hobbit || $m["y_monstre"] != $this->view->user->y_hobbit) { // on ne prend pas la case courante
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
			}
			$this->view->tabHobbits = $tabHobbits;
			$this->view->nHobbits = count($tabHobbits);
			$this->view->tabMonstres = $tabMonstres;
			$this->view->nMonstres = count($tabMonstres);
			$this->view->estRegionPvp = $estRegionPvp;
		}
		$this->view->armeTirPortee = $armeTirPortee;
		$this->view->munitionPortee = $munitionPortee;
		$this->view->idMunitionPortee = $idMunitionPortee;
	}

	function prepareFormulaire() {
		//on trie suivant la distance
		$dist=null;
		if ($this->view->nMonstres > 0) {
			foreach ($this->view->tabMonstres as $key => $row) {
				$dist[$key] = $row['dist_monstre'];
			}
			array_multisort($dist, SORT_ASC, $this->view->tabMonstres);
		}
		$dist=null;
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

		if ($this->view->armeTirPortee === false){
			throw new Zend_Exception(get_class($this)." pas d'arme de tir");
		}
		if ($this->view->munitionPortee === false){
			throw new Zend_Exception(get_class($this)." pas de munition");
		}

		$attaqueMonstre = false;
		$attaqueHobbit = false;
		if ($idHobbit != -1) {
			if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
				foreach ($this->view->tabHobbits as $h) {
					if ($h["id_hobbit"] == $idHobbit) {
						$attaqueHobbit = true;
						$this->view->distCible = $h['dist_hobbit'];
						$this->view->xCible = $h['x_hobbit'];
						$this->view->yCible = $h['y_hobbit'];
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
						$this->view->distCible = $m['dist_monstre'];
						$this->view->xCible = $m['x_monstre'];
						$this->view->yCible = $m['y_monstre'];
						break;
					}
				}
			}
			if ($attaqueMonstre === false) {
				throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
			}
		}

		if ($attaqueHobbit === true) {
			$this->calculTirer($idHobbit,"hobbit");
		} elseif ($attaqueMonstre === true) {
			$this->calculTirer($idMonstre,"monstre");
		} else {
			throw new Zend_Exception(get_class($this)." Erreur inconnue");
		}

		$this->setEvenementQueSurOkJet1(false);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}

	/*
	 * Le jet d'attaque d'un tir est différent : JA = (Jet d'AGI + BM) * coeff
	 * coeff varie suivant distance et palissade
	 */
	protected function calculJetAttaque($hobbit) {
		$coef = 0;
		$palissade = false;
		$monte=false;

		$jetAttaquant = Bral_Util_De::getLanceDe6($this->view->config->game->base_agilite + $hobbit->agilite_base_hobbit);

		if ($this->view->xCible < $hobbit->x_hobbit){
			$x_min = $this->view->xCible;
			$x_max = $hobbit->x_hobbit;
		} else{
			$x_min = $hobbit->x_hobbit;
			$x_max = $this->view->xCible;
		}

		if ($this->view->yCible < $hobbit->y_hobbit){
			$y_min = $this->view->yCible;
			$y_max = $hobbit->y_hobbit;
		} else{
			$y_min = $hobbit->y_hobbit;
			$y_max = $this->view->yCible;
		}

		$z = $hobbit->z_hobbit;

		if ($this->view->distCible > 1){
			Zend_Loader::loadClass("Palissade");

			// equation droite y = mx + p  => ax + by + c = 0
			// distance d'un point à une droite = abs ( (ax + by + c)/sqrt(a² + b²))
			// la distance entre le point et la droite doit être inférieure à sqrt(2)/2

			// calcul de m, p, a, b et c :
			if ($this->view->user->x_hobbit != $this->view->xCible){
				$m = ($this->view->user->y_hobbit-$this->view->yCible)/($this->view->user->x_hobbit-$this->view->xCible);
				$p = $this->view->yCible - $m * $this->view->xCible;
				$a = 1;
				if ($m != 0 ){
					$b = -1/$m;
				} else{
					$a=0;
					$b=1;
				}
				$c = -1*$p*$b;
			} else {
				$a = 1;
				$b = 0;
				$c = -1*$this->view->user->x_hobbit;
			}

			$palissadeTable = new Palissade();

			for ($x = $x_min; $x <= $x_max; $x++) {
				for ($y = $y_min; $y <= $y_max; $y++) {
					$dist = abs (($a * $x + $b * $y + $c)/sqrt(pow($a,2)+pow($b,2)));
					if ( round($dist,5) < sqrt(2)/2 ){
						if ($palissadeTable->findByCase($x,$y, $z)){
							$palissade = true;
							break;
						}
					}
				}
			}
		}

		if ($palissade == false){
			switch ($this->view->distCible){
				case 0 :
					$coef=0.6;
					break;
				case 1 :
					$coef=1;
					break;
				case 2 :
					$coef=0.8;
					break;
				case 3 :
					$coef=0.7;
					break;
				default :
					$coef=0.6;
			}
		} else{
			switch ($this->view->distCible){
				case 2 :
					$coef=0.533;
					break;
				case 3 :
					$coef=0.466;
					break;
				default : $coef=0.4;
			}
		}
		$jetAttaquantNonReduit = $jetAttaquant + $hobbit->agilite_bm_hobbit + $hobbit->agilite_bbdf_hobbit + $hobbit->bm_attaque_hobbit;
		$jetAttaquant = floor($coef * ($jetAttaquantNonReduit));
		if ($jetAttaquant < 0){
			$jetAttaquant = 0;
		}

		$this->view->palissade = $palissade;
		$this->view->coef = $coef;
		$this->view->jetAttaquantNonReduit = $jetAttaquantNonReduit;

		return $jetAttaquant;
	}

	protected function calculDegat($hobbit) {
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;

		$jetDegAgi = Bral_Util_De::getLanceDe6($this->view->config->game->base_agilite + $hobbit->agilite_base_hobbit);
		$jetDegSag = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $hobbit->sagesse_base_hobbit);

		$jetDegat["noncritique"] = floor(($jetDegAgi + $jetDegSag)/2);
		$jetDegat["critique"] = floor($coefCritique * ($jetDegAgi + $jetDegSag)/2);

		return $jetDegat;
	}

	private function calculTirer($id,$type){
		if ($type == "hobbit"){
			$this->view->retourAttaque = $this->attaqueHobbit($this->view->user, $id, true, true);
		} else{
			$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $id, true);
		}

		$labanMunition = new LabanMunition();
		$data = array(
			"quantite_laban_munition" => -1,
			"id_fk_type_laban_munition" => $this->view->idMunitionPortee,
			"id_fk_hobbit_laban_munition" => $this->view->user->id_hobbit,
		);
		$labanMunition->insertOrUpdate($data);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_laban", "box_profil"));
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