<?php
class Bral_Competences_Transbahuter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Echoppe");
		$config = Zend_Registry::get("config");

		$choixDepart = false;
		//liste des endroits
		//On peut essayer de transbahuter pour le sol et le laban
		$tabEndroit[1] = array("id_type_endroit" => 1,"nom_systeme" => "Element", "nom_type_endroit" => "Le sol", "est_depart" => true, "poids_restant" => -1, "panneau" => true);
		$poidsRestantLaban = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		$tabEndroit[2] = array("id_type_endroit" => 2,"nom_systeme" => "Laban", "nom_type_endroit" => "Votre laban", "est_depart" => true, "poids_restant" => $poidsRestantLaban, "panneau" => true);

		//Si on est sur une banque :
		$lieu = new Lieu();
		$banque = $lieu->findByTypeAndCase($config->game->lieu->type->banque,$this->view->user->x_hobbit,$this->view->user->y_hobbit);
		if (count($banque) > 0) {
			$tabEndroit[3] = array("id_type_endroit" => 3,"nom_systeme" => "Coffre", "nom_type_endroit" => "Votre coffre", "est_depart" => true, "poids_restant" => -1, "panneau" => true);
			$tabEndroit[4] = array("id_type_endroit" => 4,"nom_systeme" => "Coffre", "nom_type_endroit" => "Le coffre d'un autre Hobbit", "est_depart" => false, "poids_restant" => -1, "panneau" => true);
		}
		
		//Si on est sur une echoppe
		$echoppe = new Echoppe();
		$echoppeCase = $echoppe->findByCase($this->view->user->x_hobbit,$this->view->user->y_hobbit);
		if (count($echoppeCase) > 0) {
			if ($echoppeCase[0]["id_hobbit"] == $this->view->user->id_hobbit) {
				$tabEndroit[5] = array("id_type_endroit" => 5,"nom_systeme" => "Echoppe", "nom_type_endroit" => "Votre échoppe", "est_depart" => true, "poids_restant" => -1, "panneau" => true);
				//$tabEndroit[6] = array("id_type_endroit" => 6,"nom_systeme" => "Echoppe", "nom_type_endroit" => "La caisse de votre échoppe", "est_depart" => true, "poids_restant" => -1, "panneau" => true);
				$this->view->id_echoppe_depart = $echoppeCase[0]["id_echoppe"];
			}
			else {
				$tabEndroit[5] = array("id_type_endroit" => 5,"nom_systeme" => "Echoppe", "nom_type_endroit" => "L'échoppe de ".$echoppeCase[0]["prenom_hobbit"]." ".$echoppeCase[0]["nom_hobbit"], "est_depart" => false, "poids_restant" => -1, "panneau" => true);
			}
			$this->view->id_echoppe_arrivee = $echoppeCase[0]["id_echoppe"];
		}
		
		//Cas des charrettes
		$nbendroit=7;
		$charrette = new Charrette();
		$tabCharrette = $charrette->findByPositionAvecHobbit($this->view->user->x_hobbit,$this->view->user->y_hobbit);
		if ( count($tabCharrette) > 0) {
			foreach ($tabCharrette as $c) {
				Zend_Loader::loadClass("Bral_Util_Charrette");
				$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($c["id_hobbit"]);
				if ( $c["id_hobbit"] == $this->view->user->id_hobbit) {
					$panneau = Bral_Util_Charrette::possedePanneauAmovible($c["id_charrette"]);
					$tabEndroit[$nbendroit] = array("id_type_endroit" => $nbendroit,"nom_systeme" => "Charrette", "id_charrette" => $c["id_charrette"], "id_hobbit_charrette" => $c["id_fk_hobbit_charrette"], "panneau" => $panneau, "nom_type_endroit" => "Votre charrette", "est_depart" => true, "poids_restant" => $tabPoidsCharrette["place_restante"]);
					$this->view->id_charrette_depart = $c["id_charrette"];
				}
				else {
					$tabEndroit[$nbendroit] = array("id_type_endroit" => $nbendroit,"nom_systeme" => "Charrette", "id_charrette" => $c["id_charrette"], "id_hobbit_charrette" => $c["id_fk_hobbit_charrette"], "nom_type_endroit" => "La charrette de ".$c["prenom_hobbit"]." ".$c["nom_hobbit"]." (n°".$c["id_hobbit"].")", "est_depart" => false, "poids_restant" => $tabPoidsCharrette["place_restante"]);
				}
				$nbendroit++;
			}
		}
		
		// On récupère la valeur du départ
		if ($this->request->get("valeur_1") != "" && $this->request->get("valeur_1") != -1) {
			$id_type_courant_depart = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
			$choixDepart = true;
			if ($id_type_courant_depart < 1 && $id_type_courant_depart > count($tabEndroit)) {
				throw new Zend_Exception("Bral_Competences_Transbahuter Valeur invalide : id_type_courant_depart=".$id_type_courant_depart);
			}
		} else {
			$id_type_courant_depart = -1;
		}

		//Construction du tableau des départs
		$tabTypeDepart = null;
		$i=1;
		foreach ($tabEndroit as $e){
			//On ne prend que ce qui peut être dans les départs
			if ($e["est_depart"] == true){
				$this->view->deposerOk = false;
				$this->prepareType($e["nom_systeme"]);
				if ($this->view->deposerOk == true) {
					$tabTypeDepart[$i] = array("id_type_depart" => $e["id_type_endroit"], "selected" => $id_type_courant_depart, "nom_systeme" => $e["nom_systeme"], "nom_type_depart" => $e["nom_type_endroit"], "panneau" => $e["panneau"]);
					$i++;
				}
			}
		}
		if ( count($tabTypeDepart) == 1) {
			$id_type_courant_depart = $tabTypeDepart[1]["id_type_depart"];
			$choixDepart = true;
		}
		
		$this->view->typeDepart = $tabTypeDepart;

		//Si on a choisi le départ, on peut choisir l'arrivée
		if ($choixDepart === true) {
			$tabTypeArrivee = null;
			//Si l'arrivée est déjà choisie on récupère la valeur
			if ($this->request->get("valeur_2") != "") {
				$id_type_courant_arrivee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
				$choixArrivee = true;
				if ($id_type_courant_arrivee < 1 && $id_type_courant_arrivee > count($tabEndroit) && $id_type_courant_arrivee == $id_type_courant_depart) {
					throw new Zend_Exception("Bral_Competences_Transbahuter Valeur invalide : id_type_courant_arrivee=".$id_type_courant_arrivee);
				}
			} else {
				$id_type_courant_arrivee = -1;
			}
			
			$i=1;
			foreach ($tabEndroit as $e) {
				if ($e["id_type_endroit"] != $id_type_courant_depart ) {
					if ($e["poids_restant"] == -1 || $e["poids_restant"] > 0 ) {
						$tabTypeArrivee[$i] = array("id_type_arrivee" => $e["id_type_endroit"], "selected" => $id_type_courant_arrivee, "nom_systeme" => $e["nom_systeme"], "nom_type_arrivee" => $e["nom_type_endroit"], "poids_restant" => $e["poids_restant"]);
						if ($e["nom_systeme"] == "Charrette") {
							$tabTypeArrivee[$i]["id_charrette"] = $e["id_charrette"];
						}
						$i++;
					}
				}
			}
			$this->view->typeArrivee = $tabTypeArrivee;
			$this->view->nb_valeurs = 16;
			$this->prepareType($tabEndroit[$id_type_courant_depart]["nom_systeme"]);
		}
		$this->view->choixDepart = $choixDepart;
		$this->view->tabEndroit = $tabEndroit;

		//@TODO afficher ce qui est transbahuté et de koi vers koi (reprendre aux minerais)
		//@TODO gerer envoi message
		//@TODO bouton transfert equipement, potion et materiel dans echoppe

	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {

		$idDepart = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
		$idArrivee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$endroitDepart = false;
		$endroitArrivee = false;
		foreach ($this->view->tabEndroit as $e) {
			if ($e["id_type_endroit"] == $idDepart) {
				$endroitDepart = true;
				$this->view->a_panneau = $e["panneau"];
			}
			if ($e["id_type_endroit"] == $idArrivee && $idArrivee < 7) {
				$endroitArrivee = true;
				$this->view->poidsRestant = $e["poids_restant"];
			}
			if ($idArrivee >= 7 ) {
				if ($e["nom_systeme"] == "Charrette") {
					$id_charrette = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
					if ( $id_charrette == $e["id_charrette"]) {
						$endroitArrivee = true;
						$this->view->id_charrette_arrivee = $id_charrette;
						$this->view->poidsRestant = $e["poids_restant"];
					}
				}	
			}
		}
		if ($endroitDepart === false) {
			throw new Zend_Exception(get_class($this)." Endroit depart invalide = ".$idDepart.")");
		}
		if ($endroitArrivee === false) {
			throw new Zend_Exception(get_class($this)." Endroit arrivee invalide = ".$idArrivee.")");
		}

		$idCoffre = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		if ($idCoffre == -1) {
			$this->view->id_hobbit_coffre = $this->view->user->id_hobbit;
		}
		else{
			$this->view->id_hobbit_coffre = $idCoffre;
		}
		
		$this->view->poidsOk = true;
		$this->view->nbelement = 0;
		$this->view->panneau = true;
		$this->view->elementsRetires = "";
		$this->deposeType($this->view->tabEndroit[$idDepart]["nom_systeme"], $this->view->tabEndroit[$idArrivee]["nom_systeme"]);
		
		if ($this->view->elementsRetires != "") {
			$this->view->elementsRetires = mb_substr($this->view->elementsRetires, 0, -2);
		}
		
		if ($this->view->tabEndroit[$idDepart]["nom_systeme"] == "Charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit,true);
		}
		
		if ($this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->tabEndroit[$idArrivee]["id_hobbit_charrette"],true);
		}
		
		$this->detailEvenement = "";
		if ($this->view->tabEndroit[$idDepart]["nom_systeme"] == "Element"){
			$idEvenement = $this->view->config->game->evenements->type->ramasser;
			$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a ramassé des élèments à terre ";
		}
		if ($this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Element"){
			$idEvenement = $this->view->config->game->evenements->type->deposer;
			$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a déposé des élèments à terre ";
		}
		if ($this->view->tabEndroit[$idDepart]["nom_systeme"] == "Coffre" || $this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Coffre" ){
			$idEvenement = $this->view->config->game->evenements->type->service;
			$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a utilisé les services de la banque ";
		}
		if ($this->detailEvenement == ""){
			$idEvenement = $this->view->config->game->evenements->type->transbahuter;
			$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a transbahuté des élèments ";
		}
		$this->setDetailsEvenement($this->detailEvenement, $idEvenement);

		$this->setEvenementQueSurOkJet1(false);

		Zend_Loader::loadClass("Bral_Util_Quete");
		$this->view->estQueteEvenement = Bral_Util_Quete::etapePosseder($this->view->user);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		$config = Zend_Registry::get("config");
		$lieu = new Lieu();
		$banque = $lieu->findByTypeAndCase($config->game->lieu->type->banque,$this->view->user->x_hobbit,$this->view->user->y_hobbit);
		$echoppe = new Echoppe();
		$echoppeCase = $echoppe->findByCase($this->view->user->x_hobbit,$this->view->user->y_hobbit);
		if (count($banque) > 0) {
			$tab = array("box_vue", "box_laban", "box_coffre", "box_charrette", "box_banque");
		}
		elseif (count($echoppeCase) > 0) {
			$tab = array("box_vue", "box_laban", "box_charrette", "box_echoppes");
		}
		else {
			$tab = array("box_vue", "box_laban", "box_charrette");
		}
		return $this->constructListBoxRefresh($tab);
	}
	
	private function controlePoids($poidsAutorise, $quantite, $poidsElt){
		if ($poidsAutorise < $quantite * $poidsElt) {
			return false;
		}
		else {
			return true;
		}
	}
	
	private function prepareType($depart){
		$this->prepareTypeAutres($depart);
		$this->prepareTypeEquipements($depart);
		$this->prepareTypeRunes($depart);
		$this->prepareTypePotions($depart);
		$this->prepareTypeAliments($depart);
		$this->prepareTypeMunitions($depart);
		$this->prepareTypePartiesPlantes($depart);
		$this->prepareTypeMinerais($depart);
		$this->prepareTypeTabac($depart);
		$this->prepareTypeMateriel($depart);
	}

	private function deposeType($depart,$arrivee){
		$this->deposeTypeAutres($depart,$arrivee);
		$this->deposeTypeEquipements($depart,$arrivee);
		$this->deposeTypeRunes($depart,$arrivee);
		$this->deposeTypePotions($depart,$arrivee);
		$this->deposeTypeAliments($depart,$arrivee);
		$this->deposeTypeMunitions($depart,$arrivee);
		$this->deposeTypePartiesPlantes($depart,$arrivee);
		$this->deposeTypeMinerais($depart,$arrivee);
		$this->deposeTypeTabac($depart,$arrivee);
		$this->deposeTypeMateriel($depart,$arrivee);
	}

	private function prepareTypeEquipements($depart) {
		Zend_Loader::loadClass($depart."Equipement");
		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEquipements = null;
		
		switch ($depart) {
			case "Laban" :
				$labanEquipementTable = new LabanEquipement();
				$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($labanEquipementTable);
				break;
			case "Element" :
				$elementEquipementTable = new ElementEquipement();
				$equipements = $elementEquipementTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				unset($elementEquipementTable);
				break;
			case "Coffre" :
				$coffreEquipementTable = new CoffreEquipement();
				$equipements = $coffreEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($coffreEquipementTable);
				break;
			case "Charrette" :
				$charretteEquipementTable = new CharretteEquipement();
				$equipements = $charretteEquipementTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteEquipementTable);
				break;
			case "Echoppe" :
				$echoppeEquipementTable = new EchoppeEquipement();
				$equipements = $echoppeEquipementTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeEquipementTable);
				break;
		}

		if (count($equipements) > 0) {
			foreach ($equipements as $e) {
				$tabEquipements[$e["id_".strtolower($depart)."_equipement"]] = array(
						"id_equipement" => $e["id_".strtolower($depart)."_equipement"],
						"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_recette_equipement"],
						"nb_runes" => $e["nb_runes_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"poids" => $e["poids_recette_equipement"],
						"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"], 
						"id_fk_recette" => $e["id_fk_recette_equipement"] ,
						"id_fk_region" => $e["id_fk_region_equipement"],
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->equipements = $tabEquipements;
	}

	private function deposeTypeEquipements($depart,$arrivee) {
		if ($arrivee != "Echoppe") {
			Zend_Loader::loadClass($depart."Equipement");
			Zend_Loader::loadClass($arrivee."Equipement");
	
			$equipements = array();
			$equipements = $this->request->get("valeur_12");
	
			if (count($equipements) > 0 && $equipements != 0) {
				$this->view->nbelement = $this->view->nbelement + 1;
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
				}
				else {
					foreach ($equipements as $idEquipement) {
						if (!array_key_exists($idEquipement, $this->view->equipements)) {
							throw new Zend_Exception(get_class($this)." ID Equipement invalide : ".$idEquipement);
						}
		
						$equipement = $this->view->equipements[$idEquipement];
						
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant,1,$equipement["poids"]);
							if ($poidsOk == false){
								$this->view->poidsOk = false;
								break;
							}
						}
		
						$where = "id_".strtolower($depart)."_equipement=".$idEquipement;
						switch ($depart){
							case "Laban" :
								$departEquipementTable = new LabanEquipement();
								break;
							case "Element" :
								$departEquipementTable = new ElementEquipement();
								break;
							case "Coffre" :
								$departEquipementTable = new CoffreEquipement();
								break;
							case "Charrette" :
								$departEquipementTable = new CharretteEquipement();
								break;
							case "Echoppe" :
								$departEquipementTable = new EchoppeEquipement();
								break;
						}
		
						$departEquipementTable->delete($where);
						unset($departEquipementTable);
		
						switch ($arrivee){
							case "Laban" :
								$arriveeEquipementTable = new LabanEquipement();
								$data = array (
									"id_laban_equipement" => $equipement["id_equipement"],
									"id_fk_hobbit_laban_equipement" => $this->view->user->id_hobbit,
								);
								$this->view->poidsRestant = $this->view->poidsRestant - $equipement["poids"];
								break;
							case "Element" :
								$dateCreation = date("Y-m-d H:i:s");
								$nbJours = Bral_Util_De::get_2d10();
								$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		
								$arriveeEquipementTable = new ElementEquipement();
								$data = array (
									"id_element_equipement" => $equipement["id_equipement"],
									"x_element_equipement" => $this->view->user->x_hobbit,
									"y_element_equipement" => $this->view->user->y_hobbit,
									"date_fin_element_equipement" => $dateFin,
								);
								break;
							case "Coffre" :
								$arriveeEquipementTable = new CoffreEquipement();
								$data = array (
									"id_coffre_equipement" => $equipement["id_equipement"],
									"id_fk_hobbit_coffre_equipement" => $this->view->id_hobbit_coffre,
								);
								break;
							case "Charrette" :
								$arriveeEquipementTable = new CharretteEquipement();
								$data = array (
									"id_charrette_equipement" => $equipement["id_equipement"],
									"id_fk_charrette_equipement" => $this->view->id_charrette_arrivee,
								);
								break;
							/*case "Echoppe" :
								$arriveeEquipementTable = new EchoppeEquipement();
								$data = array (
									"id_echoppe_equipement" => $equipement["id_equipement"],
									"id_fk_echoppe_echoppe_equipement" => $this->view->id_echoppe_arrivee,
								);
								break;*/
						}
						$arriveeEquipementTable->insert($data);
						unset($arriveeEquipementTable);
						$this->view->elementsRetires .= "Equipement n°".$equipement["id_equipement"]." : ".$equipement["nom"].", ";
					}
				}
			}
		}
	}

	private function prepareTypeRunes($depart) {
		if ($depart != "Echoppe") {
			Zend_Loader::loadClass($depart."Rune");
			$tabRunes = null;
	
			switch ($depart) {
				case "Laban" :
					$labanRuneTable = new LabanRune();
					$runes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($labanRuneTable);
					break;
				case "Element" :
					$elementRuneTable = new ElementRune();
					$runes = $elementRuneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
					unset($elementruneTable);
					break;
				case "Coffre" :
					$coffreRuneTable = new CoffreRune();
					$runes = $coffreRuneTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($coffreRuneTable);
					break;
				case "Charrette" :
					$charretteRuneTable = new CharretteRune();
					$runes = $charretteRuneTable->findByIdCharrette($this->view->id_charrette_depart);
					unset($charretteRuneTable);
					break;
			}
	
			if (count($runes) > 0) {
				foreach ($runes as $r) {
					$tabRunes[$r["id_rune_".strtolower($depart)."_rune"]] = array(
						"id_rune" => $r["id_rune_".strtolower($depart)."_rune"],
						"type" => $r["nom_type_rune"],
						"image" => $r["image_type_rune"],
						"est_identifiee" => $r["est_identifiee_".strtolower($depart)."_rune"],
						"effet_type_rune" => $r["effet_type_rune"],
						"id_fk_type_rune" => $r["id_fk_type_".strtolower($depart)."_rune"],
					);
				}
				$this->view->deposerOk = true;
			}
			$this->view->runes = $tabRunes;
		}
		else {
			$this->view->runes = null;
		}
	}

	private function deposeTypeRunes($depart,$arrivee) {
		if ($depart != "Echoppe" && $arrivee != "Echoppe") {
			Zend_Loader::loadClass($depart."Rune");
			Zend_Loader::loadClass($arrivee."Rune");
	
			$runes = array();
			$runes = $this->request->get("valeur_14");
			if (count($runes) > 0 && $runes !=0 ) {
				$this->view->nbelement = $this->view->nbelement + 1;
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
				}
				else {
					foreach ($runes as $idRune) {
						if (!array_key_exists($idRune, $this->view->runes)) {
							throw new Zend_Exception(get_class($this)." ID Rune invalide : ".$idRune);
						}
						
						$rune = $this->view->runes[$idRune];
						
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_RUNE);
							if ($poidsOk == false){
								$this->view->poidsOk = false;
								break;
							}
						}
		
						$where = "id_rune_".strtolower($depart)."_rune=".$idRune;
		
						switch ($depart){
							case "Laban" :
								$departRuneTable = new LabanRune();
								break;
							case "Element" :
								$departRuneTable = new ElementRune();
								break;
							case "Coffre" :
								$departRuneTable = new CoffreRune();
								break;
							case "Charrette" :
								$departRuneTable = new CharretteRune();
								break;
						}
		
						$departRuneTable->delete($where);
						unset($departRuneTable);
		
						switch ($arrivee){
							case "Laban" :
								$arriveeRuneTable = new LabanRune();
								$data = array (
									"id_rune_laban_rune" => $rune["id_rune"],
									"id_fk_type_laban_rune" => $rune["id_fk_type_rune"],
									"est_identifiee_laban_rune" => $rune["est_identifiee"],
									"id_fk_hobbit_laban_rune" => $this->view->user->id_hobbit,
								);
								$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_RUNE;
								break;
							case "Element" :
								$arriveeRuneTable = new ElementRune();
								$data = array (
									"id_rune_element_rune" => $rune["id_rune"],
									"x_element_rune" => $this->view->user->x_hobbit,
									"y_element_rune" => $this->view->user->y_hobbit,
									"id_fk_type_element_rune" => $rune["id_fk_type_rune"],
									"est_identifiee_element_rune" => $rune["est_identifiee"],
								);
								break;
							case "Coffre" :
								$arriveeRuneTable = new CoffreRune();
								$data = array (
								"id_rune_coffre_rune" => $rune["id_rune"],
								"id_fk_type_coffre_rune" => $rune["id_fk_type_rune"],
								"est_identifiee_coffre_rune" => $rune["est_identifiee"],
								"id_fk_hobbit_coffre_rune" => $this->view->id_hobbit_coffre,
								);
								break;
							case "Charrette" :
								$arriveeRuneTable = new CharretteRune();
								$data = array (
								"id_rune_charrette_rune" => $rune["id_rune"],
								"id_fk_type_charrette_rune" => $rune["id_fk_type_rune"],
								"est_identifiee_charrette_rune" => $rune["est_identifiee"],
								"id_fk_charrette_rune" => $this->view->id_charrette_arrivee,
								);
								break;
						}
						$arriveeRuneTable->insert($data);
						unset($arriveeRuneTable);
						$nomRune = "non identifiée";
						if ($rune["est_identifiee"] == "oui"){
							$nomRune = $rune["type"];
						}
						$this->view->elementsRetires .= "Rune n°".$rune["id_rune"]." : ".$nomRune.", ";
					}
				}
			}
		}
	}

	private function prepareTypePotions($depart) {
		Zend_Loader::loadClass($depart."Potion");
		$tabPotions = null;

		switch ($depart) {
			case "Laban" :
				$labanPotionTable = new LabanPotion();
				$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($labanPotionTable);
				break;
			case "Element" :
				$elementPotionTable = new ElementPotion();
				$potions = $elementPotionTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				unset($elementPotionTable);
				break;
			case "Coffre" :
				$coffrePotionTable = new CoffrePotion();
				$potions = $coffrePotionTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($coffrePotionTable);
				break;
			case "Charrette" :
				$charrettePotionTable = new CharrettePotion();
				$potions = $charrettePotionTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charrettePotionTable);
				break;
			case "Echoppe" :
				$echoppePotionTable = new EchoppePotion();
				$potions = $echoppePotionTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppePotionTable);
				break;
		}

		if (count($potions) > 0) {
			foreach ($potions as $p) {
				$tabPotions[$p["id_".strtolower($depart)."_potion"]] = array(
					"id_potion" => $p["id_".strtolower($depart)."_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_".strtolower($depart)."_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"id_fk_type_qualite" => $p["id_fk_type_qualite_".strtolower($depart)."_potion"],
					"id_fk_type" => $p["id_fk_type_".strtolower($depart)."_potion"]
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->potions = $tabPotions;
	}

	private function deposeTypePotions($depart,$arrivee) {
		if ($arrivee != "Echoppe") {
			Zend_Loader::loadClass($depart."Potion");
			Zend_Loader::loadClass($arrivee."Potion");
			$potions = array();
			$potions = $this->request->get("valeur_15");
			if (count($potions) > 0 && $potions != 0) {
				$this->view->nbelement = $this->view->nbelement + 1;
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
				}
				else {
					foreach ($potions as $idPotion) {
						if (!array_key_exists($idPotion, $this->view->potions)) {
							throw new Zend_Exception(get_class($this)." ID Potion invalide : ".$idPotion);
						}
		
						$potion = $this->view->potions[$idPotion];
						
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_POTION);
							if ($poidsOk == false){
								$this->view->poidsOk = false;
								break;
							}
						}
		
						$where = "id_".strtolower($depart)."_potion=".$idPotion;
						switch ($depart){
							case "Laban" :
								$departPotionTable = new LabanPotion();
								break;
							case "Element" :
								$departPotionTable = new ElementPotion();
								break;
							case "Coffre" :
								$departPotionTable = new CoffrePotion();
								break;
							case "Charrette" :
								$departPotionTable = new CharrettePotion();
								break;
							case "Echoppe" :
								$departPotionTable = new EchoppePotion();
								break;
						}
		
						$departPotionTable->delete($where);
						unset($departPotionTable);
		
						switch($arrivee){
							case "Laban" :
								$arriveePotionTable = new LabanPotion();
								$data = array (
									"id_laban_potion" => $potion["id_potion"],
									"id_fk_hobbit_laban_potion" => $this->view->user->id_hobbit,
									"niveau_laban_potion" => $potion["niveau"],
									"id_fk_type_qualite_laban_potion" => $potion["id_fk_type_qualite"],
									"id_fk_type_laban_potion" => $potion["id_fk_type"],
								);
								$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_POTION;
								break;
							case "Element" :
								$dateCreation = date("Y-m-d H:i:s");
								$nbJours = Bral_Util_De::get_2d10();
								$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		
								$arriveePotionTable = new ElementPotion();
								$data = array (
									"id_element_potion" => $potion["id_potion"],
									"x_element_potion" => $this->view->user->x_hobbit,
									"y_element_potion" => $this->view->user->y_hobbit,
									"niveau_element_potion" => $potion["niveau"],
									"id_fk_type_qualite_element_potion" => $potion["id_fk_type_qualite"],
									"id_fk_type_element_potion" => $potion["id_fk_type"],
									"date_fin_element_potion" => $dateFin,
								);
								break;
							case "Coffre" :
								$arriveePotionTable = new CoffrePotion();
								$data = array (
									"id_coffre_potion" => $potion["id_potion"],
									"id_fk_hobbit_coffre_potion" => $this->view->id_hobbit_coffre,
									"niveau_coffre_potion" => $potion["niveau"],
									"id_fk_type_qualite_coffre_potion" => $potion["id_fk_type_qualite"],
									"id_fk_type_coffre_potion" => $potion["id_fk_type"],
								);
								break;
							case "Charrette" :
								$arriveePotionTable = new CharrettePotion();
								$data = array (
									"id_charrette_potion" => $potion["id_potion"],
									"id_fk_charrette_potion" => $this->view->id_charrette_arrivee,
									"niveau_charrette_potion" => $potion["niveau"],
									"id_fk_type_qualite_charrette_potion" => $potion["id_fk_type_qualite"],
									"id_fk_type_charrette_potion" => $potion["id_fk_type"],
								);
								break;
							/*case "Echoppe" :
								$arriveePotionTable = new EchoppePotion();
								$data = array (
									"id_echoppe_potion" => $potion["id_potion"],
									"id_fk_echoppe_echoppe_potion" => $this->view->id_echoppe_arrivee,
									"niveau_echoppe_potion" => $potion["niveau"],
									"id_fk_type_qualite_echoppe_potion" => $potion["id_fk_type_qualite"],
									"id_fk_type_echoppe_potion" => $potion["id_fk_type"],
								);
								break;*/
						}
						$arriveePotionTable->insert($data);
						unset($arriveePotionTable);
						$this->view->elementsRetires .= "Potion n°".$potion["id_potion"]." : ".$potion["nom"].", ";
					}
				}
			}
		}
	}

	private function prepareTypeAliments($depart) {
		if ($depart != "Echoppe") {
			Zend_Loader::loadClass($depart."Aliment");
			$tabAliments = null;
	
			switch ($depart) {
				case "Laban" :
					$labanAlimentTable = new LabanAliment();
					$aliments = $labanAlimentTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($labanAlimentTable);
					break;
				case "Element" :
					$elementAlimentTable = new ElementAliment();
					$aliments = $elementAlimentTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
					unset($elementAlimentTable);
					break;
				case "Coffre" :
					$coffreAlimentTable = new CoffreAliment();
					$aliments = $coffreAlimentTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($coffreAlimentTable);
					break;
				case "Charrette" :
					$charretteAlimentTable = new CharretteAliment();
					$aliments = $charretteAlimentTable->findByIdCharrette($this->view->id_charrette_depart);
					unset($charretteAlimentTable);
					break;
				case "Echoppe" :
				$aliments = null;
				break;
			}

			if (count($aliments) > 0) {
				foreach ($aliments as $p) {
					$tabAliments[$p["id_".strtolower($depart)."_aliment"]] = array(
								"id_aliment" => $p["id_".strtolower($depart)."_aliment"],
								"nom" => $p["nom_type_aliment"],
								"qualite" => $p["nom_type_qualite"],
								"bbdf" => $p["bbdf_".strtolower($depart)."_aliment"],
								"id_fk_type_qualite" => $p["id_fk_type_qualite_".strtolower($depart)."_aliment"],
								"id_fk_type" => $p["id_fk_type_".strtolower($depart)."_aliment"]
					);
				}
				$this->view->deposerOk = true;
			}
			$this->view->aliments = $tabAliments;
		}
		else {
			$this->view->aliments = null;
		}
	}

	private function deposeTypeAliments($depart,$arrivee) {
		if ($depart != "Echoppe" && $arrivee != "Echoppe") {
			Zend_Loader::loadClass($depart."Aliment");
			Zend_Loader::loadClass($arrivee."Aliment");
		
			$aliments = array();
			$aliments = $this->request->get("valeur_13");
			if (count($aliments) > 0 && $aliments !=0 ) {
				$this->view->nbelement = $this->view->nbelement + 1;
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
				}
				else {
					foreach ($aliments as $idAliment) {
						if (!array_key_exists($idAliment, $this->view->aliments)) {
							throw new Zend_Exception(get_class($this)." ID Aliment invalide : ".$idAliment);
						}
		
						$aliment = $this->view->aliments[$idAliment];
						
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_RATION);
							if ($poidsOk == false){
								$this->view->poidsOk = false;
								break;
							}
						}
		
						$where = "id_".strtolower($depart)."_aliment=".$idAliment;
						switch ($depart){
							case "Laban" :
								$departAlimentTable = new LabanAliment();
								break;
							case "Element" :
								$departAlimentTable = new ElementAliment();
								break;
							case "Coffre" :
								$departAlimentTable = new CoffreAliment();
								break;
							case "Charrette" :
								$departAlimentTable = new CharretteAliment();
								break;
						}
						$departAlimentTable->delete($where);
						unset($departAlimentTable);
		
						switch ($arrivee){
							case "Laban" :
								$arriveeAlimentTable = new LabanAliment();
								$data = array (
									"id_laban_aliment" => $aliment["id_aliment"],
									"id_fk_hobbit_laban_aliment" => $this->view->user->id_hobbit,
									"bbdf_laban_aliment" => $aliment["bbdf"],
									"id_fk_type_qualite_laban_aliment" => $aliment["id_fk_type_qualite"],
									"id_fk_type_laban_aliment" => $aliment["id_fk_type"],
								);
								$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_RATION;
								break;
							case "Element" :
								$dateCreation = date("Y-m-d H:i:s");
								$nbJours = Bral_Util_De::get_2d10();
								$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		
								$arriveeAlimentTable = new ElementAliment();
								$data = array (
											"id_element_aliment" => $aliment["id_aliment"],
											"x_element_aliment" => $this->view->user->x_hobbit,
											"y_element_aliment" => $this->view->user->y_hobbit,
											"bbdf_element_aliment" => $aliment["bbdf"],
											"id_fk_type_qualite_element_aliment" => $aliment["id_fk_type_qualite"],
											"id_fk_type_element_aliment" => $aliment["id_fk_type"],
											"date_fin_element_aliment" => $dateFin,
								);
								break;
							case "Coffre" :
								$arriveeAlimentTable = new CoffreAliment();
								$data = array (
									"id_coffre_aliment" => $aliment["id_aliment"],
									"id_fk_hobbit_coffre_aliment" => $this->view->id_hobbit_coffre,
									"bbdf_coffre_aliment" => $aliment["bbdf"],
									"id_fk_type_qualite_coffre_aliment" => $aliment["id_fk_type_qualite"],
									"id_fk_type_coffre_aliment" => $aliment["id_fk_type"],
								);
								break;
							case "Charrette" :
								$arriveeAlimentTable = new CharretteAliment();
								$data = array (
									"id_charrette_aliment" => $aliment["id_aliment"],
									"id_fk_charrette_aliment" => $this->view->id_charrette_arrivee,
									"bbdf_charrette_aliment" => $aliment["bbdf"],
									"id_fk_type_qualite_charrette_aliment" => $aliment["id_fk_type_qualite"],
									"id_fk_type_charrette_aliment" => $aliment["id_fk_type"],
								);
								break;
						}
						$arriveeAlimentTable->insert($data);
						unset($arriveeAlimentTable);
						$this->view->elementsRetires .= "Aliment n°".$aliment["id_aliment"]." : ".$aliment["nom"]." +".$aliment["bbdf"]."%, ";
					}
				}
			}
		}
	}

	private function prepareTypeMunitions($depart) {
		if ($depart != "Echoppe") {
			Zend_Loader::loadClass($depart."Munition");
	
			$tabMunitions = null;
	
			switch ($depart) {
				case "Laban" :
					$labanMunitionTable = new LabanMunition();
					$munitions = $labanMunitionTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($labanMunitionTable);
					break;
				case "Element" :
					$elementMunitionTable = new ElementMunition();
					$munitions = $elementMunitionTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
					unset($elementMunitionTable);
					break;
				case "Coffre" :
					$coffreMunitionTable = new CoffreMunition();
					$munitions = $coffreMunitionTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($coffreMunitionTable);
					break;
				case "Charrette" :
					$charretteMunitionTable = new CharretteMunition();
					$munitions = $charretteMunitionTable->findByIdCharrette($this->view->id_charrette_depart);
					unset($charretteMunitionTable);
					break;
			}
	
			if (count($munitions) > 0) {
				foreach ($munitions as $m) {
					if ($m["quantite_".strtolower($depart)."_munition"] > 0) {
						$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
						$tabMunitions[$this->view->nb_valeurs] = array(
							"id_type_munition" => $m["id_fk_type_".strtolower($depart)."_munition"],
							"type" => $m["nom_type_munition"],
							"quantite" => $m["quantite_".strtolower($depart)."_munition"],
							"indice_valeur" => $this->view->nb_valeurs,
						);
					}
				}
				$this->view->deposerOk = true;
			}
			$this->view->valeur_fin_munitions = $this->view->nb_valeurs;
			$this->view->munitions = $tabMunitions;
		}
		else {
			$this->view->valeur_fin_munitions = $this->view->nb_valeurs;
			$this->view->munitions = null;
		}
	}

	private function deposeTypeMunitions($depart,$arrivee) {
		if ($depart != "Echoppe" && $arrivee != "Echoppe") {
			Zend_Loader::loadClass($depart."Munition");
			Zend_Loader::loadClass($arrivee."Munition");

			if (count($this->view->munitions) > 0) {
				$idMunition = null;
				$nbMunition = null;
				
				for ($i=17; $i<=$this->view->valeur_fin_munitions; $i++) {
						
					if ( $this->request->get("valeur_".$i) > 0) {
						$nbMunition = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_".$i));
							
						$munition = $this->view->munitions[$i];
	
						if ($nbMunition > $munition["quantite"] || $nbMunition < 0) {
							throw new Zend_Exception(get_class($this)." Quantite Munition invalide : ".$nbMunition);
						}
	
						$this->view->nbelement = $this->view->nbelement + 1;
						if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
							$this->view->panneau = false;
							break;
						}
						
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, $nbMunition, Bral_Util_Poids::POIDS_MUNITION);
							if ($poidsOk == false){
								$this->view->poidsOk = false;
								break;
							}
						}
							
						switch ($depart){
							case "Laban" :
								$departMunitionTable = new LabanMunition();
								$data = array(
										"quantite_laban_munition" => -$nbMunition,
										"id_fk_type_laban_munition" => $munition["id_type_munition"],
										"id_fk_hobbit_laban_munition" => $this->view->user->id_hobbit,
								);
								break;
							case "Element" :
								$departMunitionTable = new ElementMunition();
								$data = array (
										"x_element_munition" => $this->view->user->x_hobbit,
										"y_element_munition" => $this->view->user->y_hobbit,
										"id_fk_type_element_munition" => $munition["id_type_munition"],
										"quantite_element_munition" => -$nbMunition,
								);
								break;					
							case "Coffre" :
								$departMunitionTable = new CoffreMunition();
								$data = array(
										"quantite_coffre_munition" => -$nbMunition,
										"id_fk_type_coffre_munition" => $munition["id_type_munition"],
										"id_fk_hobbit_coffre_munition" => $this->view->user->id_hobbit,
								);
								break;
							case "Charrette" :
								$departMunitionTable = new CharretteMunition();
								$data = array(
										"quantite_charrette_munition" => -$nbMunition,
										"id_fk_type_charrette_munition" => $munition["id_type_munition"],
										"id_fk_charrette_munition" => $this->view->id_charrette_depart,
								);
								break;
						}
							
						$departMunitionTable->insertOrUpdate($data);
						unset ($departMunitionTable);
							
						switch ($arrivee){
							case "Laban" :
								$arriveeMunitionTable = new LabanMunition();
								$data = array(
										"quantite_laban_munition" => $nbMunition,
										"id_fk_type_laban_munition" => $munition["id_type_munition"],
										"id_fk_hobbit_laban_munition" => $this->view->user->id_hobbit,
								);
								$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MUNITION * $nbMunition;
								break;
							case "Element" :
								$arriveeMunitionTable = new ElementMunition();
								$data = array (
									"x_element_munition" => $this->view->user->x_hobbit,
									"y_element_munition" => $this->view->user->y_hobbit,
									"id_fk_type_element_munition" => $munition["id_type_munition"],
									"quantite_element_munition" => $nbMunition,
								);
								break;
							case "Coffre" :
								$arriveeMunitionTable = new CoffreMunition();
								$data = array(
										"quantite_coffre_munition" => $nbMunition,
										"id_fk_type_coffre_munition" => $munition["id_type_munition"],
										"id_fk_hobbit_coffre_munition" => $this->view->id_hobbit_coffre,
								);
								break;
							case "Charrette" :
								$arriveeMunitionTable = new CharretteMunition();
								$data = array(
										"quantite_charrette_munition" => $nbMunition,
										"id_fk_type_charrette_munition" => $munition["id_type_munition"],
										"id_fk_charrette_munition" => $this->view->id_charrette_arrivee,
								);
								break;
						}
						$arriveeMunitionTable->insertOrUpdate($data);
						unset($arriveeMunitionTable);
						$this->view->elementsRetires .= $nbMunition." ".$munition["type"].", ";
					}
				}
			}
		}
	}

	private function prepareTypeMinerais($depart) {
		Zend_Loader::loadClass($depart."Minerai");

		$tabMinerais = null;

		switch ($depart) {
			case "Laban" :
				$labanMineraiTable = new labanMinerai();
				$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($labanMineraiTable);
				break;
			case "Element" :
				$elementMineraiTable = new ElementMinerai();
				$minerais = $elementMineraiTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				unset($elementMineraiTable);
				break;
			case "Coffre" :
				$coffreMineraiTable = new CoffreMinerai();
				$minerais = $coffreMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($coffreMineraiTable);
				break;
			case "Charrette" :
				$charretteMineraiTable = new CharretteMinerai();
				$minerais = $charretteMineraiTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteMineraiTable);
				break;
			case "Echoppe" :
				$echoppeMineraiTable = new EchoppeMinerai();
				$minerais = $echoppeMineraiTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeMineraiTable);
				break;
		}

		$this->view->nb_minerai_brut = 0;
		$this->view->nb_minerai_lingot = 0;

		if ($minerais != null) {
			if ($depart == "Echoppe") {
				$strqte = "arriere_echoppe";
			}
			else {
				$strqte = $depart;
			}
			foreach ($minerais as $m) {
				if ($m["quantite_brut_".strtolower($strqte)."_minerai"] > 0 || $m["quantite_lingots_".strtolower($depart)."_minerai"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
					$tabMinerais[$this->view->nb_valeurs] = array(
						"type" => $m["nom_type_minerai"],
						"id_fk_type_minerai" => $m["id_fk_type_".strtolower($depart)."_minerai"],
						"quantite_brut_minerai" => $m["quantite_brut_".strtolower($strqte)."_minerai"],
						"quantite_lingots_minerai" => $m["quantite_lingots_".strtolower($depart)."_minerai"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // lingot
					$this->view->nb_minerai_brut = $this->view->nb_minerai_brut + $m["quantite_brut_".strtolower($strqte)."_minerai"];
					$this->view->nb_minerai_lingot = $this->view->nb_minerai_lingot + $m["quantite_lingots_".strtolower($depart)."_minerai"];
				}
			}
		}
		$this->view->valeur_fin_minerais = $this->view->nb_valeurs;
		$this->view->minerais = $tabMinerais;
	}

	private function deposeTypeMinerais($depart,$arrivee) {
		Zend_Loader::loadClass($depart."Minerai");
		Zend_Loader::loadClass($arrivee."Minerai");

		for ($i=$this->view->valeur_fin_partieplantes + 1; $i<=$this->view->valeur_fin_minerais; $i = $i + 2) {
			$indice = $i;
			$indiceBrut = $i;
			$indiceLingot = $i+1;
			$nbBrut = $this->request->get("valeur_".$indiceBrut);
			$nbLingot = $this->request->get("valeur_".$indiceLingot);
				
			if ((int) $nbBrut."" != $this->request->get("valeur_".$indiceBrut)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai brut invalide=".$nbBrut. " indice=".$indiceBrut);
			} else {
				$nbBrut = (int)$nbBrut;
			}
			if ($nbBrut > $this->view->minerais[$indice]["quantite_brut_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai brut interdit=".$nbBrut);
			}
				
			if ((int) $nbLingot."" != $this->request->get("valeur_".$indiceLingot)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot invalide=".$nbLingot. " indice=".$indiceLingot);
			} else {
				$nbLingot = (int)$nbLingot;
			}
			if ($nbLingot > $this->view->minerais[$indice]["quantite_lingots_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot interdit=".$nbLingot);
			}
				
			if ($nbBrut > 0 || $nbLingot > 0) {
				
				$this->view->nbelement = $this->view->nbelement + 1;
				if ( $nbBrut > 0 && $nbLingot > 0 && $this->view->a_panneau === false) {
					$nbLingot = 0;
					$this->view->panneau = false;
				}
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
					break;
				}
				
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk1 = $this->controlePoids($this->view->poidsRestant, $nbBrut, Bral_Util_Poids::POIDS_MINERAI);
					$poidsOk2 = $this->controlePoids($this->view->poidsRestant, $nbLingot, Bral_Util_Poids::POIDS_LINGOT);
					if ($poidsOk1 == false || $poidsOk2 == false){
						$this->view->poidsOk = false;
						break;
					}
				}

				switch ($depart){
					case "Laban" :
						$departMineraiTable = new LabanMinerai();
						$data = array(
							'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_minerai"],
							'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
							'quantite_brut_laban_minerai' => -$nbBrut,
							'quantite_lingots_laban_minerai' => -$nbLingot,
						);
						break;
					case "Element" :
						$departMineraiTable = new ElementMinerai();
						$data = array (
							"x_element_minerai" => $this->view->user->x_hobbit,
							"y_element_minerai" => $this->view->user->y_hobbit,
							"id_fk_type_element_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"quantite_brut_element_minerai" => -$nbBrut,
							"quantite_lingots_element_minerai" => -$nbLingot,
						);
						break;
					case "Coffre" :
						$departMineraiTable = new CoffreMinerai();
						$data = array (
							"id_fk_hobbit_coffre_minerai" => $this->view->user->id_hobbit,
							"id_fk_type_coffre_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"quantite_brut_coffre_minerai" => -$nbBrut,
							"quantite_lingots_coffre_minerai" => -$nbLingot,
						);
						break;
					case "Charrette" :
						$departMineraiTable = new CharretteMinerai();
						$data = array (
							"id_fk_charrette_minerai" => $this->view->id_charrette_depart,
							"id_fk_type_charrette_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"quantite_brut_charrette_minerai" => -$nbBrut,
							"quantite_lingots_charrette_minerai" => -$nbLingot,
						);
						break;	
					case "Echoppe" :
						$departMineraiTable = new EchoppeMinerai();
						$data = array (
							"id_fk_echoppe_echoppe_minerai" => $this->view->id_echoppe_depart,
							"id_fk_type_echoppe_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"quantite_brut_arriere_echoppe_minerai" => -$nbBrut,
							"quantite_lingots_echoppe_minerai" => -$nbLingot,
						);
						break;	
				}
				$departMineraiTable->insertOrUpdate($data);
				unset ($departMineraiTable);

				switch ($arrivee){
					case "Laban" :
						$arriveeMineraiTable = new LabanMinerai();
						$data = array(
							"quantite_brut_laban_minerai" => $nbBrut,
							"quantite_lingots_laban_minerai" => $nbLingot,
							"id_fk_type_laban_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
						);
						$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MINERAI * $nbBrut - Bral_Util_Poids::POIDS_LINGOT * $nbLingot;
						break;
					case "Element" :
						$arriveeMineraiTable = new ElementMinerai();
						$data = array("x_element_minerai" => $this->view->user->x_hobbit,
							  "y_element_minerai" => $this->view->user->y_hobbit,
							  'quantite_brut_element_minerai' => $nbBrut,
							  'quantite_lingots_element_minerai' => $nbLingot,
							  'id_fk_type_element_minerai' => $this->view->minerais[$indice]["id_fk_type_minerai"],
						);
						break;
					case "Coffre" :
						$arriveeMineraiTable = new CoffreMinerai();
						$data = array (
							"id_fk_hobbit_coffre_minerai" => $this->view->id_hobbit_coffre,
							"id_fk_type_coffre_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"quantite_brut_coffre_minerai" => $nbBrut,
							"quantite_lingots_coffre_minerai" => $nbLingot,
						);
						break;
					case "Charrette" :
						$arriveeMineraiTable = new CharretteMinerai();
						$data = array (
							"id_fk_charrette_minerai" => $this->view->id_charrette_arrivee,
							"id_fk_type_charrette_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"quantite_brut_charrette_minerai" => $nbBrut,
							"quantite_lingots_charrette_minerai" => $nbLingot,
						);
						break;
					case "Echoppe" :
						$arriveeMineraiTable = new EchoppeMinerai();
						$data = array (
							"id_fk_echoppe_echoppe_minerai" => $this->view->id_echoppe_arrivee,
							"id_fk_type_echoppe_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
							"quantite_brut_arriere_echoppe_minerai" => $nbBrut,
							"quantite_lingots_echoppe_minerai" => $nbLingot,
						);
						break;
				}
				$arriveeMineraiTable->insertOrUpdate($data);
				unset ($arriveeMineraiTable);
			}
		}
	}

	private function prepareTypePartiesPlantes($depart) {
		Zend_Loader::loadClass($depart."Partieplante");

		$tabPartiePlantes = null;

		switch ($depart) {
			case "Laban" :
				$labanPartiePlanteTable = new LabanPartieplante();
				$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($labanPartiePlanteTable);
				break;
			case "Element" :
				$elementPartiePlanteTable = new ElementPartieplante();
				$partiePlantes = $elementPartiePlanteTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				unset($elementPartiePlanteTable);
				break;
			case "Coffre" :
				$coffrePartiePlanteTable = new CoffrePartieplante();
				$partiePlantes = $coffrePartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($coffrePartiePlanteTable);
				break;
			case "Charrette" :
				$charrettePartiePlanteTable = new CharrettePartiePlante();
				$partiePlantes = $charrettePartiePlanteTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charrettePartiePlanteTable);
				break;
			case "Echoppe" :
				$echoppePartiePlanteTable = new EchoppePartiePlante();
				$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppePartiePlanteTable);
				break;
		}

		$this->view->nb_partiePlantes = 0;
		$this->view->nb_prepareesPartiePlantes = 0;

		if ($partiePlantes != null) {
			if ($depart == "Echoppe") {
				$strqte = "arriere_echoppe";
			}
			else {
				$strqte = $depart;
			}
			foreach ($partiePlantes as $p) {
				if ($p["quantite_".strtolower($strqte)."_partieplante"] > 0 || $p["quantite_preparee_".strtolower($depart)."_partieplante"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brute
					$tabPartiePlantes[$this->view->nb_valeurs] = array(
						"nom_type" => $p["nom_type_partieplante"],
						"nom_plante" => $p["nom_type_plante"],
						"id_fk_type_partieplante" => $p["id_fk_type_".strtolower($depart)."_partieplante"],
						"id_fk_type_plante_partieplante" => $p["id_fk_type_plante_".strtolower($depart)."_partieplante"],
						"quantite_partieplante" => $p["quantite_".strtolower($strqte)."_partieplante"],
						"quantite_preparee_partieplante" => $p["quantite_preparee_".strtolower($depart)."_partieplante"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // préparée
					$this->view->nb_partiePlantes = $this->view->nb_partiePlantes + $p["quantite_".strtolower($strqte)."_partieplante"];
					$this->view->nb_prepareesPartiePlantes = $this->view->nb_prepareesPartiePlantes + $p["quantite_preparee_".strtolower($depart)."_partieplante"];
				}
			}
		}

		$this->view->valeur_fin_partieplantes = $this->view->nb_valeurs;
		$this->view->partieplantes = $tabPartiePlantes;
	}

	private function deposeTypePartiesPlantes($depart,$arrivee) {
		Zend_Loader::loadClass($depart."Partieplante");
		Zend_Loader::loadClass($arrivee."Partieplante");

		for ($i=$this->view->valeur_fin_munitions+1; $i<=$this->view->valeur_fin_partieplantes; $i = $i + 2) {
			$indice = $i;
			$indiceBrutes = $i;
			$indicePreparees = $i + 1;
			$nbBrutes = $this->request->get("valeur_".$indiceBrutes);
			$nbPreparees = $this->request->get("valeur_".$indicePreparees);
				
			if ((int) $nbBrutes."" != $this->request->get("valeur_".$indiceBrutes)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute invalide=".$nbBrutes);
			} else {
				$nbBrutes = (int)$nbBrutes;
			}
			if ($nbBrutes > $this->view->partieplantes[$indice]["quantite_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute interdit=".$nbBrutes);
			}
			if ((int) $nbPreparees."" != $this->request->get("valeur_".$indicePreparees)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Preparee invalide=".$nbPreparees);
			} else {
				$nbPreparees = (int)$nbPreparees;
			}
			if ($nbPreparees > $this->view->partieplantes[$indice]["quantite_preparee_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Preparee interdit=".$nbPreparees);
			}
			if ($nbBrutes > 0 || $nbPreparees > 0) {

				$this->view->nbelement = $this->view->nbelement + 1;
				if ( $nbBrutes > 0 && $nbPreparees > 0 && $this->view->a_panneau === false) {
					$nbPreparees = 0;
					$this->view->panneau = false;
				}
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
					break;
				}
				
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk1 = $this->controlePoids($this->view->poidsRestant, $nbBrutes, Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
					$poidsOk2 = $this->controlePoids($this->view->poidsRestant, $nbPreparees, Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE);
					if ($poidsOk1 == false || $poidsOk2 == false){
						$this->view->poidsOk = false;
						break;
					}
				}

				switch ($depart){
					case "Laban" :
						$departPartiePlanteTable = new LabanPartieplante();
						$data = array(
							'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
							'quantite_laban_partieplante' => -$nbBrutes,
							'quantite_preparee_laban_partieplante' => -$nbPreparees
						);
						break;
					case "Element" :
						$departPartiePlanteTable = new ElementPartieplante();
						$data = array (
								"x_element_partieplante" => $this->view->user->x_hobbit,
								"y_element_partieplante" => $this->view->user->y_hobbit,
								"id_fk_type_element_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
								"id_fk_type_plante_element_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
								"quantite_element_partieplante" => -$nbBrutes,
								"quantite_preparee_element_partieplante" => -$nbPreparees,
						);
						break;
					case "Coffre" :
						$departPartiePlanteTable = new CoffrePartieplante();
						$data = array(
							'id_fk_type_coffre_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							'id_fk_type_plante_coffre_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							'id_fk_hobbit_coffre_partieplante' => $this->view->user->id_hobbit,
							'quantite_coffre_partieplante' => -$nbBrutes,
							'quantite_preparee_coffre_partieplante' => -$nbPreparees
						);
						break;
					case "Charrette" :
						$departPartiePlanteTable = new CharrettePartieplante();
						$data = array(
							'id_fk_type_charrette_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							'id_fk_type_plante_charrette_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							'id_fk_charrette_partieplante' => $this->view->id_charrette_depart,
							'quantite_charrette_partieplante' => -$nbBrutes,
							'quantite_preparee_charrette_partieplante' => -$nbPreparees
						);
						break;
					case "Echoppe" :
						$departPartiePlanteTable = new EchoppePartieplante();
						$data = array(
							'id_fk_type_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							'id_fk_type_plante_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							'id_fk_echoppe_echoppe_partieplante' => $this->view->id_echoppe_depart,
							'quantite_arriere_echoppe_partieplante' => -$nbBrutes,
							'quantite_preparee_echoppe_partieplante' => -$nbPreparees
						);
						break;
				}

				$departPartiePlanteTable->insertOrUpdate($data);
				unset ($departPartiePlanteTable);

				switch ($arrivee){
					case "Laban" :
						$arriveePartiePlanteTable = new LabanPartieplante();
						$data = array (
							"id_fk_hobbit_laban_partieplante" => $this->view->id_hobbit_coffre,
							"id_fk_type_laban_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							"id_fk_type_plante_laban_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							"quantite_laban_partieplante" => $nbBrutes,
							"quantite_preparee_laban_partieplante" => $nbPreparees,
						);
						$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE * $nbBrutes - Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE * $nbPreparees;
						break;
					case "Element" :
						$arriveePartiePlanteTable = new ElementPartieplante();
						$data = array("x_element_partieplante" => $this->view->user->x_hobbit,
							  "y_element_partieplante" => $this->view->user->y_hobbit,
							  'quantite_element_partieplante' => $nbBrutes,
							  'quantite_preparee_element_partieplante' => $nbPreparees,
							  'id_fk_type_element_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							  'id_fk_type_plante_element_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
						);
						break;
					case "Coffre" :
						$arriveePartiePlanteTable = new CoffrePartieplante();
						$data = array (
							"id_fk_hobbit_coffre_partieplante" => $this->view->id_hobbit_coffre,
							"id_fk_type_coffre_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							"id_fk_type_plante_coffre_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							"quantite_coffre_partieplante" => $nbBrutes,
							"quantite_preparee_coffre_partieplante" => $nbPreparees,
						);
						break;
					case "Charrette" :
						$arriveePartiePlanteTable = new CharrettePartieplante();
						$data = array (
							"id_fk_charrette_partieplante" => $this->view->id_charrette_arrivee,
							"id_fk_type_charrette_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							"id_fk_type_plante_charrette_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							"quantite_charrette_partieplante" => $nbBrutes,
							"quantite_preparee_charrette_partieplante" => $nbPreparees,
						);
						break;
					case "Echoppe" :
						$arriveePartiePlanteTable = new EchoppePartieplante();
						$data = array (
							"id_fk_echoppe_echoppe_partieplante" => $this->view->id_echoppe_arrivee,
							"id_fk_type_echoppe_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
							"id_fk_type_plante_echoppe_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
							"quantite_arriere_echoppe_partieplante" => $nbBrutes,
							"quantite_preparee_echoppe_partieplante" => $nbPreparees,
						);
						break;
				}
				$arriveePartiePlanteTable->insertOrUpdate($data);
				unset ($arriveePartiePlanteTable);
			}
		}
	}

	private function prepareTypeTabac($depart) {
		if ( $depart != "Echoppe") {
			Zend_Loader::loadClass($depart."Tabac");
			$tabTabacs = null;
	
			switch ($depart) {
				case "Laban" :
					$labanTabacTable = new LabanTabac();
					$tabacs = $labanTabacTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($labanTabacTable);
					break;
				case "Element" :
					$elementTabacTable = new ElementTabac();
					$tabacs = $elementTabacTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
					unset($elementTabacTable);
					break;
				case "Coffre" :
					$coffreTabacTable = new CoffreTabac();
					$tabacs = $coffreTabacTable->findByIdHobbit($this->view->user->id_hobbit);
					unset($coffreTabacTable);
					break;
				case "Charrette" :
					$charretteTabacTable = new CharretteTabac();
					$tabacs = $charretteTabacTable->findByIdCharrette($this->view->id_charrette_depart);
					unset($charretteTabacTable);
					break;
			}
	
			if (count($tabacs) > 0) {
				foreach ($tabacs as $m) {
					if ($m["quantite_feuille_".strtolower($depart)."_tabac"] > 0) {
						$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
						$tabTabacs[$this->view->nb_valeurs] = array(
							"id_type_tabac" => $m["id_fk_type_".strtolower($depart)."_tabac"],
							"type" => $m["nom_type_tabac"],
							"quantite" => $m["quantite_feuille_".strtolower($depart)."_tabac"],
							"indice_valeur" => $this->view->nb_valeurs,
						);
					}
				}
				$this->view->deposerOk = true;
			}
			$this->view->valeur_fin_tabacs = $this->view->nb_valeurs;
			$this->view->tabacs = $tabTabacs;
		}
		else {
			$this->view->valeur_fin_tabacs = $this->view->nb_valeurs;
			$this->view->tabacs = null;
		}
	}

	private function deposeTypeTabac($depart,$arrivee) {
		if ($depart != "Echoppe" && $arrivee != "Echoppe") {
			Zend_Loader::loadClass($depart."Tabac");
			Zend_Loader::loadClass($arrivee."Tabac");

			if (count($this->view->tabacs) > 0) {
				$idTabac = null;
				$nbTabac = null;
	
				for ($i=$this->view->valeur_fin_minerais + 1; $i<=$this->view->valeur_fin_tabacs; $i++) {
						
					if ( $this->request->get("valeur_".$i) > 0) {
						$nbTabac = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_".$i));
							
						$tabac = $this->view->tabacs[$i];
	
						if ($nbTabac > $tabac["quantite"] || $nbTabac < 0) {
							throw new Zend_Exception(get_class($this)." Quantite Tabac invalide : ".$nbTabac. " i=".$i);
						}
						
						$this->view->nbelement = $this->view->nbelement + 1;
						if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
							$this->view->panneau = false;
							break;
						}
						
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, $nbTabac, Bral_Util_Poids::POIDS_TABAC);
							if ($poidsOk == false){
								$this->view->poidsOk = false;
								break;
							}
						}
							
						switch ($depart){
							case "Laban" :
								$departTabacTable = new LabanTabac();
								$data = array(
										"quantite_feuille_laban_tabac" => -$nbTabac,
										"id_fk_type_laban_tabac" => $tabac["id_type_tabac"],
										"id_fk_hobbit_laban_tabac" => $this->view->user->id_hobbit,
								);
								break;
							case "Element" :
								$departTabacTable = new ElementTabac();
								$data = array (
										"x_element_tabac" => $this->view->user->x_hobbit,
										"y_element_tabac" => $this->view->user->y_hobbit,
										"id_fk_type_element_tabac" => $tabac["id_type_tabac"],
										"quantite_feuille_element_tabac" => -$nbTabac,
								);
								break;		
							case "Coffre" :
								$departTabacTable = new CoffreTabac();
								$data = array(
										"quantite_feuille_coffre_tabac" => -$nbTabac,
										"id_fk_type_coffre_tabac" => $tabac["id_type_tabac"],
										"id_fk_hobbit_coffre_tabac" => $this->view->user->id_hobbit,
								);
								break;
							case "Charrette" :
								$departTabacTable = new CharretteTabac();
								$data = array(
										"quantite_feuille_charrette_tabac" => -$nbTabac,
										"id_fk_type_charrette_tabac" => $tabac["id_type_tabac"],
										"id_fk_hobbit_charrette_tabac" => $this->view->id_charrette_depart,
								);
								break;
						}
							
						$departTabacTable->insertOrUpdate($data);
						unset ($departTabacTable);
							
						switch ($arrivee){
							case "Laban" :
								$arriveeTabacTable = new LabanTabac();
								$data = array(
										"quantite_feuille_laban_tabac" => $nbTabac,
										"id_fk_type_laban_tabac" => $tabac["id_type_tabac"],
										"id_fk_hobbit_laban_tabac" => $this->view->user->id_hobbit,
								);
								$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MUNITION * $nbTabac;
								break;
							case "Element" :
								$arriveeTabacTable = new ElementTabac();
								$data = array (
									"x_element_tabac" => $this->view->user->x_hobbit,
									"y_element_tabac" => $this->view->user->y_hobbit,
									"id_fk_type_element_tabac" => $tabac["id_type_tabac"],
									"quantite_feuille_element_tabac" => $nbTabac,
								);
								break;
							case "Coffre" :
								$arriveeTabacTable = new CoffreTabac();
								$data = array(
										"quantite_feuille_coffre_tabac" => $nbTabac,
										"id_fk_type_coffre_tabac" => $tabac["id_type_tabac"],
										"id_fk_hobbit_coffre_tabac" => $this->view->id_hobbit_coffre,
								);
								break;
							case "Charrette" :
								$arriveeTabacTable = new CharretteTabac();
								$data = array(
										"quantite_feuille_charrette_tabac" => $nbTabac,
										"id_fk_type_charrette_tabac" => $tabac["id_type_tabac"],
										"id_fk_charrette_tabac" => $this->view->id_charrette_arrivee,
								);
								break;
						}
						$arriveeTabacTable->insertOrUpdate($data);
						unset($arriveeTabacTable);
					}
				}
			}
		}
	}
	
	private function prepareTypeMateriel($depart) {
		Zend_Loader::loadClass($depart."Materiel");
		$tabMateriels = null;
		
		switch ($depart) {
			case "Laban" :
				$labanMaterielTable = new LabanMateriel();
				$materiels = $labanMaterielTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($labanMaterielTable);
				break;
			case "Element" :
				$elementMaterielTable = new ElementMateriel();
				$materiels = $elementMaterielTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				unset($elementMaterielTable);
				break;
			case "Coffre" :
				$coffreMaterielTable = new CoffreMateriel();
				$materiels = $coffreMaterielTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($coffreMaterielTable);
				break;
			case "Charrette" :
				$charretteMaterielTable = new CharretteMateriel();
				$materiels = $charretteMaterielTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteMaterielTable);
				break;
			case "Echoppe" :
				$echoppeMaterielTable = new EchoppeMateriel();
				$materiels = $echoppeMaterielTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeMaterielTable);
				break;
		}

		if (count($materiels) > 0) {
			foreach ($materiels as $e) {
				$tabMateriels[$e["id_".strtolower($depart)."_materiel"]] = array(
						"id_materiel" => $e["id_".strtolower($depart)."_materiel"],
						"id_fk_type_materiel" => $e["id_fk_type_".strtolower($depart)."_materiel"],
						"nom" => $e["nom_type_materiel"],
						"poids" => $e["poids_type_materiel"],
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->materiels = $tabMateriels;
	}

	private function deposeTypeMateriel($depart,$arrivee) {
		if ($arrivee != "Echoppe") {
			Zend_Loader::loadClass($depart."Materiel");
			Zend_Loader::loadClass($arrivee."Materiel");
	
			$materiels = array();
			$materiels = $this->request->get("valeur_16");
	
			if (count($materiels) > 0 && $materiels != 0) {
				$this->view->nbelement = $this->view->nbelement + 1;
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
				}
				else {
					foreach ($materiels as $idMateriel) {
						if (!array_key_exists($idMateriel, $this->view->materiels)) {
							throw new Zend_Exception(get_class($this)." ID Materiel invalide : ".$idMateriel);
						}
		
						$materiel = $this->view->materiels[$idMateriel];
						
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant,1,$materiel["poids"]);
							if ($poidsOk == false){
								$this->view->poidsOk = false;
								break;
							}
						}
		
						$where = "id_".strtolower($depart)."_materiel=".$idMateriel;
						switch ($depart){
							case "Laban" :
								$departMaterielTable = new LabanMateriel();
								break;
							case "Element" :
								$departMaterielTable = new ElementMateriel();
								break;
							case "Coffre" :
								$departMaterielTable = new CoffreMateriel();
								break;
							case "Charrette" :
								$departMaterielTable = new CharretteMateriel();
								break;
							case "Echoppe" :
								$departMaterielTable = new EchoppeMateriel();
								break;
						}
		
						$departMaterielTable->delete($where);
						unset($departMaterielTable);
		
						switch ($arrivee){
							case "Laban" :
								$arriveeMaterielTable = new LabanMateriel();
								$data = array (
									"id_laban_materiel" => $materiel["id_materiel"],
									"id_fk_type_laban_materiel" => $materiel["id_fk_type_materiel"],
									"id_fk_hobbit_laban_materiel" => $this->view->user->id_hobbit,
								);
								$this->view->poidsRestant = $this->view->poidsRestant - $materiel["poids"];
								break;
							case "Element" :
								$dateCreation = date("Y-m-d H:i:s");
								$nbJours = Bral_Util_De::get_2d10();
								$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		
								$arriveeMaterielTable = new ElementMateriel();
								$data = array (
									"id_element_materiel" => $materiel["id_materiel"],
									"x_element_materiel" => $this->view->user->x_hobbit,
									"y_element_materiel" => $this->view->user->y_hobbit,
									"id_fk_type_element_materiel" => $materiel["id_fk_type_materiel"],
									"date_fin_element_materiel" => $dateFin,
								);
								break;
							case "Coffre" :
								$arriveeMaterielTable = new CoffreMateriel();
								$data = array (
									"id_coffre_materiel" => $materiel["id_materiel"],
									"id_fk_type_coffre_materiel" => $materiel["id_fk_type_materiel"],
									"id_fk_hobbit_coffre_materiel" => $this->view->id_hobbit_coffre,
								);
								break;
							case "Charrette" :
								$arriveeMaterielTable = new CharretteMateriel();
								$data = array (
									"id_charrette_materiel" => $materiel["id_materiel"],
									"id_fk_type_charrette_materiel" => $materiel["id_fk_type_materiel"],
									"id_fk_charrette_materiel" => $this->view->id_charrette_arrivee,
								);
								break;
							/*case "Echoppe" :
								$arriveeMaterielTable = new EchoppeMateriel();
								$data = array (
									"id_echoppe_materiel" => $materiel["id_materiel"],
									"id_fk_type_echoppe_materiel" => $materiel["id_fk_type_materiel"],
									"id_fk_echoppe_echoppe_materiel" => $this->view->id_echoppe_arrivee,
								);
								break;*/
						}
						$arriveeMaterielTable->insert($data);
						unset($arriveeMaterielTable);
					}
				}
			}
		}
	}
	
	private function prepareTypeAutres($depart){
		Zend_Loader::loadClass($depart);

		$tabAutres["nb_castar"] = 0;
		$tabAutres["nb_peau"] = 0;
		$tabAutres["nb_viande"] = 0;
		$tabAutres["nb_viande_preparee"] = 0;
		$tabAutres["nb_cuir"] = 0;
		$tabAutres["nb_fourrure"] = 0;
		$tabAutres["nb_planche"] = 0;
		$tabAutres["nb_rondin"] = 0;

		switch ($depart) {
			case "Laban" :
				$labanTable = new Laban();
				$autres = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
				if ($autres == null) { // si l'on a pas de laban
					$autres = array(
						"quantite_castar_".strtolower($depart) => 0,
						"quantite_peau_".strtolower($depart) => 0,
						"quantite_viande_".strtolower($depart) => 0,
						"quantite_viande_preparee_".strtolower($depart) => 0,
						"quantite_cuir_".strtolower($depart) => 0,
						"quantite_fourrure_".strtolower($depart) => 0,
						"quantite_planche_".strtolower($depart) => 0,
						"quantite_rondin_".strtolower($depart) => 0,
					);
				}
				if ($this->view->user->castars_hobbit > 0){
					$autres[0]["quantite_castar_laban"] = $this->view->user->castars_hobbit;
				}
				unset($labanTable);
				break;
			case "Element" :
				$elementTable = new Element();
				$autres = $elementTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
				unset($elementTable);
				break;
			case "Coffre" :
				$coffreTable = new Coffre();
				$autres = $coffreTable->findByIdHobbit($this->view->user->id_hobbit);
				unset($coffreTable);
				break;
			case "Charrette" :
				$charretteTable = new Charrette();
				$autres = $charretteTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteTable);
				break;
			case "Echoppe" :
				$echoppeTable = new Echoppe();
				$autres = $echoppeTable->findById($this->view->id_echoppe_depart);
				unset($echoppeTable);
				break;
		}

		if (count($autres) == 1) {
			if ($depart == "Echoppe") {
				$strqte = "arriere_echoppe";
				$p = $autres[0];
				$tabAutres = array(
					"nb_castar" => 0,
					"nb_peau" => $p["quantite_peau_".strtolower($strqte)],
					"nb_viande" => 0,
					"nb_viande_preparee" => 0,
					"nb_cuir" => $p["quantite_cuir_".strtolower($strqte)],
					"nb_fourrure" => $p["quantite_fourrure_".strtolower($strqte)],
					"nb_planche" => $p["quantite_planche_".strtolower($strqte)],
					"nb_rondin" => $p["quantite_rondin_".strtolower($strqte)],
				);
			}
			else {
				$strqte = $depart;
				$p = $autres[0];
				$tabAutres = array(
					"nb_castar" => $p["quantite_castar_".strtolower($strqte)],
					"nb_peau" => $p["quantite_peau_".strtolower($strqte)],
					"nb_viande" => $p["quantite_viande_".strtolower($strqte)],
					"nb_viande_preparee" => $p["quantite_viande_preparee_".strtolower($strqte)],
					"nb_cuir" => $p["quantite_cuir_".strtolower($strqte)],
					"nb_fourrure" => $p["quantite_fourrure_".strtolower($strqte)],
					"nb_planche" => $p["quantite_planche_".strtolower($strqte)],
					"nb_rondin" => $p["quantite_rondin_".strtolower($strqte)],
				);
			}
			if ( $tabAutres["nb_castar"] != 0 || $tabAutres["nb_peau"] != 0 ||
				 $tabAutres["nb_viande"] != 0 || $tabAutres["nb_viande_preparee"] != 0 ||
				 $tabAutres["nb_cuir"] != 0 || $tabAutres["nb_fourrure"] != 0 ||
				 $tabAutres["nb_planche"] != 0 || $tabAutres["nb_rondin"] != 0
			   ) {
					$this->view->deposerOk = true;
			}
		}
		$this->view->autres = $tabAutres;
	}

	private function deposeTypeAutres($depart,$arrivee) {
		Zend_Loader::loadClass($depart);
		Zend_Loader::loadClass($arrivee);

		$nbCastar = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
		$nbPeau = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
		$nbCuir = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_6"));
		$nbFourrure = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_7"));
		$nbViande = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_8"));
		$nbViandePreparee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_9"));
		$nbPlanche = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_10"));
		$nbRondin = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_11"));

		$tabElement[1] = array("nom_systeme" => "castar", "nb" => $nbCastar, "poids" => Bral_Util_Poids::POIDS_CASTARS);
		$tabElement[2] = array("nom_systeme" => "peau", "nb" => $nbPeau, "poids" => Bral_Util_Poids::POIDS_PEAU);
		$tabElement[3] = array("nom_systeme" => "cuir", "nb" => $nbCuir, "poids" => Bral_Util_Poids::POIDS_CUIR);
		$tabElement[4] = array("nom_systeme" => "fourrure", "nb" => $nbFourrure, "poids" => Bral_Util_Poids::POIDS_FOURRURE);
		$tabElement[5] = array("nom_systeme" => "viande", "nb" => $nbViande, "poids" => Bral_Util_Poids::POIDS_VIANDE);
		$tabElement[6] = array("nom_systeme" => "viande_preparee", "nb" => $nbViandePreparee, "poids" => Bral_Util_Poids::POIDS_VIANDE_PREPAREE);
		$tabElement[7] = array("nom_systeme" => "planche", "nb" => $nbPlanche, "poids" => Bral_Util_Poids::POIDS_PLANCHE);
		$tabElement[8] = array("nom_systeme" => "rondin", "nb" => $nbRondin, "poids" => Bral_Util_Poids::POIDS_RONDIN);

		foreach ($tabElement as $t){
			$nb=$t["nb"];
			$nom_systeme = $t["nom_systeme"];
			$poids = $t["poids"];
			if ($nb < 0) {
				throw new Zend_Exception(get_class($this)." Nb ".$nom_systeme." : ".$nb);
			}
				
			if ($nb > 0){
				if ($nb > $this->view->autres["nb_".$nom_systeme]) {
					$nb = $this->view->autres["nb_".$nom_systeme];
				}
				if ( ($depart == "Echoppe" || $arrivee == "Echoppe") && ($nom_systeme == "viande" || $nom_systeme == "viande_preparee")) {
					$nb = 0;
				}
				
				$this->view->nbelement = $this->view->nbelement + 1;
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 1 ){
					$this->view->panneau = false;
					break;
				}
				
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk = $this->controlePoids($this->view->poidsRestant, $nb, $poids );
					if ($poidsOk == false){
						$this->view->poidsOk = false;
						break;
					}
				}

				$data = array(
					"quantite_".$nom_systeme."_".strtolower($depart) => -$nb,
					"id_fk_hobbit_".strtolower($depart) => $this->view->user->id_hobbit,
				);
				$departTable = null;
				switch ($depart){
					case "Laban" :
						if ($nom_systeme == "castar") {
							if ($nb > $this->view->user->castars_hobbit) {
								$nb = $this->view->user->castars_hobbit;
							}
							$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $nb;
						} else {
							$departTable = new Laban();
						}
						break;
					case "Element" :
						$departTable = new Element();
						$data = array(
							"quantite_".$nom_systeme."_element" => -$nb,
							"x_element" => $this->view->user->x_hobbit,
							"y_element" => $this->view->user->y_hobbit,
						);
						break;
					case "Coffre" :
						$departTable = new Coffre();
						break;
					case "Charrette" :
						$departTable = new Charrette();
						break;
					case "Echoppe" :
						$departTable = new Echoppe();
						$data = array(
							"quantite_".$nom_systeme."_arriere_".strtolower($depart) => -$nb,
							"id_".strtolower($depart) => $this->view->id_echoppe_depart,
						);
						break;
				}
				if ($departTable){
					$departTable->insertOrUpdate($data);
					unset($departTable);
				}

				$arriveeTable = null;
				switch ($arrivee){
					case "Laban" :
						if ($nom_systeme == "castar") {
							$this->view->user->castars_hobbit = $this->view->user->castars_hobbit + $nb;
						} else {
							$data = array(
								"quantite_".$nom_systeme."_laban" => $nb,
								"id_fk_hobbit_laban" => $this->view->user->id_hobbit,
							);
							$arriveeTable = new Laban();
						}
						$this->view->poidsRestant = $this->view->poidsRestant - $poids * $nb;
						break;
					case "Element" :
						$data = array(
								"quantite_".$nom_systeme."_element" => $nb,
								"x_element" => $this->view->user->x_hobbit,
								"y_element" => $this->view->user->y_hobbit,
						);
						$arriveeTable = new Element();
						break;
					case "Coffre" :
						$data = array(
								"quantite_".$nom_systeme."_coffre" => $nb,
								"id_fk_hobbit_coffre" => $this->view->id_hobbit_coffre,
						);
						$arriveeTable = new Coffre();
						break;
					case "Charrette" :
						$data = array(
								"quantite_".$nom_systeme."_charrette" => $nb,
								"id_charrette" => $this->view->id_charrette_arrivee,
						);
						$arriveeTable = new Charrette();
						break;
					case "Echoppe" :
						$data = array(
								"quantite_".$nom_systeme."_arriere_echoppe" => $nb,
								"id_echoppe" => $this->view->id_echoppe_arrivee,
						);
						$arriveeTable = new Echoppe();
						break;
				}
				if ($arriveeTable){
					$arriveeTable->insertOrUpdate($data);
					unset($arriveeTable);
				}
			}
		}
	}
}