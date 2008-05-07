<?php

class Bral_Competences_Deposer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Castar");
		Zend_Loader::loadClass("Laban");
		
		$this->view->deposerOk = false;
		
		if ($this->request->get("valeur_1") != "") {
			$id_type_courant = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
			if ($id_type_courant < 1 && $id_type_courant > 7) {
				throw new Zend_Exception("Bral_Competences_Deposer Valeur invalide : id_type_courant=".$id_type_courant);
			}
		} else {
			$id_type_courant = -1;
		}
		
		$typesElements[1] = array("id_type_element" => 1, "selected" => $id_type_courant, "nom_systeme" => "castars", "nom_element" => "Castars");
		$typesElements[2] = array("id_type_element" => 2, "selected" => $id_type_courant, "nom_systeme" => "equipements", "nom_element" => "Equipements");
		$typesElements[3] = array("id_type_element" => 3, "selected" => $id_type_courant, "nom_systeme" => "minerais", "nom_element" => "Minerais");
		$typesElements[4] = array("id_type_element" => 4, "selected" => $id_type_courant, "nom_systeme" => "partiesplantes", "nom_element" => "Parties de Plantes");
		$typesElements[5] = array("id_type_element" => 5, "selected" => $id_type_courant, "nom_systeme" => "potions", "nom_element" => "Potions");
		$typesElements[6] = array("id_type_element" => 6, "selected" => $id_type_courant, "nom_systeme" => "runes", "nom_element" => "Runes");
		$typesElements[7] = array("id_type_element" => 7, "selected" => $id_type_courant, "nom_systeme" => "autres", "nom_element" => "Autres Elements");
		
		$this->view->typeElements = $typesElements;
		$this->view->type = null;
		
		if ($id_type_courant != -1) {
			$this->view->type = $typesElements[$id_type_courant]["nom_systeme"];
			$this->prepareDeposer();
		}
	}

	private function prepareDeposer() {
		switch($this->view->type) {
			case "castars" :
				$this->prepareTypeCastars();
				break;
			case "equipements" :
				$this->prepareTypeEquipements();
				break;
			case "runes" :
				$this->prepareTypeRunes();
				break;
			case "potions" :
				$this->prepareTypePotions();
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Deposer prepareType invalide : type=".$this->view->type);
		}
	}
	
	private function calculDeposer() {
		switch($this->view->type) {
			case "castars" :
				$this->deposeTypeCastars();
				break;
			case "equipements" :
				$this->deposeTypeEquipements();
				break;
			case "runes" :
				$this->deposeTypeRunes();
				break;
			case "potions" :
				$this->deposeTypePotions();
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Deposer prepareType invalide : type=".$this->view->type);
		}
	}
	
	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification deposer
		if ($this->view->deposerOk == false) {
			throw new Zend_Exception(get_class($this)." Deposer interdit ");
		}
		
		$this->calculDeposer();
		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_laban", "box_evenements");
	}
	
	private function prepareTypeCastars() {
		$this->view->castars = $this->view->user->castars_hobbit;
		
		if ($this->view->castars > 0) {
			$this->view->deposerOk = true;
		} else {
			$this->view->deposerOk = false;
		}
	}
	
	private function deposeTypeCastars() {
		$nbCastars = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		
		if ($nbCastars > $this->view->user->castars_hobbit || $nbCastars < 0) {
			throw new Zend_Exception(get_class($this)." NB Castars invalide : ".$nbcastars);
		} 
		
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $nbCastars;
		
		$castarsTable = new Castar();
		$data = array(
			"nb_castar" => $nbCastars,
			"x_castar" => $this->view->user->x_hobbit,
			"y_castar" => $this->view->user->y_hobbit,
		);
		$castarsTable->insertOrUpdate($data);
	}
	
	private function prepareTypeEquipements() {
		Zend_Loader::loadClass("LabanEquipement");
		$tabEquipements = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($equipements) > 0) {
			$this->view->deposerOk = true;
			foreach ($equipements as $e) {
				$tabEquipements[$e["id_laban_equipement"]] = array(
						"id_equipement" => $e["id_laban_equipement"],
						"nom" => $e["nom_type_equipement"],
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_recette_equipement"],
						"nb_runes" => $e["nb_runes_laban_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"nb_runes" => $e["nb_runes_laban_equipement"],
						"id_fk_mot_runique" => $e["id_fk_mot_runique_laban_equipement"], 
						"id_fk_recette" => $e["id_fk_recette_laban_equipement"] ,
				);
			}
		} else {
			$this->view->deposerOk = false;
		}
		$this->view->equipements = $tabEquipements;
	}
	
	private function deposeTypeEquipements() {
		Zend_Loader::loadClass("ElementEquipement");
		$idEquipement = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$this->prepareTypeEquipements();
		
		if (!array_key_exists($idEquipement, $this->view->equipements)) {
			throw new Zend_Exception(get_class($this)." ID Equipement invalide : ".$idEquipement);
		} 
		
		$equipement = $this->view->equipements[$idEquipement];
		
		$labanEquipementTable = new LabanEquipement();
		$where = "id_laban_equipement=".$idEquipement;
		$labanEquipementTable->delete($where);
		
		$elementEquipementTable = new ElementEquipement();
		$data = array (
			"id_element_equipement" => $equipement["id_equipement"],
			"x_element_equipement" => $this->view->user->x_hobbit,
			"y_element_equipement" => $this->view->user->y_hobbit,
			"id_fk_recette_element_equipement" => $equipement["id_fk_recette"],
			"nb_runes_element_equipement" => $equipement["nb_runes"],
			"id_fk_mot_runique_element_equipement" => $equipement["id_fk_mot_runique"],
		);
		$elementEquipementTable->insert($data);
	}
	
	private function prepareTypeRunes() {
		Zend_Loader::loadClass("LabanRune");
		$tabRunes = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($runes) > 0) {
			$this->view->deposerOk = true;
			foreach ($runes as $r) {
				$tabRunes[$r["id_rune_laban_rune"]] = array(
					"id_rune" => $r["id_rune_laban_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
					"id_fk_type_rune" => $r["id_fk_type_laban_rune"],
				);
			}
		} else {
			$this->view->deposerOk = false;
		}
		$this->view->runes = $tabRunes;
	}
	
	private function deposeTypeRunes() {
		Zend_Loader::loadClass("ElementRune");
		$idRune = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$this->prepareTypeRunes();
		
		if (!array_key_exists($idRune, $this->view->runes)) {
			throw new Zend_Exception(get_class($this)." ID Rune invalide : ".$idRune);
		} 
		
		$rune = $this->view->runes[$idRune];
		
		$labanRuneTable = new LabanRune();
		$where = "id_rune_laban_rune=".$idRune;
		$labanRuneTable->delete($where);
		
		$elementRuneTable = new ElementRune();
		$data = array (
			"id_element_rune" => $rune["id_rune"],
			"x_element_rune" => $this->view->user->x_hobbit,
			"y_element_rune" => $this->view->user->y_hobbit,
			"id_fk_type_element_rune" => $rune["id_fk_type_rune"],
		);
		$elementRuneTable->insert($data);
	}
	
	private function prepareTypePotions() {
		Zend_Loader::loadClass("LabanPotion");
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);
		
		if (count($potions) > 0) {
			$this->view->deposerOk = true;
			foreach ($potions as $p) {
				$tabPotions[$p["id_laban_potion"]] = array(
					"id_potion" => $p["id_laban_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_laban_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"id_fk_type_qualite" => $p["id_fk_type_qualite_laban_potion"],
					"id_fk_type" => $p["id_fk_type_laban_potion"]
				);
			}
		} else {
			$this->view->deposerOk = false;
		}
		$this->view->potions = $tabPotions;
	}
	
	private function deposeTypePotions() {
		Zend_Loader::loadClass("ElementPotion");
		$idPotion = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$this->prepareTypePotions();
		
		if (!array_key_exists($idPotion, $this->view->potions)) {
			throw new Zend_Exception(get_class($this)." ID Potion invalide : ".$idPotion);
		} 
		
		$potion = $this->view->potions[$idPotion];
		
		$labanPotionTable = new LabanPotion();
		$where = "id_laban_potion=".$idPotion;
		$labanPotionTable->delete($where);
		
		$elementPotionTable = new ElementPotion();
		$data = array (
			"id_element_potion" => $potion["id_potion"],
			"x_element_potion" => $this->view->user->x_hobbit,
			"y_element_potion" => $this->view->user->y_hobbit,
			"niveau_element_potion" => $potion["niveau"],
			"id_fk_type_qualite_element_potion" => $potion["id_fk_type_qualite"],
			"id_fk_type_element_potion" => $potion["id_fk_type"],
		);
		$elementPotionTable->insert($data);
	}
}
