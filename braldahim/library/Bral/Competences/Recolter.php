<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Recolter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Champ");
		Zend_Loader::loadClass("Bral_Util_Communaute");
		Zend_Loader::loadClass("TypeLieuCommunaute");
		Zend_Loader::loadClass("Bral_Util_Messagerie");

		if ($this->verificationChamp() == false) {
			return null;
		}

		$this->verificationChamp();

		if ($this->view->user->balance_faim_braldun >= 2) {
			$this->prepareDestination();
		}
	}

	private function verificationChamp() {
		$this->view->recolterChampOk = false;

		$champTable = new Champ();

		$niveauGrenier = Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieuCommunaute::ID_TYPE_AGRICULTURE);

		if ($niveauGrenier != null && $niveauGrenier >= Bral_Util_Communaute::NIVEAU_GRENIER_RECOLTER) {
			$champs = $champTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, null, null, $this->view->user->id_fk_communaute_braldun);
		} else {
			$champs = $champTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun);
		}

		$retour = false;
		if (count($champs) == 1) {
			$this->view->champ = $champs[0];
			if ($this->view->champ["phase_champ"] == "a_recolter") {
				$this->view->recolterChampOk = true;
				$this->idChamp = $this->view->champ["id_champ"];
				$this->idProprietaire = $this->view->champ["id_braldun"];
				$retour = true;
			}
		}
		return $retour;
	}

	private function prepareDestination() {
		$tabDestinationTransfert["laban"] = array("id_destination" => "laban", "texte" => "votre laban", "selected" => "");

		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$tabDestinationTransfert["charrette"] = array("id_destination" => "charrette", "texte" => "votre charrette", "selected" => "");
			$this->view->charrette = $charrette;
		}

		if (count($tabDestinationTransfert) == 0) {
			$selectedSol = "selected";
		} else {
			$selectedSol = "";
		}
		$tabDestinationTransfert["sol"] = array("id_destination" => "sol", "texte" => "au sol", "selected" => $selectedSol);

		$this->view->destinationTransfert = $tabDestinationTransfert;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// Verification semer
		if ($this->view->recolterChampOk == false) {
			throw new Zend_Exception(get_class($this)." Recolter Champ interdit");
		}

		$idDestination = $this->request->get("valeur_1");

		if (!array_key_exists($idDestination, $this->view->destinationTransfert)) {
			throw new Zend_Exception(get_class($this)." idDestination impossible : ".$idDestination);
		}

		$this->recolter($idDestination);
		$idType = $this->view->config->game->evenements->type->competence;
		$details = "[b".$this->view->user->id_braldun."] a récolté un champ";
		$this->setDetailsEvenement($details, $idType);
		$this->setEvenementQueSurOkJet1(false);

		//message pour le braldûn propriétaire
		if ($this->idProprietaire != $this->view->user->id_braldun) {
			Zend_Loader::loadClass("Bral_Util_Messagerie");
			$message = "[Ceci est un message automatique de récolte]".PHP_EOL;
			$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a récolté dans votre champ en x:".$this->view->champ["x_champ"].", y:".$this->view->champ["y_champ"].PHP_EOL;
			$message .= "Récolte : ".$this->view->recolte.PHP_EOL;
			if ($this->view->taupesVivantes != null) {
				$message .= "La récolte a été affectée par : ".PHP_EOL;
				foreach($this->view->taupesVivantes as $taille => $numero) {
					foreach($numero as $num => $foo) {
						$message .= "1 taupe de taille ".$taille." (n°".$num.")".PHP_EOL;
					}
				}
			}
			if ($this->view->champDetruit) {
				$message .= "Il n'y a plus rien à récolter dans le champ, il est passé en jachère.".PHP_EOL;
				$message .= "Vous pouvez maitenant re-semer votre champ si vous le souhaitez.".PHP_EOL;
			}
			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $this->idProprietaire, $message, $this->view);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	private function recolter($idDestination) {

		Zend_Loader::loadClass("TypeGraine");
		$typeGraineTable = new TypeGraine();
		$typeGraine = $typeGraineTable->findById($this->view->champ["id_fk_type_graine_champ"]);

		$quantiteKg = $this->calculQuantite($typeGraine);

		Zend_Loader::loadClass("TypeIngredient");
		$typeIngredientTable = new TypeIngredient();
		$typeIngredient = $typeIngredientTable->findById($typeGraine->id_fk_type_ingredient_type_graine);

		if ($typeGraine->type_type_graine == "tabac") {
			Zend_Loader::loadClass("TypeTabac");
			$typeTabacTable = new TypeTabac();
			$quantiteFeuille = floor($quantiteKg);
			$typeTabac = $typeTabacTable->findById($typeGraine->id_fk_type_tabac_type_graine);

			$this->view->placeDispo = true;

			$this->view->recolte = $quantiteFeuille. " feuilles ".$typeTabac->nom_court_type_tabac;
			$this->calculTransfertTabac($idDestination, $quantiteFeuille, $typeGraine->id_fk_type_tabac_type_graine);
		} else {

			$quantite = floor($quantiteKg / $typeIngredient->poids_unitaire_type_ingredient);
			$quantiteKg = $quantite * $typeIngredient->poids_unitaire_type_ingredient;

			if ($idDestination == "charrette") {
				$poidsRestant = $this->view->charrette["poids_transportable_charrette"] - $this->view->charrette["poids_transporte_charrette"];
			} elseif ($idDestination == "laban") {
				$poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
			} else {
				$poidsRestant = 10000000; // sol
			}

			if ($poidsRestant < $quantiteKg && $idDestination != "sol") { // pas assez de place dans le conteneur
				$quantitePossible = floor($poidsRestant / $typeIngredient->poids_unitaire_type_ingredient);
				$quantiteSol = $quantite - $quantitePossible;
				$quantite = $quantitePossible;
				$this->view->placeDispo = false;
			} else {
				$quantiteSol = 0;
				$this->view->placeDispo = true;
			}

			$this->view->recolte = $quantiteKg. " Kg ".$typeGraine->prefix_type_graine.$typeGraine->nom_type_graine;
			$this->calculTransferIngredient($idDestination, $quantite, $quantiteSol, $typeGraine->id_fk_type_ingredient_type_graine);
		}
		$this->majChamp();

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
		}
		$this->idDestination = $idDestination;
	}

	private function calculQuantite($typeGraine) {
		$quantite = $this->view->champ["quantite_champ"];

		if ($this->view->champ["deja_recolte_champ"] == 'non') {
			// Recuperation des taupes encore présentes dans le champ
			Zend_Loader::loadClass("ChampTaupe");
			$champTaupeTable = new ChampTaupe();
			$taupes = $champTaupeTable->findByIdChamp($this->view->champ["id_champ"]);

			$taupesVivantes = null;
			foreach($taupes as $t) {
				if ($t["etat_champ_taupe"] == "vivant") {
					$taupesVivantes[$t["taille_champ_taupe"]][$t["numero_champ_taupe"]] = 1;
				}
			}

			if ($taupesVivantes != null) {
				foreach($taupesVivantes as $taille => $numero) {

					foreach($numero as $num => $foo) {
						if ($taille == 4) {
							$quantite = $quantite - 100;
						} elseif ($taille == 3) {
							$quantite = $quantite - 75;
						} elseif ($taille == 2) {
							$quantite = $quantite - 50;
						}
					}
				}
			}
			$this->view->champ["quantite_champ"] = $quantite;
			$this->view->taupesVivantes = $taupesVivantes;
		}

		if ($quantite > $this->view->user->balance_faim_braldun) {
			$quantite = $this->view->user->balance_faim_braldun;
		}

		if ($quantite > $this->view->champ["quantite_champ"]) {
			$quantite = $this->view->champ["quantite_champ"];
		}

		$quantiteKg = round($quantite / $typeGraine->coef_poids_type_graine, 3);
		$quantiteNJuste = floor($quantiteKg * $typeGraine->coef_poids_type_graine);
		$quantiteKg = round($quantiteNJuste / $typeGraine->coef_poids_type_graine, 3);

		$this->view->champ["quantite_champ"] = $this->view->champ["quantite_champ"] - $quantite;

		$this->quantiteN = $quantite;

		return $quantiteKg;
	}

	private function calculTransfertTabac($idDestination, $quantite, $idTypeTabac) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteTabac");
			$table = new CharretteTabac();
			$suffixe = "charrette";
		} elseif ($idDestination == "sol") {
			Zend_Loader::loadClass("ElementTabac");
			$table = new ElementTabac();
			$suffixe = "element";
		} else {
			Zend_Loader::loadClass("LabanTabac");
			$table = new LabanTabac();
			$suffixe = "laban";
		}

		$data = array(
			"id_fk_type_".$suffixe."_tabac" => $idTypeTabac,
			"quantite_feuille_".$suffixe."_tabac" => $quantite,
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_tabac"] = $this->view->charrette["id_charrette"];
		} elseif ($idDestination == "sol") {
			// rien
		} else {
			$data["id_fk_braldun_laban_tabac"] = $this->view->user->id_braldun;
		}
		$table->insertOrUpdate($data);
	}

	private function calculTransferIngredient($idDestination, $quantite, $quantiteSol, $idTypeIngredient) {

		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteIngredient");
			$table = new CharretteIngredient();
			$suffixe = "charrette";
		} elseif ($idDestination == "sol") {
			Zend_Loader::loadClass("ElementIngredient");
			$table = new ElementIngredient();
			$suffixe = "element";
		} else {
			Zend_Loader::loadClass("LabanIngredient");
			$table = new LabanIngredient();
			$suffixe = "laban";
		}

		$data = array(
			"id_fk_type_".$suffixe."_ingredient" => $idTypeIngredient,
			"quantite_".$suffixe."_ingredient" => $quantite,
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_ingredient"] = $this->view->charrette["id_charrette"];
		} elseif ($idDestination == "sol") {
			$data["x_element_ingredient"] = $this->view->user->x_braldun;
			$data["y_element_ingredient"] = $this->view->user->y_braldun;
		} else {
			$data["id_fk_braldun_laban_ingredient"] = $this->view->user->id_braldun;
		}
		$table->insertOrUpdate($data);

		if ($quantiteSol > 0) {
			Zend_Loader::loadClass("ElementIngredient");
			$table = new ElementIngredient();
			$suffixe = "element";
			$data = array(
				"id_fk_type_".$suffixe."_ingredient" => $idTypeIngredient,
				"quantite_".$suffixe."_ingredient" => $quantiteSol,
			);
			$data["x_element_ingredient"] = $this->view->user->x_braldun;
			$data["y_element_ingredient"] = $this->view->user->y_braldun;
			$table->insertOrUpdate($data);
		}

	}

	private function majChamp() {
		$this->view->champDetruit = false;

		$champTable = new Champ();

		if ($this->view->champ["quantite_champ"] <= 0) { // mise à zero du champ

			$this->view->champDetruit = true;

			$data = array(
				'phase_champ' => 'jachere',
				'date_seme_champ' => null,
				'date_fin_recolte_champ' => null,
				'deja_recolte_champ' => 'non',
			//'id_fk_type_graine_champ' => null, ==> on ne vide pas, c'est utile pour le % quantité à la prochaine action semer
				'quantite_champ' => 0,
				'date_utilisation_champ' => date("Y-m-d 00:00:00"),
			);

			$where = 'id_champ='.$this->view->champ["id_champ"];
			$champTable->update($data, $where);

			// suppression des taupes et résultats d'entretenir s'il y en a
			Zend_Loader::loadClass("ChampTaupe");
			$champTaupeTable = new ChampTaupe();
			$where = 'id_fk_champ_taupe='.$this->view->champ["id_champ"];
			$champTaupeTable->delete($where);
		} else {
			$data = array(
				'quantite_champ' => $this->view->champ["quantite_champ"],
				'deja_recolte_champ' => 'oui',
				'date_utilisation_champ' => date("Y-m-d 00:00:00"),
			);

			$where = 'id_champ='.$this->view->champ["id_champ"];
			$champTable->update($data, $where);
		}
	}

	public function getIdChampCourant() {
		if (isset($this->idChamp)) {
			return $this->idChamp;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		$tab = array("box_competences_communes", "box_champs", "box_laban", "box_charrette");
		if ($this->view->placeDispo == false || $this->idDestination == "sol") {
			$tab[] = "box_vue";
		}
		return $this->constructListBoxRefresh($tab);
	}

	public function calculPx() {
		$this->view->nb_px_commun = 0;
		$this->view->calcul_px_generique = true;
		$this->view->nb_px_perso = floor($this->quantiteN / 10);
		$this->view->nb_px = floor($this->view->nb_px_perso + $this->view->nb_px_commun);
	}
}