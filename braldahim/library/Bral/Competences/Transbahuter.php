<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */

//@TODO bouton transfert equipement, potion et materiel dans echoppe ==> edit Boule. On ne remet pas quelque chose dans l'échoppe si c'est déjà sorti.
//@TODO afficher poids restant dans formulaire
class Bral_Competences_Transbahuter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Coffre");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("CharrettePartage");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("Butin");

		$choixDepart = false;
		//liste des endroits
		//On peut essayer de transbahuter pour le sol et le laban
		$tabEndroit[1] = array("id_type_endroit" => 1,"nom_systeme" => "Element", "nom_type_endroit" => "Le sol", "est_depart" => true, "poids_restant" => -1, "panneau" => true);
		$poidsRestantLaban = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		$tabEndroit[2] = array("id_type_endroit" => 2,"nom_systeme" => "Laban", "nom_type_endroit" => "Votre laban", "est_depart" => true, "poids_restant" => $poidsRestantLaban, "panneau" => true);

		//Si on est sur une banque :
		$lieu = new Lieu();
		$banque = $lieu->findByTypeAndCase(TypeLieu::ID_TYPE_BANQUE,$this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		if (count($banque) > 0 || $this->view->user->est_pnj_braldun == 'oui') {
			$tabEndroit[3] = array("id_type_endroit" => 3,"nom_systeme" => "Coffre", "nom_type_endroit" => "Votre coffre", "est_depart" => true, "poids_restant" => -1, "panneau" => true);
			$tabEndroit[4] = array("id_type_endroit" => 4,"nom_systeme" => "Coffre", "nom_type_endroit" => "Le coffre d'un autre Braldun", "est_depart" => false, "poids_restant" => -1, "panneau" => true);
		}

		//Si on est sur une echoppe
		$echoppe = new Echoppe();
		$echoppeCase = $echoppe->findByCase($this->view->user->x_braldun,$this->view->user->y_braldun, $this->view->user->z_braldun);
		if (count($echoppeCase) > 0) {
			if ($echoppeCase[0]["id_braldun"] == $this->view->user->id_braldun) {
				$tabEndroit[5] = array("id_type_endroit" => 5,"nom_systeme" => "Echoppe", "nom_type_endroit" => "Votre échoppe", "id_braldun_echoppe" => $echoppeCase[0]["id_braldun"], "est_depart" => true, "poids_restant" => -1, "panneau" => true);
				//$tabEndroit[6] = array("id_type_endroit" => 6,"nom_systeme" => "Echoppe", "nom_type_endroit" => "La caisse de votre échoppe", "est_depart" => true, "poids_restant" => -1, "panneau" => true);
				$this->view->id_echoppe_depart = $echoppeCase[0]["id_echoppe"];
			}
			else {
				$tabEndroit[5] = array("id_type_endroit" => 5,"nom_systeme" => "Echoppe", "nom_type_endroit" => "L'échoppe de ".$echoppeCase[0]["prenom_braldun"]." ".$echoppeCase[0]["nom_braldun"], "id_braldun_echoppe" => $echoppeCase[0]["id_braldun"], "est_depart" => false, "poids_restant" => -1, "panneau" => true);
			}
			$this->view->id_echoppe_arrivee = $echoppeCase[0]["id_echoppe"];
		}

		//Cas des charrettes
		$nbendroit=7;
		$charrette = new Charrette();
		$charrettePartage = new CharrettePartage();

		$tabCharrette = $charrette->findByPositionAvecBraldun($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		if (count($tabCharrette) > 0) {
			foreach ($tabCharrette as $c) {
				Zend_Loader::loadClass("Bral_Util_Charrette");
				$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($c["id_braldun"]);
				if ( $c["id_braldun"] == $this->view->user->id_braldun) {
					$panneau = Bral_Util_Charrette::possedePanneauAmovible($c["id_charrette"]);
					$tabEndroit[$nbendroit] = array("id_type_endroit" => $nbendroit,"nom_systeme" => "Charrette", "id_charrette" => $c["id_charrette"], "id_braldun_charrette" => $c["id_fk_braldun_charrette"], "panneau" => $panneau, "nom_type_endroit" => "Votre charrette", "est_depart" => true, "poids_restant" => $tabPoidsCharrette["place_restante"]);
					//$this->view->id_charrette_depart = $c["id_charrette"];
				}
				else {
					$estDepart = false;

					if ($c["est_partage_bralduns_charrette"] == "oui") { // tous les bralduns
						$estDepart = true;
					} else {
						if ($c["est_partage_communaute_charrette"] == "oui" &&
						$c["id_fk_communaute_braldun"]  == $this->view->user->id_fk_communaute_braldun) { // bralduns de la comunaute
							$estDepart = true;
						}

						if ($estDepart == false) { // on regarde dans les partages bralduns
							$partage = $charrettePartage->findByIdCharretteAndIdBraldun($c["id_charrette"], $this->view->user->id_braldun);
							if ($partage != null && count($partage) > 0) {
								$estDepart = true;
							}
						}
					}

					if ($estDepart == true) {
						$panneau = Bral_Util_Charrette::possedePanneauAmovible($c["id_charrette"]);
					} else {
						$panneau = false;
					}

					$tabEndroit[$nbendroit] = array("id_type_endroit" => $nbendroit,"nom_systeme" => "Charrette", "id_charrette" => $c["id_charrette"], "id_braldun_charrette" => $c["id_fk_braldun_charrette"], "nom_type_endroit" => "La charrette de ".$c["prenom_braldun"]." ".$c["nom_braldun"]." (n°".$c["id_braldun"].")", "est_depart" => $estDepart, "panneau" => $panneau, "poids_restant" => $tabPoidsCharrette["place_restante"]);
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

		$this->prepareButins();

		//Construction du tableau des départs
		$tabTypeDepart = null;
		$i=1;
		foreach ($tabEndroit as $e) {
			//On ne prend que ce qui peut être dans les départs
			if ($e["est_depart"] == true) {
				$this->view->deposerOk = false;
				if ($e["nom_systeme"] == "Charrette") {
					$this->view->id_charrette_depart = $e["id_charrette"];
				}
				$this->prepareType($e["nom_systeme"]);
				if ($this->view->deposerOk == true) {
					$tabTypeDepart[$i] = array("id_type_depart" => $e["id_type_endroit"], "selected" => $id_type_courant_depart, "nom_systeme" => $e["nom_systeme"], "nom_type_depart" => $e["nom_type_endroit"], "panneau" => $e["panneau"]);
					$i++;
				}
			}
		}

		if (count($tabTypeDepart) == 1) {
			$id_type_courant_depart = $tabTypeDepart[1]["id_type_depart"];
			$choixDepart = true;
		}

		$this->view->typeDepart = $tabTypeDepart;

		//Si on a choisi le départ, on peut choisir l'arrivée
		if ($choixDepart === true) {

			if ($tabEndroit[$id_type_courant_depart]["nom_systeme"] == "Charrette") { // positionnement de la charrette choisie
				$this->view->id_charrette_depart = $tabEndroit[$id_type_courant_depart]["id_charrette"];
			}

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
					if ($id_charrette == $e["id_charrette"]) {
						$endroitArrivee = true;
						$this->view->id_charrette_arrivee = $id_charrette;
						$this->view->poidsRestant = $e["poids_restant"];
					}
				}
			}
		}
		if ($endroitDepart === false) {
			throw new Zend_Exception(get_class($this)." Endroit depart invalide = ".$idDepart);
		}
		if ($endroitArrivee === false) {
			throw new Zend_Exception(get_class($this)." Endroit arrivee invalide = ".$idArrivee);
		}

		if ($this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Coffre") {
			$idBraldunCoffre = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
			$this->view->id_braldun_coffre = null;
			if ($idBraldunCoffre == -1) {
				$this->view->id_braldun_coffre = $this->view->user->id_braldun;
			} else{
				$this->view->id_braldun_coffre = $idBraldunCoffre;
			}
				
			if ($this->view->id_braldun_coffre != null) {
				$coffreTable = new Coffre();
				$coffre = $coffreTable->findByIdBraldun($this->view->id_braldun_coffre);
				if (count($coffre) != 1) {
					throw new Zend_Exception(get_class($this)." Coffre arrivee invalide = ".$this->view->id_braldun_coffre);
				}
				$this->view->id_coffre_arrivee = $coffre[0]["id_coffre"];
			}
		}

		$this->view->poidsOk = true;
		$this->view->nbelement = 0;
		$this->view->panneau = true;
		$this->view->elementsRetires = "";
		$this->view->elementsNonRetiresPoids = "";
		$this->view->elementsNonRetiresPanneau = "";
		$this->deposeType($this->view->tabEndroit[$idDepart]["nom_systeme"], $this->view->tabEndroit[$idArrivee]["nom_systeme"]);
		$this->view->depart = $this->view->tabEndroit[$idDepart]["nom_type_endroit"];

		if ($this->view->nbelement > 0) {

			if ($idArrivee == 4) {
				Zend_Loader::loadClass("Braldun");
				$braldun = new Braldun();
				$nomBraldun = $braldun->findNomById($this->view->id_braldun_coffre);
				$this->view->arrivee = "le coffre de ".$nomBraldun;
			}
			else {
				$this->view->arrivee = $this->view->tabEndroit[$idArrivee]["nom_type_endroit"];
			}

			if ($this->view->elementsRetires != "") {
				// on enlève la dernière virgule de la chaîne
				$this->view->elementsRetires = mb_substr($this->view->elementsRetires, 0, -2);
			}

			// Historique
			if ($this->view->tabEndroit[$idDepart]["nom_systeme"] == "Charrette") {
				Bral_Util_Poids::calculPoidsCharrette($this->view->tabEndroit[$idDepart]["id_braldun_charrette"], true);

				$texte = $this->calculTexte($this->view->tabEndroit[$idDepart]["nom_systeme"], $this->view->tabEndroit[$idArrivee]["nom_systeme"]);
				$details = "[b".$this->view->user->id_braldun."] a transbahuté des choses depuis la [t".$this->view->tabEndroit[$idDepart]["id_charrette"]. "] (".$texte["departTexte"]." vers ".$texte["arriveeTexte"].")";
				Zend_Loader::loadClass("Bral_Util_Materiel");
				Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_TRANSBAHUTER_ID, $this->view->tabEndroit[$idDepart]["id_charrette"], $details);
			}

			if ($this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Charrette") {
				Bral_Util_Poids::calculPoidsCharrette($this->view->tabEndroit[$idArrivee]["id_braldun_charrette"], true);

				$texte = $this->calculTexte($this->view->tabEndroit[$idDepart]["nom_systeme"], $this->view->tabEndroit[$idArrivee]["nom_systeme"]);
				$details = "[b".$this->view->user->id_braldun."] a transbahuté des choses dans la [t".$this->view->tabEndroit[$idArrivee]["id_charrette"]. "] (".$texte["departTexte"]." vers ".$texte["arriveeTexte"].")";
				Zend_Loader::loadClass("Bral_Util_Materiel");
				Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_TRANSBAHUTER_ID, $this->view->tabEndroit[$idArrivee]["id_charrette"], $details);
			}

			// événements
			$this->detailEvenement = "";
			if ($this->view->tabEndroit[$idDepart]["nom_systeme"] == "Element") {
				$idEvenement = $this->view->config->game->evenements->type->ramasser;
				$this->detailEvenement = "[b".$this->view->user->id_braldun."] a ramassé des éléments à terre ";
			}
			if ($this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Element") {
				$idEvenement = $this->view->config->game->evenements->type->deposer;
				$this->detailEvenement = "[b".$this->view->user->id_braldun."] a déposé des éléments à terre ";
			}
			if ($this->view->tabEndroit[$idDepart]["nom_systeme"] == "Coffre" || $this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Coffre" ) {
				$idEvenement = $this->view->config->game->evenements->type->service;
				if ($this->view->id_braldun_coffre != $this->view->user->id_braldun && $this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Coffre") {
					$message = "[Ceci est un message automatique de transbahutage]".PHP_EOL;
					$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a transbahuté ces éléments dans votre coffre : ".PHP_EOL;
					$message .= $this->view->elementsRetires;
					$data = Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $this->view->id_braldun_coffre, $message, $this->view);

					$messageCible = $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a transbahuté ces éléments dans votre coffre : ".PHP_EOL;
					$messageCible .= $this->view->elementsRetires;
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a transbahuté des éléments dans le coffre de [b".$this->view->id_braldun_coffre."]";
					$this->setDetailsEvenementCible($this->view->id_braldun_coffre, "braldun", 0, $messageCible);
				}
				else {
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a utilisé les services de la banque ";
				}
			}
			if ($this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Charrette" ) {
				$idEvenement = $this->view->config->game->evenements->type->transbahuter;
				if ($this->view->tabEndroit[$idArrivee]["id_braldun_charrette"] != $this->view->user->id_braldun) {
					$message = "[Ceci est un message automatique de transbahutage]".PHP_EOL;
					$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a transbahuté ces éléments dans votre charrette : ".PHP_EOL;
					$message .= $this->view->elementsRetires;
					$data = Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $this->view->tabEndroit[$idArrivee]["id_braldun_charrette"], $message, $this->view);

					$messageCible = $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a transbahuté ces éléments dans votre charrette : ".PHP_EOL;
					$messageCible .= $this->view->elementsRetires;
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a transbahuté des éléments dans la charrette de [b".$this->view->tabEndroit[$idArrivee]["id_braldun_charrette"]."]";
					$this->setDetailsEvenementCible($this->view->tabEndroit[$idArrivee]["id_braldun_charrette"], "braldun", 0, $messageCible);
				}
				else {
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a transbahuté des éléments dans sa charrette ";
				}
			}
			if ($this->view->tabEndroit[$idArrivee]["nom_systeme"] == "Echoppe" ) {
				$idEvenement = $this->view->config->game->evenements->type->transbahuter;
				if ($this->view->tabEndroit[$idArrivee]["id_braldun_echoppe"] != $this->view->user->id_braldun) {
					$message = "[Ceci est un message automatique de transbahutage]".PHP_EOL;
					$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a transbahuté ces éléments dans votre échoppe : ".PHP_EOL;
					$message .= $this->view->elementsRetires;
					$data = Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $this->view->tabEndroit[$idArrivee]["id_braldun_echoppe"], $message, $this->view);

					$messageCible = $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a transbahuté ces éléments dans votre échoppe : ".PHP_EOL;
					$messageCible .= $this->view->elementsRetires;
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a transbahuté des éléments dans l'échoppe de [b".$this->view->tabEndroit[$idArrivee]["id_braldun_echoppe"]."]";
					$this->setDetailsEvenementCible($this->view->tabEndroit[$idArrivee]["id_braldun_echoppe"], "braldun", 0, $messageCible);
				}
				else {
					$this->detailEvenement = "[b".$this->view->user->id_braldun."] a transbahuté des éléments dans son échoppe ";
				}
			}
			if ($this->detailEvenement == "") {
				$idEvenement = $this->view->config->game->evenements->type->transbahuter;
				$this->detailEvenement = "[b".$this->view->user->id_braldun."] a transbahuté des éléments ";
			}

			$this->setDetailsEvenement($this->detailEvenement, $idEvenement);
			$this->setEvenementQueSurOkJet1(false);

			Zend_Loader::loadClass("Bral_Util_Quete");
			$this->view->estQueteEvenement = Bral_Util_Quete::etapePosseder($this->view->user);

			$this->calculBalanceFaim();
			$this->calculPoids();
			$this->majBraldun();
		}
	}

	function getListBoxRefresh() {
		$lieu = new Lieu();
		$banque = $lieu->findByTypeAndCase(TypeLieu::ID_TYPE_BANQUE,$this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		$echoppe = new Echoppe();
		$echoppeCase = $echoppe->findByCase($this->view->user->x_braldun,$this->view->user->y_braldun, $this->view->user->z_braldun);
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

	private function controlePoids($poidsAutorise, $quantite, $poidsElt) {
		if (round($poidsAutorise,4) < intval($quantite) * floatval($poidsElt)) {
			return false;
		} else {
			return true;
		}
	}

	private function prepareType($depart) {
		$this->prepareTypeAutres($depart);
		$this->prepareTypeEquipements($depart);
		$this->prepareTypeRunes($depart);
		$this->prepareTypePotions($depart);
		$this->prepareTypeAliments($depart);
		$this->prepareTypeMunitions($depart);
		$this->prepareTypePartiesPlantes($depart);
		$this->prepareTypeMinerais($depart);
		$this->prepareTypeGraines($depart);
		$this->prepareTypeIngredients($depart);
		$this->prepareTypeTabac($depart);
		$this->prepareTypeMateriel($depart);
	}

	private function deposeType($depart,$arrivee) {
		$this->deposeTypeAutres($depart,$arrivee);
		$this->deposeTypeEquipements($depart,$arrivee);
		$this->deposeTypeRunes($depart,$arrivee);
		$this->deposeTypePotions($depart,$arrivee);
		$this->deposeTypeAliments($depart,$arrivee);
		$this->deposeTypeMunitions($depart,$arrivee);
		$this->deposeTypePartiesPlantes($depart,$arrivee);
		$this->deposeTypeMinerais($depart,$arrivee);
		$this->deposeTypeGraines($depart,$arrivee);
		$this->deposeTypeIngredients($depart,$arrivee);
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
				$equipements = $labanEquipementTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanEquipementTable);
				break;
			case "Element" :
				$elementEquipementTable = new ElementEquipement();
				$equipements = $elementEquipementTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementEquipementTable);
				break;
			case "Coffre" :
				$coffreEquipementTable = new CoffreEquipement();
				$equipements = $coffreEquipementTable->findByIdCoffre($this->view->id_coffre_depart);
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
						"poids" => $e["poids_equipement"],
						"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"], 
						"id_fk_recette" => $e["id_fk_recette_equipement"] ,
						"id_fk_type_munition_type_equipement" => $e["id_fk_type_munition_type_equipement"],
						"nb_munition_type_equipement" => $e["nb_munition_type_equipement"],
						"nom_systeme_type_piece" => $e["nom_systeme_type_piece"],
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
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					foreach ($equipements as $idEquipement) {
						if (!array_key_exists($idEquipement, $this->view->equipements)) {
							throw new Zend_Exception(get_class($this)." ID Equipement invalide : ".$idEquipement);
						}
						$equipement = $this->view->equipements[$idEquipement];
						$this->view->elementsNonRetiresPanneau .= "Equipement n°".$equipement["id_equipement"]." : ".$equipement["nom"].", ";
					}
				}
				else {
					foreach ($equipements as $idEquipement) {
						if (!array_key_exists($idEquipement, $this->view->equipements)) {
							throw new Zend_Exception(get_class($this)." ID Equipement invalide : ".$idEquipement);
						}

						$equipement = $this->view->equipements[$idEquipement];
						$poidsOk = true;
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant,1,$equipement["poids"]);
							if ($poidsOk == false) {
								$this->view->poidsOk = false;
								$this->view->elementsNonRetiresPoids .= "Equipement n°".$equipement["id_equipement"]." : ".$equipement["nom"].", ";
							}
						}
						if ($poidsOk == true) {
							$this->view->nbelement = $this->view->nbelement + 1;

							$where = "id_".strtolower($depart)."_equipement=".$idEquipement;
							switch ($depart) {
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

							switch ($arrivee) {
								case "Laban" :
									if ($equipement["nom_systeme_type_piece"] == "munition") {
										Zend_Loader::loadClass("LabanMunition");
										$arriveeEquipementTable = new LabanMunition();
										$data = array(
										"id_fk_braldun_laban_munition" => $this->view->user->id_braldun,
										"id_fk_type_laban_munition" => $equipement["id_fk_type_munition_type_equipement"],
										"quantite_laban_munition" => $equipement["nb_munition_type_equipement"],
										);
										$arriveeEquipementTable->insertOrUpdate($data);
									}
									else {
										$arriveeEquipementTable = new LabanEquipement();
										$data = array (
											"id_laban_equipement" => $equipement["id_equipement"],
											"id_fk_braldun_laban_equipement" => $this->view->user->id_braldun,
										);
										$arriveeEquipementTable->insert($data);
									}
									$this->view->poidsRestant = $this->view->poidsRestant - $equipement["poids"];
									break;
								case "Element" :
									$dateCreation = date("Y-m-d H:i:s");
									$nbJours = Bral_Util_De::get_2d10();
									$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

									if ($equipement["nom_systeme_type_piece"] == "munition") {
										Zend_Loader::loadClass("ElementMunition");
										$arriveeEquipementTable = new ElementMunition();
										$data = array(
										"x_element_munition" => $this->view->user->x_braldun,
										"y_element_munition" => $this->view->user->y_braldun,
										"z_element_munition" => $this->view->user->z_braldun,
										"date_fin_element_munition" => $dateFin,
										"id_fk_type_element_munition" => $equipement["id_fk_type_munition_type_equipement"],
										"quantite_element_munition" => $equipement["nb_munition_type_equipement"],
										);
										$arriveeEquipementTable->insertOrUpdate($data);
									}
									else {
										$arriveeEquipementTable = new ElementEquipement();
										$data = array (
											"id_element_equipement" => $equipement["id_equipement"],
											"x_element_equipement" => $this->view->user->x_braldun,
											"y_element_equipement" => $this->view->user->y_braldun,
											"z_element_equipement" => $this->view->user->z_braldun,
											"date_fin_element_equipement" => $dateFin,
										);
										$arriveeEquipementTable->insert($data);
									}
									break;
								case "Coffre" :
									$arriveeEquipementTable = new CoffreEquipement();
									$data = array (
										"id_coffre_equipement" => $equipement["id_equipement"],
										"id_fk_coffre_coffre_equipement" => $this->view->id_coffre_arrivee,
									);
									$arriveeEquipementTable->insert($data);
									break;
								case "Charrette" :
									if ($equipement["nom_systeme_type_piece"] == "munition") {
										Zend_Loader::loadClass("CharretteMunition");
										$arriveeEquipementTable = new CharretteMunition();
										$data = array(
										"id_fk_charrette_munition" => $this->view->id_charrette_arrivee,
										"id_fk_type_charrette_munition" => $equipement["id_fk_type_munition_type_equipement"],
										"quantite_charrette_munition" => $equipement["nb_munition_type_equipement"],
										);
										$arriveeEquipementTable->insertOrUpdate($data);
									}
									else {
										$arriveeEquipementTable = new CharretteEquipement();
										$data = array (
											"id_charrette_equipement" => $equipement["id_equipement"],
											"id_fk_charrette_equipement" => $this->view->id_charrette_arrivee,
										);
										$arriveeEquipementTable->insert($data);
									}
									break;
									/*case "Echoppe" :
									 $arriveeEquipementTable = new EchoppeEquipement();
									 $data = array (
										"id_echoppe_equipement" => $equipement["id_equipement"],
										"id_fk_echoppe_echoppe_equipement" => $this->view->id_echoppe_arrivee,
										);
										break;*/
							}
							unset($arriveeEquipementTable);
							$this->view->elementsRetires .= "Equipement n°".$equipement["id_equipement"]." : ".$equipement["nom"].", ";

							$texte = $this->calculTexte($depart, $arrivee);
							$details = "[b".$this->view->user->id_braldun."] a transbahuté la pièce d'équipement n°".$equipement["id_equipement"]. " (".$texte["departTexte"]." vers ".$texte["arriveeTexte"].")";
							Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_TRANSBAHUTER_ID, $equipement["id_equipement"], $details);
						}
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
					$runes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun);
					unset($labanRuneTable);
					break;
				case "Element" :
					$elementRuneTable = new ElementRune();
					$runes = $elementRuneTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, true, $this->tabButins);
					unset($elementruneTable);
					break;
				case "Coffre" :
					$coffreRuneTable = new CoffreRune();
					$runes = $coffreRuneTable->findByIdCoffre($this->view->id_coffre_depart);
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
						"est_identifiee" => $r["est_identifiee_rune"],
						"effet_type_rune" => $r["effet_type_rune"],
						"id_fk_type_rune" => $r["id_fk_type_rune"],
						"info" => "",
					);
					if ($depart == "Element" && $r["id_fk_butin_element_rune"] != null) {
						$tabRunes[$r["id_rune_".strtolower($depart)."_rune"]]["info"] = " (Butin n°".$r["id_fk_butin_element_rune"].")";
					}
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
			Zend_Loader::loadClass("Bral_Util_Rune");

			$runes = array();
			$runes = $this->request->get("valeur_14");
			if (count($runes) > 0 && $runes !=0 ) {
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					foreach ($runes as $idRune) {
						if (!array_key_exists($idRune, $this->view->runes)) {
							throw new Zend_Exception(get_class($this)." ID Rune invalide : ".$idRune);
						}
						$rune = $this->view->runes[$idRune];
						$nomRune = "non identifiée";
						if ($rune["est_identifiee"] == "oui") {
							$nomRune = $rune["type"];
						}
						$this->view->elementsNonRetiresPanneau .= "Rune n°".$rune["id_rune"]." : ".$nomRune.", ";
					}
				}
				else {
					foreach ($runes as $idRune) {
						if (!array_key_exists($idRune, $this->view->runes)) {
							throw new Zend_Exception(get_class($this)." ID Rune invalide : ".$idRune);
						}

						$rune = $this->view->runes[$idRune];
						$poidsOk = true;
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_RUNE);
							if ($poidsOk == false) {
								$this->view->poidsOk = false;
								$nomRune = "non identifiée";
								if ($rune["est_identifiee"] == "oui") {
									$nomRune = $rune["type"];
								}
								$this->view->elementsNonRetiresPoids .= "Rune n°".$rune["id_rune"]." : ".$nomRune.", ";
							}
						}
						if ($poidsOk == true) {
							$this->view->nbelement = $this->view->nbelement + 1;

							$where = "id_rune_".strtolower($depart)."_rune=".$idRune;

							switch ($depart) {
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

							switch ($arrivee) {
								case "Laban" :
									$arriveeRuneTable = new LabanRune();
									$data = array (
										"id_rune_laban_rune" => $rune["id_rune"],
										"id_fk_braldun_laban_rune" => $this->view->user->id_braldun,
									);
									$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_RUNE;
									break;
								case "Element" :
									$dateCreation = date("Y-m-d H:i:s");
									$nbJours = Bral_Util_De::get_2d10();
									$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

									$arriveeRuneTable = new ElementRune();
									$data = array (
										"id_rune_element_rune" => $rune["id_rune"],
										"x_element_rune" => $this->view->user->x_braldun,
										"y_element_rune" => $this->view->user->y_braldun,
										"z_element_rune" => $this->view->user->z_braldun,
										"date_fin_element_rune" => $dateFin,
									);
									break;
								case "Coffre" :
									$arriveeRuneTable = new CoffreRune();
									$data = array (
									"id_rune_coffre_rune" => $rune["id_rune"],
									"id_fk_coffre_coffre_rune" => $this->view->id_coffre_arrivee,
									);
									break;
								case "Charrette" :
									$arriveeRuneTable = new CharretteRune();
									$data = array (
									"id_rune_charrette_rune" => $rune["id_rune"],
									"id_fk_charrette_rune" => $this->view->id_charrette_arrivee,
									);
									break;
							}
							$arriveeRuneTable->insert($data);
							unset($arriveeRuneTable);
							$nomRune = "non identifiée";
							if ($rune["est_identifiee"] == "oui") {
								$nomRune = $rune["type"];
							}
							$this->view->elementsRetires .= "Rune n°".$rune["id_rune"]." : ".$nomRune.", ";

							$texte = $this->calculTexte($depart, $arrivee);
							$details = "[b".$this->view->user->id_braldun."] a transbahuté la rune n°".$rune["id_rune"]. " (".$texte["departTexte"]." vers ".$texte["arriveeTexte"].")";
							Bral_Util_Rune::insertHistorique(Bral_Util_Rune::HISTORIQUE_TRANSBAHUTER_ID, $rune["id_rune"], $details);
						}
					}
				}
			}
		}
	}

	private function prepareTypePotions($depart) {
		Zend_Loader::loadClass($depart."Potion");
		Zend_Loader::loadClass("Bral_Util_Potion");
		$tabPotions = null;

		switch ($depart) {
			case "Laban" :
				$labanPotionTable = new LabanPotion();
				$potions = $labanPotionTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanPotionTable);
				break;
			case "Element" :
				$elementPotionTable = new ElementPotion();
				$potions = $elementPotionTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementPotionTable);
				break;
			case "Coffre" :
				$coffrePotionTable = new CoffrePotion();
				$potions = $coffrePotionTable->findByIdCoffre($this->view->id_coffre_depart);
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

		$this->calculEchoppe("cuisinier");
		if (count($potions) > 0) {
			foreach ($potions as $p) {
				$tabPotions[$p["id_".strtolower($depart)."_potion"]] = array(
					"id_potion" => $p["id_".strtolower($depart)."_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
					"id_fk_type_qualite" => $p["id_fk_type_qualite_potion"],
					"id_fk_type" => $p["id_fk_type_potion"]
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->potions = $tabPotions;
	}

	private function deposeTypePotions($depart, $arrivee) {
		if ($arrivee != "Echoppe" ||
		($this->view->idEchoppe != null)) { // echoppe cuisinier calcule dans prepare{
			Zend_Loader::loadClass($depart."Potion");
			Zend_Loader::loadClass($arrivee."Potion");
			$potions = array();
			$potions = $this->request->get("valeur_15");
			if (count($potions) > 0 && $potions != 0) {
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					foreach ($potions as $idPotion) {
						if (!array_key_exists($idPotion, $this->view->potions)) {
							throw new Zend_Exception(get_class($this)." ID Potion invalide : ".$idPotion);
						}
						$potion = $this->view->potions[$idPotion];
						$this->view->elementsNonRetiresPanneau .= $potion["nom_type"]." ".$potion["nom"]. " n°".$potion["id_potion"].", ";
					}
				}
				else {
					foreach ($potions as $idPotion) {
						if (!array_key_exists($idPotion, $this->view->potions)) {
							throw new Zend_Exception(get_class($this)." ID Potion invalide : ".$idPotion);
						}

						$potion = $this->view->potions[$idPotion];
						$poidsOk = true;
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_POTION);
							if ($poidsOk == false) {
								$this->view->poidsOk = false;
								$this->view->elementsNonRetiresPoids .= $potion["nom_type"]." ".$potion["nom"]. " n°".$potion["id_potion"].", ";
							}
						}
						if ($poidsOk == true) {
							$this->view->nbelement = $this->view->nbelement + 1;
							$where = "id_".strtolower($depart)."_potion=".$idPotion;
							switch ($depart) {
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

							switch($arrivee) {
								case "Laban" :
									$arriveePotionTable = new LabanPotion();
									$data = array (
										"id_laban_potion" => $potion["id_potion"],
										"id_fk_braldun_laban_potion" => $this->view->user->id_braldun,
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
										"x_element_potion" => $this->view->user->x_braldun,
										"y_element_potion" => $this->view->user->y_braldun,
										"z_element_potion" => $this->view->user->z_braldun,
										"date_fin_element_potion" => $dateFin,
									);
									break;
								case "Coffre" :
									$arriveePotionTable = new CoffrePotion();
									$data = array (
										"id_coffre_potion" => $potion["id_potion"],
										"id_fk_coffre_coffre_potion" => $this->view->id_coffre_arrivee,
									);
									break;
								case "Charrette" :
									$arriveePotionTable = new CharrettePotion();
									$data = array (
										"id_charrette_potion" => $potion["id_potion"],
										"id_fk_charrette_potion" => $this->view->id_charrette_arrivee,
									);
									break;
								case "Echoppe" :
									// si le joueur est sur son echoppe de cuisinier
									if ($this->calculEchoppe("cuisinier")) {
										$arriveePotionTable = new EchoppePotion();
										$data = array (
										"id_echoppe_potion" => $potion["id_potion"],
										"id_fk_echoppe_echoppe_potion" => $this->view->id_echoppe_arrivee,
										);
										break;
									}
							}
							$arriveePotionTable->insert($data);
							unset($arriveePotionTable);
							$this->view->elementsRetires .= $potion["nom_type"]." ".$potion["nom"]. " n°".$potion["id_potion"].", ";

							$texte = $this->calculTexte($depart, $arrivee);
							$details = "[b".$this->view->user->id_braldun."] a transbahuté ".$potion["nom_type"]." ".$potion["nom"]. " n°".$potion["id_potion"]. " (".$texte["departTexte"]." vers ".$texte["arriveeTexte"].")";
							Bral_Util_Potion::insertHistorique(Bral_Util_Potion::HISTORIQUE_TRANSBAHUTER_ID, $potion["id_potion"], $details);
						}
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
					$aliments = $labanAlimentTable->findByIdBraldun($this->view->user->id_braldun);
					unset($labanAlimentTable);
					break;
				case "Element" :
					$elementAlimentTable = new ElementAliment();
					$aliments = $elementAlimentTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
					unset($elementAlimentTable);
					break;
				case "Coffre" :
					$coffreAlimentTable = new CoffreAliment();
					$aliments = $coffreAlimentTable->findByIdCoffre($this->view->id_coffre_depart);
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
								"bbdf" => $p["bbdf_aliment"],
								"id_fk_type_qualite" => $p["id_fk_type_qualite_aliment"],
								"id_fk_type" => $p["id_fk_type_aliment"]
					);
				}
				$this->view->deposerOk = true;
			}
			$this->view->aliments = $tabAliments;
		} else {
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
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					foreach ($aliments as $idAliment) {
						if (!array_key_exists($idAliment, $this->view->aliments)) {
							throw new Zend_Exception(get_class($this)." ID Aliment invalide : ".$idAliment);
						}
						$aliment = $this->view->aliments[$idAliment];
						$this->view->elementsNonRetiresPanneau .= "Aliment n°".$aliment["id_aliment"]." : ".$aliment["nom"]." +".$aliment["bbdf"]."%, ";
					}
				} else {
					foreach ($aliments as $idAliment) {
						if (!array_key_exists($idAliment, $this->view->aliments)) {
							throw new Zend_Exception(get_class($this)." ID Aliment invalide : ".$idAliment);
						}

						$aliment = $this->view->aliments[$idAliment];
						$poidsOk = true;
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_RATION);
							if ($poidsOk == false) {
								$this->view->poidsOk = false;
								$this->view->elementsNonRetiresPoids .= "Aliment n°".$aliment["id_aliment"]." : ".$aliment["nom"]." +".$aliment["bbdf"]."%, ";
							}
						}
						if ($poidsOk == true) {
							$this->view->nbelement = $this->view->nbelement + 1;
							$where = "id_".strtolower($depart)."_aliment=".$idAliment;
							switch ($depart) {
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

							switch ($arrivee) {
								case "Laban" :
									$arriveeAlimentTable = new LabanAliment();
									$data = array (
										"id_laban_aliment" => $aliment["id_aliment"],
										"id_fk_braldun_laban_aliment" => $this->view->user->id_braldun,
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
												"x_element_aliment" => $this->view->user->x_braldun,
												"y_element_aliment" => $this->view->user->y_braldun,
												"z_element_aliment" => $this->view->user->z_braldun,
												"date_fin_element_aliment" => $dateFin,
									);
									break;
								case "Coffre" :
									$arriveeAlimentTable = new CoffreAliment();
									$data = array (
										"id_coffre_aliment" => $aliment["id_aliment"],
										"id_fk_coffre_coffre_aliment" => $this->view->id_coffre_arrivee,
									);
									break;
								case "Charrette" :
									$arriveeAlimentTable = new CharretteAliment();
									$data = array (
										"id_charrette_aliment" => $aliment["id_aliment"],
										"id_fk_charrette_aliment" => $this->view->id_charrette_arrivee,
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
	}

	private function prepareTypeMunitions($depart) {
		if ($depart != "Echoppe") {
			Zend_Loader::loadClass($depart."Munition");

			$tabMunitions = null;

			switch ($depart) {
				case "Laban" :
					$labanMunitionTable = new LabanMunition();
					$munitions = $labanMunitionTable->findByIdBraldun($this->view->user->id_braldun);
					unset($labanMunitionTable);
					break;
				case "Element" :
					$elementMunitionTable = new ElementMunition();
					$munitions = $elementMunitionTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
					unset($elementMunitionTable);
					break;
				case "Coffre" :
					$coffreMunitionTable = new CoffreMunition();
					$munitions = $coffreMunitionTable->findByIdCoffre($this->view->id_coffre_depart);
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
							"type_pluriel" => $m["nom_pluriel_type_munition"],
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


						if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
							$this->view->panneau = false;
							$this->view->elementsNonRetiresPanneau .= $nbMunition." ".$munition["type_pluriel"].", ";
						}

						$poidsOk = true;
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, $nbMunition, Bral_Util_Poids::POIDS_MUNITION);
							if ($poidsOk == false) {
								$this->view->poidsOk = false;
								$this->view->elementsNonRetiresPoids .= $nbMunition." ".$munition["type_pluriel"].", ";
							}
						}

						if ($poidsOk == true && $this->view->panneau != false) {

							$this->view->nbelement = $this->view->nbelement + 1;

							switch ($depart) {
								case "Laban" :
									$departMunitionTable = new LabanMunition();
									$data = array(
											"quantite_laban_munition" => -$nbMunition,
											"id_fk_type_laban_munition" => $munition["id_type_munition"],
											"id_fk_braldun_laban_munition" => $this->view->user->id_braldun,
									);
									break;
								case "Element" :
									$departMunitionTable = new ElementMunition();
									$data = array (
											"x_element_munition" => $this->view->user->x_braldun,
											"y_element_munition" => $this->view->user->y_braldun,
											"z_element_munition" => $this->view->user->z_braldun,
											"id_fk_type_element_munition" => $munition["id_type_munition"],
											"quantite_element_munition" => -$nbMunition,
									);
									break;
								case "Coffre" :
									$departMunitionTable = new CoffreMunition();
									$data = array(
											"quantite_coffre_munition" => -$nbMunition,
											"id_fk_type_coffre_munition" => $munition["id_type_munition"],
											"id_fk_coffre_coffre_munition" => $this->view->id_coffre_depart,
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

							switch ($arrivee) {
								case "Laban" :
									$arriveeMunitionTable = new LabanMunition();
									$data = array(
											"quantite_laban_munition" => $nbMunition,
											"id_fk_type_laban_munition" => $munition["id_type_munition"],
											"id_fk_braldun_laban_munition" => $this->view->user->id_braldun,
									);
									$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MUNITION * $nbMunition;
									break;
								case "Element" :
									$dateCreation = date("Y-m-d H:i:s");
									$nbJours = Bral_Util_De::get_2d10();
									$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

									$arriveeMunitionTable = new ElementMunition();
									$data = array (
										"x_element_munition" => $this->view->user->x_braldun,
										"y_element_munition" => $this->view->user->y_braldun,
										"z_element_munition" => $this->view->user->z_braldun,
										"id_fk_type_element_munition" => $munition["id_type_munition"],
										"quantite_element_munition" => $nbMunition,
										'date_fin_element_munition' => $dateFin,
									);
									break;
								case "Coffre" :
									$arriveeMunitionTable = new CoffreMunition();
									$data = array(
											"quantite_coffre_munition" => $nbMunition,
											"id_fk_type_coffre_munition" => $munition["id_type_munition"],
											"id_fk_coffre_coffre_munition" => $this->view->id_coffre_arrivee,
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
							if ($nbMunition > 1) {
								$this->view->elementsRetires .= $nbMunition." ".$munition["type_pluriel"].", ";
							}
							else {
								$this->view->elementsRetires .= $nbMunition." ".$munition["type"].", ";
							}
						}
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
				$minerais = $labanMineraiTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanMineraiTable);
				break;
			case "Element" :
				$elementMineraiTable = new ElementMinerai();
				$minerais = $elementMineraiTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementMineraiTable);
				break;
			case "Coffre" :
				$coffreMineraiTable = new CoffreMinerai();
				$minerais = $coffreMineraiTable->findByIdCoffre($this->view->id_coffre_depart);
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
			$sbrut = "";
			$slingot = "";
			if ($nbBrut > 1) $sbrut = "s";
			if ($nbLingot > 1) $slingot = "s";

			if ($nbBrut < 0) $nbBrut = 0;
			if ($nbLingot < 0) $nbLingot = 0;

			if ($nbBrut > 0 || $nbLingot > 0) {
				if ($depart == "Charrette" && $this->view->a_panneau === false && ( $this->view->nbelement > 0 || ($nbBrut > 0 && $nbLingot > 0))) {
					$this->view->panneau = false;
					$this->view->elementsNonRetiresPanneau .= $this->view->minerais[$indice]["type"]. " : ".$nbBrut. " minerai".$sbrut." brut".$sbrut." et ".$nbLingot." lingot".$slingot.",";
				}

				$poidsOk = true;
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk1 = $this->controlePoids($this->view->poidsRestant, $nbBrut, Bral_Util_Poids::POIDS_MINERAI);
					$poidsOk2 = $this->controlePoids($this->view->poidsRestant, $nbLingot, Bral_Util_Poids::POIDS_LINGOT);
					if ($poidsOk1 == false || $poidsOk2 == false) {
						$this->view->poidsOk = false;
						$poidsOk = false;
						$this->view->elementsNonRetiresPoids .= $this->view->minerais[$indice]["type"]. " : ".$nbBrut. " minerai".$sbrut." brut".$sbrut." et ".$nbLingot." lingot".$slingot.",";
					}
				}

				if ($poidsOk == true && $this->view->panneau != false) {

					$this->view->nbelement = $this->view->nbelement + 1;

					switch ($depart) {
						case "Laban" :
							$departMineraiTable = new LabanMinerai();
							$data = array(
								'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_minerai"],
								'id_fk_braldun_laban_minerai' => $this->view->user->id_braldun,
								'quantite_brut_laban_minerai' => -$nbBrut,
								'quantite_lingots_laban_minerai' => -$nbLingot,
							);
							break;
						case "Element" :
							$departMineraiTable = new ElementMinerai();
							$data = array (
								"x_element_minerai" => $this->view->user->x_braldun,
								"y_element_minerai" => $this->view->user->y_braldun,
								"z_element_minerai" => $this->view->user->z_braldun,
								"id_fk_type_element_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
								"quantite_brut_element_minerai" => -$nbBrut,
								"quantite_lingots_element_minerai" => -$nbLingot,
							);
							break;
						case "Coffre" :
							$departMineraiTable = new CoffreMinerai();
							$data = array (
								"id_fk_coffre_coffre_minerai" => $this->view->id_coffre_depart,
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

					switch ($arrivee) {
						case "Laban" :
							$arriveeMineraiTable = new LabanMinerai();
							$data = array(
								"quantite_brut_laban_minerai" => $nbBrut,
								"quantite_lingots_laban_minerai" => $nbLingot,
								"id_fk_type_laban_minerai" => $this->view->minerais[$indice]["id_fk_type_minerai"],
								"id_fk_braldun_laban_minerai" => $this->view->user->id_braldun,
							);
							$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MINERAI * $nbBrut - Bral_Util_Poids::POIDS_LINGOT * $nbLingot;
							break;
						case "Element" :
							$dateCreation = date("Y-m-d H:i:s");
							$nbJours = Bral_Util_De::get_2d10();
							$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

							$arriveeMineraiTable = new ElementMinerai();
							$data = array("x_element_minerai" => $this->view->user->x_braldun,
								  "y_element_minerai" => $this->view->user->y_braldun,
								  "z_element_minerai" => $this->view->user->z_braldun,
								  'quantite_brut_element_minerai' => $nbBrut,
								  'quantite_lingots_element_minerai' => $nbLingot,
								  'id_fk_type_element_minerai' => $this->view->minerais[$indice]["id_fk_type_minerai"],
								  'date_fin_element_minerai' => $dateFin,
							);
							break;
						case "Coffre" :
							$arriveeMineraiTable = new CoffreMinerai();
							$data = array (
								"id_fk_coffre_coffre_minerai" => $this->view->id_coffre_arrivee,
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

					$this->view->elementsRetires .= $this->view->minerais[$indice]["type"]. " : ".$nbBrut. " minerai".$sbrut." brut".$sbrut." et ".$nbLingot." lingot".$slingot.", ";
				}
			}
		}
	}

	private function prepareTypePartiesPlantes($depart) {
		Zend_Loader::loadClass($depart."Partieplante");

		$tabPartiePlantes = null;

		switch ($depart) {
			case "Laban" :
				$labanPartiePlanteTable = new LabanPartieplante();
				$partiePlantes = $labanPartiePlanteTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanPartiePlanteTable);
				break;
			case "Element" :
				$elementPartiePlanteTable = new ElementPartieplante();
				$partiePlantes = $elementPartiePlanteTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementPartiePlanteTable);
				break;
			case "Coffre" :
				$coffrePartiePlanteTable = new CoffrePartieplante();
				$partiePlantes = $coffrePartiePlanteTable->findByIdCoffre($this->view->id_coffre_depart);
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

			$sbrute = "";
			$spreparee = "";
			if ($nbBrutes > 1) $sbrute = "s";
			if ($nbPreparees > 1) $spreparee = "s";

			if ($nbBrutes > 0 || $nbPreparees > 0) {

				if ($depart == "Charrette" && $this->view->a_panneau === false && ( $this->view->nbelement > 0 || ($nbBrutes > 0 && $nbPreparees > 0))) {
					$this->view->panneau = false;
					$this->view->elementsNonRetiresPanneau .= $this->view->partieplantes[$indice]["nom_plante"]. " : ";
					$this->view->elementsNonRetiresPanneau .= $nbBrutes. " ".$this->view->partieplantes[$indice]["nom_type"]. " brute".$sbrute;
					$this->view->elementsNonRetiresPanneau .=  " et ".$nbPreparees. " ".$this->view->partieplantes[$indice]["nom_type"]. " préparée".$spreparee;
					$this->view->elementsNonRetiresPanneau .= ", ";
				}

				$poidsOk = true;
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk1 = $this->controlePoids($this->view->poidsRestant, $nbBrutes, Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
					$poidsOk2 = $this->controlePoids($this->view->poidsRestant, $nbPreparees, Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE);
					if ($poidsOk1 == false || $poidsOk2 == false) {
						$this->view->poidsOk = false;
						$poidsOk = false;
						$this->view->elementsNonRetiresPoids .= $this->view->partieplantes[$indice]["nom_plante"]. " : ";
						$this->view->elementsNonRetiresPoids .= $nbBrutes. " ".$this->view->partieplantes[$indice]["nom_type"]. " brute".$sbrute;
						$this->view->elementsNonRetiresPoids .=  " et ".$nbPreparees. " ".$this->view->partieplantes[$indice]["nom_type"]. " préparée".$spreparee;
						$this->view->elementsNonRetiresPoids .= ", ";
					}
				}

				if ($poidsOk == true && $this->view->panneau != false) {

					$this->view->nbelement = $this->view->nbelement + 1;

					switch ($depart) {
						case "Laban" :
							$departPartiePlanteTable = new LabanPartieplante();
							$data = array(
								'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
								'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
								'id_fk_braldun_laban_partieplante' => $this->view->user->id_braldun,
								'quantite_laban_partieplante' => -$nbBrutes,
								'quantite_preparee_laban_partieplante' => -$nbPreparees
							);
							break;
						case "Element" :
							$departPartiePlanteTable = new ElementPartieplante();
							$data = array (
									"x_element_partieplante" => $this->view->user->x_braldun,
									"y_element_partieplante" => $this->view->user->y_braldun,
									"z_element_partieplante" => $this->view->user->z_braldun,
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
								'id_fk_coffre_coffre_partieplante' => $this->view->id_coffre_depart,
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

					switch ($arrivee) {
						case "Laban" :
							$arriveePartiePlanteTable = new LabanPartieplante();
							$data = array (
								"id_fk_braldun_laban_partieplante" => $this->view->user->id_braldun,
								"id_fk_type_laban_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
								"id_fk_type_plante_laban_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
								"quantite_laban_partieplante" => $nbBrutes,
								"quantite_preparee_laban_partieplante" => $nbPreparees,
							);
							$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE * $nbBrutes - Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE * $nbPreparees;
							break;
						case "Element" :
							$dateCreation = date("Y-m-d H:i:s");
							$nbJours = Bral_Util_De::get_2d10();
							$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

							$arriveePartiePlanteTable = new ElementPartieplante();
							$data = array("x_element_partieplante" => $this->view->user->x_braldun,
								  "y_element_partieplante" => $this->view->user->y_braldun,
								  "z_element_partieplante" => $this->view->user->z_braldun,
								  'quantite_element_partieplante' => $nbBrutes,
								  'quantite_preparee_element_partieplante' => $nbPreparees,
								  'id_fk_type_element_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_partieplante"],
								  'id_fk_type_plante_element_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_partieplante"],
								  'date_fin_element_partieplante' => $dateFin,
							);
							break;
						case "Coffre" :
							$arriveePartiePlanteTable = new CoffrePartieplante();
							$data = array (
								"id_fk_coffre_coffre_partieplante" => $this->view->id_coffre_arrivee,
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
					$this->view->elementsRetires .= $this->view->partieplantes[$indice]["nom_plante"]. " : ";
					$this->view->elementsRetires .= $nbBrutes. " ".$this->view->partieplantes[$indice]["nom_type"]. " brute".$sbrute;
					$this->view->elementsRetires .=  " et ".$nbPreparees. " ".$this->view->partieplantes[$indice]["nom_type"]. " préparée".$spreparee;
					$this->view->elementsRetires .= ", ";
				}
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
					$tabacs = $labanTabacTable->findByIdBraldun($this->view->user->id_braldun);
					unset($labanTabacTable);
					break;
				case "Element" :
					$elementTabacTable = new ElementTabac();
					$tabacs = $elementTabacTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
					unset($elementTabacTable);
					break;
				case "Coffre" :
					$coffreTabacTable = new CoffreTabac();
					$tabacs = $coffreTabacTable->findByIdCoffre($this->view->id_coffre_depart);
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
		} else {
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

				for ($i=$this->view->valeur_fin_ingredients + 1; $i<=$this->view->valeur_fin_tabacs; $i++) {

					if ( $this->request->get("valeur_".$i) > 0) {
						$nbTabac = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_".$i));
							
						$tabac = $this->view->tabacs[$i];

						if ($nbTabac > $tabac["quantite"] || $nbTabac < 0) {
							throw new Zend_Exception(get_class($this)." Quantite Tabac invalide : ".$nbTabac. " i=".$i);
						}

						if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
							$this->view->panneau = false;
							$this->view->elementsNonRetiresPanneau .= $nbTabac." feuille".$stabac." de ".$tabac["type"].", ";
						}

						$poidsOk=true;
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant, $nbTabac, Bral_Util_Poids::POIDS_TABAC);
							if ($poidsOk == false) {
								$this->view->poidsOk = false;
								$this->view->elementsNonRetiresPoids .= $nbTabac." feuille".$stabac." de ".$tabac["type"].", ";
							}
						}

						if ($poidsOk == true && $this->view->panneau != false) {

							$this->view->nbelement = $this->view->nbelement + 1;
							switch ($depart) {
								case "Laban" :
									$departTabacTable = new LabanTabac();
									$data = array(
											"quantite_feuille_laban_tabac" => -$nbTabac,
											"id_fk_type_laban_tabac" => $tabac["id_type_tabac"],
											"id_fk_braldun_laban_tabac" => $this->view->user->id_braldun,
									);
									break;
								case "Element" :
									$departTabacTable = new ElementTabac();
									$data = array (
											"x_element_tabac" => $this->view->user->x_braldun,
											"y_element_tabac" => $this->view->user->y_braldun,
											"z_element_tabac" => $this->view->user->z_braldun,
											"id_fk_type_element_tabac" => $tabac["id_type_tabac"],
											"quantite_feuille_element_tabac" => -$nbTabac,
									);
									break;
								case "Coffre" :
									$departTabacTable = new CoffreTabac();
									$data = array(
											"quantite_feuille_coffre_tabac" => -$nbTabac,
											"id_fk_type_coffre_tabac" => $tabac["id_type_tabac"],
											"id_fk_coffre_coffre_tabac" => $this->view->id_coffre_depart,
									);
									break;
								case "Charrette" :
									$departTabacTable = new CharretteTabac();
									$data = array(
											"quantite_feuille_charrette_tabac" => -$nbTabac,
											"id_fk_type_charrette_tabac" => $tabac["id_type_tabac"],
											"id_fk_charrette_tabac" => $this->view->id_charrette_depart,
									);
									break;
							}

							$departTabacTable->insertOrUpdate($data);
							unset ($departTabacTable);

							switch ($arrivee) {
								case "Laban" :
									$arriveeTabacTable = new LabanTabac();
									$data = array(
											"quantite_feuille_laban_tabac" => $nbTabac,
											"id_fk_type_laban_tabac" => $tabac["id_type_tabac"],
											"id_fk_braldun_laban_tabac" => $this->view->user->id_braldun,
									);
									$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MUNITION * $nbTabac;
									break;
								case "Element" :
									$arriveeTabacTable = new ElementTabac();
									$data = array (
										"x_element_tabac" => $this->view->user->x_braldun,
										"y_element_tabac" => $this->view->user->y_braldun,
										"z_element_tabac" => $this->view->user->z_braldun,
										"id_fk_type_element_tabac" => $tabac["id_type_tabac"],
										"quantite_feuille_element_tabac" => $nbTabac,
									);
									break;
								case "Coffre" :
									$arriveeTabacTable = new CoffreTabac();
									$data = array(
											"quantite_feuille_coffre_tabac" => $nbTabac,
											"id_fk_type_coffre_tabac" => $tabac["id_type_tabac"],
											"id_fk_coffre_coffre_tabac" => $this->view->id_coffre_arrivee,
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
							$stabac = "";
							if ($nbTabac > 1) $stabac = "s";
							$this->view->elementsRetires.= $nbTabac." feuille".$stabac." de ".$tabac["type"].", ";
						}
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
				$materiels = $labanMaterielTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanMaterielTable);
				break;
			case "Element" :
				$elementMaterielTable = new ElementMateriel();
				$materiels = $elementMaterielTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementMaterielTable);
				break;
			case "Coffre" :
				$coffreMaterielTable = new CoffreMateriel();
				$materiels = $coffreMaterielTable->findByIdCoffre($this->view->id_coffre_depart);
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
						"id_fk_type_materiel" => $e["id_fk_type_materiel"],
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
			Zend_Loader::loadClass("Bral_Util_Materiel");

			$materiels = array();
			$materiels = $this->request->get("valeur_16");

			if (count($materiels) > 0 && $materiels != 0) {
				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					foreach ($materiels as $idMateriel) {
						if (!array_key_exists($idMateriel, $this->view->materiels)) {
							throw new Zend_Exception(get_class($this)." ID Materiel invalide : ".$idMateriel);
						}

						$materiel = $this->view->materiels[$idMateriel];
						$this->view->elementsNonRetiresPanneau .= "Matériel n°".$materiel["id_materiel"]." : ".$materiel["nom"].", ";
					}
				} else {
					foreach ($materiels as $idMateriel) {
						if (!array_key_exists($idMateriel, $this->view->materiels)) {
							throw new Zend_Exception(get_class($this)." ID Materiel invalide : ".$idMateriel);
						}

						$materiel = $this->view->materiels[$idMateriel];

						$poidsOk = true;
						if ($arrivee == "Laban" || $arrivee == "Charrette") {
							$poidsOk = $this->controlePoids($this->view->poidsRestant,1,$materiel["poids"]);
							if ($poidsOk == false) {
								$this->view->poidsOk = false;
								$this->view->elementsNonRetiresPoids .= "Matériel n°".$materiel["id_materiel"]." : ".$materiel["nom"].", ";
							}
						}

						if ($poidsOk == true) {

							$this->view->nbelement = $this->view->nbelement + 1;
							$where = "id_".strtolower($depart)."_materiel=".$idMateriel;
							switch ($depart) {
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

							switch ($arrivee) {
								case "Laban" :
									$arriveeMaterielTable = new LabanMateriel();
									$data = array (
										"id_laban_materiel" => $materiel["id_materiel"],
										"id_fk_braldun_laban_materiel" => $this->view->user->id_braldun,
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
										"x_element_materiel" => $this->view->user->x_braldun,
										"y_element_materiel" => $this->view->user->y_braldun,
										"z_element_materiel" => $this->view->user->z_braldun,
										"date_fin_element_materiel" => $dateFin,
									);
									break;
								case "Coffre" :
									$arriveeMaterielTable = new CoffreMateriel();
									$data = array (
										"id_coffre_materiel" => $materiel["id_materiel"],
										"id_fk_coffre_coffre_materiel" => $this->view->id_coffre_arrivee,
									);
									break;
								case "Charrette" :
									$arriveeMaterielTable = new CharretteMateriel();
									$data = array (
										"id_charrette_materiel" => $materiel["id_materiel"],
										"id_fk_charrette_materiel" => $this->view->id_charrette_arrivee,
									);
									break;
									/*case "Echoppe" :
									 $arriveeMaterielTable = new EchoppeMateriel();
									 $data = array (
										"id_echoppe_materiel" => $materiel["id_materiel"],
										"id_fk_echoppe_echoppe_materiel" => $this->view->id_echoppe_arrivee,
										);
										break;*/
							}
							$arriveeMaterielTable->insert($data);
							unset($arriveeMaterielTable);
							$this->view->elementsRetires .= "Matériel n°".$materiel["id_materiel"]." : ".$materiel["nom"].", ";

							$texte = $this->calculTexte($depart, $arrivee);
							$details = "[b".$this->view->user->id_braldun."] a transbahuté le matériel n°".$materiel["id_materiel"]. " (".$texte["departTexte"]." vers ".$texte["arriveeTexte"].")";
							Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_TRANSBAHUTER_ID, $materiel["id_materiel"], $details);
						}
					}
				}
			}
		}
	}

	private function prepareTypeGraines($depart) {
		Zend_Loader::loadClass($depart."Graine");

		$tabGraines = null;

		switch ($depart) {
			case "Laban" :
				$labanGraineTable = new LabanGraine();
				$graines = $labanGraineTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanGraineTable);
				break;
			case "Element" :
				$elementGraineTable = new ElementGraine();
				$graines = $elementGraineTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementGraineTable);
				break;
			case "Coffre" :
				$coffreGraineTable = new CoffreGraine();
				$graines = $coffreGraineTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffreGraineTable);
				break;
			case "Charrette" :
				$charretteGraineTable = new CharretteGraine();
				$graines = $charretteGraineTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteGraineTable);
				break;
			case "Echoppe" :
				$echoppeGraineTable = new EchoppeGraine();
				$graines = $echoppeGraineTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeGraineTable);
				break;
		}

		$this->view->nb_graine = 0;

		if ($graines != null) {
			if ($depart == "Echoppe") {
				$strqte = "arriere_echoppe";
			} else {
				$strqte = $depart;
			}
			foreach ($graines as $m) {
				if ($m["quantite_".strtolower($strqte)."_graine"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabGraines[$this->view->nb_valeurs] = array(
						"type" => $m["nom_type_graine"],
						"id_fk_type_graine" => $m["id_fk_type_".strtolower($depart)."_graine"],
						"quantite_graine" => $m["quantite_".strtolower($strqte)."_graine"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_graine = $this->view->nb_graine + $m["quantite_".strtolower($strqte)."_graine"];
				}
			}
		}
		$this->view->valeur_fin_graines = $this->view->nb_valeurs;
		$this->view->graines = $tabGraines;
	}

	private function deposeTypeGraines($depart,$arrivee) {
		Zend_Loader::loadClass($depart."Graine");
		Zend_Loader::loadClass($arrivee."Graine");

		for ($i=$this->view->valeur_fin_minerais + 1; $i<=$this->view->valeur_fin_graines; $i++) {
			$indice = $i;
			$nb = $this->request->get("valeur_".$indice);

			if ((int) $nb."" != $this->request->get("valeur_".$indice)."") {
				throw new Zend_Exception(get_class($this)." NB Graine invalide=".$nb. " indice=".$indice);
			} else {
				$nb = (int)$nb;
			}
			if ($nb > $this->view->graines[$indice]["quantite_graine"]) {
				throw new Zend_Exception(get_class($this)." NB Graine interdit=".$nb);
			}

			if ($nb > 0) {

				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					$this->view->elementsNonRetiresPanneau .= $this->view->graines[$indice]["type"]. " : ".$nb. " poignée".$s." de graines, ";
				}

				$poidsOk = true;
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk = $this->controlePoids($this->view->poidsRestant, $nb, Bral_Util_Poids::POIDS_POIGNEE_GRAINES);
					if ($poidsOk == false) {
						$this->view->poidsOk = false;
						$this->view->elementsNonRetiresPoids .= $this->view->graines[$indice]["type"]. " : ".$nb. " poignée".$s." de graines, ";
					}
				}

				if ($poidsOk == true && $this->view->panneau != false) {

					$this->view->nbelement = $this->view->nbelement + 1;
					switch ($depart) {
						case "Laban" :
							$departGraineTable = new LabanGraine();
							$data = array(
								'id_fk_type_laban_graine' => $this->view->graines[$indice]["id_fk_type_graine"],
								'id_fk_braldun_laban_graine' => $this->view->user->id_braldun,
								'quantite_laban_graine' => -$nb,
							);
							break;
						case "Element" :
							$departGraineTable = new ElementGraine();
							$data = array (
								"x_element_graine" => $this->view->user->x_braldun,
								"y_element_graine" => $this->view->user->y_braldun,
								"z_element_graine" => $this->view->user->z_braldun,
								"id_fk_type_element_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"quantite_element_graine" => -$nb,
							);
							break;
						case "Coffre" :
							$departGraineTable = new CoffreGraine();
							$data = array (
								"id_fk_coffre_coffre_graine" => $this->view->id_coffre_depart,
								"id_fk_type_coffre_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"quantite_coffre_graine" => -$nb,
							);
							break;
						case "Charrette" :
							$departGraineTable = new CharretteGraine();
							$data = array (
								"id_fk_charrette_graine" => $this->view->id_charrette_depart,
								"id_fk_type_charrette_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"quantite_charrette_graine" => -$nb,
							);
							break;
						case "Echoppe" :
							$departGraineTable = new EchoppeGraine();
							$data = array (
								"id_fk_echoppe_echoppe_graine" => $this->view->id_echoppe_depart,
								"id_fk_type_echoppe_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"quantite_arriere_echoppe_graine" => -$nb,
							);
							break;
					}
					$departGraineTable->insertOrUpdate($data);
					unset ($departGraineTable);

					switch ($arrivee) {
						case "Laban" :
							$arriveeGraineTable = new LabanGraine();
							$data = array(
								"quantite_laban_graine" => $nb,
								"id_fk_type_laban_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"id_fk_braldun_laban_graine" => $this->view->user->id_braldun,
							);
							$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_POIGNEE_GRAINES * $nb;
							break;
						case "Element" :
							$dateCreation = date("Y-m-d H:i:s");
							$nbJours = Bral_Util_De::get_2d10();
							$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

							$arriveeGraineTable = new ElementGraine();
							$data = array("x_element_graine" => $this->view->user->x_braldun,
								  "y_element_graine" => $this->view->user->y_braldun,
								  "z_element_graine" => $this->view->user->z_braldun,
								  'quantite_element_graine' => $nb,
								  'id_fk_type_element_graine' => $this->view->graines[$indice]["id_fk_type_graine"],
								  'date_fin_element_graine' => $dateFin,
							);
							break;
						case "Coffre" :
							$arriveeGraineTable = new CoffreGraine();
							$data = array (
								"id_fk_coffre_coffre_graine" => $this->view->id_coffre_arrivee,
								"id_fk_type_coffre_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"quantite_coffre_graine" => $nb,
							);
							break;
						case "Charrette" :
							$arriveeGraineTable = new CharretteGraine();
							$data = array (
								"id_fk_charrette_graine" => $this->view->id_charrette_arrivee,
								"id_fk_type_charrette_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"quantite_charrette_graine" => $nb,
							);
							break;
							/*
							 case "Echoppe" :
							 $arriveeGraineTable = new EchoppeGraine();
							 $data = array (
								"id_fk_echoppe_echoppe_graine" => $this->view->id_echoppe_arrivee,
								"id_fk_type_echoppe_graine" => $this->view->graines[$indice]["id_fk_type_graine"],
								"quantite_arriere_echoppe_graine" => $nb,
								);
								break;*/
					}
					$arriveeGraineTable->insertOrUpdate($data);
					unset ($arriveeGraineTable);
					$s = "";
					if ($nb > 1) $s = "s";
					$this->view->elementsRetires .= $this->view->graines[$indice]["type"]. " : ".$nb. " poignée".$s." de graines, ";
				}
			}
		}
	}

	private function prepareTypeIngredients($depart) {
		Zend_Loader::loadClass($depart."Ingredient");

		$tabIngredients = null;

		switch ($depart) {
			case "Laban" :
				$labanIngredientTable = new LabanIngredient();
				$ingredients = $labanIngredientTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanIngredientTable);
				break;
			case "Element" :
				$elementIngredientTable = new ElementIngredient();
				$ingredients = $elementIngredientTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementIngredientTable);
				break;
			case "Coffre" :
				$coffreIngredientTable = new CoffreIngredient();
				$ingredients = $coffreIngredientTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffreIngredientTable);
				break;
			case "Charrette" :
				$charretteIngredientTable = new CharretteIngredient();
				$ingredients = $charretteIngredientTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteIngredientTable);
				break;
			case "Echoppe" :
				$echoppeIngredientTable = new EchoppeIngredient();
				$ingredients = $echoppeIngredientTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeIngredientTable);
				break;
		}

		$this->view->nb_ingredient = 0;

		if ($ingredients != null) {
			if ($depart == "Echoppe") {
				$strqte = "arriere_echoppe";
			} else {
				$strqte = $depart;
			}
			foreach ($ingredients as $m) {
				if ($m["quantite_".strtolower($strqte)."_ingredient"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabIngredients[$this->view->nb_valeurs] = array(
						"type" => $m["nom_type_ingredient"],
						"id_fk_type_ingredient" => $m["id_fk_type_".strtolower($depart)."_ingredient"],
						"quantite_ingredient" => $m["quantite_".strtolower($strqte)."_ingredient"],
						"poids_unitaire_ingredient" => $m["poids_unitaire_type_ingredient"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_ingredient = $this->view->nb_ingredient + $m["quantite_".strtolower($strqte)."_ingredient"];
				}
			}
		}
		$this->view->valeur_fin_ingredients = $this->view->nb_valeurs;
		$this->view->ingredients = $tabIngredients;
	}

	private function deposeTypeIngredients($depart,$arrivee) {
		Zend_Loader::loadClass($depart."Ingredient");
		Zend_Loader::loadClass($arrivee."Ingredient");

		for ($i=$this->view->valeur_fin_graines + 1; $i<=$this->view->valeur_fin_ingredients; $i++) {
			$indice = $i;
			$nb = $this->request->get("valeur_".$indice);

			if ((int) $nb."" != $this->request->get("valeur_".$indice)."") {
				throw new Zend_Exception(get_class($this)." NB Ingredient invalide=".$nb. " indice=".$indice);
			} else {
				$nb = (int)$nb;
			}
			if ($nb > $this->view->ingredients[$indice]["quantite_ingredient"]) {
				throw new Zend_Exception(get_class($this)." NB Ingredient interdit=".$nb);
			}

			if ($nb > 0) {

				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					$this->view->elementsNonRetiresPanneau .= $this->view->ingredients[$indice]["type"]. " : ".$nb.", ";
				}

				$poidsOk = true;
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk = $this->controlePoids($this->view->poidsRestant, $nb, Bral_Util_Poids::POIDS_POIGNEE_GRAINES);
					if ($poidsOk == false) {
						$this->view->poidsOk = false;
						$this->view->elementsNonRetiresPoids .= $this->view->ingredients[$indice]["type"]. " : ".$nb.", ";
					}
				}

				if ($poidsOk == true && $this->view->panneau != false) {
					$this->view->nbelement = $this->view->nbelement + 1;
					switch ($depart) {
						case "Laban" :
							$departIngredientTable = new LabanIngredient();
							$data = array(
								'id_fk_type_laban_ingredient' => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								'id_fk_braldun_laban_ingredient' => $this->view->user->id_braldun,
								'quantite_laban_ingredient' => -$nb,
							);
							break;
						case "Element" :
							$departIngredientTable = new ElementIngredient();
							$data = array (
								"x_element_ingredient" => $this->view->user->x_braldun,
								"y_element_ingredient" => $this->view->user->y_braldun,
								"z_element_ingredient" => $this->view->user->z_braldun,
								"id_fk_type_element_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"quantite_element_ingredient" => -$nb,
							);
							break;
						case "Coffre" :
							$departIngredientTable = new CoffreIngredient();
							$data = array (
								"id_fk_coffre_coffre_ingredient" => $this->view->id_coffre_depart,
								"id_fk_type_coffre_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"quantite_coffre_ingredient" => -$nb,
							);
							break;
						case "Charrette" :
							$departIngredientTable = new CharretteIngredient();
							$data = array (
								"id_fk_charrette_ingredient" => $this->view->id_charrette_depart,
								"id_fk_type_charrette_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"quantite_charrette_ingredient" => -$nb,
							);
							break;
						case "Echoppe" :
							$departIngredientTable = new EchoppeIngredient();
							$data = array (
								"id_fk_echoppe_echoppe_ingredient" => $this->view->id_echoppe_depart,
								"id_fk_type_echoppe_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"quantite_arriere_echoppe_ingredient" => -$nb,
							);
							break;
					}
					$departIngredientTable->insertOrUpdate($data);
					unset ($departIngredientTable);

					switch ($arrivee) {
						case "Laban" :
							$arriveeIngredientTable = new LabanIngredient();
							$data = array(
								"quantite_laban_ingredient" => $nb,
								"id_fk_type_laban_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"id_fk_braldun_laban_ingredient" => $this->view->user->id_braldun,
							);
							$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_POIGNEE_GRAINES * $nb;
							break;
						case "Element" :
							$dateCreation = date("Y-m-d H:i:s");
							$nbJours = Bral_Util_De::get_2d10();
							$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

							$arriveeIngredientTable = new ElementIngredient();
							$data = array("x_element_ingredient" => $this->view->user->x_braldun,
								  "y_element_ingredient" => $this->view->user->y_braldun,
								  "z_element_ingredient" => $this->view->user->z_braldun,
								  'quantite_element_ingredient' => $nb,
								  'id_fk_type_element_ingredient' => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								  'date_fin_element_ingredient' => $dateFin,
							);
							break;
						case "Coffre" :
							$arriveeIngredientTable = new CoffreIngredient();
							$data = array (
								"id_fk_coffre_coffre_ingredient" => $this->view->id_coffre_arrivee,
								"id_fk_type_coffre_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"quantite_coffre_ingredient" => $nb,
							);
							break;
						case "Charrette" :
							$arriveeIngredientTable = new CharretteIngredient();
							$data = array (
								"id_fk_charrette_ingredient" => $this->view->id_charrette_arrivee,
								"id_fk_type_charrette_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"quantite_charrette_ingredient" => $nb,
							);
							break;
						case "Echoppe" :
							$arriveeIngredientTable = new EchoppeIngredient();
							$data = array (
								"id_fk_echoppe_echoppe_ingredient" => $this->view->id_echoppe_arrivee,
								"id_fk_type_echoppe_ingredient" => $this->view->ingredients[$indice]["id_fk_type_ingredient"],
								"quantite_arriere_echoppe_ingredient" => $nb,
							);
							break;
					}
					$arriveeIngredientTable->insertOrUpdate($data);
					unset ($arriveeIngredientTable);
					$s = "";
					if ($nb > 1) $s = "s";
					$this->view->elementsRetires .= $this->view->ingredients[$indice]["type"]. " : ".$nb.", ";
				}
			}
		}
	}

	private function prepareTypeAutres($depart) {
		Zend_Loader::loadClass($depart);

		$tabAutres["nb_castar"] = 0;
		$tabAutres["nb_peau"] = 0;
		$tabAutres["nb_cuir"] = 0;
		$tabAutres["nb_fourrure"] = 0;
		$tabAutres["nb_planche"] = 0;
		$tabAutres["nb_rondin"] = 0;

		switch ($depart) {
			case "Laban" :
				$labanTable = new Laban();
				$autres = $labanTable->findByIdBraldun($this->view->user->id_braldun);
				if ($autres == null) { // si l'on a pas de laban
					$autres [0] = array(
						"quantite_castar_".strtolower($depart) => 0,
						"quantite_peau_".strtolower($depart) => 0,
						"quantite_cuir_".strtolower($depart) => 0,
						"quantite_fourrure_".strtolower($depart) => 0,
						"quantite_planche_".strtolower($depart) => 0,
						"quantite_rondin_".strtolower($depart) => 0,
					);
				}
				if ($this->view->user->castars_braldun > 0) {
					$autres[0]["quantite_castar_laban"] = $this->view->user->castars_braldun;
				}
				unset($labanTable);
				break;
			case "Element" :
				$elementTable = new Element();
				$autres = $elementTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, true, $this->tabButins);
				unset($elementTable);
				break;
			case "Coffre" :
				$coffreTable = new Coffre();
				$coffre = $autres = $coffreTable->findByIdBraldun($this->view->user->id_braldun);
				if (count($coffre) != 1) {
					throw new Zend_Exception(get_class($this)." Coffre depart invalide = idb:".$this->view->user->id_braldun);
				}
				$this->view->id_coffre_depart = $coffre[0]["id_coffre"];
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

		$autresButin = null;

		if (count($autres) >= 1) {
			$tabAutres = array(
					"nb_castar" => 0,
					"nb_peau" => 0,
					"nb_cuir" => 0,
					"nb_fourrure" => 0,
					"nb_planche" => 0,
					"nb_rondin" => 0,
					"info_castar" => "",
					"info_peau" => "",
					"info_cuir" => "",
					"info_fourrure" => "",
					"info_planche" => "",
					"info_rondin" => "",
			);

			if ($depart == "Echoppe") {
				$strqte = "arriere_echoppe";
			} else {
				$strqte = $depart;
			}

			foreach($autres as $p) {
				if ($depart == "Echoppe") {
					$tabAutres["nb_castar"] = 0;
				} else {
					$tabAutres["nb_castar"] = $tabAutres["nb_castar"] + $p["quantite_castar_".strtolower($strqte)];
				}
				$tabAutres["nb_peau"] = $tabAutres["nb_peau"] + $p["quantite_peau_".strtolower($strqte)];
				$tabAutres["nb_cuir"] = $tabAutres["nb_cuir"] + $p["quantite_cuir_".strtolower($strqte)];
				$tabAutres["nb_fourrure"] = $tabAutres["nb_fourrure"] + $p["quantite_fourrure_".strtolower($strqte)];
				$tabAutres["nb_planche"] = $tabAutres["nb_planche"] + $p["quantite_planche_".strtolower($strqte)];
				$tabAutres["nb_rondin"] = $tabAutres["nb_rondin"] + $p["quantite_rondin_".strtolower($strqte)];
				if ($depart == "Element") {
					if ($p["id_fk_butin_element"] != null) {
						$autresButin[] = $p;
						if ($p["quantite_castar_".strtolower($strqte)] > 0) $tabAutres["info_castar"] .= " Butin n°".$p["id_fk_butin_element"];
						if ($p["quantite_peau_".strtolower($strqte)] > 0) $tabAutres["info_peau"] .= " Butin n°".$p["id_fk_butin_element"];
						if ($p["quantite_cuir_".strtolower($strqte)] > 0) $tabAutres["info_cuir"] .= " Butin n°".$p["id_fk_butin_element"];
						if ($p["quantite_fourrure_".strtolower($strqte)] > 0) $tabAutres["info_fourrure"] .= " Butin n°".$p["id_fk_butin_element"];
						if ($p["quantite_planche_".strtolower($strqte)] > 0) $tabAutres["info_planche"] .= " Butin n°".$p["id_fk_butin_element"];
						if ($p["quantite_rondin_".strtolower($strqte)] > 0) $tabAutres["info_rondin"] .= " Butin n°".$p["id_fk_butin_element"];
					}


				}
			}

			if ( $tabAutres["nb_castar"] != 0 || $tabAutres["nb_peau"] != 0 ||
			$tabAutres["nb_cuir"] != 0 || $tabAutres["nb_fourrure"] != 0 ||
			$tabAutres["nb_planche"] != 0 || $tabAutres["nb_rondin"] != 0
			) {
				$this->view->deposerOk = true;

				if ($tabAutres["info_castar"] != "") $tabAutres["info_castar"] = " (dont ".$tabAutres["info_castar"].")";
				if ($tabAutres["info_peau"] != "") $tabAutres["info_peau"] = " (dont ".$tabAutres["info_castar"].")";
				if ($tabAutres["info_cuir"] != "") $tabAutres["info_cuir"] = " (dont ".$tabAutres["info_castar"].")";
				if ($tabAutres["info_fourrure"] != "") $tabAutres["info_fourrure"] = " (dont ".$tabAutres["info_castar"].")";
				if ($tabAutres["info_planche"] != "") $tabAutres["info_planche"] = " (dont ".$tabAutres["info_castar"].")";
				if ($tabAutres["info_rondin"] != "") $tabAutres["info_rondin"] = " (dont ".$tabAutres["info_castar"].")";
			}
		}
		$this->view->autres = $tabAutres;
		$this->autresButin = $autresButin;
	}

	private function deposeTypeAutres($depart,$arrivee) {
		Zend_Loader::loadClass($depart);
		Zend_Loader::loadClass($arrivee);

		$nbCastar = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
		$nbPeau = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
		$nbCuir = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_6"));
		$nbFourrure = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_7"));
		$nbPlanche = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_8"));
		$nbRondin = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_9"));

		$tabElement[1] = array("nom_systeme" => "castar", "nb" => $nbCastar, "poids" => Bral_Util_Poids::POIDS_CASTARS);
		$tabElement[2] = array("nom_systeme" => "peau", "nb" => $nbPeau, "poids" => Bral_Util_Poids::POIDS_PEAU);
		$tabElement[3] = array("nom_systeme" => "cuir", "nb" => $nbCuir, "poids" => Bral_Util_Poids::POIDS_CUIR);
		$tabElement[4] = array("nom_systeme" => "fourrure", "nb" => $nbFourrure, "poids" => Bral_Util_Poids::POIDS_FOURRURE);
		$tabElement[5] = array("nom_systeme" => "planche", "nb" => $nbPlanche, "poids" => Bral_Util_Poids::POIDS_PLANCHE);
		$tabElement[6] = array("nom_systeme" => "rondin", "nb" => $nbRondin, "poids" => Bral_Util_Poids::POIDS_RONDIN);

		foreach ($tabElement as $t) {
			$nb=$t["nb"];
			$nom_systeme = $t["nom_systeme"];
			$poids = $t["poids"];
			if ($nb < 0) {
				throw new Zend_Exception(get_class($this)." Nb ".$nom_systeme." : ".$nb);
			}

			if ($nb > 0) {
				if ($nb > $this->view->autres["nb_".$nom_systeme]) {
					$nb = $this->view->autres["nb_".$nom_systeme];
				}
				if ( ($depart == "Echoppe" || $arrivee == "Echoppe") && ($nom_systeme == "viande" || $nom_systeme == "viande_preparee")) {
					$nb = 0;
				}

				if ($depart == "Charrette" && $this->view->a_panneau === false && $this->view->nbelement > 0 ) {
					$this->view->panneau = false;
					$this->view->elementsNonRetiresPanneau .= $nb." ".str_replace("_preparee"," préparée",$nom_systeme).", ";
				}

				$poidsOk = true;
				if ($arrivee == "Laban" || $arrivee == "Charrette") {
					$poidsOk = $this->controlePoids($this->view->poidsRestant, $nb, $poids );
					if ($poidsOk == false) {
						$this->view->poidsOk = false;
						$this->view->elementsNonRetiresPoids .= $nb." ".str_replace("_preparee"," préparée",$nom_systeme).", ";
					}
				}

				if ($poidsOk == true && $this->view->panneau != false) {
					$this->view->nbelement = $this->view->nbelement + 1;
					$data = array(
						"quantite_".$nom_systeme."_".strtolower($depart) => -$nb,
						"id_fk_braldun_".strtolower($depart) => $this->view->user->id_braldun,
					);
					$departTable = null;
					switch ($depart) {
						case "Laban" :
							if ($nom_systeme == "castar") {
								if ($nb > $this->view->user->castars_braldun) {
									$nb = $this->view->user->castars_braldun;
								}
								$this->view->user->castars_braldun = $this->view->user->castars_braldun - $nb;
							} else {
								$departTable = new Laban();
							}
							break;
						case "Element" :
							$departTable = new Element();

							$nbAEnlever = $nb;

							// on supprime les butins en premier
							if ($this->autresButin != null) {
								foreach($this->autresButin as $b) {

									$data = array(
											"quantite_".$nom_systeme."_element" => -$nbAEnlever,
											"x_element" => $this->view->user->x_braldun,
											"y_element" => $this->view->user->y_braldun,
											"z_element" => $this->view->user->z_braldun,
											"id_fk_butin_element" => $b["id_fk_butin_element"],
									);

									$departTable->insertOrUpdate($data);

									if ($b["quantite_".$nom_systeme."_element"] >= $nbAEnlever) {
										$nbAEnlever = 0;
									} else {
										$nbAEnlever = $nbAEnlever - $b["quantite_".$nom_systeme."_element"];
									}

									if ($nbAEnlever <= 0) {
										break;
									}
								}
							}

							if ($nbAEnlever > 0) {
								$data = array(
								"quantite_".$nom_systeme."_element" => -$nb,
								"x_element" => $this->view->user->x_braldun,
								"y_element" => $this->view->user->y_braldun,
								"z_element" => $this->view->user->z_braldun,
								"id_fk_butin_element" => null,
								);
								$departTable->insertOrUpdate($data);
							}
							break;
						case "Coffre" :
							$departTable = new Coffre();
							$data = array (
								"id_coffre" => $this->view->id_coffre_depart,
								"quantite_".$nom_systeme."_".strtolower($depart) => -$nb,
							);
							break;
						case "Charrette" :
							$departTable = new Charrette();
							$data = array (
								"id_charrette" => $this->view->id_charrette_depart,
								"quantite_".$nom_systeme."_".strtolower($depart) => -$nb,
							);
							break;
						case "Echoppe" :
							$departTable = new Echoppe();
							$data = array(
								"quantite_".$nom_systeme."_arriere_".strtolower($depart) => -$nb,
								"id_".strtolower($depart) => $this->view->id_echoppe_depart,
							);
							break;
					}
					if ($departTable) {
						if ($depart != "Element") {
							$departTable->insertOrUpdate($data);
							unset($departTable);
						}
					}

					$arriveeTable = null;
					switch ($arrivee) {
						case "Laban" :
							if ($nom_systeme == "castar") {
								$this->view->user->castars_braldun = $this->view->user->castars_braldun + $nb;
								$this->view->elementsRetires .= $nb. " castar";
								if ($nb > 1) $this->view->elementsRetires .= "s";
								$this->view->elementsRetires .= ", ";
							} else {
								$data = array(
									"quantite_".$nom_systeme."_laban" => $nb,
									"id_fk_braldun_laban" => $this->view->user->id_braldun,
								);
								$arriveeTable = new Laban();
							}
							$this->view->poidsRestant = $this->view->poidsRestant - $poids * $nb;
							break;
						case "Element" :
							$data = array(
									"quantite_".$nom_systeme."_element" => $nb,
									"x_element" => $this->view->user->x_braldun,
									"y_element" => $this->view->user->y_braldun,
									"z_element" => $this->view->user->z_braldun,
							);
							$arriveeTable = new Element();
							break;
						case "Coffre" :
							$data = array(
									"quantite_".$nom_systeme."_coffre" => $nb,
									"id_coffre" => $this->view->id_coffre_arrivee,
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
					if ($arriveeTable) {
						$arriveeTable->insertOrUpdate($data);
						unset($arriveeTable);
						if ($nom_systeme == "peau") {
							$this->view->elementsRetires .= $nb. " peau";
							if ($nb > 1) $this->view->elementsRetires .= "x";
							$this->view->elementsRetires .= ", ";
						}
						else{
							$this->view->elementsRetires .= $nb." ".str_replace("_preparee"," préparée",$nom_systeme);
							if ($nb > 1) $this->view->elementsRetires .= "s";
							$this->view->elementsRetires .= ", ";
						}
					}
				}
			}
		}
	}

	private function calculTexte($depart, $arrivee) {
		$departTexte = $tabRetour["departTexte"] = $depart;
		$arriveeTexte = $tabRetour["arriveeTexte"] = $arrivee;
		if ($depart == "Element") $tabRetour["departTexte"] = "Sol";
		if ($arrivee == "Element") $tabRetour["arriveeTexte"] = "Sol";
		return $tabRetour;
	}

	private function prepareButins() {
		Zend_Loader::loadClass("ButinPartage");
		$butinPartageTable = new ButinPartage();

		$partage = $butinPartageTable->findByIdBraldunAutorise($this->view->user->id_braldun);
		$proprietaires[$this->view->user->id_braldun] = $this->view->user->id_braldun;
		foreach($partage as $p) {
			$proprietaires[$p["id_fk_braldun_butin_partage"]] = $p["id_fk_braldun_butin_partage"];
		}

		$tabButinsATerreAutorises = null;
		$butinTable = new Butin();
		$butinsATerreAutorises = $butinTable->findByCaseAndProprietaires($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $proprietaires);
		foreach($butinsATerreAutorises as $b) {
			$tabButinsATerreAutorises[$b["id_butin"]] = $b["id_butin"];
		}

		if ($this->view->user->id_fk_communaute_braldun != null) {
			$butinsCommunaute = $butinTable->findByCaseAndIdCommunaute($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_fk_communaute_braldun);
			if ($butinsCommunaute != null) {
				foreach($butinsCommunaute as $b) {
					$tabButinsATerreAutorises[$b["id_butin"]] = $b["id_butin"];
				}
			}
		}

		$this->tabButins = $tabButinsATerreAutorises;
	}

}