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
	const CARACT_VIGUEUR = 'VIGUEUR';
	const CARACT_SAGESSE = 'SAG';
	const CARACT_PV = 'PV';
	const CARACT_VUE = 'VUE';
	const CARACT_ARMURE = 'ARM';
	const CARACT_POIDS = 'POIDS';
	const CARACT_ATTAQUE = 'ATT';
	const CARACT_DEGAT = 'DEG';
	const CARACT_DEF = 'DEF';
	
	public static function ajouteEtAppliqueEffet($idHobbit, $caract, $type, $nbTour, $bm) {
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
		);
		$effetHobbitTable->insert($data);

		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($idHobbit);
		$hobbit = $hobbitRowset->current();

		$potion["nb_tour_restant"] = $nbTour;
		Zend_Loader::loadClass("Bral_Util_Effets");
		return Bral_Util_Effets::appliqueEffetSurHobbit($effet, $hobbit, false);
	}

	public static function calculEffetHobbit($hobbitCible, $appliqueEffet) {
		Bral_Util_Log::potion()->trace("Bral_Util_Effets - calculEffetHobbit - enter - appliqueEffet:".$appliqueEffet. " idH:".$hobbitCible->id_hobbit);
		Zend_Loader::loadClass("EffetHobbit");
		$effetHobbitTable = new EffetHobbit();
		$effetHobbitRowset = $effetHobbitTable->findByIdHobbitCible($hobbitCible->id_hobbit);
		unset($effetHobbitTable);

		$effets = null;
		foreach ($effetHobbitRowset as $e) {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - calculEffetHobbit - effet ".$e["id_effet_hobbit"]. " trouve");
			$effet = array(
					"id_effet_hobbit" => $e["id_effet_hobbit"],
					"nb_tour_restant" => $e["nb_tour_restant_effet_hobbit"],
					"caracteristique" => $e["caract_effet_hobbit"],
					"bm_type" => $e["bm_type_effet_hobbit"],
					"bm_effet_hobbit" => $e["bm_effet_hobbit"]
			);
				
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

	public static function appliqueEffetSurHobbit($effet, $hobbitCible, $majTableEffetHobbit = true, $majTableHobbit = true) {
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

		if ($effet["caracteristique"] == 'AGI') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur AGI avant = ".$hobbitCible->agilite_bm_hobbit);
			$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur AGI apres = ".$hobbitCible->agilite_bm_hobbit);
		} else if ($effet["caracteristique"] == 'FOR') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur FOR avant = ".$hobbitCible->force_bm_hobbit);
			$hobbitCible->force_bm_hobbit = $hobbitCible->force_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur FOR apres = ".$hobbitCible->force_bm_hobbit);
		} else if ($effet["caracteristique"] == 'PV') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur PV avant = ".$hobbitCible->pv_restant_hobbit);
			$hobbitCible->pv_restant_hobbit = $hobbitCible->pv_restant_hobbit + $coef * $retourEffet["nEffet"];
			if ($hobbitCible->pv_restant_hobbit > $hobbitCible->pv_max_hobbit) {
				$hobbitCible->pv_restant_hobbit = $hobbitCible->pv_max_hobbit;
			}
			if ($hobbitCible->pv_restant_hobbit <= 0) {
				$hobbitCible->pv_restant_hobbit = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur PV apres = ".$hobbitCible->pv_restant_hobbit);
		} else if ($effet["caracteristique"] == 'VIG') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VIG avant = ".$hobbitCible->vigueur_bm_hobbit);
			$hobbitCible->vigueur_bm_hobbit = $hobbitCible->vigueur_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur VIG apres = ".$hobbitCible->vigueur_bm_hobbit);
		} else if ($effet["caracteristique"] == 'SAG') {
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur SAG avant = ".$hobbitCible->sagesse_bm_hobbit);
			$hobbitCible->sagesse_bm_hobbit = $hobbitCible->sagesse_bm_hobbit + $coef * $retourEffet["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_Effets - appliqueEffetSurHobbit - effet sur SAG apres = ".$hobbitCible->sagesse_bm_hobbit);
		} else {
			throw new Zend_Exception("Bral_Util_Effets - appliqueEffetSurHobbit - type effet non gere =".$effet["caracteristique"]);
		}

		$data = array(
				'force_bm_hobbit' => $hobbitCible->force_bm_hobbit,
				'agilite_bm_hobbit' => $hobbitCible->agilite_bm_hobbit,
				'vigueur_bm_hobbit' => $hobbitCible->vigueur_bm_hobbit,
				'sagesse_bm_hobbit' => $hobbitCible->sagesse_bm_hobbit,
				'pv_restant_hobbit' => $hobbitCible->pv_restant_hobbit,
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
}
