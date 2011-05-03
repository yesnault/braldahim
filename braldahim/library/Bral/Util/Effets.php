<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Effets {

	const TYPE_BONUS = "bonus";
	const TYPE_MALUS = "malus";

	const CARACT_FORCE = 'FOR';
	const CARACT_AGILITE = 'AGI';
	const CARACT_VIGUEUR = 'VIG';
	const CARACT_SAGESSE = 'SAG';
	const CARACT_PV = 'PV';
	const CARACT_BBDF = 'BBDF';
	const CARACT_VUE = 'VUE';
	const CARACT_ARMURE = 'ARM';
	const CARACT_POIDS = 'POIDS';
	const CARACT_ATTAQUE = 'ATT';
	const CARACT_DEGAT = 'DEG';
	const CARACT_DEFENSE = 'DEF';
	const CARACT_ATT_DEG_DEF = 'ATTDEGDEF';
	const CARACT_FOR_AGI_VIG_SAG = 'FORAGIVIGSAG';
	const CARACT_STOUT = 'STOUT';

	const CARACT_PA_MARCHER = 'PAMARCHER';

	const CARACT_DUREE_TOUR = 'TOUR';

	public static function ajouteEtAppliqueEffetBraldun($idBraldun, $caract, $type, $nbTour, $bm, $texte = null) {
		Zend_Loader::loadClass("EffetBraldun");

		$effet["nb_tour_restant"] = $nbTour;
		$effet["caracteristique"] = $caract;
		$effet["bm_type"] = $type;
		$effet["bm_effet_braldun"] = $bm;

		$effetBraldunTable = new EffetBraldun();
		$data = array(
			'id_fk_braldun_cible_effet_braldun' => $idBraldun,
			'caract_effet_braldun' => $effet["caracteristique"],
			'bm_effet_braldun' => $effet["bm_effet_braldun"],
			'nb_tour_restant_effet_braldun' => $effet["nb_tour_restant"],
			'bm_type_effet_braldun' => $effet["bm_type"], 
			'texte_effet_braldun' => $texte,
			'texte_calcule_effet_braldun' => null,
		);
		$effet["id_effet_braldun"] = $effetBraldunTable->insert($data);
		$effet["actif"] = true;

		$effetBraldunRowset = $effetBraldunTable->findByIdBraldunCibleAndTypeEffet($idBraldun, array(self::CARACT_FOR_AGI_VIG_SAG, self::CARACT_STOUT));
		$effetSoutEnCours = false;
		$effetQuadrupleEnCours = false;
		// s'il y a déjà des effets Stout ou quadruple en cours
		foreach ($effetBraldunRowset as $e) {
			if ($effet["caracteristique"] == self::CARACT_STOUT) {
				if ($effetStoutEnCours == true) {
					$effet["actif"] = false;
				} else {
					$effetStoutEnCours = true;
				}
			}

			if ($effet["caracteristique"] == self::CARACT_ATT_DEG_DEF) {
				if ($effetQuadrupleEnCours == true) {
					$effet["actif"] = false;
				} else {
					$effetQuadrupleEnCours = true;
				}
			}
		}

		if ($idBraldun != null) {
			$braldunTable = new Braldun();
			$braldunRowset = $braldunTable->find($idBraldun);
			$braldun = $braldunRowset->current();

			Zend_Loader::loadClass("Bral_Util_Effets");
			return Bral_Util_Effets::appliqueEffetSurBraldun($effet, $braldun, false);
		} else {
			return $effet["id_effet_braldun"];
		}
	}

	public static function calculEffetBraldun(&$braldunCible, $appliqueEffet, $idEffet = null) {
		Bral_Util_Log::potion()->trace("Bral_Util_Effets - calculEffetBraldun - enter - appliqueEffet:".$appliqueEffet. " idH:".$braldunCible->id_braldun. " idE:".$idEffet);
		Zend_Loader::loadClass("EffetBraldun");
		$effetBraldunTable = new EffetBraldun();
		if ($idEffet == null) {
			$effetBraldunRowset = $effetBraldunTable->findByIdBraldunCible($braldunCible->id_braldun);
		} else {
			$effetBraldunRowset = $effetBraldunTable->findByIdEffetBraldun($idEffet);
		}
		unset($effetBraldunTable);

		$effets = null;
		$effetStoutEnCours = false;
		$effetQuadrupleEnCours = false;
		foreach ($effetBraldunRowset as $e) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - calculEffetBraldun - effet ".$e["id_effet_braldun"]. " trouve");

			$effet = array(
					"id_effet_braldun" => $e["id_effet_braldun"],
					"nb_tour_restant" => $e["nb_tour_restant_effet_braldun"],
					"caracteristique" => $e["caract_effet_braldun"],
					"bm_type" => $e["bm_type_effet_braldun"],
					"bm_effet_braldun" => $e["bm_effet_braldun"],
					"texte_effet_braldun" => $e["texte_effet_braldun"],
					"texte_calcule_effet_braldun" => $e["texte_calcule_effet_braldun"],
					"actif" => true,
			);

			if ($effet["caracteristique"] == self::CARACT_STOUT) {
				if ($effetStoutEnCours == true) {
					$effet["actif"] = false;
				} else {
					$effetStoutEnCours = true;
				}
			}

			if ($effet["caracteristique"] == self::CARACT_ATT_DEG_DEF) {
				if ($effetQuadrupleEnCours == true) {
					$effet["actif"] = false;
				} else {
					$effetQuadrupleEnCours = true;
				}
			}

			$retourEffet = null;
			if ($appliqueEffet) {
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - calculEffetBraldun - application de l'effet ".$e["id_effet_braldun"]);
				$retourEffet = self::appliqueEffetSurBraldun($effet, $braldunCible, true, false);
				if ($retourEffet != null) {
					$effets[] = array('effet' => $effet, 'retourEffet' => $retourEffet);
				}
			} else {
				$effets[] = array('effet' => $effet, 'retourEffet' => $retourEffet);
			}
		}

		unset($effetBraldunRowset);
		Bral_Util_Log::potion()->trace("Bral_Util_Effets - calculEffetBraldun - exit");
		return $effets;
	}

	private static function appliqueEffetSurBraldun($effet, &$braldunCible, $majTableEffetBraldun = true, $majTableBraldun = true) {
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - enter");
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - braldunCible->id_braldun = ".$braldunCible->id_braldun);
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - majTableEffetBraldun = ".$majTableEffetBraldun);

		Zend_Loader::loadClass("EffetBraldun");

		$retourEffet["nb_tour_restant"] = $effet["nb_tour_restant"];

		if ($majTableEffetBraldun === true) {
			Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurBraldun - maj table effet debut");
			$effetEffetBraldunTable = new EffetBraldun();
			$estSupprime = $effetEffetBraldunTable->enleveUnTour($effet);
			$retourEffet["nb_tour_restant"] = $effet["nb_tour_restant"] - 1;
			unset($effetEffetBraldunTable);
			Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurBraldun - maj table effet fin");
			if ($estSupprime) {
				Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurBraldun - suppression effet");
				return null;
			}
		}

		if ($effet["bm_type"] == 'malus') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - malus");
			$coef = -1;
		} else { // bonus
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - bonus");
			$coef = 1;
		}

		$retourEffet["nEffet"] = $effet["bm_effet_braldun"];

		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - nEffet = ".$retourEffet["nEffet"]);

		if ($effet["caracteristique"] == self::CARACT_AGILITE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur AGI avant = ".$braldunCible->agilite_bm_braldun);
			$braldunCible->agilite_bm_braldun = $braldunCible->agilite_bm_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur AGI apres = ".$braldunCible->agilite_bm_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_FORCE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur FOR avant = ".$braldunCible->force_bm_braldun);
			$braldunCible->force_bm_braldun = $braldunCible->force_bm_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur FOR apres = ".$braldunCible->force_bm_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_PV) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur PV avant = ".$braldunCible->pv_restant_braldun);
			$braldunCible->pv_restant_braldun = $braldunCible->pv_restant_braldun + $coef * $retourEffet["nEffet"];
			if ($braldunCible->pv_restant_braldun > $braldunCible->pv_max_braldun) {
				$braldunCible->pv_restant_braldun = $braldunCible->pv_max_braldun;
			}
			if ($braldunCible->pv_restant_braldun <= 0) {
				$braldunCible->pv_restant_braldun = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur PV apres = ".$braldunCible->pv_restant_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_VUE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur VUE avant = ".$braldunCible->vue_bm_braldun);
			$braldunCible->vue_bm_braldun = $braldunCible->vue_bm_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur VUE apres = ".$braldunCible->vue_bm_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_VIGUEUR) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur VIG avant = ".$braldunCible->vigueur_bm_braldun);
			$braldunCible->vigueur_bm_braldun = $braldunCible->vigueur_bm_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur VIG apres = ".$braldunCible->vigueur_bm_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_SAGESSE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur SAG avant = ".$braldunCible->sagesse_bm_braldun);
			$braldunCible->sagesse_bm_braldun = $braldunCible->sagesse_bm_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur SAG apres = ".$braldunCible->sagesse_bm_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_BBDF) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur BBDF avant = ".$braldunCible->sagesse_bm_braldun);
			$braldunCible->balance_faim_braldun = $braldunCible->balance_faim_braldun + $coef * $retourEffet["nEffet"];
			if ($braldunCible->balance_faim_braldun <= 0) {
				$braldunCible->balance_faim_braldun = 0;
			}
				
			if ($braldunCible->balance_faim_braldun > 100) {
				$braldunCible->balance_faim_braldun = 100;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur BBDF apres = ".$braldunCible->sagesse_bm_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_ATTAQUE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ATT avant = ".$braldunCible->bm_attaque_braldun);
			$braldunCible->bm_attaque_braldun = $braldunCible->bm_attaque_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur SAG apres = ".$braldunCible->bm_attaque_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_DEGAT) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEG avant = ".$braldunCible->bm_degat_braldun);
			$braldunCible->bm_degat_braldun = $braldunCible->bm_degat_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEG apres = ".$braldunCible->bm_degat_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_DEFENSE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEF avant = ".$braldunCible->bm_defense_braldun);
			$braldunCible->bm_defense_braldun = $braldunCible->bm_defense_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEF apres = ".$braldunCible->bm_defense_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_ARMURE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ARM avant = ".$braldunCible->armure_bm_braldun);
			$braldunCible->armure_bm_braldun = $braldunCible->armure_bm_braldun + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ARM apres = ".$braldunCible->armure_bm_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_ATT_DEG_DEF) {
			if ($effet["actif"] == true) {
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ATT_DEG_DEF");
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ATT avant = ".$braldunCible->bm_attaque_braldun);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEF avant = ".$braldunCible->bm_degat_braldun);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEF avant = ".$braldunCible->bm_defense_braldun);
				$braldunCible->bm_attaque_braldun = $braldunCible->bm_attaque_braldun + $coef * $retourEffet["nEffet"];
				$braldunCible->bm_degat_braldun = $braldunCible->bm_degat_braldun + $coef * $retourEffet["nEffet"];
				$braldunCible->bm_defense_braldun = $braldunCible->bm_defense_braldun + $coef * $retourEffet["nEffet"];
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ATT apres = ".$braldunCible->bm_attaque_braldun);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEF apres = ".$braldunCible->bm_degat_braldun);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur DEF apres = ".$braldunCible->bm_defense_braldun);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ATT_DEG_DEF");
			}
		} else if ($effet["caracteristique"] == self::CARACT_STOUT) { // Lovely day for a Stout
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur FOR_AGI_VIG_SAG");
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur FOR avant = ".$braldunCible->force_bm_braldun);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur AGI avant = ".$braldunCible->agilite_bm_braldun);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur VIG avant = ".$braldunCible->vigueur_bm_braldun);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur SAG avant = ".$braldunCible->sagesse_bm_braldun);
			$bmForce = (floor($braldunCible->niveau_braldun / 10) + 1) * 4;
			$bmAgilite = (floor($braldunCible->niveau_braldun / 10) + 1) * 4;
			$bmVigueur = (floor($braldunCible->niveau_braldun / 10) + 1) * 4;
			$bmSagesse = (floor($braldunCible->niveau_braldun / 10) + 1) * 4;

			if ($effet["actif"] == true) {
				$braldunCible->force_bm_braldun = $braldunCible->force_bm_braldun + $bmForce;
				$braldunCible->agilite_bm_braldun = $braldunCible->agilite_bm_braldun + $bmAgilite;
				$braldunCible->vigueur_bm_braldun = $braldunCible->vigueur_bm_braldun + $bmVigueur;
				$braldunCible->sagesse_bm_braldun = $braldunCible->sagesse_bm_braldun + $bmSagesse;
			}
			$texte = "Force : +".$bmForce;
			$texte .= ", Agilité : +".$bmAgilite;
			$texte .= ", Vigueur : +".$bmVigueur;
			$texte .= ", Sagesse : +".$bmSagesse;

			$effetBraldunTable = new EffetBraldun();
			$data = array(
				'texte_calcule_effet_braldun' => $texte,
			);
			$where = 'id_effet_braldun = '.$effet["id_effet_braldun"];
			$effetBraldunTable->update($data, $where);
			$retourEffet["texte_calcule_effet_braldun"] = $texte;

			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur FOR avant = ".$braldunCible->force_bm_braldun);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur AGI avant = ".$braldunCible->agilite_bm_braldun);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur VIG avant = ".$braldunCible->vigueur_bm_braldun);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur SAG avant = ".$braldunCible->sagesse_bm_braldun);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur ATT_DEG_DEF");
		} else if ($effet["caracteristique"] == self::CARACT_PA_MARCHER) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur CARACT_PA_MARCHER avant = ".$braldunCible->bm_marcher_braldun);
			$braldunCible->bm_marcher_braldun = $braldunCible->bm_marcher_braldun + $coef * $retourEffet["nEffet"];

			$texte = $retourEffet["nEffet"]." PA de malus pour marcher";

			$effetBraldunTable = new EffetBraldun();
			$data = array(
				'texte_calcule_effet_braldun' => $texte,
			);
			$where = 'id_effet_braldun = '.$effet["id_effet_braldun"];
			$effetBraldunTable->update($data, $where);
			$retourEffet["texte_calcule_effet_braldun"] = $texte;

			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur CARACT_PA_MARCHER apres = ".$braldunCible->bm_marcher_braldun);
		} else if ($effet["caracteristique"] == self::CARACT_DUREE_TOUR) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur CARACT_DUREE_TOUR avant = ".$braldunCible->duree_bm_tour_braldun);
			$braldunCible->duree_bm_tour_braldun = $braldunCible->duree_bm_tour_braldun + $retourEffet["nEffet"];

			$texte = $retourEffet["nEffet"]." min de malus sur votre prochain tour";

			$effetBraldunTable = new EffetBraldun();
			$data = array(
				'texte_calcule_effet_braldun' => $texte,
			);
			$where = 'id_effet_braldun = '.$effet["id_effet_braldun"];
			$effetBraldunTable->update($data, $where);
			$retourEffet["texte_calcule_effet_braldun"] = $texte;

			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - effet sur CARACT_DUREE_TOUR apres = ".$braldunCible->duree_bm_tour_braldun);
		} else {
			throw new Zend_Exception("Bral_Util_Effets - appliqueEffetSurBraldun - type effet non gere =".$effet["caracteristique"]);
		}

		$data = array(
			'force_bm_braldun' => $braldunCible->force_bm_braldun,
			'agilite_bm_braldun' => $braldunCible->agilite_bm_braldun,
			'vigueur_bm_braldun' => $braldunCible->vigueur_bm_braldun,
			'sagesse_bm_braldun' => $braldunCible->sagesse_bm_braldun,
			'pv_restant_braldun' => $braldunCible->pv_restant_braldun,
			'balance_faim_braldun' => $braldunCible->balance_faim_braldun,
			'bm_attaque_braldun' => $braldunCible->bm_attaque_braldun,
			'bm_degat_braldun' => $braldunCible->bm_degat_braldun,
			'bm_defense_braldun' => $braldunCible->bm_defense_braldun,
			'vue_bm_braldun' => $braldunCible->vue_bm_braldun,
			'bm_marcher_braldun' => $braldunCible->bm_marcher_braldun,
			'duree_bm_tour_braldun' => $braldunCible->duree_bm_tour_braldun,
			'armure_bm_braldun' => $braldunCible->armure_bm_braldun,
		);
		$where = "id_braldun=".$braldunCible->id_braldun;

		if ($majTableBraldun === true) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - maj du braldun ".$braldunCible->id_braldun. " en base");
			$braldunTable = new Braldun();
			$braldunTable->update($data, $where);
			unset($braldunTable);
		}
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurBraldun - exit");
		return $retourEffet;
	}

	public static function ajouteEtAppliqueEffetMonstre(&$monstre, $caract, $type, $nbTour, $bm) {
		Zend_Loader::loadClass("EffetMonstre");

		$effet["nb_tour_restant"] = $nbTour;
		$effet["caracteristique"] = $caract;
		$effet["bm_type"] = $type;
		$effet["bm_effet_monstre"] = $bm;

		$effetMonstreTable = new EffetMonstre();
		$data = array(
			'id_fk_monstre_cible_effet_monstre' => $monstre["id_monstre"],
			'caract_effet_monstre' => $effet["caracteristique"],
			'bm_effet_monstre' => $effet["bm_effet_monstre"],
			'nb_tour_restant_effet_monstre' => $effet["nb_tour_restant"],
			'bm_type_effet_monstre' => $effet["bm_type"], 
		);
		$effetMonstreTable->insert($data);

		$potion["nb_tour_restant"] = $nbTour;
		Zend_Loader::loadClass("Bral_Util_Effets");
		return Bral_Util_Effets::appliqueEffetSurMonstre($effet, $monstre, false);
	}

	public static function calculEffetMonstre(&$monstreCible) {
		Bral_Util_Log::potion()->trace("Bral_Util_Effets - calculEffetMonstre - enter - idm:".$monstreCible["id_monstre"]);
		Zend_Loader::loadClass("EffetMonstre");
		$effetMonstreTable = new EffetMonstre();
		$effetMonstreRowset = $effetMonstreTable->findByIdMonstreCible($monstreCible["id_monstre"]);
		unset($effetMonstreTable);

		$effets = null;
		foreach ($effetMonstreRowset as $e) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - calculEffetMonstre - effet ".$e["id_effet_monstre"]. " trouve");
			$effet = array(
					"id_effet_monstre" => $e["id_effet_monstre"],
					"nb_tour_restant" => $e["nb_tour_restant_effet_monstre"],
					"caracteristique" => $e["caract_effet_monstre"],
					"bm_type" => $e["bm_type_effet_monstre"],
					"bm_effet_monstre" => $e["bm_effet_monstre"]
			);

			Bral_Util_Log::potion()->debug("Bral_Util_Effets - calculEffetMonstre - application de l'effet ".$e["id_effet_monstre"]);
			$retourEffet = self::appliqueEffetSurMonstre($effet, $monstreCible, true, false);
			$effets[] = array('effet' => $effet, 'retourEffet' => $retourEffet);
		}

		unset($effetMonstreRowset);
		Bral_Util_Log::potion()->trace("Bral_Util_Effets - calculEffetMonstre - exit");
		return $effets;
	}

	public static function appliqueEffetSurMonstre($effet, &$monstreCible, $majTableEffetMonstre = true, $majTableMonstre = true) {
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - enter");
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - monstreCible->id_monstre = ".$monstreCible["id_monstre"]);
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - majTableEffetMonstre = ".$majTableEffetMonstre);

		Zend_Loader::loadClass("EffetMonstre");

		$retourEffet["nb_tour_restant"] = $effet["nb_tour_restant"];

		if ($majTableEffetMonstre === true) {
			Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurMonstre - maj table effet debut");
			$effetEffetMonstreTable = new EffetMonstre();
			$estSupprime = $effetEffetMonstreTable->enleveUnTour($effet);
			$retourEffet["nb_tour_restant"] = $effet["nb_tour_restant"] - 1;
			unset($effetEffetMonstreTable);
			Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurMonstre - maj table effet fin");
			if ($estSupprime) {
				return null;
			}
		}

		if ($effet["bm_type"] == 'malus') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - malus");
			$coef = -1;
		} else { // bonus
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - bonus");
			$coef = 1;
		}

		$retourEffet["nEffet"] = $effet["bm_effet_monstre"];

		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - nEffet = ".$retourEffet["nEffet"]);

		if ($effet["caracteristique"] == 'AGI') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur AGI avant = ".$monstreCible["agilite_bm_monstre"]);
			$monstreCible["agilite_bm_monstre"] = $monstreCible["agilite_bm_monstre"] + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur AGI apres = ".$monstreCible["agilite_bm_monstre"]);
		} else if ($effet["caracteristique"] == 'FOR') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur FOR avant = ".$monstreCible["force_bm_monstre"]);
			$monstreCible["force_bm_monstre"] = $monstreCible["force_bm_monstre"] + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur FOR apres = ".$monstreCible["force_bm_monstre"]);
		} else if ($effet["caracteristique"] == 'PV') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur PV avant = ".$monstreCible["pv_restant_monstre"]);
			$monstreCible["pv_restant_monstre"] = $monstreCible["pv_restant_monstre"] + $coef * $retourEffet["nEffet"];
			if ($monstreCible["pv_restant_monstre"] > $monstreCible["pv_max_monstre"]) {
				$monstreCible["pv_restant_monstre"] = $monstreCible["pv_max_monstre"];
			}
			if ($monstreCible["pv_restant_monstre"] <= 0) {
				$monstreCible["pv_restant_monstre"] = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur PV apres = ".$monstreCible["pv_restant_monstre"]);
		} else if ($effet["caracteristique"] == 'VIG') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur VIG avant = ".$monstreCible["vigueur_bm_monstre"]);
			$monstreCible["vigueur_bm_monstre"] = $monstreCible["vigueur_bm_monstre"] + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur VIG apres = ".$monstreCible["vigueur_bm_monstre"]);
		} else if ($effet["caracteristique"] == 'SAG') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur SAG avant = ".$monstreCible["sagesse_bm_monstre"]);
			$monstreCible["sagesse_bm_monstre"] = $monstreCible["sagesse_bm_monstre"] + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - effet sur SAG apres = ".$monstreCible["sagesse_bm_monstre"]);
		} else {
			throw new Zend_Exception("Bral_Util_Effets - appliqueEffetSurMonstre - type effet non gere =".$effet["caracteristique"]);
		}

		$data = array(
			'force_bm_monstre' => $monstreCible["force_bm_monstre"],
			'agilite_bm_monstre' => $monstreCible["agilite_bm_monstre"],
			'vigueur_bm_monstre' => $monstreCible["vigueur_bm_monstre"],
			'sagesse_bm_monstre' => $monstreCible["sagesse_bm_monstre"],
			'pv_restant_monstre' => $monstreCible["pv_restant_monstre"],
		);
		$where = "id_monstre=".$monstreCible["id_monstre"];

		if ($majTableMonstre === true) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - maj du monstre ".$monstreCible["id_monstre"]. " en base");
			$monstreTable = new Monstre();
			$monstreTable->update($data, $where);
			unset($monstreTable);
		}
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurMonstre - exit");
		return $retourEffet;
	}
}
