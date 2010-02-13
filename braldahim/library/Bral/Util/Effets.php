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

	public static function ajouteEtAppliqueEffetHobbit($idHobbit, $caract, $type, $nbTour, $bm, $texte = null) {
		Zend_Loader::loadClass("EffetHobbit");

		$effet["nb_tour_restant"] = $nbTour;
		$effet["caracteristique"] = $caract;
		$effet["bm_type"] = $type;
		$effet["bm_effet_hobbit"] = $bm;

		$effetHobbitTable = new EffetHobbit();
		$data = array(
			'id_fk_hobbit_cible_effet_hobbit' => $idHobbit,
			'caract_effet_hobbit' => $effet["caracteristique"],
			'bm_effet_hobbit' => $effet["bm_effet_hobbit"],
			'nb_tour_restant_effet_hobbit' => $effet["nb_tour_restant"],
			'bm_type_effet_hobbit' => $effet["bm_type"], 
			'texte_effet_hobbit' => $texte,
			'texte_calcule_effet_hobbit' => null,
		);
		$effet["id_effet_hobbit"] = $effetHobbitTable->insert($data);
		$effet["actif"] = true;

		$effetHobbitRowset = $effetHobbitTable->findByIdHobbitCibleAndTypeEffet($idHobbit, array(self::CARACT_FOR_AGI_VIG_SAG, self::CARACT_STOUT));
		$effetSoutEnCours = false;
		$effetQuadrupleEnCours = false;
		// s'il y a déjà des effets Stout ou quadruple en cours
		foreach ($effetHobbitRowset as $e) {
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

		if ($idHobbit != null) {
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($idHobbit);
			$hobbit = $hobbitRowset->current();

			Zend_Loader::loadClass("Bral_Util_Effets");
			return Bral_Util_Effets::appliqueEffetSurHobbit($effet, $hobbit, false);
		} else {
			return $effet["id_effet_hobbit"];
		}
	}

	public static function calculEffetHobbit($hobbitCible, $appliqueEffet, $idEffet = null) {
		Bral_Util_Log::potion()->trace("Bral_Util_Effets - calculEffetHobbit - enter - appliqueEffet:".$appliqueEffet. " idH:".$hobbitCible->id_hobbit. " idE:".$idEffet);
		Zend_Loader::loadClass("EffetHobbit");
		$effetHobbitTable = new EffetHobbit();
		if ($idEffet == null) {
			$effetHobbitRowset = $effetHobbitTable->findByIdHobbitCible($hobbitCible->id_hobbit);
		} else {
			$effetHobbitRowset = $effetHobbitTable->findByIdEffetHobbit($idEffet);
		}
		unset($effetHobbitTable);

		$effets = null;
		$effetStoutEnCours = false;
		$effetQuadrupleEnCours = false;
		foreach ($effetHobbitRowset as $e) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - calculEffetHobbit - effet ".$e["id_effet_hobbit"]. " trouve");

			$effet = array(
					"id_effet_hobbit" => $e["id_effet_hobbit"],
					"nb_tour_restant" => $e["nb_tour_restant_effet_hobbit"],
					"caracteristique" => $e["caract_effet_hobbit"],
					"bm_type" => $e["bm_type_effet_hobbit"],
					"bm_effet_hobbit" => $e["bm_effet_hobbit"],
					"texte_effet_hobbit" => $e["texte_effet_hobbit"],
					"texte_calcule_effet_hobbit" => $e["texte_calcule_effet_hobbit"],
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
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - calculEffetHobbit - application de l'effet ".$e["id_effet_hobbit"]);
				$retourEffet = self::appliqueEffetSurHobbit($effet, $hobbitCible, true, false);
				if ($retourEffet != null) {
					$effets[] = array('effet' => $effet, 'retourEffet' => $retourEffet);
				}
			} else {
				$effets[] = array('effet' => $effet, 'retourEffet' => $retourEffet);
			}
		}

		unset($effetHobbitRowset);
		Bral_Util_Log::potion()->trace("Bral_Util_Effets - calculEffetHobbit - exit");
		return $effets;
	}

	private static function appliqueEffetSurHobbit($effet, $hobbitCible, $majTableEffetHobbit = true, $majTableHobbit = true) {
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - enter");
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - hobbitCible->id_hobbit = ".$hobbitCible->id_hobbit);
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - majTableEffetHobbit = ".$majTableEffetHobbit);

		Zend_Loader::loadClass("EffetHobbit");

		$retourEffet["nb_tour_restant"] = $effet["nb_tour_restant"];

		if ($majTableEffetHobbit === true) {
			Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurHobbit - maj table effet debut");
			$effetEffetHobbitTable = new EffetHobbit();
			$estSupprime = $effetEffetHobbitTable->enleveUnTour($effet);
			$retourEffet["nb_tour_restant"] = $effet["nb_tour_restant"] - 1;
			unset($effetEffetHobbitTable);
			Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurHobbit - maj table effet fin");
			if ($estSupprime) {
				Bral_Util_Log::potion()->trace("Bral_Util_Effets - appliqueEffetSurHobbit - suppression effet");
				return null;
			}
		}

		if ($effet["bm_type"] == 'malus') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - malus");
			$coef = -1;
		} else { // bonus
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - bonus");
			$coef = 1;
		}

		$retourEffet["nEffet"] = $effet["bm_effet_hobbit"];

		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - nEffet = ".$retourEffet["nEffet"]);

		if ($effet["caracteristique"] == self::CARACT_AGILITE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur AGI avant = ".$hobbitCible->agilite_bm_hobbit);
			$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur AGI apres = ".$hobbitCible->agilite_bm_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_FORCE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur FOR avant = ".$hobbitCible->force_bm_hobbit);
			$hobbitCible->force_bm_hobbit = $hobbitCible->force_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur FOR apres = ".$hobbitCible->force_bm_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_PV) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur PV avant = ".$hobbitCible->pv_restant_hobbit);
			$hobbitCible->pv_restant_hobbit = $hobbitCible->pv_restant_hobbit + $coef * $retourEffet["nEffet"];
			if ($hobbitCible->pv_restant_hobbit > $hobbitCible->pv_max_hobbit) {
				$hobbitCible->pv_restant_hobbit = $hobbitCible->pv_max_hobbit;
			}
			if ($hobbitCible->pv_restant_hobbit <= 0) {
				$hobbitCible->pv_restant_hobbit = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur PV apres = ".$hobbitCible->pv_restant_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_VUE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VUE avant = ".$hobbitCible->vue_bm_hobbit);
			$hobbitCible->vue_bm_hobbit = $hobbitCible->vue_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VUE apres = ".$hobbitCible->vue_bm_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_VIGUEUR) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VIG avant = ".$hobbitCible->vigueur_bm_hobbit);
			$hobbitCible->vigueur_bm_hobbit = $hobbitCible->vigueur_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VIG apres = ".$hobbitCible->vigueur_bm_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_SAGESSE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur SAG avant = ".$hobbitCible->sagesse_bm_hobbit);
			$hobbitCible->sagesse_bm_hobbit = $hobbitCible->sagesse_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur SAG apres = ".$hobbitCible->sagesse_bm_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_BBDF) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur BBDF avant = ".$hobbitCible->sagesse_bm_hobbit);
			$hobbitCible->balance_faim_hobbit = $hobbitCible->balance_faim_hobbit + $coef * $retourEffet["nEffet"];
			if ($hobbitCible->balance_faim_hobbit <= 0) {
				$hobbitCible->balance_faim_hobbit = 0;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur BBDF apres = ".$hobbitCible->sagesse_bm_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_ATTAQUE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ATT avant = ".$hobbitCible->bm_attaque_hobbit);
			$hobbitCible->bm_attaque_hobbit = $hobbitCible->bm_attaque_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur SAG apres = ".$hobbitCible->bm_attaque_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_DEGAT) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEG avant = ".$hobbitCible->bm_degat_hobbit);
			$hobbitCible->bm_degat_hobbit = $hobbitCible->bm_degat_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEG apres = ".$hobbitCible->bm_degat_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_DEFENSE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEF avant = ".$hobbitCible->bm_defense_hobbit);
			$hobbitCible->bm_defense_hobbit = $hobbitCible->bm_defense_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEF apres = ".$hobbitCible->bm_defense_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_ARMURE) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ARM avant = ".$hobbitCible->armure_bm_hobbit);
			$hobbitCible->armure_bm_hobbit = $hobbitCible->armure_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ARM apres = ".$hobbitCible->armure_bm_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_ATT_DEG_DEF) {
			if ($effet["actif"] == true) {
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ATT_DEG_DEF");
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ATT avant = ".$hobbitCible->bm_attaque_hobbit);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEF avant = ".$hobbitCible->bm_degat_hobbit);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEF avant = ".$hobbitCible->bm_defense_hobbit);
				$hobbitCible->bm_attaque_hobbit = $hobbitCible->bm_attaque_hobbit + $coef * $retourEffet["nEffet"];
				$hobbitCible->bm_degat_hobbit = $hobbitCible->bm_degat_hobbit + $coef * $retourEffet["nEffet"];
				$hobbitCible->bm_defense_hobbit = $hobbitCible->bm_defense_hobbit + $coef * $retourEffet["nEffet"];
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ATT apres = ".$hobbitCible->bm_attaque_hobbit);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEF apres = ".$hobbitCible->bm_degat_hobbit);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur DEF apres = ".$hobbitCible->bm_defense_hobbit);
				Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ATT_DEG_DEF");
			}
		} else if ($effet["caracteristique"] == self::CARACT_STOUT) { // Lovely day for a Stout
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur FOR_AGI_VIG_SAG");
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur FOR avant = ".$hobbitCible->force_bm_hobbit);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur AGI avant = ".$hobbitCible->agilite_bm_hobbit);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VIG avant = ".$hobbitCible->vigueur_bm_hobbit);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur SAG avant = ".$hobbitCible->sagesse_bm_hobbit);
			$bmForce = (floor($hobbitCible->niveau_hobbit / 10) + 1) * 4;
			$bmAgilite = (floor($hobbitCible->niveau_hobbit / 10) + 1) * 4;
			$bmVigueur = (floor($hobbitCible->niveau_hobbit / 10) + 1) * 4;
			$bmSagesse = (floor($hobbitCible->niveau_hobbit / 10) + 1) * 4;

			if ($effet["actif"] == true) {
				$hobbitCible->force_bm_hobbit = $hobbitCible->force_bm_hobbit + $bmForce;
				$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit + $bmAgilite;
				$hobbitCible->vigueur_bm_hobbit = $hobbitCible->vigueur_bm_hobbit + $bmVigueur;
				$hobbitCible->sagesse_bm_hobbit = $hobbitCible->sagesse_bm_hobbit + $bmSagesse;
			}
			$texte = "Force : +".$bmForce;
			$texte .= ", Agilité : +".$bmAgilite;
			$texte .= ", Vigueur : +".$bmVigueur;
			$texte .= ", Sagesse : +".$bmSagesse;

			$effetHobbitTable = new EffetHobbit();
			$data = array(
				'texte_calcule_effet_hobbit' => $texte,
			);
			$where = 'id_effet_hobbit = '.$effet["id_effet_hobbit"];
			$effetHobbitTable->update($data, $where);
			$retourEffet["texte_calcule_effet_hobbit"] = $texte;

			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur FOR avant = ".$hobbitCible->force_bm_hobbit);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur AGI avant = ".$hobbitCible->agilite_bm_hobbit);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VIG avant = ".$hobbitCible->vigueur_bm_hobbit);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur SAG avant = ".$hobbitCible->sagesse_bm_hobbit);
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur ATT_DEG_DEF");
		} else if ($effet["caracteristique"] == self::CARACT_PA_MARCHER) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur CARACT_PA_MARCHER avant = ".$hobbitCible->bm_marcher_hobbit);
			$hobbitCible->bm_marcher_hobbit = $hobbitCible->bm_marcher_hobbit + $coef * $retourEffet["nEffet"];

			$texte = $retourEffet["nEffet"]." PA de malus pour marcher";

			$effetHobbitTable = new EffetHobbit();
			$data = array(
				'texte_calcule_effet_hobbit' => $texte,
			);
			$where = 'id_effet_hobbit = '.$effet["id_effet_hobbit"];
			$effetHobbitTable->update($data, $where);
			$retourEffet["texte_calcule_effet_hobbit"] = $texte;

			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur CARACT_PA_MARCHER apres = ".$hobbitCible->bm_marcher_hobbit);
		} else if ($effet["caracteristique"] == self::CARACT_DUREE_TOUR) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur CARACT_DUREE_TOUR avant = ".$hobbitCible->duree_bm_tour_hobbit);
			$hobbitCible->duree_bm_tour_hobbit = $hobbitCible->duree_bm_tour_hobbit + $retourEffet["nEffet"];

			$texte = $retourEffet["nEffet"]." min de malus sur votre prochain tour";

			$effetHobbitTable = new EffetHobbit();
			$data = array(
				'texte_calcule_effet_hobbit' => $texte,
			);
			$where = 'id_effet_hobbit = '.$effet["id_effet_hobbit"];
			$effetHobbitTable->update($data, $where);
			$retourEffet["texte_calcule_effet_hobbit"] = $texte;

			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur CARACT_DUREE_TOUR apres = ".$hobbitCible->duree_bm_tour_hobbit);
		} else {
			throw new Zend_Exception("Bral_Util_Effets - appliqueEffetSurHobbit - type effet non gere =".$effet["caracteristique"]);
		}

		$data = array(
				'force_bm_hobbit' => $hobbitCible->force_bm_hobbit,
				'agilite_bm_hobbit' => $hobbitCible->agilite_bm_hobbit,
				'vigueur_bm_hobbit' => $hobbitCible->vigueur_bm_hobbit,
				'sagesse_bm_hobbit' => $hobbitCible->sagesse_bm_hobbit,
				'pv_restant_hobbit' => $hobbitCible->pv_restant_hobbit,
				'balance_faim_hobbit' => $hobbitCible->balance_faim_hobbit,
				'bm_attaque_hobbit' => $hobbitCible->bm_attaque_hobbit,
				'bm_degat_hobbit' => $hobbitCible->bm_degat_hobbit,
				'bm_defense_hobbit' => $hobbitCible->bm_defense_hobbit,
				'vue_bm_hobbit' => $hobbitCible->vue_bm_hobbit,
				'bm_marcher_hobbit' => $hobbitCible->bm_marcher_hobbit,
				'duree_bm_tour_hobbit' => $hobbitCible->duree_bm_tour_hobbit,
				'armure_bm_hobbit' => $hobbitCible->armure_bm_hobbit,
		);
		$where = "id_hobbit=".$hobbitCible->id_hobbit;

		if ($majTableHobbit === true) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - maj du hobbit ".$hobbitCible->id_hobbit. " en base");
			$hobbitTable = new Hobbit();
			$hobbitTable->update($data, $where);
			unset($hobbitTable);
		}
		Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - exit");
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
