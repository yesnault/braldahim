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
class Bral_Competences_Deposer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Laban');

		$tabLaban["nb_castar"] = 0;
		$tabLaban["nb_peau"] = 0;
		$tabLaban["nb_viande"] = 0;
		$tabLaban["nb_viande_preparee"] = 0;
		$tabLaban["nb_cuir"] = 0;
		$tabLaban["nb_fourrure"] = 0;
		$tabLaban["nb_planche"] = 0;

		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);

		if (count($laban) == 1) {
			$p = $laban[0];
			$tabLaban = array(
				"nb_castar" => $this->view->user->castars_hobbit,
				"nb_peau" => $p["quantite_peau_laban"],
				"nb_viande" => $p["quantite_viande_laban"],
				"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
				"nb_cuir" => $p["quantite_cuir_laban"],
				"nb_fourrure" => $p["quantite_fourrure_laban"],
				"nb_planche" => $p["quantite_planche_laban"],
			);
		}

		$this->view->deposerOk = false;
		if ($tabLaban["nb_peau"] > 0  || $tabLaban["nb_cuir"] > 0 || $tabLaban["nb_castar"] ||
		$tabLaban["nb_fourrure"] > 0 || $tabLaban["nb_planche"] > 0 || $tabLaban["nb_viande"] ||
		$tabLaban["nb_viande_preparee"]) {
			$this->view->deposerOk = true;
		}

		$this->prepareTypeRunes();
		$this->prepareTypeEquipements();
		$this->prepareTypePotions();
		$this->prepareTypeAliments();
		$this->view->nb_valeurs = 11;
		$this->prepareTypeMunitions();
		$this->prepareTypePartiesPlantes();
		$this->prepareTypeMinerais();
		$this->view->laban = $tabLaban;
	}

	private function calculDeposer() {
		$nbCastars = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
		$this->deposeTypeAutres("castar", $nbCastars);

		$nbPeau = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		$this->deposeTypeAutres("peau", $nbPeau);

		$nbCuir = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		$this->deposeTypeAutres("cuir", $nbCuir);

		$nbFourrure = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
		$this->deposeTypeAutres("fourrure", $nbFourrure);

		$nbViande = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
		$this->deposeTypeAutres("viande", $nbViande);

		$nbViandePreparee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_6"));
		$this->deposeTypeAutres("viande_preparee", $nbViandePreparee);

		$nbPlanche = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_7"));
		$this->deposeTypeAutres("planche", $nbPlanche);

		$this->deposeTypeRunes();
		$this->deposeTypePotions();
		$this->deposeTypeEquipements();
		$this->deposeTypeAliments();
		$this->deposeTypeMunitions();
		$this->deposeTypeMinerais();
		$this->deposeTypePartiesPlantes();
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

		$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a déposé des éléments à terre";
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->deposer);

		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_vue", "box_laban", "box_profil");
	}

	private function prepareTypeEquipements() {
		Zend_Loader::loadClass("LabanEquipement");
		$tabEquipements = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanEquipementTable);

		Zend_Loader::loadClass("Bral_Util_Equipement");

		if (count($equipements) > 0) {
			foreach ($equipements as $e) {
				$tabEquipements[$e["id_laban_equipement"]] = array(
						"id_equipement" => $e["id_laban_equipement"],
						"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_laban_equipement"]),
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_recette_equipement"],
						"nb_runes" => $e["nb_runes_laban_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"nb_runes" => $e["nb_runes_laban_equipement"],
						"id_fk_mot_runique" => $e["id_fk_mot_runique_laban_equipement"], 
						"id_fk_recette" => $e["id_fk_recette_laban_equipement"] ,
						"id_fk_region" => $e["id_fk_region_laban_equipement"],
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->equipements = $tabEquipements;
	}

	private function deposeTypeEquipements() {
		Zend_Loader::loadClass("ElementEquipement");

		$equipements = array();
		$equipements = $this->request->get("valeur_10");

		if (count($equipements) > 0 && $equipements != 0) {
			foreach ($equipements as $idEquipement) {
				if (!array_key_exists($idEquipement, $this->view->equipements)) {
					throw new Zend_Exception(get_class($this)." ID Equipement invalide : ".$idEquipement);
				}

				$equipement = $this->view->equipements[$idEquipement];

				$labanEquipementTable = new LabanEquipement();
				$where = "id_laban_equipement=".$idEquipement;
				$labanEquipementTable->delete($where);
				unset($labanEquipementTable);

				$dateCreation = date("Y-m-d H:i:s");
				$nbJours = Bral_Util_De::get_2d10();
				$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

				$elementEquipementTable = new ElementEquipement();
				$data = array (
					"id_element_equipement" => $equipement["id_equipement"],
					"x_element_equipement" => $this->view->user->x_hobbit,
					"y_element_equipement" => $this->view->user->y_hobbit,
					"id_fk_recette_element_equipement" => $equipement["id_fk_recette"],
					"nb_runes_element_equipement" => $equipement["nb_runes"],
					"id_fk_mot_runique_element_equipement" => $equipement["id_fk_mot_runique"],
					"date_fin_element_equipement" => $dateFin,
					"id_fk_region_element_equipement" => $equipement["id_fk_region"],
				);
				$elementEquipementTable->insert($data);
				unset($elementEquipementTable);
			}
		}
	}

	private function prepareTypeRunes() {
		Zend_Loader::loadClass("LabanRune");
		$tabRunes = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanRuneTable);

		if (count($runes) > 0) {
			foreach ($runes as $r) {
				$tabRunes[$r["id_rune_laban_rune"]] = array(
					"id_rune" => $r["id_rune_laban_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_laban_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
					"id_fk_type_rune" => $r["id_fk_type_laban_rune"],
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->runes = $tabRunes;
	}

	private function deposeTypeRunes() {
		Zend_Loader::loadClass("ElementRune");
		$runes = array();
		$runes = $this->request->get("valeur_8");
		if (count($runes) > 0 && $runes !=0 ) {
			foreach ($runes as $idRune) {
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
					"id_rune_element_rune" => $rune["id_rune"],
					"x_element_rune" => $this->view->user->x_hobbit,
					"y_element_rune" => $this->view->user->y_hobbit,
					"id_fk_type_element_rune" => $rune["id_fk_type_rune"],
					"est_identifiee_element_rune" => $rune["est_identifiee"],
				);
				$elementRuneTable->insert($data);
				unset($elementRuneTable);
			}
		}
	}

	private function prepareTypePotions() {
		Zend_Loader::loadClass("LabanPotion");
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanPotionTable);

		if (count($potions) > 0) {
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
			$this->view->deposerOk = true;
		}
		$this->view->potions = $tabPotions;
	}

	private function deposeTypePotions() {
		Zend_Loader::loadClass("ElementPotion");
		$potions = array();
		$potions = $this->request->get("valeur_9");
		if (count($potions) > 0 && $potions != 0) {
			foreach ($potions as $idPotion) {
				if (!array_key_exists($idPotion, $this->view->potions)) {
					throw new Zend_Exception(get_class($this)." ID Potion invalide : ".$idPotion);
				}

				$potion = $this->view->potions[$idPotion];

				$labanPotionTable = new LabanPotion();
				$where = "id_laban_potion=".$idPotion;
				$labanPotionTable->delete($where);
				unset($labanPotionTable);

				$dateCreation = date("Y-m-d H:i:s");
				$nbJours = Bral_Util_De::get_2d10();
				$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

				$elementPotionTable = new ElementPotion();
				$data = array (
					"id_element_potion" => $potion["id_potion"],
					"x_element_potion" => $this->view->user->x_hobbit,
					"y_element_potion" => $this->view->user->y_hobbit,
					"niveau_element_potion" => $potion["niveau"],
					"id_fk_type_qualite_element_potion" => $potion["id_fk_type_qualite"],
					"id_fk_type_element_potion" => $potion["id_fk_type"],
					"date_fin_element_potion" => $dateFin,
				);
				$elementPotionTable->insert($data);
				unset($elementPotionTable);
			}
		}
	}

	private function prepareTypeAliments() {
		Zend_Loader::loadClass("LabanAliment");
		$tabAliments = null;
		$labanAlimentTable = new LabanAliment();
		$aliments = $labanAlimentTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanAlimentTable);

		if (count($aliments) > 0) {
			foreach ($aliments as $p) {
				$tabAliments[$p["id_laban_aliment"]] = array(
							"id_aliment" => $p["id_laban_aliment"],
							"nom" => $p["nom_type_aliment"],
							"qualite" => $p["nom_type_qualite"],
							"bbdf" => $p["bbdf_laban_aliment"],
							"id_fk_type_qualite" => $p["id_fk_type_qualite_laban_aliment"],
							"id_fk_type" => $p["id_fk_type_laban_aliment"]
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->aliments = $tabAliments;
	}

	private function deposeTypeAliments() {
		Zend_Loader::loadClass("ElementAliment");
		$aliments = array();
		$aliments = $this->request->get("valeur_11");
		if (count($aliments) > 0 && $aliments !=0 ) {
			foreach ($aliments as $idAliment) {
				if (!array_key_exists($idAliment, $this->view->aliments)) {
					throw new Zend_Exception(get_class($this)." ID Aliment invalide : ".$idAliment);
				}

				$aliment = $this->view->aliments[$idAliment];

				$labanAlimentTable = new LabanAliment();
				$where = "id_laban_aliment=".$idAliment;
				$labanAlimentTable->delete($where);
				unset($labanAlimentTable);

				$dateCreation = date("Y-m-d H:i:s");
				$nbJours = Bral_Util_De::get_2d10();
				$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

				$elementAlimentTable = new ElementAliment();
				$data = array (
							"id_element_aliment" => $aliment["id_aliment"],
							"x_element_aliment" => $this->view->user->x_hobbit,
							"y_element_aliment" => $this->view->user->y_hobbit,
							"bbdf_element_aliment" => $aliment["bbdf"],
							"id_fk_type_qualite_element_aliment" => $aliment["id_fk_type_qualite"],
							"id_fk_type_element_aliment" => $aliment["id_fk_type"],
							"date_fin_element_aliment" => $dateFin,
				);
				$elementAlimentTable->insert($data);
				unset($elementAlimentTable);
			}
		}
	}

	private function prepareTypeMunitions() {
		Zend_Loader::loadClass("LabanMunition");
		$tabMunitionsBruts = null;
		$tabMunitions = null;
		$labanMunitionTable = new LabanMunition();
		$munitions = $labanMunitionTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanMunitionTable);

		if (count($munitions) > 0) {
			foreach ($munitions as $m) {
				if ($m["quantite_laban_munition"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabMunitions[$this->view->nb_valeurs] = array(
						"id_type_munition" => $m["id_fk_type_laban_munition"],
						"type" => $m["nom_type_munition"],
						"quantite" => $m["quantite_laban_munition"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
				}
			}
			$this->view->deposerOk = true;
		}
		$this->view->valeur_fin_munitions = $this->view->nb_valeurs;
		$this->view->munitions = $tabMunitions;
	}

	private function deposeTypeMunitions() {
		Zend_Loader::loadClass("ElementMunition");

		if (count($this->view->munitions) > 0) {
			$idMunition = null;
			$nbMunition = null;

			$labanMunitionTable = new LabanMunition();
			$elementMunitionTable = new ElementMunition();

			for ($i=12; $i<=$this->view->valeur_fin_munitions; $i++) {
					
				if ( $this->request->get("valeur_".$i) > 0) {
					$nbMunition = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_".$i));

					$munition = $this->view->munitions[$i];

					if ($nbMunition > $munition["quantite"] || $nbMunition < 0) {
						throw new Zend_Exception(get_class($this)." Quantite Munition invalide : ".$nbMunition);
					}

					$data = array(
						"quantite_laban_munition" => -$nbMunition,
						"id_fk_type_laban_munition" => $munition["id_type_munition"],
						"id_fk_hobbit_laban_munition" => $this->view->user->id_hobbit,
					);
					$labanMunitionTable->insertOrUpdate($data);

					$data = array (
						"x_element_munition" => $this->view->user->x_hobbit,
						"y_element_munition" => $this->view->user->y_hobbit,
						"id_fk_type_element_munition" => $munition["id_type_munition"],
						"quantite_element_munition" => $nbMunition,
					);
					$elementMunitionTable->insertOrUpdate($data);
				}
			}
			unset($elementMunitionTable);
			unset($labanMunitionTable);
		}
	}

	private function prepareTypeMinerais() {
		Zend_Loader::loadClass("LabanMinerai");

		$tabMinerais = null;
		$labanMineraiTable = new labanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->nb_minerai_brut = 0;
		$this->view->nb_minerai_lingot = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				if ($m["quantite_brut_laban_minerai"] > 0 || $m["quantite_lingots_laban_minerai"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
					$tabMinerais[$this->view->nb_valeurs] = array(
						"type" => $m["nom_type_minerai"],
						"id_fk_type_laban_minerai" => $m["id_fk_type_laban_minerai"],
						"id_fk_hobbit_laban_minerai" => $m["id_fk_hobbit_laban_minerai"],
						"quantite_brut_laban_minerai" => $m["quantite_brut_laban_minerai"],
						"quantite_lingots_laban_minerai" => $m["quantite_lingots_laban_minerai"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // lingot
					$this->view->nb_minerai_brut = $this->view->nb_minerai_brut + $m["quantite_brut_laban_minerai"];
					$this->view->nb_minerai_lingot = $this->view->nb_minerai_lingot + $m["quantite_lingots_laban_minerai"];
				}
			}
		}

		$this->view->minerais = $tabMinerais;
	}

	private function deposeTypeMinerais() {
		Zend_Loader::loadClass("ElementMinerai");
		Zend_Loader::loadClass("LabanMinerai");

		$labanMineraiTable = new LabanMinerai();
		$elementMineraiTable = new ElementMinerai();

		for ($i=$this->view->valeur_fin_partieplantes + 1; $i<=$this->view->nb_valeurs; $i = $i + 2) {
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
			if ($nbBrut > $this->view->minerais[$indice]["quantite_brut_laban_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai brut interdit=".$nbBrut);
			}

			if ((int) $nbLingot."" != $this->request->get("valeur_".$indiceLingot)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot invalide=".$nbLingot. " indice=".$indiceLingot);
			} else {
				$nbLingot = (int)$nbLingot;
			}
			if ($nbLingot > $this->view->minerais[$indice]["quantite_lingots_laban_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot interdit=".$nbLingot);
			}

			if ($nbBrut > 0 || $nbLingot > 0) {
				$data = array("x_element_minerai" => $this->view->user->x_hobbit,
							  "y_element_minerai" => $this->view->user->y_hobbit,
							  'quantite_brut_element_minerai' => $nbBrut,
							  'quantite_lingots_element_minerai' => $nbLingot,
							  'id_fk_type_element_minerai' => $this->view->minerais[$indice]["id_fk_type_laban_minerai"],
				);
				$elementMineraiTable->insertOrUpdate($data);

				$data = array(
					'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_laban_minerai"],
					'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
					'quantite_brut_laban_minerai' => -$nbBrut,
					'quantite_lingots_laban_minerai' => -$nbLingot,
				);

				$labanMineraiTable->insertOrUpdate($data);
			}
		}
	}

	private function prepareTypePartiesPlantes() {
		Zend_Loader::loadClass("LabanPartieplante");

		$tabPartiePlantes = null;
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->nb_partiePlantes = 0;
		$this->view->nb_prepareesPartiePlantes = 0;

		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				if ($p["quantite_laban_partieplante"] > 0 || $p["quantite_preparee_laban_partieplante"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brute
					$tabPartiePlantes[$this->view->nb_valeurs] = array(
						"nom_type" => $p["nom_type_partieplante"],
						"nom_plante" => $p["nom_type_plante"],
						"id_fk_type_laban_partieplante" => $p["id_fk_type_laban_partieplante"],
						"id_fk_type_plante_laban_partieplante" => $p["id_fk_type_plante_laban_partieplante"],
						"id_fk_hobbit_laban_partieplante" => $p["id_fk_hobbit_laban_partieplante"],
						"quantite_laban_partieplante" => $p["quantite_laban_partieplante"],
						"quantite_preparee_laban_partieplante" => $p["quantite_preparee_laban_partieplante"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // préparée
					$this->view->nb_partiePlantes = $this->view->nb_partiePlantes + $p["quantite_laban_partieplante"];
					$this->view->nb_prepareesPartiePlantes = $this->view->nb_prepareesPartiePlantes + $p["quantite_preparee_laban_partieplante"];
				}
			}
		}

		$this->view->valeur_fin_partieplantes = $this->view->nb_valeurs;
		$this->view->partieplantes = $tabPartiePlantes;
	}

	private function deposeTypePartiesPlantes() {
		Zend_Loader::loadClass("ElementPartieplante");
		Zend_Loader::loadClass("LabanPartieplante");

		$labanPartiePlanteTable = new LabanPartieplante();
		$elementPartiePlanteTable = new ElementPartieplante();

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
			if ($nbBrutes > $this->view->partieplantes[$indice]["quantite_laban_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute interdit=".$nbBrutes);
			}
			if ((int) $nbPreparees."" != $this->request->get("valeur_".$indicePreparees)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Preparee invalide=".$nbPreparees);
			} else {
				$nbPreparees = (int)$nbPreparees;
			}
			if ($nbPreparees > $this->view->partieplantes[$indice]["quantite_preparee_laban_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Preparee interdit=".$nbPreparees);
			}
			if ($nbBrutes > 0 || $nbPreparees > 0) {
				$data = array("x_element_partieplante" => $this->view->user->x_hobbit,
							  "y_element_partieplante" => $this->view->user->y_hobbit,
							  'quantite_element_partieplante' => $nbBrutes,
							  'quantite_preparee_element_partieplante' => $nbPreparees,
							  'id_fk_type_element_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_laban_partieplante"],
							  'id_fk_type_plante_element_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_laban_partieplante"],
				);
				$elementPartiePlanteTable->insertOrUpdate($data);

				$data = array(
						'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_laban_partieplante"],
						'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_laban_partieplante"],
						'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
						'quantite_laban_partieplante' => -$nbBrutes,
						'quantite_preparee_laban_partieplante' => -$nbPreparees
				);
				$labanPartiePlanteTable->insertOrUpdate($data);
			}
		}
	}

	private function deposeTypeAutres($nom_systeme, $nb) {
		Zend_Loader::loadClass("Element");

		if ($nb < 0) {
			throw new Zend_Exception(get_class($this)." Nb ".$nom_systeme." : ".$nb);
		}

		if ($nb > 0){

			if ($nom_systeme == "castar") {
				if ($nb > $this->view->user->castars_hobbit) {
					$nb = $this->view->user->castars_hobbit;
				}
				$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $nb;
			} else {
				if ($nb > $this->view->laban["nb_".$nom_systeme]) {
					$nb = $this->view->laban["nb_".$nom_systeme];
				}
				$labanTable = new Laban();
				$data = array(
					"quantite_".$nom_systeme."_laban" => -$nb,
					"id_fk_hobbit_laban" => $this->view->user->id_hobbit,
				);
				$labanTable->insertOrUpdate($data);
				unset($labanTable);
			}

			$elementTable = new Element();
			$data = array(
				"quantite_".$nom_systeme."_element" => $nb,
				"x_element" => $this->view->user->x_hobbit,
				"y_element" => $this->view->user->y_hobbit,
			);
			$elementTable->insertOrUpdate($data);
			unset($elementTable);
		}
	}
}