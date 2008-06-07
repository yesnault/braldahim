<?php

class Bral_Competences_Deposer extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->deposerOk = false;
		$this->listBoxRefresh = array("box_profil", "box_laban", "box_evenements");
		
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
			case "minerais" :
				$this->prepareTypeMinerais();
				break;
			case "partiesplantes" :
				$this->prepareTypePartiesPlantes();
				break;
			case "autres" :
				$this->prepareTypeAutres();
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Deposer prepareType invalide : type=".$this->view->type);
		}
	}
	
	private function calculDeposer() {
		$this->listBoxRefresh = array("box_profil", "box_vue", "box_laban", "box_evenements");
		
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
			case "minerais" :
				$this->deposeTypeMinerais();
				break;
			case "partiesplantes" :
				$this->deposeTypePartiesPlantes();
				break;
			case "autres" :
				$this->deposeTypeAutres();
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
		$this->calculPoids();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return $this->listBoxRefresh;
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
		Zend_Loader::loadClass("Castar");
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
		unset($castarsTable);
	}
	
	private function prepareTypeEquipements() {
		Zend_Loader::loadClass("LabanEquipement");
		$tabEquipements = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanEquipementTable);
		
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
		unset($labanEquipementTable);
		
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
		unset($elementEquipementTable);
	}
	
	private function prepareTypeRunes() {
		Zend_Loader::loadClass("LabanRune");
		$tabRunes = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanRuneTable);
		
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
		unset($labanRuneTable);
		
		$elementRuneTable = new ElementRune();
		$data = array (
			"id_element_rune" => $rune["id_rune"],
			"x_element_rune" => $this->view->user->x_hobbit,
			"y_element_rune" => $this->view->user->y_hobbit,
			"id_fk_type_element_rune" => $rune["id_fk_type_rune"],
		);
		$elementRuneTable->insert($data);
		unset($elementRuneTable);
	}
	
	private function prepareTypePotions() {
		Zend_Loader::loadClass("LabanPotion");
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanPotionTable);
		
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
		unset($labanPotionTable);
		
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
		unset($elementPotionTable);
	}
	
	private function prepareTypeMinerais() {
		Zend_Loader::loadClass("LabanMinerai");
		$tabMineraisBruts = null;
		$tabLingots = null;
		
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanMineraiTable);
		
		if (count($minerais) > 0) {
			$this->view->deposerOk = true;

			foreach ($minerais as $m) {
				if ($m["quantite_brut_laban_minerai"] > 0) {
					$tabMineraisBruts[$m["id_fk_type_laban_minerai"]] = array(
						"id_type_minerai" => $m["id_fk_type_laban_minerai"],
						"type" => $m["nom_type_minerai"],
						"quantite" => $m["quantite_brut_laban_minerai"],
					);
				}
				
				if ($m["quantite_lingots_laban_minerai"] > 0) {
					$tabLingots[$m["id_fk_type_laban_minerai"]] = array(
						"id_type_minerai" => $m["id_fk_type_laban_minerai"],
						"type" => $m["nom_type_minerai"],
						"quantite" => $m["quantite_lingots_laban_minerai"],
					);
				}
			}
		} else {
			$this->view->deposerOk = false;
		}
		$this->view->mineraisBruts = $tabMineraisBruts;
		$this->view->lingots = $tabLingots;
	}
	
	private function deposeTypeMinerais() {
		Zend_Loader::loadClass("ElementMinerai");
		$this->prepareTypeMinerais();
		
		$idMineraiBrut = null;
		$nbMineraiBrut = null;
		
		$idLingot = null;
		$nbLingot = null;
		
		$labanMineraiTable = new LabanMinerai();
		$elementMineraiTable = new ElementMinerai();
		
		if ($this->request->get("valeur_2") > 0 && $this->request->get("valeur_3") > 0) {
			$idMineraiBrut = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
			$nbMineraiBrut = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
			
			if (!array_key_exists($idMineraiBrut, $this->view->mineraisBruts)) {
				throw new Zend_Exception(get_class($this)." ID Minerai Brut invalide : ".$idMineraiBrut);
			} 
			
			$minerai = $this->view->mineraisBruts[$idMineraiBrut];

			if ($nbMineraiBrut > $minerai["quantite"] || $nbMineraiBrut < 0) {
				throw new Zend_Exception(get_class($this)." Quantite Minerai Brut invalide : ".$nbMineraiBrut);
			}
			
			$data = array(
				"quantite_brut_laban_minerai" => -$nbMineraiBrut,
				"id_fk_type_laban_minerai" => $minerai["id_type_minerai"],
				"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
			);
			$labanMineraiTable->insertOrUpdate($data);
			
			$data = array (
				"x_element_minerai" => $this->view->user->x_hobbit,
				"y_element_minerai" => $this->view->user->y_hobbit,
				"id_fk_type_element_minerai" => $minerai["id_type_minerai"],
				"quantite_brut_element_minerai" => $nbMineraiBrut,
			);
			$elementMineraiTable->insertOrUpdate($data);
		}
		
		if ($this->request->get("valeur_4") > 0 && $this->request->get("valeur_5") > 0) {
			$idLingot = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
			$nbLingot = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
			
			if (!array_key_exists($idLingot, $this->view->lingots)) {
				throw new Zend_Exception(get_class($this)." ID Lingot invalide : ".$idLingot);
			} 
			
			$lingot = $this->view->lingots[$idLingot];
			
			if ($nbLingot > $lingot["quantite"] || $nbLingot < 0) {
				throw new Zend_Exception(get_class($this)." Quantite lingot invalide : ".$nbLingot);
			}
			
			$data = array(
				"quantite_lingots_laban_minerai" => -$nbLingot,
				"id_fk_type_laban_minerai" => $lingot["id_type_minerai"],
				"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
			);
			$labanMineraiTable->insertOrUpdate($data);
			
			$data = array (
				"x_element_minerai" => $this->view->user->x_hobbit,
				"y_element_minerai" => $this->view->user->y_hobbit,
				"id_fk_type_element_minerai" => $lingot["id_type_minerai"],
				"quantite_lingots_element_minerai" => $nbLingot,
			);
			$elementMineraiTable->insertOrUpdate($data);
		}
		unset($elementMineraiTable);
		unset($labanMineraiTable);
	}
	
	private function prepareTypePartiesPlantes() {
		Zend_Loader::loadClass("LabanPartieplante");
		$tabPartiePlantesBrutes = null;
		$tabPartiePlantesPreparees = null;
		$tabLingots = null;
		
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiesPlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanPartiePlanteTable);
		
		if (count($partiesPlantes) > 0) {
			$this->view->deposerOk = true;

			foreach ($partiesPlantes as $m) {
				if ($m["quantite_laban_partieplante"] > 0) {
					$tabPartiePlantesBrutes[$m["id_fk_type_laban_partieplante"]."-".$m["id_fk_type_plante_laban_partieplante"]] = array(
						"id_type_partieplante" => $m["id_fk_type_laban_partieplante"],
						"id_type_plante" => $m["id_fk_type_plante_laban_partieplante"],
						"type" => $m["nom_type_partieplante"],
						"type_plante" => $m["nom_type_plante"],
						"quantite" => $m["quantite_laban_partieplante"],
					);
				}
				
				if ($m["quantite_preparee_laban_partieplante"] > 0) {
					$tabPartiePlantesPreparees[$m["id_fk_type_laban_partieplante"]."-".$m["id_fk_type_plante_laban_partieplante"]] = array(
						"id_type_partieplante" => $m["id_fk_type_laban_partieplante"],
						"id_type_plante" => $m["id_fk_type_plante_laban_partieplante"],
						"type" => $m["nom_type_partieplante"],
						"type_plante" => $m["nom_type_plante"],
						"quantite" => $m["quantite_preparee_laban_partieplante"],
					);
				}
			}
		} else {
			$this->view->deposerOk = false;
		}
		$this->view->partiePlantesBrutes = $tabPartiePlantesBrutes;
		$this->view->partiePlantesPreparees = $tabPartiePlantesPreparees;
	}
	
	private function deposeTypePartiesPlantes() {
		Zend_Loader::loadClass("ElementPartieplante");
		$this->prepareTypePartiesPlantes();
		
		$idPartiePlanteBrute = null;
		$nbPartiePlanteBrute = null;
		
		$idPartiePlantePreparee = null;
		$nbPartiePlantePreparee = null;
		
		$labanPartiePlanteTable = new LabanPartieplante();
		$elementPartiePlanteTable = new ElementPartieplante();
		
		if ($this->request->get("valeur_2") > 0 && $this->request->get("valeur_3") > 0) {
			$idPartiePlanteBrute = $this->request->get("valeur_2");
			$nbPartiePlanteBrute = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
			
			if (!array_key_exists($idPartiePlanteBrute, $this->view->partiePlantesBrutes)) {
				throw new Zend_Exception(get_class($this)." ID PartiePlante Brute invalide : ".$idPartiePlanteBrute);
			} 
			
			$partiePlanteBrute = $this->view->partiePlantesBrutes[$idPartiePlanteBrute];

			if ($nbPartiePlanteBrute > $partiePlanteBrute["quantite"] || $nbPartiePlanteBrute < 0) {
				throw new Zend_Exception(get_class($this)." Quantite PartiePlante Brute invalide : ".$nbPartiePlanteBrute);
			}
			
			$data = array(
				"quantite_laban_partieplante" => -$nbPartiePlanteBrute,
				"id_fk_type_laban_partieplante" => $partiePlanteBrute["id_type_partieplante"],
				"id_fk_type_plante_laban_partieplante" => $partiePlanteBrute["id_type_plante"],
				"id_fk_hobbit_laban_partieplante" => $this->view->user->id_hobbit,
			);
			$labanPartiePlanteTable->insertOrUpdate($data);
			
			$data = array (
				"x_element_partieplante" => $this->view->user->x_hobbit,
				"y_element_partieplante" => $this->view->user->y_hobbit,
				"id_fk_type_element_partieplante" => $partiePlanteBrute["id_type_partieplante"],
				"id_fk_type_plante_element_partieplante" => $partiePlanteBrute["id_type_plante"],
				"quantite_element_partieplante" => $nbPartiePlanteBrute,
			);
			$elementPartiePlanteTable->insertOrUpdate($data);
		}
		
		if ($this->request->get("valeur_4") > 0 && $this->request->get("valeur_5") > 0) {
			$idPartiePlantePreparee = $this->request->get("valeur_4");
			$nbPartiePlantePreparee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
			
			if (!array_key_exists($idPartiePlantePreparee, $this->view->partiePlantesPreparees)) {
				throw new Zend_Exception(get_class($this)." ID PartiePlantePreparee invalide : ".$idPartiePlantePreparee);
			} 
			
			$partiePlantePreparee = $this->view->partiePlantesPreparees[$idPartiePlantePreparee];
			
			if ($nbPartiePlantePreparee > $partiePlantePreparee["quantite"] || $nbPartiePlantePreparee < 0) {
				throw new Zend_Exception(get_class($this)." Quantite Plante Preparee invalide : ".$nbPartiePlantePreparee);
			}
			
			$data = array(
				"quantite_preparee_laban_partieplante" => -$nbPartiePlantePreparee,
				"id_fk_type_laban_partieplante" => $partiePlantePreparee["id_type_partieplante"],
				"id_fk_type_plante_laban_partieplante" => $partiePlantePreparee["id_type_plante"],
				"id_fk_hobbit_laban_partieplante" => $this->view->user->id_hobbit,
			);
			
			$data = array (
				"x_element_partieplante" => $this->view->user->x_hobbit,
				"y_element_partieplante" => $this->view->user->y_hobbit,
				"id_fk_type_element_partieplante" => $partiePlantePreparee["id_type_partieplante"],
				"id_fk_type_plante_element_partieplante" => $partiePlantePreparee["id_type_plante"],
				"quantite_preparee_element_partieplante" => $nbPartiePlantePreparee,
			);
			$elementPartiePlanteTable->insertOrUpdate($data);
		}
		unset($elementPartiePlanteTable);
		unset($labanPartiePlanteTable);
	}
	
	private function prepareTypeAutres() {
		Zend_Loader::loadClass("Laban");
		$tabAutres = null;
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanTable);
		
		if (count($laban) == 1) {
			foreach ($laban as $p) {
				if ($p["quantite_peau_laban"] > 0) $tabAutres[1] = array("nom" => "Peau", "nom_systeme" => "quantite_peau" , "nb" => $p["quantite_peau_laban"]);
				if ($p["quantite_viande_laban"] > 0) $tabAutres[2] = array("nom" => "Viande", "nom_systeme" => "quantite_viande" , "nb" => $p["quantite_viande_laban"]);
				if ($p["quantite_viande_preparee_laban"] > 0) $tabAutres[3] = array("nom" => "Viande pr&eacute;par&eacute;e", "nom_systeme" => "quantite_viande_preparee" , "nb" => $p["quantite_viande_preparee_laban"]);
				if ($p["quantite_ration_laban"] > 0) $tabAutres[4] = array("nom" => "Ration", "nom_systeme" => "quantite_ration" , "nb" => $p["quantite_ration_laban"]);
				if ($p["quantite_cuir_laban"] > 0) $tabAutres[5] = array("nom" => "Cuir", "nom_systeme" => "quantite_cuir" , "nb" => $p["quantite_cuir_laban"]);
				if ($p["quantite_fourrure_laban"] > 0) $tabAutres[6] = array("nom" => "Fourrure", "nom_systeme" => "quantite_fourrure" , "nb" => $p["quantite_fourrure_laban"]);
				if ($p["quantite_planche_laban"] > 0) $tabAutres[7] = array("nom" => "Planche", "nom_systeme" => "quantite_planche" , "nb" => $p["quantite_planche_laban"]);
				
				if (count($tabAutres) > 0) {
					$this->view->deposerOk = true;
				}
			}
		} else {
			$this->view->deposerOk = false;
		}
		$this->view->autres = $tabAutres;
	}
	
	private function deposeTypeAutres() {
		Zend_Loader::loadClass("Element");
		$this->prepareTypeAutres();
		
		$idAutre = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$nb = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		
		if (!array_key_exists($idAutre, $this->view->autres)) {
			throw new Zend_Exception(get_class($this)." ID Autres invalide : ".$idAutre);
		} 
		
		$autre = $this->view->autres[$idAutre];

		if ($nb > $autre["nb"]) {
			$nb = $autre["nb"];
		}
		
		if ($nb < 0) {
			throw new Zend_Exception(get_class($this)." Quantite invalide : ".$nb);
		}
		
		$labanTable = new Laban();
		$data = array(
			$autre["nom_systeme"]."_laban" => -$nb,
			"id_fk_hobbit_laban" => $this->view->user->id_hobbit,
		);
		$labanTable->insertOrUpdate($data);
		unset($labanTable);
		
		$elementTable = new Element();
		$data = array(
			$autre["nom_systeme"]."_element" => $nb,
			"x_element" => $this->view->user->x_hobbit,
			"y_element" => $this->view->user->y_hobbit,
		);
		$elementTable->insertOrUpdate($data);
		unset($elementTable);
	}
}
