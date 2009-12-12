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
		$this->view->sourceEtDestinationOk = false;
		$this->view->estQuintuple = false;
		$this->idDestination = null;
		$this->idSource = null;

		$this->prepareAliments();
		$this->calculEchoppe("cuisinier");

		if ($this->view->typeAlimentCourant != null) {
			$this->calculCharrette();
			$this->prepareIngredients(); // présent soit dans l'échoppe, soit dans le laban
			$this->prepareDestinations(); // Soit dans l'échoppe, soit le laban, soit la charrette
			$this->controleSource();
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
				'selected' => $selected,
				'poids_unitaire_type_aliment' => $t['poids_unitaire_type_aliment'],
				'type_bbdf_type_aliment' => $t['type_bbdf_type_aliment'],
				'poids_ingredients' => 100000, // calculé ensuite
				'type_bbdf_type_aliment' => $t['type_bbdf_type_aliment'],
				'texte_type_bbdf' => $this->texteTypeBbdf($t['type_bbdf_type_aliment']),
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

		Zend_Loader::loadClass("Bral_Util_Potion");
		Zend_Loader::loadClass("RecetteAliments");
		$recetteAlimentsTable = new RecetteAliments();
		$ingredientsRecetteRowset = $recetteAlimentsTable->findByIdTypeAliment($this->view->typeAlimentCourant["id_type_aliment"]);

		if ($ingredientsRecetteRowset == null || count($ingredientsRecetteRowset) < 0) {
			throw new Zend_Exception("Erreur recette aliment".$this->view->typeAlimentCourant["id_type_aliment"]);
		}

		Zend_Loader::loadClass("RecetteAlimentsPotions");
		$recetteAlimentsPotionsTable = new RecetteAlimentsPotions();
		$potionsRecetteRowset = $recetteAlimentsPotionsTable->findByIdTypeAliment($this->view->typeAlimentCourant["id_type_aliment"]);
		if ($potionsRecetteRowset != null && count($potionsRecetteRowset) > 0) {
			$this->view->recetteAvecPotion = true;
			$potionDemandee = $potionsRecetteRowset[0];
		} else {
			$this->view->recetteAvecPotion = false;
		}

		$tabSources = null;
		$poidsIngredients = 0;
		if ($this->view->estSurEchoppe === true) {
			$tabSources["echoppe"]["nom"] = "Votre échoppe";
			$tabSources["echoppe"]["possible"] = true;

			Zend_Loader::loadClass("EchoppeIngredient");
			$echoppeIngredientTable = new EchoppeIngredient();
			$ingredients = $echoppeIngredientTable->findByIdEchoppe($this->view->idEchoppe);
			$tabSources["echoppe"]["ingredients"] = $ingredients;

			if ($this->view->recetteAvecPotion) {
				Zend_Loader::loadClass("EchoppePotion");
				$echoppePotionTable = new EchoppePotion();
				$potions = $echoppePotionTable->findByIdEchoppe($this->view->idEchoppe, $potionDemandee["id_type_potion"]);
				$tabSources["echoppe"]["potions"] = Bral_Util_Potion::prepareTabPotions($potions);
			}
		} else {

			if ($this->view->possedeCharrette === true) {
				$tabSources["charrette"]["nom"] = "Votre charrette";
				$tabSources["charrette"]["possible"] = true;

				Zend_Loader::loadClass("CharretteIngredient");
				$charretteIngredientTable = new CharretteIngredient();
				$ingredients = $charretteIngredientTable->findByIdCharrette($this->view->idCharrette);
				$tabSources["charrette"]["ingredients"] = $ingredients;

				if ($this->view->recetteAvecPotion) {
					Zend_Loader::loadClass("CharrettePotion");
					$charrettePotionTable = new CharrettePotion();
					$potions = $charrettePotionTable->findByIdCharrette($this->view->idCharrette, $potionDemandee["id_type_potion"]);
					$tabSources["charrette"]["potions"] = Bral_Util_Potion::prepareTabPotions($potions);
				}
			}

			$tabSources["laban"]["nom"] = "Votre laban";
			$tabSources["laban"]["possible"] = true;

			Zend_Loader::loadClass("LabanIngredient");
			$labanIngredientTable = new LabanIngredient();
			$ingredients = $labanIngredientTable->findByIdHobbit($this->view->user->id_hobbit);
			$tabSources["laban"]["ingredients"] = $ingredients;

			if ($this->view->recetteAvecPotion) {
				Zend_Loader::loadClass("LabanPotion");
				$labanPotionTable = new LabanPotion();
				$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit, $potionDemandee["id_type_potion"]);
				$tabSources["laban"]["potions"] = Bral_Util_Potion::prepareTabPotions($potions);
			}
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

		$tabPotions = null;
		if ($potionsRecetteRowset != null) {
			if (count($potionsRecetteRowset) > 1) {
				throw new Zend_Exception('Erreur parametrage nb potion');
			}

			foreach($potionsRecetteRowset as $i) {
				$tabPotions[] = array(
					'nom_type_potion' => $i['nom_type_potion'],
					'id_type_potion' => $i['id_type_potion'],
				);
				$this->controlePotionsDispo($tabSources, $i["id_type_potion"]);
				$this->view->idPotionIngredient = $i['id_type_potion'];
			}
		}

		$this->view->typeAlimentCourant["poids_ingredients"] = $poidsIngredients;

		$this->view->ingredients = $tabIngredients;
		$this->view->potions = $tabPotions;
		$this->view->sources = $tabSources;
	}

	private function controleIngredientsDispo(&$tabSources, $idTypeIngredient, $quantite) {
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
				}
			}
		}
	}

	private function controlePotionsDispo(&$tabSources, $idTypePotion) {
		foreach($tabSources as $k => $v) {
			if ($tabSources[$k]["possible"] === true) {
				if ($tabSources[$k]["potions"] != null && count($tabSources[$k]["potions"]) > 0) {
					$ingredientOk = false;
					foreach($tabSources[$k]["potions"] as $i) {
						if ($i["id_type_potion"] == $idTypePotion) {
							$potionOk = true;
						}
					}
					$tabSources[$k]["possible"] = $potionOk;
				} else {
					$tabSources[$k]["possible"] = false;
				}
			}
		}
	}

	private function prepareDestinations() {
		$tabDestinations = null;

		if ($this->view->typeAlimentCourant['type_bbdf_type_aliment'] == 'quintuple') {
			$tabDestinations["case"]["possible"] = true;
			$tabDestinations["case"]["nom"] = "Tous les hobbits sur votre case";
			$tabDestinations["case"]["poids_apres_ingredient"] = 10000;
			$tabDestinations["case"]["poids_restant"] = 10000;
		} else {
			if ($this->view->estSurEchoppe === true) {
				$tabDestinations["echoppe"]["possible"] = true;
				$tabDestinations["echoppe"]["nom"] = "Votre échoppe";
				$tabDestinations["echoppe"]["poids_apres_ingredient"] = 10000;
				$tabDestinations["echoppe"]["poids_restant"] = 10000;
			} else {

				if ($this->view->possedeCharrette === true) {
					$tabDestinations["charrette"]["possible"] = true;
					$tabDestinations["charrette"]["nom"] = "Votre charrette";
					$tabDestinations["charrette"]["poids_apres_ingredient"] = $this->view->poidsRestantCharrette + $this->view->typeAlimentCourant["poids_ingredients"];
					$tabDestinations["charrette"]["poids_restant"] = $this->view->poidsRestantCharrette;
				}

				$tabDestinations["laban"]["possible"] = true;
				$tabDestinations["laban"]["nom"] = "Votre laban";
				$tabDestinations["laban"]["poids_apres_ingredient"] = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit + $this->view->typeAlimentCourant["poids_ingredients"];
				$tabDestinations["laban"]["poids_restant"] = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;

				$tabDestinations["sol"]["possible"] = true;
				$tabDestinations["sol"]["nom"] = "Au Sol";
				$tabDestinations["sol"]["poids_apres_ingredient"] = 10000;
				$tabDestinations["sol"]["poids_restant"] = 10000;
			}
		}

		$this->view->destinations = $tabDestinations;
	}

	private function controleSource() {

		$uneSourceOk = false;
		foreach($this->view->sources as $s) {
			if ($s["possible"] == true) {
				$uneSourceOk = true;
			}
		}

		if ($uneSourceOk === true) {
			$this->view->sourceOk = true;
		} else {
			$this->view->sourceOk = false;
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

		// Verification cuisiner
		if ($this->view->sourceOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdit source KO ");
		}

		$idTypeAliment = (int)$this->request->get("valeur_1");
		$idSource = $this->request->get("valeur_2");
		$idDestination = $this->request->get("valeur_3");

		if ($idTypeAliment != $this->view->typeAlimentCourant["id_type_aliment"]) {
			throw new Zend_Exception(get_class($this)." idTypeAliment interdit A=".$idTypeAliment. " B=".$this->view->typeAlimentCourant["id_type_aliment"]);
		}

		if ($this->view->recetteAvecPotion === true) {
			list ($idSource, $idPotion) = split("-", $idSource);
		} else {
			$idPotion = null;
		}
		$sourceOk = false;
		foreach ($this->view->sources as $k => $v) {
			if ($k == $idSource && $v["possible"] === true) {
				if ($idPotion != null) {
					foreach ($v["potions"] as $x => $y) {
						if ($idPotion == $y["id_potion"]) {
							$sourceOkPotion = true;
						}
					}
				}
				$sourceOk = true;
			}
		}

		if ($sourceOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdit source KO B idSource:".$idSource);
		}

		if ($this->view->recetteAvecPotion === true && $sourceOkPotion === false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdit source KO Potion $idPotion:".$idPotion);
		}

		$this->idSource = $idSource;

		$destinationOk = false;
		foreach ($this->view->destinations as $k => $v) {
			if ($k == $idDestination && $v["possible"] === true) {
				$destinationOk = true;
			}
		}

		if ($destinationOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdit destination KO idDestination:".$idDestination);
		}
		$this->idDestination = $idDestination;

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculCuisiner($idSource, $idDestination, $idPotion);
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeConstuire($this->view->user, $this->nom_systeme);
		} else {
			$this->calculRateCuisiner($idSource);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculRateCuisiner($idSource) {
		$this->retireIngredients($idSource, true);
	}

	private function calculCuisiner($idSource, $idDestination, $idPotion) {

		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("TypeAliment");
		Zend_Loader::loadClass("ElementAliment");

		Zend_Loader::loadClass('Bral_Util_Commun');
		$this->view->effetRune = false;

		$this->view->nbAliment = Bral_Util_De::get_1d2() + 1;
		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "RU")) { // s'il possède une rune RU
			$this->view->nbAliment = $this->view->nbAliment + 1;
			$this->view->effetRune = true;
		} else {
			$this->view->nbAliment = $this->view->nbAliment + 0;
		}

		$this->retireIngredients($idSource);

		$poidsRestant = $this->view->destinations[$idDestination]["poids_restant"];
		if ($idSource == $idDestination) {
			$poidsRestant = $this->view->destinations[$idDestination]["poids_apres_ingredient"];
		}

		$nbAlimentPossible = floor($poidsRestant / $this->view->typeAlimentCourant['poids_unitaire_type_aliment']);
		if ($nbAlimentPossible < 0) {
			$nbAlimentPossible = 0;
		}

		$this->view->nbAlimentATerre = 0;
		if ($this->view->nbAliment > $nbAlimentPossible) {
			$this->view->nbAlimentDestination = intval($nbAlimentPossible);
			$this->view->nbAlimentATerre = floor($this->view->nbAliment - $this->view->nbAlimentDestination);
		} else {
			$this->view->nbAlimentDestination = $this->view->nbAliment;
		}

		$this->calculQualite();
		$this->view->qualiteAliment = $this->view->niveauQualite;
		$this->view->bbdfAliment = $this->calculBBDF($this->view->typeAlimentCourant['type_bbdf_type_aliment'], $this->view->niveauQualite);

		if ($this->view->typeAlimentCourant['type_bbdf_type_aliment'] == 'quintuple') {
			$this->appliqueQuintuple();
			$idType = $this->view->config->game->evenements->type->competence;
			$details = "[h".$this->view->user->id_hobbit."] cuisine un banquet pour tous ses confrères.";
			$this->setDetailsEvenement($details, $idType);
		} else {
			$this->creationAliment($idDestination, $idSource, $idPotion);
			$idType = $this->view->config->game->evenements->type->competence;
			$details = "[h".$this->view->user->id_hobbit."] a cuisiné";
			$this->setDetailsEvenement($details, $idType);
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

	private function calculBBDF($typeBbdf, $niveauQualite) {
		//$typeBbdf = 'simple', 'double', 'double_ameliore', 'triple', 'quadruple', 'quintuple'

		if ($typeBbdf == 'simple') {
			$base = 10;
		} elseif ($typeBbdf == 'double') {
			$base = 35;
		} elseif ($typeBbdf == 'double_ameliore') {
			$base = 35;
		} elseif ($typeBbdf == 'triple') {
			$base = 60;
		} elseif ($typeBbdf == 'quadruple') {
			$base = 85;
		} elseif ($typeBbdf == 'quintuple') {
			$base = 60;
		} else {
			throw new Zend_Exception('typeBddf invalide:'.$typeBbdf);
		}

		$bm = Bral_Util_De::get_de_specifique(0, 5);

		if ($niveauQualite == 1) {
			// $base = $base + 0;
		} else if ($niveauQualite == 2) {
			$base = $base + 10;
		} else { // 3
			$base = $base + 20;
		}
		return $base + $bm;
	}

	private function creationAliment($idDestination, $idSource, $idPotion) {
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

		Zend_Loader::loadClass("Bral_Util_Effets");
		// on applique l'effet de la potion
		$potion = null;
		if ($this->view->recetteAvecPotion == true) {
			Zend_Loader::loadClass('Bral_Util_EffetsPotion');
			foreach($this->view->sources[$idSource]["potions"] as $p) {
				if ($idPotion == $p["id_potion"] && $p["id_type_potion"] == $this->view->idPotionIngredient) {

					$potion = $p;
					$this->supprimeDuConteneur($idSource, $p);
					break;
				}
			}
		}
			
		for ($i = 1; $i <= $this->view->nbAliment; $i++) {
			$idAliment = $idsAliment->prepareNext();

			$idEffetHobbit = null;
			if ($potion != null) {
				$idEffetHobbit = Bral_Util_Effets::ajouteEtAppliqueEffetHobbit(null, $potion["caracteristique"], Bral_Util_Effets::TYPE_BONUS, Bral_Util_EffetsPotion::calculNbTour($potion), Bral_Util_EffetsPotion::calculBM($potion));
			}

			if ($this->view->typeAlimentCourant['type_bbdf_type_aliment'] == 'quadruple') {
				$idEffetHobbit = Bral_Util_Effets::ajouteEtAppliqueEffetHobbit(null, Bral_Util_Effets::CARACT_ATT_DEG_DEF, Bral_Util_Effets::TYPE_BONUS, Bral_Util_De::get_2d3(), (floor($this->view->user->niveau_hobbit / 10) + 1) * 4);
			}

			$data = array(
				"id_aliment" => $idAliment,
				"id_fk_type_aliment" => $this->view->typeAlimentCourant['id_type_aliment'],
				"id_fk_type_qualite_aliment" => $this->view->qualiteAliment,
				"bbdf_aliment" => $this->view->bbdfAliment,
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

			if ($i <= $this->view->nbAlimentDestination) {
				$where = "id_element_aliment = ".(int)$idAliment;
				$elementAlimentTable->delete($where);

				$data = $tabBase;
				$data['id_'.$prefix.'_aliment'] = $idAliment;
				$table->insert($data);
			}
		}
		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}
	}

	private function supprimeDuConteneur($idSource, $potion) {
		if ($idSource == "echoppe") {
			$prefix = "echoppe";
			$table = new EchoppePotion();
		} else if ($idSource == "charrette") {
			$prefix = "charrette";
			$table = new CharrettePotion();
		} else if ($idSource == "laban") {
			$prefix = "laban";
			$table = new LabanPotion();
		} else {
			throw new Zend_Exception("creationAliment::Source invalide:".$idSource);
		}

		$where = 'id_'.$prefix.'_potion = '.$potion["id_potion"];
		$table->delete($where);

		Zend_Loader::loadClass('Potion');
		$potionTable = new Potion();
		$where = 'id_potion = '.$potion["id_potion"];
		$data = array('date_utilisation_potion' => date("Y-m-d H:i:s"));
		$potionTable->update($data, $where);
		$this->view->idPotion = $potion["id_potion"];
	}

	// nourrit tous les hobbits sur la cases
	private function appliqueQuintuple() {
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit, -1, false);

		$hobbitTable = new Hobbit();
		$tabHobbit = null;
		foreach($hobbits as $h) {
			$idTypeEvenement = $this->view->config->game->evenements->type->effet;
			if ($this->view->user->id_hobbit != $h["id_hobbit"]) {
				$details = "[h".$h["id_hobbit"]."] s'empresse de manger une bonne assiette de pot au feu offert par [h".$this->view->user->id_hobbit."]";
				$detailsBot = "Balance de faim : +".$this->view->bbdfAliment." %";
				Bral_Util_Evenement::majEvenements($h["id_hobbit"], $idTypeEvenement, $details, $detailsBot, $h["niveau_hobbit"]);
			}
			$tabHobbit[] = $h;

			$data["balance_faim_hobbit"] = $h["balance_faim_hobbit"] + $this->view->bbdfAliment;
			if ($data["balance_faim_hobbit"] > 100) {
				$data["balance_faim_hobbit"] = 100;
			}
			$where = "id_hobbit = ".$h["id_hobbit"];
			$hobbitTable->update($data, $where);
		}

		$this->view->estQuintuple = true;
		$this->view->hobbits = $tabHobbit;
	}

	private function texteTypeBbdf($typeBbdf) {
		if ($typeBbdf == 'simple') {
			$retour = "Simple";
		} elseif ($typeBbdf == 'double') {
			$retour = "Double";
		} elseif ($typeBbdf == 'double_ameliore') {
			$retour = "Double Amélioré";
		} elseif ($typeBbdf == 'triple') {
			$retour = "Triple";
		} elseif ($typeBbdf == 'quadruple') {
			$retour = "Quadruple";
		} elseif ($typeBbdf == 'quintuple') {
			$retour = "Quintuple";
		} else {
			throw new Zend_Exception('Erreut type typeBbdf:'.$typeBbdf);
		}
		return $retour;
	}

	function getListBoxRefresh() {
		$tab[] = 'box_competences_metiers';
		if ($this->idDestination == 'echoppe' || $this->idSource == 'echoppe') {
			$tab[] = 'box_echoppes';
		}
		if ($this->idDestination == 'laban' || $this->idSource == 'laban') {
			$tab[] = 'box_laban';
		}
		if ($this->idDestination == 'sol' || $this->idSource == 'sol' || $this->view->nbAlimentATerre > 0) {
			$tab[] = 'box_vue';
		}
		if ($this->idDestination == 'charrette' || $this->idSource == 'charrette') {
			$tab[] = 'box_charrette';
		}
		return $this->constructListBoxRefresh($tab);
	}

	protected function calculNbPa() {
		if ($this->view->user->pa_hobbit - $this->competence["pa_utilisation"] < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}

		if ($this->view->estSurEchoppe) {
			$this->view->nb_pa = $this->competence["pa_utilisation"] / 2;
		} else {
			$this->view->nb_pa = $this->competence["pa_utilisation"];
		}
	}
}
