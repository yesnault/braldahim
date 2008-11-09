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
class Bral_Competences_Attaquer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('Bral_Util_Attaque');
		Zend_Loader::loadClass("Ville"); 
		
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->attaquerVilleOk = true;
		
		if (count($villes) > 0) {
			$this->view->attaquerVilleOk = false;
			return;
		}	
		
		$tabHobbits = null;
		$tabMonstres = null;

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

		if ($idMonstre == -1 && $idHobbit == -1) {
			throw new Zend_Exception(get_class($this)." Montre ou Hobbit invalide (==-1)");
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

		if ($attaqueHobbit === true) {
			$this->view->retourAttaque = $this->attaqueHobbit($this->view->user, $idHobbit);
		} elseif ($attaqueMonstre === true) {
			$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre);
		} else {
			throw new Zend_Exception(get_class($this)." Erreur inconnue");
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_lieu", "box_evenements");
	}

	protected function calculJetAttaque($hobbit) {
		return Bral_Util_Attaque::calculJetAttaqueNormale($hobbit);
	}

	protected function calculDegat($hobbit) {
		return Bral_Util_Attaque::calculDegatAttaqueNormale($hobbit);
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