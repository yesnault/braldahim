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

/*
 * Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
 * dégats : 0.5*(jet FOR)+BM FOR+ bonus arme dégats
 * dégats critiques : (1.5*(0.5*FOR))+BM FOR+bonus arme dégats
 */
class Bral_Competences_Frenesie extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Attaque');
		
		$tabHobbits = null;
		$tabMonstres = null;

		$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		if ($estRegionPvp) {
			// recuperation des hobbits qui sont presents sur la vue
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
		
		// recuperation des monstres qui sont presents sur la vue
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

	function prepareFormulaire() {
		// rien à faire ici
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
	
		// calcul des jets
		$this->calculJets();
		
		if ($this->view->okJet1 === true) {
			if ($attaqueHobbit === true) {
				$this->view->retourAttaque = $this->attaqueHobbit($this->view->user, $idHobbit);
			} elseif ($attaqueMonstre === true) {
				$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre);
			} else {
				throw new Zend_Exception(get_class($this)." Erreur inconnue");
			}
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue", "box_lieu"));
	}

	protected function calculJetAttaque($hobbit) {
		//Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->view->config->game->base_agilite + $hobbit->agilite_base_hobbit; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = floor((0.5 * $jetAttaquant) + $hobbit->agilite_bm_hobbit + $hobbit->agilite_bbdf_hobbit + $hobbit->bm_attaque_hobbit);
		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		return $jetAttaquant;
	}

	protected function calculDegat($hobbit) {
		$this->view->effetRune = false;
		
		$jetsDegat["critique"] = 0;
		$jetsDegat["noncritique"] = 0;
		$jetDegatForce = 0;
		$coefCritique = 1.5;
			
		for ($i=1; $i<= ($this->view->config->game->base_force + $hobbit->force_base_hobbit); $i++) {
			$jetDegatForce = $jetDegatForce + Bral_Util_De::get_1d6();
		}
		
		if (Bral_Util_Commun::isRunePortee($hobbit->id_hobbit, "EM")) { 
			$this->view->effetRune = true;
			// dégats : Jet FOR + BM + Bonus de dégat de l'arme
			// dégats critiques : Jet FOR *1,5 + BM + Bonus de l'arme
			$jetsDegat["critique"] = $coefCritique * $jetDegatForce;
			$jetsDegat["noncritique"] = $jetDegatForce;
		} else {
			// * dégats : 0.5*(jet FOR)+BM FOR+ bonus arme dégats
 			// * dégats critiques : (1.5*(0.5*FOR))+BM FOR+bonus arme dégats
			$jetsDegat["critique"] = $coefCritique * (0.5 * $jetDegatForce);
			$jetsDegat["noncritique"] = 0.5 * $jetDegatForce;
		}
		
		$jetsDegat["critique"] = $jetsDegat["critique"] + $hobbit->force_bm_hobbit + $hobbit->force_bbdf_hobbit + $hobbit->bm_degat_hobbit;
		$jetsDegat["noncritique"] = $jetsDegat["noncritique"] + $hobbit->force_bm_hobbit + $hobbit->force_bbdf_hobbit + $hobbit->bm_degat_hobbit;
		
		return $jetsDegat;
	}

	public function calculPx() {
		parent::calculPx();
			
		$this->view->nb_px_commun = 0;
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