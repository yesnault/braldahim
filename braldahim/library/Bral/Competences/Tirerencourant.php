<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Tirer.php 1047 2009-01-24 14:20:00Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-24 15:20:00 +0100 (sam., 24 janv. 2009) $
 * $LastChangedRevision: 1047 $
 * $LastChangedBy: yvonnickesnault $
 */

class Bral_Competences_Tirerencourant extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass("Bral_Util_Commun");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Util_Attaque");
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("LabanMunition");
		Zend_Loader::loadClass("Palissade");
		
		//on verifie que le hobbit porte une arme de tir
		$armeTirPortee = false;
		$munitionPortee = false;
		$idMunitionPortee = null;
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
							$idMunitionPortee = $eq['id_fk_type_munition_type_equipement'];
							break;
						}
					}
				}
			}
		}
		
		if ($armeTirPortee == true && $munitionPortee == true){
			
			//on vérifie que le hobbit peut courrir (palissade et coins du jeu)
			$x_min = $this->view->user->x_hobbit - 1;
			$x_max = $this->view->user->x_hobbit + 1;
			$y_min = $this->view->user->y_hobbit - 1;
			$y_max = $this->view->user->y_hobbit + 1;
			
			$tabCoord = null;
			$course = false;
			$palissadeTable = new Palissade();
			$config = Zend_Registry::get('config');
			for ($x=$x_min;$x<=$x_max;$x++){
				for ($y=$y_min;$y<=$y_max;$y++){
					if ( !($x == $this->view->user->x_hobbit && $y == $this->view->user->y_hobbit)){
						if (($palissadeTable->findByCase($x,$y)==false) && ($x <= $config->game->x_max) && ($x >= $config->game->x_min) && ($y <= $config->game->y_max) && ($y >= $config->game->y_min)){
							$tabCoord[]=array(
								'x' => $x,
								'y' => $y,
							);
						}
					}
				}
			}
			if (count($tabCoord) == 0){
				//Aucune case de libre à proximité du hobbit
				$course = false;
			}
			else{
				$course = true;
				$tabHobbits = null;
				$tabMonstres = null;
				$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				
				if ($estRegionPvp) {
					// recuperation des hobbits qui sont presents sur la case
					$hobbitTable = new Hobbit();
					$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->id_hobbit);
					foreach($hobbits as $h) {
						$tab = array(
							'id_hobbit' => $h["id_hobbit"],
							'nom_hobbit' => $h["nom_hobbit"],
							'prenom_hobbit' => $h["prenom_hobbit"],
						);
						$tabHobbits[] = $tab;
					}
				}
				
				// recuperation des monstres qui sont presents sur la case
				$monstreTable = new Monstre();
				$monstres = $monstreTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				foreach($monstres as $m) {
					if ($m["genre_type_monstre"] == 'feminin') {
						$m_taille = $m["nom_taille_f_monstre"];
					} else {
						$m_taille = $m["nom_taille_m_monstre"];
					}
					$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"]);
				}
				$this->view->tabHobbits = $tabHobbits;
				$this->view->nHobbits = count($tabHobbits);
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
			throw new Zend_Exception(get_class($this)." Hobbit invalide : ".$this->request->get("valeur_2"));
		} else {
			$idHobbit = (int)$this->request->get("valeur_2");
		}

		if ($idMonstre == -1 && $idHobbit == -1) {
			throw new Zend_Exception(get_class($this)." Monstre ou Hobbit invalide (==-1)");
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
			if ($attaqueHobbit === true) {
				$this->view->retourAttaque = $this->attaqueHobbit($this->view->user, $idHobbit, true, true);
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
				"id_fk_hobbit_laban_munition" => $this->view->user->id_hobbit,
			);
			$labanMunition->insertOrUpdate($data);
			
			/* on va à une case aléatoire autour du hobbit parmi celles disponibles*/
			$nbCasePossible = count ($this->view->tabCourse);

			$n=Bral_Util_De::getLanceDeSpecifique(1,1,$nbCasePossible);
			
			$this->view->user->x_hobbit = $this->view->tabCourse[$n-1]["x"];
			$this->view->user->y_hobbit = $this->view->tabCourse[$n-1]["y"];
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	protected function calculJetAttaque($hobbit) {
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->view->config->game->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = $jetAttaquant + $hobbit->agilite_bm_hobbit + $hobbit->agilite_bbdf_hobbit + $hobbit->bm_attaque_hobbit;
		if ($jetAttaquant < 0){
			$jetAttaquant = 0;
		}
		return $jetAttaquant;
	}
	
	protected function calculDegat($hobbit) {
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$jetDegAgi = 0;
		$jetDegSag = 0;
		$coefCritique = 1.5;
		
		for ($i=1; $i<= ($this->view->config->game->base_agilite + $hobbit->agilite_base_hobbit); $i++) {
			$jetDegAgi = $jetDegAgi + Bral_Util_De::get_1d6();
		}
		$jetDegAgi = $jetDegAgi + $this->view->user->agilite_bm_hobbit + $this->view->user->agilite_bbdf_hobbit;
		
		for ($i=1; $i<= ($this->view->config->game->base_sagesse + $hobbit->sagesse_base_hobbit); $i++) {
			$jetDegSag = $jetDegSag + Bral_Util_De::get_1d6();
		}
		$jetDegSag = $jetDegSag + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;
		
		$jetDegat["noncritique"] = floor(($jetDegAgi + $jetDegSag)/2);
		$jetDegat["critique"] = floor($coefCritique * ($jetDegAgi + $jetDegSag)/2);

		return $jetDegat;
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_laban", "box_profil", "box_competences_communes"));
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