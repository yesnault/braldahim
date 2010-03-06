<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Competences_Recolter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Champ");

		if ($this->verificationChamp() == false) {
			return null;
		}

		$this->verificationChamp();

		if ($this->view->user->balance_faim_hobbit >= 2) {
			$this->prepareDestination();
		}
	}

	private function verificationChamp() {
		$this->view->recolterChampOk = false;

		$champTable = new Champ();
		$champs = $champTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit, $this->view->user->id_hobbit);

		$retour = false;
		if (count($champs) == 1) {
			$this->view->champ = $champs[0];
			if ($this->view->champ["phase_champ"] == "a_recolter") {
				$this->view->recolterChampOk = true;
				$this->idChamp = $this->view->champ["id_champ"];
				$retour = true;
			}
		}
		return $retour;
	}

	private function prepareDestination() {
		$tabDestinationTransfert["laban"] = array("id_destination" => "laban", "texte" => "votre laban", "selected" => "");

		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

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
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
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
		$details = "[h".$this->view->user->id_hobbit."] a récolté un champ";
		$this->setDetailsEvenement($details, $idType);
		$this->setEvenementQueSurOkJet1(false);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
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
				$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
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
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
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

		if ($quantite > $this->view->user->balance_faim_hobbit) {
			$quantite = $this->view->user->balance_faim_hobbit;
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
			$data["id_fk_hobbit_laban_tabac"] = $this->view->user->id_hobbit;
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
			$data["x_element_ingredient"] = $this->view->user->x_hobbit; 
			$data["y_element_ingredient"] = $this->view->user->y_hobbit;
		} else {
			$data["id_fk_hobbit_laban_ingredient"] = $this->view->user->id_hobbit;
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
			$data["x_element_ingredient"] = $this->view->user->x_hobbit; 
			$data["y_element_ingredient"] = $this->view->user->y_hobbit;
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