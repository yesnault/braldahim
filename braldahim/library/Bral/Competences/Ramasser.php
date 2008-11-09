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
class Bral_Competences_Ramasser extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->ramasserOk = false;
		$this->listBoxRefresh = array("box_profil", "box_laban", "box_evenements");
		
		if ($this->request->get("valeur_1") != "") {
			$id_type_courant = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
			if ($id_type_courant < 1 && $id_type_courant > 7) {
				throw new Zend_Exception("Bral_Competences_Ramasser Valeur invalide : id_type_courant=".$id_type_courant);
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
			$this->prepareRamasser();
		}
	}

	private function prepareRamasser() {
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
				throw new Zend_Exception("Bral_Competences_Ramasser prepareType invalide : type=".$this->view->type);
		}
	}
	
	private function calculRamasser() {
		$this->listBoxRefresh = array("box_profil", "box_vue", "box_laban", "box_evenements");
		
		switch($this->view->type) {
			case "castars" :
				$this->ramasseTypeCastars();
				break;
			case "equipements" :
				$this->ramasseTypeEquipements();
				break;
			case "runes" :
				$this->ramasseTypeRunes();
				break;
			case "potions" :
				$this->ramasseTypePotions();
			case "minerais" :
				$this->ramasseTypeMinerais();
				break;
			case "partiesplantes" :
				$this->ramasseTypePartiesPlantes();
				break;
			case "autres" :
				$this->ramasseTypeAutres();
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Ramasser prepareType invalide : type=".$this->view->type);
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

		// Verification ramasser
		if ($this->view->ramasserOk == false) {
			throw new Zend_Exception(get_class($this)." Ramasser interdit ");
		}
		
		$this->calculRamasser();
		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return $this->listBoxRefresh;
	}
	
	private function prepareTypeCastars() {
		Zend_Loader::loadClass("Castar");
		$castarTable = new Castar();
		$castars = $castarTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($castarTable);
		
		$this->view->castars = 0;
		if ($castars != null && count($castars) == 1) {
			$this->view->castars = $castars[0]["nb_castar"];
		}
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$this->view->nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);
		
		if ($this->view->nbCastarsPossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
		
		if ($this->view->castars > 0) {
			$this->view->ramasserOk = true;
		} else {
			$this->view->ramasserOk = false;
		}
	}
	
	private function ramasseTypeCastars() {
		$nbCastars = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		
		if ($nbCastars > $this->view->castars || $nbCastars < 0) {
			throw new Zend_Exception(get_class($this)." NB Castars invalide : ".$nbCastars);
		} 
		
		if ($this->view->poidsPlaceDisponible == false) {
			throw new Zend_Exception(get_class($this)." Castars place non disponible");
		} 
		
		if ($nbCastars > $this->view->nbCastarsPossible) {
			throw new Zend_Exception(get_class($this)." Castars place non disponible. Nb Invalide possible:". $this->view->nbCastarsPossible. " nb=".$nbCastars);
		}
		
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit + $nbCastars;
		
		$castarTable = new Castar();
		$data = array(
			"nb_castar" => -$nbCastars,
			"x_castar" => $this->view->user->x_hobbit,
			"y_castar" => $this->view->user->y_hobbit,
		);
		$castarTable->insertOrUpdate($data);
		unset($castarTable);
		
		$this->view->texteRamassage = $nbCastars. " castar";
		if ($nbCastars > 1) $this->view->texteRamassage .= "s";
	}
	
	private function prepareTypeEquipements() {
		Zend_Loader::loadClass("ElementEquipement");
		$tabEquipements = null;
		$elementEquipementTable = new ElementEquipement();
		$equipements = $elementEquipementTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($elementEquipementTable);
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		
		$this->view->poidsRestant = $poidsRestant;
		$this->view->poidsPlaceDisponible = false;
		
		if (count($equipements) > 0) {
			$this->view->ramasserOk = true;
			foreach ($equipements as $e) {
				if ($poidsRestant >= $e["poids_recette_equipement"]) {
					$this->view->poidsPlaceDisponible = true;
					$poids_ok = true;
				} else {
					$poids_ok = false;
				}
				$tabEquipements[$e["id_element_equipement"]] = array(
						"id_equipement" => $e["id_element_equipement"],
						"nom" => $e["nom_type_equipement"],
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_recette_equipement"],
						"nb_runes" => $e["nb_runes_element_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"nb_runes" => $e["nb_runes_element_equipement"],
						"id_fk_mot_runique" => $e["id_fk_mot_runique_element_equipement"], 
						"id_fk_recette" => $e["id_fk_recette_element_equipement"],
						"poids" => $e["poids_recette_equipement"],
						"poids_ok" => $poids_ok,
				);
			}
		} else {
			$this->view->ramasserOk = false;
		}
		$this->view->equipements = $tabEquipements;
	}
	
	private function ramasseTypeEquipements() {
		Zend_Loader::loadClass("LabanEquipement");
		$idEquipement = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$this->prepareTypeEquipements();
		
		if (!array_key_exists($idEquipement, $this->view->equipements)) {
			throw new Zend_Exception(get_class($this)." ID Equipement invalide : ".$idEquipement);
		} 
		
		$equipement = $this->view->equipements[$idEquipement];
		
		if ($equipement["poids_ok"] !== true) {
			throw new Zend_Exception(get_class($this)." ID Equipement poids invalide : ".$idEquipement);
		} 
		
		$elementEquipementTable = new ElementEquipement();
		$where = "id_element_equipement=".$idEquipement;
		$elementEquipementTable->delete($where);
		unset($elementEquipementTable);
		
		$labanEquipementTable = new LabanEquipement();
		$data = array (
			"id_laban_equipement" => $equipement["id_equipement"],
			"id_fk_hobbit_laban_equipement" => $this->view->user->id_hobbit,
			"id_fk_recette_laban_equipement" => $equipement["id_fk_recette"],
			"nb_runes_laban_equipement" => $equipement["nb_runes"],
			"id_fk_mot_runique_laban_equipement" => $equipement["id_fk_mot_runique"],
		);
		$labanEquipementTable->insert($data);
		unset($labanEquipementTable);
		
		$this->view->texteRamassage = "l'&eacute;quipement n&deg; ". $equipement["id_equipement"];
	}
	
	private function prepareTypeRunes() {
		Zend_Loader::loadClass("ElementRune");
		$tabRunes = null;
		$elementRuneTable = new ElementRune();
		$runes = $elementRuneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($elementRuneTable);
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$this->view->nbRunePossible = floor($poidsRestant / Bral_Util_Poids::POIDS_RUNE);
		
		if ($this->view->nbRunePossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
		
		if (count($runes) > 0) {
			$this->view->ramasserOk = true;
			foreach ($runes as $r) {
				$tabRunes[$r["id_element_rune"]] = array(
					"id_rune" => $r["id_element_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"id_fk_type_rune" => $r["id_fk_type_element_rune"],
				);
			}
		} else {
			$this->view->ramasserOk = false;
		}
		$this->view->runes = $tabRunes;
	}
	
	private function ramasseTypeRunes() {
		Zend_Loader::loadClass("LabanRune");
		$idRune = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$this->prepareTypeRunes();
		
		if (!array_key_exists($idRune, $this->view->runes)) {
			throw new Zend_Exception(get_class($this)." ID Rune invalide : ".$idRune);
		} 
		
		if ($this->view->poidsPlaceDisponible == false) {
			throw new Zend_Exception(get_class($this)." Rune place non disponible");
		} 
		
		$rune = $this->view->runes[$idRune];
		
		
		$elementRuneTable = new ElementRune();
		$where = "id_element_rune=".$rune["id_rune"];
		$elementRuneTable->delete($where);
		unset($elementRuneTable);
		
		$labanRuneTable = new LabanRune();
		$data = array (
			"id_rune_laban_rune" => $rune["id_rune"],
			"id_fk_type_laban_rune" => $rune["id_fk_type_rune"],
			"est_identifiee_rune" => "non",
			"id_fk_hobbit_laban_rune" => $this->view->user->id_hobbit,
		);
		$labanRuneTable->insert($data);
		unset($labanRuneTable);
		
		$this->view->texteRamassage = "la rune n&deg;".$rune["id_rune"];
	}
	
	private function prepareTypePotions() {
		Zend_Loader::loadClass("ElementPotion");
		$tabPotions = null;
		$elementPotionTable = new ElementPotion();
		$potions = $elementPotionTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($elementPotionTable);
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$this->view->nbPotionPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_POTION);
		
		if ($this->view->nbPotionPossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
		
		if (count($potions) > 0) {
			$this->view->ramasserOk = true;
			foreach ($potions as $p) {
				$tabPotions[$p["id_element_potion"]] = array(
					"id_potion" => $p["id_element_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_element_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"id_fk_type_qualite" => $p["id_fk_type_qualite_element_potion"],
					"id_fk_type" => $p["id_fk_type_element_potion"]
				);
			}
		} else {
			$this->view->ramasserOk = false;
		}
		$this->view->potions = $tabPotions;
	}
	
	private function ramasseTypePotions() {
		Zend_Loader::loadClass("LabanPotion");
		$idPotion = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$this->prepareTypePotions();
		
		if (!array_key_exists($idPotion, $this->view->potions)) {
			throw new Zend_Exception(get_class($this)." ID Potion invalide : ".$idPotion);
		} 
		
		if ($this->view->poidsPlaceDisponible == false) {
			throw new Zend_Exception(get_class($this)." Potion place non disponible");
		} 
		
		$potion = $this->view->potions[$idPotion];
		
		$elementPotionTable = new ElementPotion();
		$where = "id_element_potion=".$idPotion;
		$elementPotionTable->delete($where);
		unset($elementPotionTable);
		
		$labanPotionTable = new LabanPotion();
		$data = array (
			"id_laban_potion" => $potion["id_potion"],
			"id_fk_hobbit_laban_potion" => $this->view->user->id_hobbit,
			"niveau_laban_potion" => $potion["niveau"],
			"id_fk_type_qualite_laban_potion" => $potion["id_fk_type_qualite"],
			"id_fk_type_laban_potion" => $potion["id_fk_type"],
		);
		$labanPotionTable->insert($data);
		unset($labanPotionTable);
		
		$this->view->texteRamassage = "la potion n&deg;".$potion["id_potion"];
	}
	
	private function prepareTypeMinerais() {
		Zend_Loader::loadClass("ElementMinerai");
		$tabMineraisBruts = null;
		$tabLingots = null;
		
		$elementMineraiTable = new ElementMinerai();
		$minerais = $elementMineraiTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($elementMineraiTable);
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$this->view->nbMineraisPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_MINERAI);
		$this->view->nbLingotsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_LINGOT);
		$this->view->poidsRestant = $poidsRestant;
		
		if ($this->view->nbMineraisPossible < 1 && $this->view->nbLingotsPossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
		
		if (count($minerais) > 0) {
			$this->view->ramasserOk = true;

			foreach ($minerais as $m) {
				if ($m["quantite_brut_element_minerai"] > 0) {
					$tabMineraisBruts[$m["id_fk_type_element_minerai"]] = array(
						"id_type_minerai" => $m["id_fk_type_element_minerai"],
						"type" => $m["nom_type_minerai"],
						"quantite" => $m["quantite_brut_element_minerai"],
					);
				}
				
				if ($m["quantite_lingots_element_minerai"] > 0) {
					$tabLingots[$m["id_fk_type_element_minerai"]] = array(
						"id_type_minerai" => $m["id_fk_type_element_minerai"],
						"type" => $m["nom_type_minerai"],
						"quantite" => $m["quantite_lingots_element_minerai"],
					);
				}
			}
		} else {
			$this->view->ramasserOk = false;
		}
		$this->view->mineraisBruts = $tabMineraisBruts;
		$this->view->lingots = $tabLingots;
	}
	
	private function ramasseTypeMinerais() {
		Zend_Loader::loadClass("LabanMinerai");
		$this->prepareTypeMinerais();
		
		if ($this->view->poidsPlaceDisponible == false) {
			throw new Zend_Exception(get_class($this)." Minerais place non disponible");
		}
		
		$idMineraiBrut = null;
		$nbMineraiBrut = null;
		
		$idLingot = null;
		$nbLingot = null;
		
		$labanMineraiTable = new LabanMinerai();
		$elementMineraiTable = new ElementMinerai();
		
		$this->view->texteRamassage = "";
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbMineraisPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_MINERAI);
		
		if ($this->request->get("valeur_2") > 0 && $this->request->get("valeur_3") > 0) {
			$idMineraiBrut = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
			$nbMineraiBrut = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
			
			if (!array_key_exists($idMineraiBrut, $this->view->mineraisBruts)) {
				throw new Zend_Exception(get_class($this)." ID Minerai Brut invalide : ".$idMineraiBrut);
			} 
			
			$minerai = $this->view->mineraisBruts[$idMineraiBrut];
			
			if ($nbMineraiBrut > $nbMineraisPossible) {
				$nbMineraiBrut = $nbMineraisPossible;
				$poidsRestant = $poidsRestant - $nbMineraiBrut * Bral_Util_Poids::POIDS_MINERAI;
			}
			
			if ($nbMineraiBrut > $minerai["quantite"] || $nbMineraiBrut < 0) {
				throw new Zend_Exception(get_class($this)." Quantite Minerai Brut invalide : ".$nbMineraiBrut);
			}
			
			$data = array(
				"quantite_brut_laban_minerai" => $nbMineraiBrut,
				"id_fk_type_laban_minerai" => $minerai["id_type_minerai"],
				"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
			);
			$labanMineraiTable->insertOrUpdate($data);
			
			$data = array (
				"x_element_minerai" => $this->view->user->x_hobbit,
				"y_element_minerai" => $this->view->user->y_hobbit,
				"id_fk_type_element_minerai" => $minerai["id_type_minerai"],
				"quantite_brut_element_minerai" => -$nbMineraiBrut,
			);
			$elementMineraiTable->insertOrUpdate($data);
			if ($nbMineraiBrut > 1) {
				$s = "s";
			} else {
				$s = "";
			}
			$this->view->texteRamassage = $nbMineraiBrut." minerai".$s. " brut".$s;
		}
		
		$nbLingotsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_LINGOT);
		
		if ($this->request->get("valeur_4") > 0 && $this->request->get("valeur_5") > 0) {
			$idLingot = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
			$nbLingot = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
			
			if (!array_key_exists($idLingot, $this->view->lingots)) {
				throw new Zend_Exception(get_class($this)." ID Lingot invalide : ".$idLingot);
			} 
			
			$lingot = $this->view->lingots[$idLingot];
			
			if ($nbLingot > $nbLingotsPossible) {
				$nbLingot = $nbLingotsPossible;
				$poidsRestant = $poidsRestant - $nbLingot * Bral_Util_Poids::POIDS_LINGOT;
			}
			
			if ($nbLingot > $lingot["quantite"] || $nbLingot < 0) {
				throw new Zend_Exception(get_class($this)." Quantite lingot invalide : ".$nbLingot);
			}
			
			$data = array(
				"quantite_lingots_laban_minerai" => $nbLingot,
				"id_fk_type_laban_minerai" => $lingot["id_type_minerai"],
				"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
			);
			$labanMineraiTable->insertOrUpdate($data);
			
			$data = array (
				"x_element_minerai" => $this->view->user->x_hobbit,
				"y_element_minerai" => $this->view->user->y_hobbit,
				"id_fk_type_element_minerai" => $lingot["id_type_minerai"],
				"quantite_lingots_element_minerai" => -$nbLingot,
			);
			$elementMineraiTable->insertOrUpdate($data);
			
			if ($nbLingot > 1) {
				$s = "s";
			} else {
				$s = "";
			}
			if ($this->view->texteRamassage != "" ) {
				$this->view->texteRamassage .= " et ";
			}
			$this->view->texteRamassage .= $nbLingot." lingot".$s;
		}
		unset($elementMineraiTable);
		unset($labanMineraiTable);
	}
	
	private function prepareTypePartiesPlantes() {
		Zend_Loader::loadClass("ElementPartieplante");
		$tabPartiePlantesBrutes = null;
		$tabPartiePlantesPreparees = null;
		$tabLingots = null;
		
		$elementPartiePlanteTable = new ElementPartieplante();
		$partiesPlantes = $elementPartiePlanteTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($elementPartiePlanteTable);
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$this->view->nbPartiesPlantesBrutesPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
		$this->view->nbPartiesPlantesPrepareesPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE);
		$this->view->poidsRestant = $poidsRestant;
		
		if ($this->view->nbPartiesPlantesBrutesPossible < 1 && $this->view->nbPartiesPlantesPrepareesPossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
		
		if (count($partiesPlantes) > 0) {
			$this->view->ramasserOk = true;

			foreach ($partiesPlantes as $m) {
				if ($m["quantite_element_partieplante"] > 0) {
					$tabPartiePlantesBrutes[$m["id_fk_type_element_partieplante"]."-".$m["id_fk_type_plante_element_partieplante"]] = array(
						"id_type_partieplante" => $m["id_fk_type_element_partieplante"],
						"id_type_plante" => $m["id_fk_type_plante_element_partieplante"],
						"type" => $m["nom_type_partieplante"],
						"type_plante" => $m["nom_type_plante"],
						"quantite" => $m["quantite_element_partieplante"],
					);
				}
				
				if ($m["quantite_preparee_element_partieplante"] > 0) {
					$tabPartiePlantesPreparees[$m["id_fk_type_element_partieplante"]."-".$m["id_fk_type_plante_element_partieplante"]] = array(
						"id_type_partieplante" => $m["id_fk_type_element_partieplante"],
						"id_type_plante" => $m["id_fk_type_plante_element_partieplante"],
						"type" => $m["nom_type_partieplante"],
						"type_plante" => $m["nom_type_plante"],
						"quantite" => $m["quantite_preparee_element_partieplante"],
					);
				}
			}
		} else {
			$this->view->ramasserOk = false;
		}
		$this->view->partiePlantesBrutes = $tabPartiePlantesBrutes;
		$this->view->partiePlantesPreparees = $tabPartiePlantesPreparees;
	}
	
	private function ramasseTypePartiesPlantes() {
		Zend_Loader::loadClass("LabanPartieplante");
		$this->prepareTypePartiesPlantes();
		
		$idPartiePlanteBrute = null;
		$nbPartiePlanteBrute = null;
		
		$idPartiePlantePreparee = null;
		$nbPartiePlantePreparee = null;
		
		$labanPartiePlanteTable = new LabanPartieplante();
		$elementPartiePlanteTable = new ElementPartieplante();
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbPartiesPlantesBrutesPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
		
		if ($this->request->get("valeur_2") > 0 && $this->request->get("valeur_3") > 0) {
			$idPartiePlanteBrute = $this->request->get("valeur_2");
			$nbPartiePlanteBrute = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
			
			if (!array_key_exists($idPartiePlanteBrute, $this->view->partiePlantesBrutes)) {
				throw new Zend_Exception(get_class($this)." ID PartiePlante Brute invalide : ".$idPartiePlanteBrute);
			} 
			
			$partiePlanteBrute = $this->view->partiePlantesBrutes[$idPartiePlanteBrute];
			
			if ($nbPartiePlanteBrute > $nbPartiesPlantesBrutesPossible) {
				$nbPartiePlanteBrute = $nbPartiesPlantesBrutesPossible;
				$poidsRestant = $poidsRestant - $nbPartiePlanteBrute * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
			}
			
			if ($nbPartiePlanteBrute > $partiePlanteBrute["quantite"] || $nbPartiePlanteBrute < 0) {
				throw new Zend_Exception(get_class($this)." Quantite PartiePlante Brute invalide : ".$nbPartiePlanteBrute);
			}
			
			$data = array(
				"quantite_laban_partieplante" => $nbPartiePlanteBrute,
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
				"quantite_element_partieplante" => -$nbPartiePlanteBrute,
			);
			$elementPartiePlanteTable->insertOrUpdate($data);
			if ($nbPartiePlanteBrute > 1) {
				$s = "s";
			} else {
				$s = "";
			}
			$this->view->texteRamassage = $nbPartiePlanteBrute." &eacute;l&eacute;ment".$s." de plante".$s." brute".$s;
		}
		
		$nbPartiesPlantesPrepareesPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE);
		
		if ($this->request->get("valeur_4") > 0 && $this->request->get("valeur_5") > 0) {
			$idPartiePlantePreparee = $this->request->get("valeur_4");
			$nbPartiePlantePreparee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
			
			if (!array_key_exists($idPartiePlantePreparee, $this->view->partiePlantesPreparees)) {
				throw new Zend_Exception(get_class($this)." ID PartiePlantePreparee invalide : ".$idPartiePlantePreparee);
			} 
			
			$partiePlantePreparee = $this->view->partiePlantesPreparees[$idPartiePlantePreparee];
			
			if ($nbPartiePlantePreparee > $nbPartiesPlantesPrepareesPossible) {
				$nbPartiePlantePreparee = $nbPartiesPlantesPrepareesPossible;
				$poidsRestant = $poidsRestant - $nbPartiePlantePreparee * Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;
			}
			
			if ($nbPartiePlantePreparee > $partiePlantePreparee["quantite"] || $nbPartiePlantePreparee < 0) {
				throw new Zend_Exception(get_class($this)." Quantite Plante Preparee invalide : ".$nbPartiePlantePreparee);
			}
			
			$data = array(
				"quantite_preparee_laban_partieplante" => $nbPartiePlantePreparee,
				"id_fk_type_laban_partieplante" => $partiePlantePreparee["id_type_partieplante"],
				"id_fk_type_plante_laban_partieplante" => $partiePlantePreparee["id_type_plante"],
				"id_fk_hobbit_laban_partieplante" => $this->view->user->id_hobbit,
			);
			$labanPartiePlanteTable->insertOrUpdate($data);
			
			$data = array (
				"x_element_partieplante" => $this->view->user->x_hobbit,
				"y_element_partieplante" => $this->view->user->y_hobbit,
				"id_fk_type_element_partieplante" => $partiePlantePreparee["id_type_partieplante"],
				"id_fk_type_plante_element_partieplante" => $partiePlantePreparee["id_type_plante"],
				"quantite_preparee_element_partieplante" => -$nbPartiePlantePreparee,
			);
			$elementPartiePlanteTable->insertOrUpdate($data);
			
			if ($nbPartiePlantePreparee > 1) {
				$s = "s";
			} else {
				$s = "";
			}
			if ($this->view->texteRamassage != "" ) {
				$this->view->texteRamassage .= " et ";
			}
			$this->view->texteRamassage .= $nbPartiePlantePreparee." &eacute;l&eacute;ment".$s. " de plante".$s." pr&eacute;par&eacute;e".$s;
		}
		unset($elementPartiePlanteTable);
		unset($labanPartiePlanteTable);
	}
	
	private function prepareTypeAutres() {
		Zend_Loader::loadClass("Element");
		
		$tabAutres = null;
		$elementTable = new Element();
		$elements = $elementTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($elementTable);
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbPeauPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PEAU);
		$nbViandePossible = floor($poidsRestant / Bral_Util_Poids::POIDS_VIANDE);
		$nbViandePrepareePossible = floor($poidsRestant / Bral_Util_Poids::POIDS_VIANDE_PREPAREE);
		$nbRationPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_RATION);
		$nbCuirPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CUIR);
		$nbFourrurePossible = floor($poidsRestant / Bral_Util_Poids::POIDS_FOURRURE);
		$nbPlanchePossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PLANCHE);
		
		if ($nbPeauPossible > 0 || $nbViandePossible > 0 || $nbViandePrepareePossible > 0 || 
			$nbRationPossible > 0 || $nbCuirPossible > 0 || $nbFourrurePossible > 0 || $nbPlanchePossible > 0) {
			$this->view->poidsPlaceDisponible = true;
		} else {
			$this->view->poidsPlaceDisponible = false;
		}
		
		$this->view->ramasserOk = false;
		 
		if (count($elements) == 1) {
			foreach ($elements as $e) {
				if ($e["quantite_peau_element"] > 0) $tabAutres[1] = array("nom" => "Peau", "pluriel" => "Peaux", "nom_systeme" => "quantite_peau" , "nb" => $e["quantite_peau_element"], "nbPossible" => $nbPeauPossible);
				if ($e["quantite_viande_element"] > 0) $tabAutres[2] = array("nom" => "Viande", "pluriel" => "Viandes", "nom_systeme" => "quantite_viande" , "nb" => $e["quantite_viande_element"], "nbPossible" => $nbViandePossible);
				if ($e["quantite_viande_preparee_element"] > 0) $tabAutres[3] = array("nom" => "Viande pr&eacute;par&eacute;e", "pluriel" => "Viandes Préparées", "nom_systeme" => "quantite_viande_preparee" , "nb" => $e["quantite_viande_preparee_element"], "nbPossible" => $nbViandePrepareePossible);
				if ($e["quantite_ration_element"] > 0) $tabAutres[4] = array("nom" => "Ration", "nom_systeme" => "quantite_ration", "pluriel" => "Rations", "nb" => $e["quantite_ration_element"], "nbPossible" => $nbRationPossible);
				if ($e["quantite_cuir_element"] > 0) $tabAutres[5] = array("nom" => "Cuir", "nom_systeme" => "quantite_cuir" , "pluriel" => "Cuirs", "nb" => $e["quantite_cuir_element"], "nbPossible" => $nbCuirPossible);
				if ($e["quantite_fourrure_element"] > 0) $tabAutres[6] = array("nom" => "Fourrure", "nom_systeme" => "quantite_fourrure" , "pluriel" => "Fourrures", "nb" => $e["quantite_fourrure_element"], "nbPossible" => $nbFourrurePossible);
				if ($e["quantite_planche_element"] > 0) $tabAutres[7] = array("nom" => "Planche", "nom_systeme" => "quantite_planche" , "pluriel" => "Planches", "nb" => $e["quantite_planche_element"], "nbPossible" => $nbPlanchePossible);
				
				if (count($tabAutres) > 0) {
					$this->view->ramasserOk = true;
				}
			}
		} else {
			$this->view->ramasserOk = false;
		}
		$this->view->autres = $tabAutres;
	}
	
	private function ramasseTypeAutres() {
		Zend_Loader::loadClass("Laban");
		$this->prepareTypeAutres();
		
		$idAutre = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$nb = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		
		if (!array_key_exists($idAutre, $this->view->autres)) {
			throw new Zend_Exception(get_class($this)." ID Autres invalide : ".$idAutre);
		} 
		
		$autre = $this->view->autres[$idAutre];
		
		if ($nb > $autre["nbPossible"]) {
			throw new Zend_Exception(get_class($this)." NB invalide : ".$nb. " nbPossible:".$autre["nbPossible"]);
		}
		
		if ($this->view->poidsPlaceDisponible == false) {
			throw new Zend_Exception(get_class($this)." Place non valide");
		}
		
		if ($nb > $autre["nb"]) {
			$nb = $autre["nb"];
		}
		
		if ($nb < 0) {
			throw new Zend_Exception(get_class($this)." Quantite invalide : ".$nb);
		}
		
		$labanTable = new Laban();
		$data = array(
			$autre["nom_systeme"]."_laban" => $nb,
			"id_fk_hobbit_laban" => $this->view->user->id_hobbit,
		);
		$labanTable->insertOrUpdate($data);
		unset($labanTable);
		
		$elementTable = new Element();
		$data = array(
			$autre["nom_systeme"]."_element" => -$nb,
			"x_element" => $this->view->user->x_hobbit,
			"y_element" => $this->view->user->y_hobbit,
		);
		$elementTable->insertOrUpdate($data);
		unset($elementTable);
		
		$this->view->texteRamassage = $nb. " ";
		if ($nb > 1) {
			$this->view->texteRamassage .= $this->view->autres[$idAutre]["pluriel"];
		} else {
			$this->view->texteRamassage .= $this->view->autres[$idAutre]["nom"];
		}
	}
}
