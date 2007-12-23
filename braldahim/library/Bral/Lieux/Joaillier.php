<?php

class Bral_Lieux_Joaillier extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabCompetences = null;

	function prepareCommun() {
		Zend_Loader::loadClass("LabanEquipement");

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
			$equipementRunes = $equipementRuneTable->findByIdEquipement($this->view->user->id_hobbit);
			
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
				$tabLabanRune[] = array(
				"id_rune_laban_rune" => $l["id_rune_laban_rune"],
				"id_fk_type_rune_laban_rune" => $l["id_fk_type_laban_rune"],
				"nom_type_rune" => $l["nom_type_rune"],
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
	
	}


	function getListBoxRefresh() {
		return array("box_profil", "box_metier", "box_laban");
	}

	private function calculCoutCastars() {
		return 100;
	}
}