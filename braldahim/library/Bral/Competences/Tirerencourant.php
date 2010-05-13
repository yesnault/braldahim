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

class Bral_Competences_Tirerencourant extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass("Bral_Util_Commun");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("LabanMunition");
		Zend_Loader::loadClass("Palissade");
		
		//on verifie que le braldun porte une arme de tir
		$armeTirPortee = false;
		$munitionPortee = false;
		$idMunitionPortee = null;
		$braldunEquipement = new BraldunEquipement();
		$equipementPorteRowset = $braldunEquipement->findByTypePiece($this->view->user->id_braldun,"arme_tir");
		
		if (count($equipementPorteRowset) > 0){
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
			
			//on vérifie que le braldun peut courrir (palissade et coins du jeu)
			$x_min = $this->view->user->x_braldun - 1;
			$x_max = $this->view->user->x_braldun + 1;
			$y_min = $this->view->user->y_braldun - 1;
			$y_max = $this->view->user->y_braldun + 1;
			$z = $this->view->user->z_braldun;
			
			$tabCoord = null;
			$course = false;
			$palissadeTable = new Palissade();
			$config = Zend_Registry::get('config');
			for ($x=$x_min;$x<=$x_max;$x++){
				for ($y=$y_min;$y<=$y_max;$y++){
					if ( !($x == $this->view->user->x_braldun && $y == $this->view->user->y_braldun)){
						if (($palissadeTable->findByCase($x,$y,$z)==false) && ($x <= $config->game->x_max) && ($x >= $config->game->x_min) && ($y <= $config->game->y_max) && ($y >= $config->game->y_min)){
							$tabCoord[]=array(
								'x' => $x,
								'y' => $y,
							);
						}
					}
				}
			}
			if (count($tabCoord) == 0) {
				//Aucune case de libre à proximité du braldun
				$course = false;
			} else {
				$course = true;
				$tabBralduns = null;
				$tabMonstres = null;
				$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_braldun, $this->view->user->y_braldun);
				
				if ($estRegionPvp) {
					// recuperation des bralduns qui sont presents sur la case
					$braldunTable = new Braldun();
					$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->id_braldun, false);
					foreach($bralduns as $h) {
						$tab = array(
							'id_braldun' => $h["id_braldun"],
							'nom_braldun' => $h["nom_braldun"],
							'prenom_braldun' => $h["prenom_braldun"],
						);
						$tabBralduns[] = $tab;
					}
				}
				
				// recuperation des monstres qui sont presents sur la case
				$monstreTable = new Monstre();
				$monstres = $monstreTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				foreach($monstres as $m) {
					if ($m["genre_type_monstre"] == 'feminin') {
						$m_taille = $m["nom_taille_f_monstre"];
					} else {
						$m_taille = $m["nom_taille_m_monstre"];
					}
					$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"]);
				}
				$this->view->tabBralduns = $tabBralduns;
				$this->view->nBralduns = count($tabBralduns);
				$this->view->tabMonstres = $tabMonstres;
				$this->view->nMonstres = count($tabMonstres);
				$this->view->estRegionPvp = $estRegionPvp;
			}
			$this->view->course = $course;
			$this->view->tabCourse = $tabCoord;
		}
		$this->view->armeTirPortee = $armeTirPortee;
		$this->view->munitionPortee = $munitionPortee;
		$this->view->idMunitionPortee = $idMunitionPortee;
	}
	
	function prepareFormulaire() {
		
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

		if ($idMonstre == -1 && $idBraldun == -1) {
			throw new Zend_Exception(get_class($this)." Monstre ou Braldun invalide (==-1)");
		}

		$attaqueMonstre = false;
		$attaqueBraldun = false;
		if ($idBraldun != -1) {
			if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
				foreach ($this->view->tabBralduns as $h) {
					if ($h["id_braldun"] == $idBraldun) {
						$attaqueBraldun = true;
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
						break;
					}
				}
			}
			if ($attaqueMonstre === false) {
				throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
			}
		}
		
		if ($this->view->course === false) {
			throw new Zend_Exception(get_class($this)." impossible de courrir");
		}
		
		if ($this->view->armeTirPortee === false){
			throw new Zend_Exception(get_class($this)." pas d'arme de tir");
		}
		if ($this->view->munitionPortee === false){
			throw new Zend_Exception(get_class($this)." pas de munitions !");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			if ($attaqueBraldun === true) {
				$this->view->retourAttaque = $this->attaqueBraldun($this->view->user, $idBraldun, true, true);
			} elseif ($attaqueMonstre === true) {
				$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre, true, true);
			} else {
				throw new Zend_Exception(get_class($this)." Erreur inconnue");
			}
			//On perd une munition
			$labanMunition = new LabanMunition();
			$data = array(
				"quantite_laban_munition" => -1,
				"id_fk_type_laban_munition" => $this->view->idMunitionPortee,
				"id_fk_braldun_laban_munition" => $this->view->user->id_braldun,
			);
			$labanMunition->insertOrUpdate($data);
			
			/* on va à une case aléatoire autour du braldun parmi celles disponibles*/
			$nbCasePossible = count ($this->view->tabCourse);

			$n=Bral_Util_De::getLanceDeSpecifique(1,1,$nbCasePossible);
			
			$this->view->user->x_braldun = $this->view->tabCourse[$n-1]["x"];
			$this->view->user->y_braldun = $this->view->tabCourse[$n-1]["y"];
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}
	
	protected function calculJetAttaque($braldun) {
		$jetAttaquant = Bral_Util_De::getLanceDe6($this->view->config->game->base_agilite + $braldun->agilite_base_braldun);
		$jetAttaquant = $jetAttaquant + $braldun->agilite_bm_braldun + $braldun->agilite_bbdf_braldun + $braldun->bm_attaque_braldun;
		
		if ($jetAttaquant < 0){
			$jetAttaquant = 0;
		}
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
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_laban", "box_profil", "box_competences_communes", "box_lieu", "box_echoppes"));
	}
	
	public function calculPx() {
		parent::calculPx();
		$this->view->calcul_px_generique = false;

		if ($this->view->retourAttaque["attaqueReussie"] === true) {
			$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
		}

		if ($this->view->retourAttaque["mort"] === true && $this->view->retourAttaque["idTypeGroupeMonstre"] != $this->view->config->game->groupe_monstre->type->gibier) {
			// [10+2*(diff de niveau) + Niveau Cible ]
			$this->view->nb_px_commun = 10+2*($this->view->retourAttaque["cible"]["niveau_cible"] - $this->view->user->niveau_braldun) + $this->view->retourAttaque["cible"]["niveau_cible"];
			if ($this->view->nb_px_commun < $this->view->nb_px_perso ) {
				$this->view->nb_px_commun = $this->view->nb_px_perso;
			}
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}	
	
}