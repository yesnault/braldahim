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
class Bral_Competences_Utiliserpotion extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("LabanPotion");
		
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);
		foreach ($potions as $p) {
			$tabPotions[$p["id_laban_potion"]] = array(
					"id_potion" => $p["id_laban_potion"],
					"id_fk_type_potion" => $p["id_fk_type_laban_potion"],
					"id_fk_type_qualite_potion" => $p["id_fk_type_qualite_laban_potion"],
					"nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
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
				'niveau_hobbit' => $h["niveau_hobbit"],
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
		// rien a faire ici
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
			$idMonstre = (int)$this->request->get("valeur_2");
		}
		
		if (((int)$this->request->get("valeur_3").""!=$this->request->get("valeur_3")."")) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide : ".$this->request->get("valeur_3"));
		} else {
			$idHobbit = (int)$this->request->get("valeur_3");
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
		
		$this->retourPotion = null;
		
		$utiliserPotionMonstre = false;
		$utiliserPotionHobbit = false;
		if ($idHobbit != -1) {
			if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
				foreach ($this->view->tabHobbits as $h) {
					if ($h["id_hobbit"] == $idHobbit) {
						$utiliserPotionHobbit = true;
						$this->retourPotion['cible'] = array('nom_cible' => $h["prenom_hobbit"]. " ". $h["nom_hobbit"], 
													   'id_cible' => $h["id_hobbit"],
													   'niveau_cible' => $h["niveau_hobbit"]
													  );
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
						$this->retourPotion['cible'] = array('nom_cible' => $m["nom_monstre"], 
													   'id_cible' => $m["id_monstre"],
														'niveau_cible' => $m["niveau_monstre"],
														);
						break;
					}
				}
			}
			if ($utiliserPotionMonstre === false) {
				throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
			}
		}
		
		Zend_Loader::loadClass("Bral_Util_EffetsPotion");
		
		$this->detailEvenement = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a ";
		if ($this->retourPotion['cible']["id_cible"] == $this->view->user->id_hobbit && $utiliserPotionHobbit === true) {
			$this->detailEvenement .= "bu une potion";
		} else {
			$this->detailEvenement .= "utilisé une potion sur ".$this->retourPotion['cible']["nom_cible"]. " (".$this->retourPotion['cible']["id_cible"].")";
		}
		$this->setEvenementQueSurOkJet1(false);
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->competence);
		
		if ($utiliserPotionHobbit === true) {
			$this->utiliserPotionHobbit($potion, $idHobbit);
			$detailsBot = $this->getDetailEvenementCible($potion);
			Bral_Util_Evenement::majEvenements($this->retourPotion['cible']["id_cible"], $this->view->config->game->evenements->type->competence, $this->detailEvenement, $detailsBot, "hobbit", true, $this->view);
		} elseif ($utiliserPotionMonstre === true) {
			$this->utiliserPotionMonstre($potion, $idMonstre);
			$this->setDetailsEvenementCible($idMonstre, "monstre");
		} else {
			throw new Zend_Exception(get_class($this)." Erreur inconnue");
		}
		
		$this->retourPotion['potion'] = $potion;
		$this->view->retourPotion = $this->retourPotion;
		
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_lieu", "box_laban"));
	}
	
	private function utiliserPotionHobbit($potion, $idHobbit) {
		Zend_Loader::loadClass("EffetPotionHobbit"); 
		
		$nbTour = $this->calculNbTour($potion);
		
		if ($nbTour > 1) {
			$effetPotionHobbitTable = new EffetPotionHobbit();
			$data = array(
				  'id_effet_potion_hobbit' => $potion["id_potion"],
				  'id_fk_type_potion_effet_potion_hobbit' => $potion["id_fk_type_potion"],
				  'id_fk_hobbit_cible_effet_potion_hobbit' => $idHobbit,
				  'id_fk_hobbit_lanceur_effet_potion_hobbit' => $this->view->user->id_hobbit,
				  'id_fk_type_qualite_effet_potion_hobbit' => $potion["id_fk_type_qualite_potion"],
				  'nb_tour_restant_effet_potion_hobbit' => $nbTour,
				  'niveau_effet_potion_hobbit' => $potion["niveau"],
			);
			$effetPotionHobbitTable->insert($data);
		}
		$this->supprimeDuLaban($potion);
		
		if ($this->view->user->id_hobbit == $idHobbit) {
			$hobbit = $this->view->user;
		} else {
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($idHobbit);
			$hobbit = $hobbitRowset->current();
		}
		
		$potion["nb_tour_restant"] = $nbTour;
		$this->retourPotion["effet"] = Bral_Util_EffetsPotion::appliquePotionSurHobbit(true, $potion, $this->view->user->id_hobbit, $hobbit, false);
	}
	
	private function utiliserPotionMonstre($potion, $idMonstre) {
		Zend_Loader::loadClass("EffetPotionMonstre"); 
		
		$nbTour = $this->calculNbTour($potion);
		
		if ($nbTour > 1) {
			$effetPotionMonstreTable = new EffetPotionMonstre();
			$data = array(
				  'id_effet_potion_monstre' => $potion["id_potion"],
				  'id_fk_type_potion_effet_potion_monstre' => $potion["id_fk_type_potion"],
				  'id_fk_monstre_cible_effet_potion_monstre' => $idMonstre,
				  'id_fk_hobbit_lanceur_effet_potion_monstre' => $this->view->user->id_hobbit,
				  'id_fk_type_qualite_effet_potion_monstre' => $potion["id_fk_type_qualite_potion"],
				  'nb_tour_restant_effet_potion_monstre' => $nbTour,
				  'niveau_effet_potion_monstre' => $potion["niveau"],
			);
			$effetPotionMonstreTable->insert($data);
		}
		$this->supprimeDuLaban($potion);
		
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->find($idMonstre);
		$monstre = $monstreRowset->current();
		
		$potion["nb_tour_restant"] = $nbTour;
		$this->retourPotion["effet"] = Bral_Util_EffetsPotion::appliquePotionSurMonstre($potion, $this->view->user->id_hobbit, $monstre, false);
	}
	
	private function calculNbTour($potion) {
		$nbTour = Bral_Util_De::get_1d3();
		if ($potion["nom_systeme_type_qualite"] == 'standard') {
			$nbTour = $nbTour + 1;
		} else if ($potion["nom_systeme_type_qualite"] == 'bonne') {
			$nbTour = $nbTour + 2;
		}
		$nbTour = $nbTour - 1; // tour courant
		if ($nbTour < 1) {
			$nbTour = 1;
		}
		return $nbTour;
	}
	
	private function supprimeDuLaban($potion) {
		$labanPotionTable = new LabanPotion();
		$where = 'id_laban_potion = '.$potion["id_potion"];
		$labanPotionTable->delete($where);
	}
	
	private function getDetailEvenementCible($potion) {
		$retour = "";
		
		if ($this->view->user->id_hobbit != $this->retourPotion['cible']["id_cible"]) {
			$retour .= $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a utilisé";
		} else {
			$retour .= "Vous avez bu";
		}
		$retour .= " une potion ".htmlspecialchars($potion["nom"])." de qualité ";
		$retour .= htmlspecialchars($potion["qualite"]);
		if ($this->view->user->id_hobbit != $this->retourPotion['cible']["id_cible"]) {
			$retour .= " sur vous.";
		}
		$retour .= "
";
		$retour .= "L'effet de la potion porte sur ".$this->retourPotion['effet']['nb_tour_restant']." tour";
		if ($this->retourPotion['effet']['nb_tour_restant'] > 1): $retour .= 's'; endif;
		$retour .= ". 
Vous venez de subir ".$this->retourPotion['effet']["nEffet"];
		$retour .= " point";
		if ($this->retourPotion['effet']["nEffet"] > 1): $retour .= 's'; endif; 
		$retour .= " de ".$potion["bm_type"];
		$retour .= " sur ".$potion["caracteristique"];
		$retour .= "
L'effet est immédiat.";
		
		return $retour;
	}
}
