<?php

class Bral_Lieux_Joaillier extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabCompetences = null;

	function prepareCommun() {
		Zend_Loader::loadClass("LabanEquipement");
		Zend_Loader::loadClass("MotRunique");

		$id_equipement_courant = $this->request->get("id_equipement");

		$this->_coutCastars = $this->calculCoutCastars();

		$tabEquipementsLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);

		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				$selected = "";
				if ($id_equipement_courant == $e["id_laban_equipement"]) {
					$selected = "selected";
				}
			
				$t = array(
					"id_laban_equipement" => $e["id_laban_equipement"],
					"id_fk_recette_laban_equipement" => $e["id_fk_recette_laban_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_laban_equipement"],
					"nb_runes" => $e["nb_runes_laban_equipement"],
					"id_fk_type_piece" => $e["id_fk_type_piece_type_equipement"],
					"selected" => $selected
				);
				
				if ($id_equipement_courant == $e["id_laban_equipement"]) {
					$equipementCourant = $t;
				}
				$tabEquipementsLaban[] = $t;
			}
		}
		
		$this->view->nbEquipementsLaban = count($tabEquipementsLaban);
		$this->view->equipementsLaban = $tabEquipementsLaban;

		$this->view->coutCastars = $this->_coutCastars;
		$this->view->achatPossibleCastars = ($this->view->user->castars_hobbit - $this->_coutCastars >= 0);
		// $this->view->utilisationPaPossible initialisé dans Bral_Lieux_Lieu
		
		$this->view->equipementEnCours = null;
		
		if (isset($equipementCourant)) {
			Zend_Loader::loadClass("EquipementRune");
			$tabEquipementsRune = null;
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdEquipement($id_equipement_courant);
			
			foreach($equipementRunes as $e) {
				$tabEquipementsRune[] = array(
				"id_rune_equipement_rune" => $e["id_rune_equipement_rune"],
				"id_fk_type_rune_equipement_rune" => $e["id_fk_type_rune_equipement_rune"],
				"nom_type_rune" => $e["nom_type_rune"],
				);
			}
			
			$this->view->nbEquipementRune = count($tabEquipementsRune);
			$this->view->equipementRunes = $tabEquipementsRune;
			$this->view->equipementCourant = $equipementCourant;
			
			Zend_Loader::loadClass("LabanRune");
			$tabLabanRune = null;
			$labanRuneTable = new LabanRune();
			$labanRunes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);
			
			foreach($labanRunes as $l) {
				$tabLabanRune[$l["id_rune_laban_rune"]] = array(
				"id_fk_type_rune_laban_rune" => $l["id_fk_type_laban_rune"],
				"nom_type_rune" => $l["nom_type_rune"],
				"image_type_rune" => $l["image_type_rune"],
				);
			}
			$this->view->nbLabanRune = count($tabLabanRune);
			$this->view->labanRunes = $tabLabanRune;
		}
	}

	function prepareFormulaire() {
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {
	
		$idEquipementLaban = $this->request->get("valeur_1");
		$nbRunes = $this->request->get("valeur_2");
		$runes = $this->request->get("valeur_3");
		
		if ((int) $idEquipementLaban."" != $this->request->get("valeur_1")."") {
			throw new Zend_Exception(get_class($this)." Equipement Laban invalide=".$idEquipementLaban);
		} else {
			$idEquipementLaban = (int)$idEquipementLaban;
		}
		
		if ($idEquipementLaban != $this->view->equipementCourant["id_laban_equipement"]) {
			throw new Zend_Exception(get_class($this)." idEquipement interdit A=".$idEquipementLaban. " B=".$this->view->equipementCourant["id_laban_equipement"]);
		}
		
		if ((int) $nbRunes."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Nb Rune invalide=".$nbRunes);
		} else {
			$nbRunes = (int)$nbRunes;
		}
		
		if ($runes == "" || $runes == null) {
			throw new Zend_Exception(get_class($this)." Runes invalides=".$runes);
		}
		
		$tabRunesJs = explode(",", $runes);
		$tabRunes = null;
		// on regarde si les runes sont présentes dans le laban
		
		$tmp = $this->view->labanRunes;
		$nb = 0;
		foreach($tabRunesJs as $u) {
			$trouve = false;
			foreach($tmp as $k => $r) {
				if ((int)$u == $k) {
					$tabRunes[$k] = $r;
					$trouve = true;
					$nb++;
					break;
				}
			}
			if ($trouve == false) {
				throw new Zend_Exception(get_class($this)." Rune invalide =".$u);
			}
		}
		
		if ($nb != $nbRunes) {
				throw new Zend_Exception(get_class($this)." Nombre de runes invalides A n1=".$nb. " n2=".$nbRunes);
		}

		if ($nb == 0 || $nb > $this->view->equipementCourant["nb_runes"]) {
				throw new Zend_Exception(get_class($this)." Nombre de runes invalides B n1=".$nb. " n2=".$this->view->equipementCourant["nb_runes"]);
		}
		
		$this->view->suffixe = "";
		$this->calculSertir($tabRunes);
		$this->view->nbRunes = $nb;
		$this->view->tabRunes = $tabRunes;
	}

	private function calculSertir($tabRunes) {
		$equipementRuneTable = new EquipementRune();
		$labanRuneTable = new LabanRune();
		
		// on regarde si les runes ne signifient pas un mot runique
		$motRuniqueTable = new MotRunique();
		
		$id_fk_mot_runique_laban_equipement = null;
		
		$motsRowset = $motRuniqueTable->findByIdTypePieceAndRunes($this->view->equipementCourant["id_fk_type_piece"], $tabRunes);
		if (count($motsRowset) > 0) {
			foreach ($motsRowset as $m) {
				$id_fk_mot_runique_laban_equipement = $m["id_mot_runique"];
				$this->view->suffixe = $m["suffixe_mot_runique"];
				break; // s'il y a plusieurs mots (ce qui devrait jamais arriver), on prend le premier
			}
		}
		
		$ordre = 0;
		foreach($tabRunes as $k => $v) {
			$ordre++;
			$data = array(
				'id_equipement_rune' => $this->view->equipementCourant["id_laban_equipement"],
				'id_rune_equipement_rune' => $k,
				'id_fk_type_rune_equipement_rune' => $v["id_fk_type_rune_laban_rune"],
				'ordre_equipement_rune' => $ordre
			);
			$equipementRuneTable->insert($data);
			
			// Suppression des runes du laban
			$where = "id_rune_laban_rune = ".$k;
			$labanRunes = $labanRuneTable->delete($where);
		}
		
		if ($id_fk_mot_runique_laban_equipement != null) {
			$labanEquipementTable = new LabanEquipement();
			$data = array(
				'id_fk_mot_runique_laban_equipement' => $id_fk_mot_runique_laban_equipement,
			);
			$where = "id_laban_equipement=".$this->view->equipementCourant["id_laban_equipement"];
			$labanEquipementTable->update($data, $where);
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_metier", "box_laban");
	}

	private function calculCoutCastars() {
		return 100;
	}
}