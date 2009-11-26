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
class Bral_Competences_Cuisiner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass('Bral_Util_Quete');

		$this->view->typeAlimentCourant = null;
		$this->view->estSurEchoppe = false;
		$this->view->possedeCharrette = false;

		$this->prepareAliments();

		if ($this->view->typeAlimentCourant != null) {
			$this->calculEchoppe();
			$this->calculCharrette();
			$this->prepareIngredients(); // présent soit dans l'échoppe, soit dans le laban
			$this->prepareDestinations(); // Soit dans l'échoppe, soit le laban, soit la charrette
		}
	}

	private function calculEchoppe() {
		// On regarde si le hobbit est dans une de ses echopppes

		Zend_Loader::loadClass("Echoppe");
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);

		$idEchoppe = null;
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
			$e["nom_systeme_metier"] == "cuisinier" &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit &&
			$e["z_echoppe"] == $this->view->user->z_hobbit) {
				$this->view->estSurEchoppe = true;
				$idEchoppe = $e["id_echoppe"];
				break;
			}
		}
		$this->view->idEchoppe = $idEchoppe;
	}

	private function calculCharrette() {
		// On regarde si le hobbit possède une charrette
		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
		if ($charrette != null && count($charrette) == 1) {
			$this->view->possedeCharrette = true;
			$this->view->idCharrette = $charrette[0]["id_charrette"];
		}
	}

	private function prepareAliments() {
		$typeAlimentCourant = null;

		$idTypeCourant = $this->request->get("type_aliment");

		Zend_Loader::loadClass("TypeAliment");
		$typeAlimentTable = new TypeAliment();
		$typeAlimentsRowset = $typeAlimentTable->findAllByType('manger');
		$tabTypeAliment = null;
		foreach($typeAlimentsRowset as $t) {
			$selected = "";
			if ($idTypeCourant == $t["id_type_aliment"]) {
				$selected = "selected";
			}
			$t = array(
				'id_type_aliment' => $t["id_type_aliment"],
				'nom_type_aliment' => $t["nom_type_aliment"],
				'selected' => $selected
			);
			if ($idTypeCourant == $t["id_type_aliment"]) {
				$typeAlimentCourant = $t;
			}
			$tabTypeAliment[] = $t;
		}

		$this->view->typeAliment = $tabTypeAliment;
		$this->view->typeAlimentCourant = $typeAlimentCourant;
	}

	private function prepareIngredients() {
		// TODO présents soit dans l'échoppe, soit dans le laban

		$tabIngredients = null;

		Zend_Loader::loadClass("RecetteAliments");
		$recetteAlimentsTable = new RecetteAliments();
		$ingredientsRecetteRowset = $recetteAlimentsTable->findByIdTypeAliment($this->view->typeAlimentCourant["id_type_aliment"]);

		if ($ingredientsRecetteRowset == null || count($ingredientsRecetteRowset) < 0) {
			throw new Zend_Exception("Erreur recette aliment".$this->view->typeAlimentCourant["id_type_aliment"]);
		}

		$tabSources = null;
		if ($this->view->estSurEchoppe === true) {
			Zend_Loader::loadClass("EchoppeIngredient");
			$echoppeIngredientTable = new EchoppeIngredient();
			$ingredients = $echoppeIngredientTable->findByIdEchoppe($this->view->idEchoppe);
			$tabSources["echoppe"]["ingredients"] = $ingredients;
			$tabSources["echoppe"]["possible"] = true;
			$tabSources["echoppe"]["nom"] = "Votre échoppe";
		}

		if ($this->view->possedeCharrette === true) {
			Zend_Loader::loadClass("CharretteIngredient");
			$charretteIngredientTable = new CharretteIngredient();
			$ingredients = $charretteIngredientTable->findByIdCharrette($this->view->idCharrette);
			$tabSources["charrette"]["ingredients"] = $ingredients;
			$tabSources["charrette"]["possible"] = true;
			$tabSources["charrette"]["nom"] = "Votre charrette";
		}

		Zend_Loader::loadClass("LabanIngredient");
		$labanIngredientTable = new LabanIngredient();
		$ingredients = $labanIngredientTable->findByIdHobbit($this->view->user->id_hobbit);
		$tabSources["laban"]["ingredients"] = $ingredients;
		$tabSources["laban"]["possible"] = true;
		$tabSources["laban"]["nom"] = "Votre laban";
			
		foreach($ingredientsRecetteRowset as $i) {
			$tabIngredients = array(
				'nom_type_ingredient' => $i["nom_type_ingredient"],
				'id_type_ingredient' => $i["id_type_ingredient"],
				'quantite_recette_aliments' => $i["quantite_recette_aliments"],
			);
			$this->controleIngredientsDispo($tabSources, $i["id_type_ingredient"], $i["quantite_recette_aliments"]);
		}

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

	private function prepareDestinations() {
		// TODO soit le laban, soit la charrette, soit l'échoppe
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

		// Verification cuisiner
		if ($this->view->cuisinerNbViandeOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdit ");
		}

		if ((int)$this->request->get("valeur_1")."" != $this->request->get("valeur_1")."") {
			throw new Zend_Exception(get_class($this)." Nombre invalide");
		} else {
			$nombre = (int)$this->request->get("valeur_1");
		}

		if ($nombre > $this->view->nbViandePreparee) {
			throw new Zend_Exception(get_class($this)." Nombre invalide 2 n:".$nombre. " n1:".$this->view->nbViandePreparee);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculCuisiner($nombre);
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeConstuire($this->view->user, $this->nom_systeme);
		} else {
			$this->calculRateCuisiner($nombre);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculRateCuisiner($nombre) {
		$this->view->nbViandePrepareePerdue = floor($nombre / 2);

		if ($this->view->nbViandePrepareePerdue < 1) {
			$this->view->nbViandePrepareePerdue = 1;
		}

		Zend_Loader::loadClass("Laban");
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_preparee_laban' => -$this->view->nbViandePrepareePerdue,
		);
		$labanTable->insertOrUpdate($data);
	}

	/*
	 * Transforme 1 unité de viande préparée en 1D2+1 aliment
	 */
	private function calculCuisiner($nombre) {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("TypeAliment");
		Zend_Loader::loadClass("ElementAliment");

		Zend_Loader::loadClass('Bral_Util_Commun');
		$this->view->effetRune = false;

		$this->view->nbAliment = $nombre;
		$this->view->nbViandePreparee = $nombre;

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "RU")) { // s'il possède une rune RU
			$this->view->nbAliment = $this->view->nbAliment + 1;
			$this->view->effetRune = true;
		} else {
			$this->view->nbAliment = $this->view->nbAliment + 0;
		}

		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_preparee_laban' => -$nombre,
		);
		$labanTable->insertOrUpdate($data);

		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		$poidsRestant = $poidsRestant + (Bral_Util_Poids::POIDS_VIANDE_PREPAREE * $nombre);
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbAlimentPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_ALIMENT);

		$this->view->nbAlimentATerre = 0;
		if ($this->view->nbAliment > $nbAlimentPossible) {
			$this->view->nbAlimentLaban = $nbAlimentPossible;
			$this->view->nbAlimentATerre = $this->view->nbAliment - $this->view->nbAlimentLaban;
		} else {
			$this->view->nbAlimentLaban = $this->view->nbAliment;
		}

		$this->calculQualite();
		$this->view->qualiteAliment = $this->view->niveauQualite;

		$typeAlimentTable = new TypeAliment();
		$aliment = $typeAlimentTable->findById(TypeAliment::ID_TYPE_RAGOUT);

		$this->view->typeAliment = $aliment;

		$this->view->bbdfAliment = $this->calculBBDF($aliment->bbdf_base_type_aliment, $this->view->niveauQualite);

		$elementAlimentTable = new ElementAliment();
		$labanAlimentTable = new LabanAliment();

		Zend_Loader::loadClass("IdsAliment");
		$idsAliment = new IdsAliment();

		for ($i = 1; $i <= $this->view->nbAliment; $i++) {

			$id_aliment = $idsAliment->prepareNext();

			$data = array(
				"id_element_aliment" => $id_aliment,
				"id_fk_type_element_aliment" => TypeAliment::ID_TYPE_RAGOUT,
				"x_element_aliment" => $this->view->user->x_hobbit,
				"y_element_aliment" => $this->view->user->y_hobbit,
				"z_element_aliment" => $this->view->user->z_hobbit,
				"id_fk_type_qualite_element_aliment" => $this->view->qualiteAliment,
				"bbdf_element_aliment" => $this->view->bbdfAliment,
			);
			$elementAlimentTable->insert($data);

			if ($i <= $this->view->nbAlimentLaban) {
				$where = "id_element_aliment = ".(int)$id_aliment;
				$elementAlimentTable->delete($where);

				$data = array(
					'id_laban_aliment' => $id_aliment,
					'id_fk_hobbit_laban_aliment' => $this->view->user->id_hobbit,
					'id_fk_type_laban_aliment' => TypeAliment::ID_TYPE_RAGOUT,
					'id_fk_type_qualite_laban_aliment' => $this->view->qualiteAliment,
					'bbdf_laban_aliment' => $this->view->bbdfAliment,
				);
				$labanAlimentTable->insert($data);
			}
		}

		Zend_Loader::loadClass("StatsFabricants");
		$statsFabricants = new StatsFabricants();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataFabricants["niveau_hobbit_stats_fabricants"] = $this->view->user->niveau_hobbit;
		$dataFabricants["id_fk_hobbit_stats_fabricants"] = $this->view->user->id_hobbit;
		$dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
		$dataFabricants["nb_piece_stats_fabricants"] = $this->view->nbAliment;
		$dataFabricants["id_fk_metier_stats_fabricants"] = $this->view->config->game->metier->cuisinier->id;
		$statsFabricants->insertOrUpdate($dataFabricants);
	}

	private function calculQualite() {
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"] / 100;

		$chance_a = -0.375 * $maitrise + 53.75 ;
		$chance_b = 0.25 * $maitrise + 42.5 ;
		$chance_c = 0.125 * $maitrise + 3.75 ;

		/*
		 * Seul le meilleur des n jets est gardé. n=(BM SAG/2)+1.
		 */
		$n = (($this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit) / 2 ) + 1;

		if ($n < 1) $n = 1;

		$tirage = 0;

		for ($i = 1; $i <= $n; $i ++) {
			$tirageTemp = Bral_Util_De::get_1d100();
			if ($tirageTemp > $tirage) {
				$tirage = $tirageTemp;
			}
		}

		$qualite = -1;
		if ($tirage > 0 && $tirage <= $chance_a) {
			$qualite = 1;
			$this->view->qualite = "frugale";
		} elseif ($tirage > $chance_a && $tirage <= $chance_a + $chance_b) {
			$qualite = 2;
			$this->view->qualite = "correcte";
		} else {
			$qualite = 3;
			$this->view->qualite = "copieuse";
		}
		$this->view->niveauQualite = $qualite;
	}

	private function calculBBDF($base, $niveauQualite) {
		$bm = 0;
		/*
		 * Mauvaise : -20%/-10%
		 * Normale : -5%/+10%
		 * Bonne : +15%/+25%
		 */
		if ($niveauQualite == 1) {
			$bm = - Bral_Util_De::get_de_specifique(10, 20);
		} else if ($niveauQualite == 2) {
			$bm = - 5 + Bral_Util_De::get_de_specifique(0, 15);
		} else { // 3
			$bm = Bral_Util_De::get_de_specifique(15, 25);
		}
		return $base + $bm;
	}

	function getListBoxRefresh() {
		if ($this->view->nbAlimentATerre == 0) {
			return $this->constructListBoxRefresh(array("box_competences_metiers", "box_laban"));
		} else {
			return $this->constructListBoxRefresh(array("box_vue", "box_competences_metiers", "box_laban"));
		}
	}
}
