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
		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("LabanMunition");

		//on verifie que le braldun porte une arme de tir
		$armeTirPortee = false;
		$munitionPortee = false;
		$idMunitionPortee = null;
		$braldunEquipement = new BraldunEquipement();
		$equipementPorteRowset = $braldunEquipement->findByTypePiece($this->view->user->id_braldun,"arme_tir");

		if (count($equipementPorteRowset) > 0) {
			$armeTirPortee = true;
			//on verifie qu'il a des munitions et que ce sont les bonnes
			$labanMunition = new LabanMunition();
			$munitionPorteRowset = 	$labanMunition->findByIdBraldun($this->view->user->id_braldun);
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

		if ($armeTirPortee == true && $munitionPortee == true && $this->view->user->est_intangible_braldun == "non") {

			//On ne peut tirer qu'à 4 cases maxi.
			$this->view->tir_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun;
			if ($this->view->tir_nb_cases > 4) {
				$this->view->tir_nb_cases = 4;
			}

			//On calcule les cases où on peut tirer.
			$x_min = $this->view->user->x_braldun - $this->view->tir_nb_cases;
			$x_max = $this->view->user->x_braldun + $this->view->tir_nb_cases;
			$y_min = $this->view->user->y_braldun - $this->view->tir_nb_cases;
			$y_max = $this->view->user->y_braldun + $this->view->tir_nb_cases;

			$tabBralduns = null;
			$tabMonstres = null;

			$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_braldun, $this->view->user->y_braldun);

			if ($estRegionPvp) {
				// recuperation des bralduns qui sont presents sur la vue
				$braldunTable = new Braldun();
				$bralduns = $braldunTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun, $this->view->user->id_braldun, false);

				foreach($bralduns as $h) {
					if ($h["x_braldun"] != $this->view->user->x_braldun || $h["y_braldun"] != $this->view->user->y_braldun) { // on ne prend pas la case courante
						$tabBralduns[] = array(
							'id_braldun' => $h["id_braldun"],
							'nom_braldun' => $h["nom_braldun"],
							'prenom_braldun' => $h["prenom_braldun"],
							'x_braldun' => $h["x_braldun"],
							'y_braldun' => $h["y_braldun"],
							'dist_braldun' => max(abs($h["x_braldun"] - $this->view->user->x_braldun), abs($h["y_braldun"] - $this->view->user->y_braldun))
						);
					}
				}
			}

			// recuperation des monstres qui sont presents sur la vue
			$monstreTable = new Monstre();
			$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun);
			foreach($monstres as $m) {
				if ($m["x_monstre"] != $this->view->user->x_braldun || $m["y_monstre"] != $this->view->user->y_braldun) { // on ne prend pas la case courante
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
					'dist_monstre' => max(abs($m["x_monstre"] - $this->view->user->x_braldun), abs($m["y_monstre"]-$this->view->user->y_braldun))
					);
				}
			}
			$this->view->tabBralduns = $tabBralduns;
			$this->view->nBralduns = count($tabBralduns);
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
		if ($this->view->nBralduns > 0) {
			foreach ($this->view->tabBralduns as $key => $row) {
				$dist[$key] = $row['dist_braldun'];
			}
			array_multisort($dist, SORT_ASC, $this->view->tabBralduns);
		}
	}

	function prepareResultat() {
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_1");
		}
		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Braldun invalide : ".$this->request->get("valeur_2"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_2");
		}

		if ($idMonstre != -1 && $idBraldun != -1) {
			throw new Zend_Exception(get_class($this)." Monstre ou Braldun invalide (!=-1)");
		}

		if ($this->view->armeTirPortee === false){
			throw new Zend_Exception(get_class($this)." pas d'arme de tir");
		}
		if ($this->view->munitionPortee === false){
			throw new Zend_Exception(get_class($this)." pas de munition");
		}

		$attaqueMonstre = false;
		$attaqueBraldun = false;
		if ($idBraldun != -1) {
			if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
				foreach ($this->view->tabBralduns as $h) {
					if ($h["id_braldun"] == $idBraldun) {
						$attaqueBraldun = true;
						$this->view->distCible = $h['dist_braldun'];
						$this->view->xCible = $h['x_braldun'];
						$this->view->yCible = $h['y_braldun'];
						break;
					}
				}
			}
			if ($attaqueBraldun === false) {
				throw new Zend_Exception(get_class($this)." Braldun invalide (".$idBraldun.")");
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

		if ($attaqueBraldun === true) {
			$this->calculTirer($idBraldun,"braldun");
		} elseif ($attaqueMonstre === true) {
			$this->calculTirer($idMonstre,"monstre");
		} else {
			throw new Zend_Exception(get_class($this)." Erreur inconnue");
		}

		$this->setEvenementQueSurOkJet1(false);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	/*
	 * Le jet d'attaque d'un tir est différent : JA = (Jet d'AGI + BM) * coeff
	 * coeff varie suivant distance et palissade
	 */
	protected function calculJetAttaque($braldun) {
		$coef = 0;
		$palissade = false;
		$monte=false;

		$jetAttaquant = Bral_Util_De::getLanceDe6($this->view->config->game->base_agilite + $braldun->agilite_base_braldun);

		if ($this->view->xCible < $braldun->x_braldun){
			$x_min = $this->view->xCible;
			$x_max = $braldun->x_braldun;
		} else{
			$x_min = $braldun->x_braldun;
			$x_max = $this->view->xCible;
		}

		if ($this->view->yCible < $braldun->y_braldun){
			$y_min = $this->view->yCible;
			$y_max = $braldun->y_braldun;
		} else{
			$y_min = $braldun->y_braldun;
			$y_max = $this->view->yCible;
		}

		$z = $braldun->z_braldun;

		if ($this->view->distCible > 1){
			Zend_Loader::loadClass("Palissade");

			// equation droite y = mx + p  => ax + by + c = 0
			// distance d'un point à une droite = abs ( (ax + by + c)/sqrt(a² + b²))
			// la distance entre le point et la droite doit être inférieure à sqrt(2)/2

			// calcul de m, p, a, b et c :
			if ($this->view->user->x_braldun != $this->view->xCible){
				$m = ($this->view->user->y_braldun-$this->view->yCible)/($this->view->user->x_braldun-$this->view->xCible);
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
				$c = -1*$this->view->user->x_braldun;
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
		$jetAttaquantNonReduit = $jetAttaquant + $braldun->agilite_bm_braldun + $braldun->agilite_bbdf_braldun + $braldun->bm_attaque_braldun;
		$jetAttaquant = floor($coef * ($jetAttaquantNonReduit));
		if ($jetAttaquant < 0){
			$jetAttaquant = 0;
		}

		$this->view->palissade = $palissade;
		$this->view->coef = $coef;
		$this->view->jetAttaquantNonReduit = $jetAttaquantNonReduit;

		return $jetAttaquant;
	}

	protected function calculDegat($braldun) {
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;

		$jetDegAgi = Bral_Util_De::getLanceDe6($this->view->config->game->base_agilite + $braldun->agilite_base_braldun);
		$jetDegSag = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $braldun->sagesse_base_braldun);

		$jetDegat["noncritique"] = floor(($jetDegAgi + $jetDegSag)/2);
		$jetDegat["critique"] = floor($coefCritique * ($jetDegAgi + $jetDegSag)/2);

		return $jetDegat;
	}

	private function calculTirer($id,$type){
		if ($type == "braldun"){
			$this->view->retourAttaque = $this->attaqueBraldun($this->view->user, $id, true, true);
		} else{
			$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $id, true);
		}

		$labanMunition = new LabanMunition();
		$data = array(
			"quantite_laban_munition" => -1,
			"id_fk_type_laban_munition" => $this->view->idMunitionPortee,
			"id_fk_braldun_laban_munition" => $this->view->user->id_braldun,
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
			$this->view->nb_px_commun = 10+2*($this->view->retourAttaque["cible"]["niveau_cible"] - $this->view->user->niveau_braldun) + $this->view->retourAttaque["cible"]["niveau_cible"];
			if ($this->view->nb_px_commun < $this->view->nb_px_perso ) {
				$this->view->nb_px_commun = $this->view->nb_px_perso;
			}
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}

}