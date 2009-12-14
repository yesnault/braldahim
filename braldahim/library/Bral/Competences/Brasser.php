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
		$this->view->sourceOk = false;
		$this->view->nbBieres = $this->view->user->force_base_hobbit;

		$this->idDestination = null;
		$this->idSource = null;

		if ($this->view->nbBieres < 1) {
			$this->view->nbBieres = 1;
		}

		$this->calculEchoppe("cuisinier");
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
			$tabDestinations["echoppe"]["selected"] = "selected";
		} else {

			/*			if ($this->view->possedeCharrette === true) {
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
				*/
		}


		$this->view->destinations = $tabDestinations;
	}

	private function prepareIngredients() {

		Zend_Loader::loadClass("RecetteAliments");
		Zend_Loader::loadClass("TypeAliment");
		$recetteAlimentsTable = new RecetteAliments();
		$ingredientsRecetteRowset = $recetteAlimentsTable->findByIdTypeAliment(TypeAliment::ID_TYPE_LAGER);

		if ($ingredientsRecetteRowset == null || count($ingredientsRecetteRowset) < 0) {
			throw new Zend_Exception("Erreur recette aliment".TypeAliment::ID_TYPE_LAGER);
		}

		$tabSources = null;
		if ($this->view->estSurEchoppe === true) {
			$tabSources["echoppe"]["nom"] = "Votre échoppe";
			$tabSources["echoppe"]["possible"] = true;
			$tabSources["echoppe"]["selected"] = "selected";

			Zend_Loader::loadClass("EchoppeIngredient");
			$echoppeIngredientTable = new EchoppeIngredient();
			$ingredients = $echoppeIngredientTable->findByIdEchoppe($this->view->idEchoppe);
			$tabSources["echoppe"]["ingredients"] = $ingredients;
		} else {
			/*
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
				*/
		}
			
		$tabIngredients = null;
		$poidsIngredients = 0;
		foreach($ingredientsRecetteRowset as $i) {
			$tabIngredients[] = array(
				'nom_type_ingredient' => $i["nom_type_ingredient"],
				'id_type_ingredient' => $i["id_type_ingredient"],
				'quantite_recette_aliments' => $i["quantite_recette_aliments"],
			);
			$poidsIngredients = $poidsIngredients + ($i["quantite_recette_aliments"] * $i["poids_unitaire_type_ingredient"]);
			$this->controleIngredientsDispo($tabSources, $i["id_type_ingredient"], $i["quantite_recette_aliments"]);
		}

		$this->view->ingredients = $tabIngredients;
		$this->view->sources = $tabSources;
	}

	private function controleIngredientsDispo(&$tabSources, $idTypeIngredient, $quantite) {
		if ($tabSources != null && count($tabSources) > 0) {
			foreach($tabSources as $k => $v) {
				if ($tabSources[$k]["possible"] === true) {
					if ($tabSources[$k]["ingredients"] != null && count($tabSources[$k]["ingredients"]) > 0) {
						$ingredientOk = false;
						foreach($tabSources[$k]["ingredients"] as $i) {
							$prefix = $k;
							if ($k == "echoppe") {
								$prefix = "arriere_echoppe";
							}
							if ($i["id_type_ingredient"] == $idTypeIngredient && $i["quantite_".$prefix."_ingredient"] >= $quantite) {
								$ingredientOk = true;
							}
						}
						$tabSources[$k]["possible"] = $ingredientOk;
					} else {
						$tabSources[$k]["possible"] = false;
						$tabSources[$k]["selected"] = "";
					}
				}
			}
		}
	}

	private function controleSource() {

		$uneSourceOk = false;
		if ($this->view->sources != null && count($this->view->sources) > 0) {
			foreach($this->view->sources as $s) {
				if ($s["possible"] == true) {
					$uneSourceOk = true;
				}
			}
		}

		if ($uneSourceOk === true) {
			$this->view->sourceOk = true;
		} else {
			$this->view->sourceOk = false;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification cuisiner
		if ($this->view->sourceOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdit source KO ");
		}

		$idSource = $this->request->get("valeur_1");
		$idDestination = $this->request->get("valeur_2");

		$sourceOk = false;
		foreach ($this->view->sources as $k => $v) {
			if ($k == $idSource && $v["possible"] === true) {
				$sourceOk = true;
			}
		}

		if ($sourceOk == false) {
			throw new Zend_Exception(get_class($this)." Brasser interdit source KO B idSource:".$idSource);
		}

		$this->idSource = $idSource;

		$destinationOk = false;
		foreach ($this->view->destinations as $k => $v) {
			if ($k == $idDestination && $v["possible"] === true) {
				$destinationOk = true;
			}
		}

		if ($destinationOk == false) {
			throw new Zend_Exception(get_class($this)." Brasser interdit destination KO idDestination:".$idDestination);
		}
		$this->idDestination = $idDestination;

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculBrasser($idSource, $idDestination);
		} else {
			$this->retireIngredients($idSource, true);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculBrasser($idSource, $idDestination) {
		$idTypeAliment = $this->calculQualite();

		$this->retireIngredients($idSource);

		$poidsRestant = $this->view->destinations[$idDestination]["poids_restant"];
		if ($idSource == $idDestination) {
			$poidsRestant = $this->view->destinations[$idDestination]["poids_apres_ingredient"];
		}

		$nbBieresPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_BIERE);
		if ($nbBieresPossible < 0) {
			$nbBieresPossible = 0;
		}

		$this->view->nbBieresATerre = 0;
		if ($this->view->nbBieres > $nbBieresPossible) {
			$this->view->nbBieresDestination = intval($nbBieresPossible);
			$this->view->nbBieresATerre = floor($this->view->nbAliment - $this->view->nbBieresDestination);
		} else {
			$this->view->nbBieresDestination = $this->view->nbBieres;
		}

		$this->creationBiere($idTypeAliment, $idDestination, $idSource);
	}

	private function calculQualite() {
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"] / 100;
		$chance_a = -0.375 * $maitrise + 40 + $this->view->user->force_base_hobbit;
		$chance_b = 0.25 * $maitrise + 50 - ($this->view->user->force_base_hobbit / 2);
		$chance_c = 0.125 * $maitrise + 10 - ($this->view->user->force_base_hobbit / 2);

		Zend_Loader::loadClass("Aliment");
		$tirage = Bral_Util_De::get_1d100();
		$tirage = 100;
		if ($tirage > 0 && $tirage <= $chance_a) {
			$this->view->qualite = "de Lager";
			$idTypeAliment = TypeAliment::ID_TYPE_LAGER;
		} elseif ($tirage > $chance_a && $tirage <= $chance_a + $chance_b) {
			$this->view->qualite = "d'Ale";
			$idTypeAliment = TypeAliment::ID_TYPE_ALE;
		} else {
			$this->view->qualite = "de Stout";
			$idTypeAliment = TypeAliment::ID_TYPE_STOUT;
		}
		return $idTypeAliment;
	}

	private function creationBiere($idTypeAliment, $idDestination, $idSource) {
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

		Zend_Loader::loadClass("ElementAliment");
		$elementAlimentTable = new ElementAliment();

		Zend_Loader::loadClass("IdsAliment");
		$idsAliment = new IdsAliment();

		Zend_Loader::loadClass('Aliment');
		$alimentTable = new Aliment();

		Zend_Loader::loadClass("Bral_Util_Effets");
		
		for ($i = 1; $i <= $this->view->nbBieres; $i++) {
			$idAliment = $idsAliment->prepareNext();

			$idEffetHobbit = null;

			if ($idTypeAliment == TypeAliment::ID_TYPE_STOUT) {
				// la valeur est calculée sur l'application de l'effet
				$idEffetHobbit = Bral_Util_Effets::ajouteEtAppliqueEffetHobbit(null, Bral_Util_Effets::CARACT_STOUT, Bral_Util_Effets::TYPE_BONUS, Bral_Util_De::get_1d3(), 0, 'Lovely day for a stout !');
			}
				
			$data = array(
				"id_aliment" => $idAliment,
				"id_fk_type_aliment" => $idTypeAliment,
				"id_fk_type_qualite_aliment" => 2,
				"bbdf_aliment" => 0,
				"id_fk_effet_hobbit_aliment" => $idEffetHobbit,
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

	private function retireIngredients($idSource, $estRate = false) {
		if ($idSource == "echoppe") {
			$prefix = "echoppe";
			Zend_Loader::loadClass("EchoppeIngredient");
			$table = new EchoppeIngredient();
			$data["id_fk_echoppe_echoppe_ingredient"] = $this->view->idEchoppe;
		} else if ($idSource == "charrette") {
			$prefix = "charrette";
			Zend_Loader::loadClass("CharretteIngredient");
			$table = new CharretteIngredient();
			$data["id_fk_charrette_ingredient"] = $this->view->idCharrette;
		} else if ($idSource == "laban") {
			$prefix = "laban";
			Zend_Loader::loadClass("LabanIngredient");
			$table = new LabanIngredient();
			$data["id_fk_hobbit_laban_ingredient"] = $this->view->user->id_hobbit;
		} else {
			throw new Zend_Exception("retireIngredients::Source invalide:".$idSource);
		}

		foreach($this->view->ingredients as $i) {
			$quantite = -$i["quantite_recette_aliments"];
			if ($estRate) {
				$quantite = floor($quantite / 2);
			}
			$data["id_fk_type_".$prefix."_ingredient"] = $i["id_type_ingredient"];
			$data["quantite_".$prefix."_ingredient"] = $quantite;
			$table->insertOrUpdate($data);
		}
	}

	function getListBoxRefresh() {
		$tab[] = 'box_competences_metiers';
		if ($this->idDestination == 'echoppe' || $this->idSource == 'echoppe') {
			$tab[] = 'box_echoppes';
		}
		if ($this->idDestination == 'laban' || $this->idSource == 'laban') {
			$tab[] = 'box_laban';
		}
		if ($this->idDestination == 'sol' || $this->idSource == 'sol' || $this->view->nbBieresATerre > 0) {
			$tab[] = 'box_vue';
		}
		if ($this->idDestination == 'charrette' || $this->idSource == 'charrette') {
			$tab[] = 'box_charrette';
		}
		return $this->constructListBoxRefresh($tab);
	}
}
