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
class Bral_Competences_Brasser extends Bral_Competences_Competence {

	const POIDS_INGREDIENT = 5.025; // 5KG + 25g

	function prepareCommun() {
		$this->view->brasserOk = false;
		$this->view->nbBieres = $this->view->user->force_base_hobbit;
		if ($this->view->nbBieres < 1) {
			$this->view->nbBieres = 1;
		}

		$this->calculEchoppe();
		$this->calculCharrette();
		$this->prepareDestinations(); // Soit dans l'échoppe, soit le laban, soit la charrette
		$this->prepareIngredients();
		$this->controleSource();

	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	private function prepareDestinations() {
		$tabDestinations = null;

		if ($this->view->estSurEchoppe === true) {
			$tabDestinations["echoppe"]["possible"] = true;
			$tabDestinations["echoppe"]["nom"] = "Votre échoppe";
			$tabDestinations["echoppe"]["poids_apres_ingredient"] = 10000;
			$tabDestinations["echoppe"]["poids_restant"] = 10000;
		}

		if ($this->view->possedeCharrette === true) {
			$tabDestinations["charrette"]["possible"] = true;
			$tabDestinations["charrette"]["nom"] = "Votre charrette";
			$tabDestinations["charrette"]["poids_apres_ingredient"] = $this->view->poidsRestantCharrette + self::POIDS_INGREDIENT;
			$tabDestinations["charrette"]["poids_restant"] = $this->view->poidsRestantCharrette;
		}

		$tabDestinations["laban"]["possible"] = true;
		$tabDestinations["laban"]["nom"] = "Votre laban";
		$tabDestinations["laban"]["poids_apres_ingredient"] = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit + self::POIDS_INGREDIENT;
		$tabDestinations["laban"]["poids_restant"] = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;

		$tabDestinations["sol"]["possible"] = true;
		$tabDestinations["sol"]["nom"] = "Au Sol";
		$tabDestinations["sol"]["poids_apres_ingredient"] = 10000;
		$tabDestinations["sol"]["poids_restant"] = 10000;


		$this->view->destinations = $tabDestinations;
	}

	private function prepareIngredients() {

		$tabSources = null;
		if ($this->view->estSurEchoppe === true) {
			$tabSources["echoppe"]["nom"] = "Votre échoppe";
			$tabSources["echoppe"]["possible"] = true;

			Zend_Loader::loadClass("EchoppeIngredient");
			$echoppeIngredientTable = new EchoppeIngredient();
			$ingredients = $echoppeIngredientTable->findByIdEchoppe($this->view->idEchoppe);
			$tabSources["echoppe"]["ingredients"] = $ingredients;
		}

		if ($this->view->possedeCharrette === true) {
			$tabSources["charrette"]["nom"] = "Votre charrette";
			$tabSources["charrette"]["possible"] = true;

			Zend_Loader::loadClass("CharretteIngredient");
			$charretteIngredientTable = new CharretteIngredient();
			$ingredients = $charretteIngredientTable->findByIdCharrette($this->view->idCharrette);
			$tabSources["charrette"]["ingredients"] = $ingredients;
		}

		$tabSources["laban"]["nom"] = "Votre laban";
		$tabSources["laban"]["possible"] = true;

		Zend_Loader::loadClass("LabanIngredient");
		$labanIngredientTable = new LabanIngredient();
		$ingredients = $labanIngredientTable->findByIdHobbit($this->view->user->id_hobbit);
		$tabSources["laban"]["ingredients"] = $ingredients;
			
		$tabIngredients = null;
		$poidsIngredients = 0;
		Zend_Loader::loadClass("TypeIngredient");
		$this->controleIngredientsDispo($tabSources, TypeIngredient::ID_TYPE_ORGE, 20);
		$this->controleIngredientsDispo($tabSources, TypeIngredient::ID_TYPE_HOUBLON, 13);

		$this->view->ingredients = $tabIngredients;
		$this->view->sources = $tabSources;
	}

	private function controleIngredientsDispo(&$tabSources, $idTypeIngredient, $quantite) {
		foreach($tabSources as $k => $v) {
			if ($tabSources[$k]["possible"] === true) {
				if ($tabSources[$k]["ingredients"] != null && count($tabSources[$k]["ingredients"]) > 0) {
					$ingredientOk = false;
					foreach($tabSources[$k]["ingredients"] as $i) {
						if ($i["id_type_ingredient"] == $idTypeIngredient && $i["quantite_".$k."_ingredient"] >= $quantite) {
							$ingredientOk = true;
						}
					}
					$tabSources[$k]["possible"] = $ingredientOk;
				} else {
					$tabSources[$k]["possible"] = false;
				}
			}
		}
	}

	private function controleSource() {

		$uneSourceOk = false;
		foreach($this->view->sources as $s) {
			if ($s["possible"] == true) {
				$uneSourceOk = true;
			}
		}

		if ($uneSourceOk === true) {
			$this->view->brasserOk = true;
		} else {
			$this->view->brasserOk = false;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		//TODO

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculBrasser();
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculBrasser() {
		$idTypeAliment = $this->calculQualite();
		$this->creationAliment($idTypeAliment, $idDestination, $idSource);
	}

	private function calculQualite() {
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"] / 100;
		$chance_a = -0.375 * $maitrise + 40 + $this->view->user->force_base_hobbit;
		$chance_b = 0.25 * $maitrise + 50 - ($this->view->user->force_base_hobbit / 2);
		$chance_c = 0.125 * $maitrise + 10 - ($this->view->user->force_base_hobbit / 2);

		Zend_Loader::loadClass("Aliment");
		$tirage = Bral_Util_De::get_1d100();
		if ($tirage > 0 && $tirage <= $chance_a) {
			$this->view->qualite = "de Lager";
			$idTypeAliment = Aliment::ID_TYPE_LAGER;
		} elseif ($tirage > $chance_a && $tirage <= $chance_a + $chance_b) {
			$this->view->qualite = "d'Ale";
			$idTypeAliment = Aliment::ID_TYPE_ALE;
		} else {
			$this->view->qualite = "de Stout";
			$idTypeAliment = Aliment::ID_TYPE_STOUT;
		}
		return $idTypeAliment;
	}

	private function creationAliment($idTypeAliment, $idDestination, $idSource) {
		if ($idDestination == "echoppe") {
			$prefix = "echoppe";
			Zend_Loader::loadClass("EchoppeAliment");
			$table = new EchoppeAliment();
			$tabBase["id_fk_echoppe_echoppe_aliment"] = $this->view->idEchoppe;
		} else if ($idDestination == "charrette") {
			$prefix = "charrette";
			Zend_Loader::loadClass("CharretteAliment");
			$table = new CharretteAliment();
			$tabBase["id_fk_charrette_aliment"] = $this->view->idCharrette;
		} else if ($idDestination == "laban") {
			$prefix = "laban";
			Zend_Loader::loadClass("LabanAliment");
			$table = new LabanAliment();
			$tabBase["id_fk_hobbit_laban_aliment"] = $this->view->user->id_hobbit;
		} else if ($idDestination == "sol") {
			$prefix = "element";
			Zend_Loader::loadClass("ElementAliment");
			$table = new ElementAliment();
			$tabBase["x_element_aliment"] = $this->view->user->x_hobbit;
			$tabBase["y_element_aliment"] = $this->view->user->y_hobbit;
			$tabBase["z_element_aliment"] = $this->view->user->z_hobbit;
		} else {
			throw new Zend_Exception("creationAliment::Source invalide:".$idDestination);
		}

		$elementAlimentTable = new ElementAliment();

		Zend_Loader::loadClass("IdsAliment");
		$idsAliment = new IdsAliment();

		Zend_Loader::loadClass('Aliment');
		$alimentTable = new Aliment();

		for ($i = 1; $i <= $this->view->nbBieres; $i++) {
			$idAliment = $idsAliment->prepareNext();

			$data = array(
				"id_aliment" => $idAliment,
				"id_fk_type_aliment" => $idTypeAliment,
				"id_fk_type_qualite_aliment" => $this->view->qualiteAliment,
				"bbdf_aliment" => 0,
			);
			$alimentTable->insert($data);

			$data = array(
				"id_element_aliment" => $idAliment,
				"x_element_aliment" => $this->view->user->x_hobbit,
				"y_element_aliment" => $this->view->user->y_hobbit,
				"z_element_aliment" => $this->view->user->z_hobbit,
			);
			$elementAlimentTable->insert($data);

			if ($i <= $this->view->nbBieresDestination) {
				$where = "id_element_aliment = ".(int)$idAliment;
				$elementAlimentTable->delete($where);

				$data = $tabBase;
				$data['id_'.$prefix.'_aliment'] = $idAliment;
				$table->insert($data);
			}
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_laban"));
	}
}
