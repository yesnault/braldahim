<?php

class Bral_Competences_Utiliserpotion extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("LabanPotion");
		Zend_Loader::loadClass("Ville"); 
		
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->utiliserPotionVilleOk = true;
		
		if (count($villes) > 0) {
			$this->view->utiliserPotionVilleOk = false;
			return;
		}
		
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);
		foreach ($potions as $p) {
			$tabPotions[$p["id_laban_potion"]] = array(
					"id_potion" => $p["id_laban_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_laban_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
			);
		}

		$tabHobbits = null;
		$tabMonstres = null;
		// recuperation des hobbits qui sont presents sur la case
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
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

		$this->view->nPotions = count($tabPotions);
		$this->view->tabPotions = $tabPotions;
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
			throw new Zend_Exception(get_class($this)." Potion invalide : ".$this->request->get("valeur_1"));
		} else {
			$idPotion = (int)$this->request->get("valeur_1");
		}
		
		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_2"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_1");
		}
		
		if (((int)$this->request->get("valeur_3").""!=$this->request->get("valeur_3")."")) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide : ".$this->request->get("valeur_3"));
		} else {
			$idHobbit = (int)$this->request->get("valeur_2");
		}

		if ($idMonstre == -1 && $idHobbit == -1) {
			throw new Zend_Exception(get_class($this)." Montre ou Hobbit invalide (==-1)");
		}
		
		$potion = null;
		foreach ($this->view->tabPotions as $p) {
			if ($p["id_potion"] == $idPotion) {
				$potion = $p;
				break;
			}
		}
		
		if ($potion == null) {
			throw new Zend_Exception(get_class($this)." Potion invalide (".$idPotion.")");
		}

		$utiliserPotionMonstre = false;
		$utiliserPotionHobbit = false;
		if ($idHobbit != -1) {
			if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
				foreach ($this->view->tabHobbits as $h) {
					if ($h["id_hobbit"] == $idHobbit) {
						$utiliserPotionHobbit = true;
						break;
					}
				}
			}
			if ($utiliserPotionHobbit === false) {
				throw new Zend_Exception(get_class($this)." Hobbit invalide (".$idHobbit.")");
			}
		} else {
			if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
				foreach ($this->view->tabMonstres as $m) {
					if ($m["id_monstre"] == $idMonstre) {
						$utiliserPotionMonstre = true;
						break;
					}
				}
			}
			if ($utiliserPotionMonstre === false) {
				throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
			}
		}

		if ($utiliserPotionHobbit === true) {
			$this->utiliserPotionHobbit($idHobbit, $potion);
		} elseif ($utiliserPotionMonstre === true) {
			$this->utiliserPotionMonstre($idMonstre, $potion);
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
	
	private function utiliserPotionHobbit($potion, $idHobbit) {
		Zend_Loader::loadClass("EffetPotionHobbit"); 
	
		// TODO
	}
	
	private function utiliserPotionMonstre($potion, ) {
		Zend_Loader::loadClass("EffetPotionMonstre"); 
		
		// TODO
	}
}
