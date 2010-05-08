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
class Bral_Util_EffetsPotion {

	public static function calculPotionBraldun($braldunCible, $appliqueEffet) {
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionBraldun - enter - appliqueEffet:".$appliqueEffet. " idH:".$braldunCible->id_braldun);
		Zend_Loader::loadClass("Bral_Util_Potion");
		Zend_Loader::loadClass("EffetPotionBraldun");
		$effetPotionBraldunTable = new EffetPotionBraldun();
		$effetPotionBraldunRowset = $effetPotionBraldunTable->findByIdBraldunCible($braldunCible->id_braldun);
		unset($effetPotionBraldunTable);

		$potions = null;
		foreach ($effetPotionBraldunRowset as $p) {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - calculPotionBraldun - potion ".$p["id_effet_potion_braldun"]. " trouvee");
			$potion = array(
					"id_potion" => $p["id_effet_potion_braldun"],
					"id_fk_type_potion" => $p["id_fk_type_potion"],
					"id_fk_type_qualite_potion" => $p["id_fk_type_qualite_potion"],
					"nb_tour_restant" => $p["nb_tour_restant_effet_potion_braldun"],
					"nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
					"nom" => $p["nom_type_potion"],
					"de" => $p["de_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
					"bm_effet_potion" => $p["bm_effet_potion_braldun"],
			);

			$retourPotion = null;
			if ($appliqueEffet) {
				Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - calculPotionBraldun - application de l'effet potion ".$p["id_effet_potion_braldun"]);
				$retourPotion = self::appliquePotionSurBraldun($potion, $p["id_fk_braldun_lanceur_effet_potion_braldun"], $braldunCible, true, false);
				if ($retourPotion != null) {
					$potions[] = array('potion' => $potion, 'retourPotion' => $retourPotion);
				}
			} else {
				$potions[] = array('potion' => $potion, 'retourPotion' => $retourPotion);
			}
		}

		unset($effetPotionBraldunRowset);
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionBraldun - exit");
		return $potions;
	}

	public static function calculPotionMonstre(&$monstreCible) {
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionMonstre - enter");

		Zend_Loader::loadClass("Bral_Util_Potion");
		Zend_Loader::loadClass("EffetPotionMonstre");
		$effetPotionMonstreTable = new EffetPotionMonstre();
		$effetPotionMonstreRowset = $effetPotionMonstreTable->findByIdMonstreCible($monstreCible["id_monstre"]);
		unset($effetPotionMonstreTable);

		$potions = null;
		foreach ($effetPotionMonstreRowset as $p) {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - calculPotionMonstre - potion ".$p["id_effet_potion_monstre"]. " trouvee");
			$potion = array(
					"id_potion" => $p["id_effet_potion_monstre"],
					"id_fk_type_potion" => $p["id_fk_type_potion"],
					"id_fk_type_qualite_potion" => $p["id_fk_type_qualite_potion"],
					"nb_tour_restant" => $p["nb_tour_restant_effet_potion_monstre"],
					"nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
					"bm_effet_potion" => $p["bm_effet_potion_monstre"],
			);

			$retourPotion = self::appliquePotionSurMonstre($potion, $p["id_fk_braldun_lanceur_effet_potion_monstre"], $monstreCible, true, false);
			$potions[] = array('potion' => $potion, 'retourPotion' => $retourPotion);
		}

		unset($effetPotionMonstreRowset);
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionMonstre - exit");
		return $potions;
	}

	public static function appliquePotionSurBraldun($potion, $idBraldunSource, $braldunCible, $majTableEffetPotion = true, $majTableBraldun = true, $initialisePotion = false) {
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - enter");
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - idBraldunSource = ".$idBraldunSource);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - braldunCible->id_braldun = ".$braldunCible->id_braldun);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - majTableEffetPotion = ".$majTableEffetPotion);

		Zend_Loader::loadClass("EffetPotionBraldun");

		if ($initialisePotion) {
			$potion["bm_effet_potion"] = self::calculBM($potion);
			$potion["nb_tour_restant"] = self::calculNbTour($potion);

			if ($potion["nb_tour_restant"] > 1) {
				$effetPotionBraldunTable = new EffetPotionBraldun();
				$data = array(
				  'id_effet_potion_braldun' => $potion["id_potion"],
				  'id_fk_braldun_cible_effet_potion_braldun' => $braldunCible->id_braldun,
				  'id_fk_braldun_lanceur_effet_potion_braldun' => $idBraldunSource,
				  'nb_tour_restant_effet_potion_braldun' => $potion["nb_tour_restant"],
				  'bm_effet_potion_braldun' => $potion["bm_effet_potion"],
				);
				$effetPotionBraldunTable->insert($data);
			}
		}

		$retourPotion["nb_tour_restant"] = $potion["nb_tour_restant"];

		if ($majTableEffetPotion === true) {
			Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - appliquePotionSurBraldun - maj table effet debut");
			$effetPotionBraldunTable = new EffetPotionBraldun();
			$estSupprime = $effetPotionBraldunTable->enleveUnTour($potion);
			$retourPotion["nb_tour_restant"] = $potion["nb_tour_restant"] - 1;
			unset($effetPotionBraldunTable);
			Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - appliquePotionSurBraldun - maj table effet fin");
			if ($estSupprime) {
				return null;
			}
		}

		if ($potion["bm_type"] == 'malus') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - malus");
			$coef = -1;
		} else { // bonus
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - bonus");
			$coef = 1;
		}

		$retourPotion["nEffet"] = $potion["bm_effet_potion"];

		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - nEffet = ".$retourPotion["nEffet"]);

		if ($potion["caracteristique"] == 'AGI') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur AGI avant = ".$braldunCible->agilite_bm_braldun);
			$braldunCible->agilite_bm_braldun = $braldunCible->agilite_bm_braldun + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur AGI apres = ".$braldunCible->agilite_bm_braldun);
		} else if ($potion["caracteristique"] == 'FOR') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur FOR avant = ".$braldunCible->force_bm_braldun);
			$braldunCible->force_bm_braldun = $braldunCible->force_bm_braldun + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur FOR apres = ".$braldunCible->force_bm_braldun);
		} else if ($potion["caracteristique"] == 'PV') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur PV avant = ".$braldunCible->pv_restant_braldun);
			$braldunCible->pv_restant_braldun = $braldunCible->pv_restant_braldun + $coef * $retourPotion["nEffet"];
			if ($braldunCible->pv_restant_braldun > $braldunCible->pv_max_braldun) {
				$braldunCible->pv_restant_braldun = $braldunCible->pv_max_braldun;
			}
			if ($braldunCible->pv_restant_braldun <= 0) {
				$braldunCible->pv_restant_braldun = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur PV apres = ".$braldunCible->pv_restant_braldun);
		} else if ($potion["caracteristique"] == 'VIG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur VIG avant = ".$braldunCible->vigueur_bm_braldun);
			$braldunCible->vigueur_bm_braldun = $braldunCible->vigueur_bm_braldun + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur VIG apres = ".$braldunCible->vigueur_bm_braldun);
		} else if ($potion["caracteristique"] == 'SAG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur SAG avant = ".$braldunCible->sagesse_bm_braldun);
			$braldunCible->sagesse_bm_braldun = $braldunCible->sagesse_bm_braldun + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - effet sur SAG apres = ".$braldunCible->sagesse_bm_braldun);
		} else {
			throw new Zend_Exception("Bral_Util_EffetsPotion - appliquePotionSurBraldun - type effet non gere =".$potion["caracteristique"]);
		}

		$data = array(
				'force_bm_braldun' => $braldunCible->force_bm_braldun,
				'agilite_bm_braldun' => $braldunCible->agilite_bm_braldun,
				'vigueur_bm_braldun' => $braldunCible->vigueur_bm_braldun,
				'sagesse_bm_braldun' => $braldunCible->sagesse_bm_braldun,
				'pv_restant_braldun' => $braldunCible->pv_restant_braldun,
		);
		$where = "id_braldun=".$braldunCible->id_braldun;

		if ($majTableBraldun === true) {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - maj du braldun ".$braldunCible->id_braldun. " en base");
			$braldunTable = new Braldun();
			$braldunTable->update($data, $where);
			unset($braldunTable);
		}
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - exit");
		return $retourPotion;
	}

	public static function appliquePotionSurMonstre($potion, $idBraldunSource, &$monstre, $majTableEffetPotion = true, $majTableMonstre = true, $initialisePotion = false) {
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - enter");
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - idBraldunSource = ".$idBraldunSource);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - monstre->id_monstre= ".$monstre["id_monstre"]);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - majTableEffetPotion = ".$majTableEffetPotion);

		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("EffetPotionMonstre");

		if ($initialisePotion) {
			$potion["bm_effet_potion"] = self::calculBM($potion);
			$potion["nb_tour_restant"] = self::calculNbTour($potion);

			if ($potion["nb_tour_restant"] > 1) {
				$effetPotionMonstreTable = new EffetPotionMonstre();
				$data = array(
				  'id_effet_potion_monstre' => $potion["id_potion"],
				  'id_fk_monstre_cible_effet_potion_monstre' => $monstre["id_monstre"],
				  'id_fk_braldun_lanceur_effet_potion_monstre' => $idBraldunSource,
				  'nb_tour_restant_effet_potion_monstre' => $potion["nb_tour_restant"],
				  'bm_effet_potion_monstre' => $potion["bm_effet_potion"],
				);
				$effetPotionMonstreTable->insert($data);
			}
		}

		if ($potion["bm_type"] == 'malus') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - malus");
			$coef = -1;
		} else { // bonus
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - bonus");
			$coef = 1;
		}

		$retourPotion["nEffet"] = $potion["bm_effet_potion"];

		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - nEffet = ".$retourPotion["nEffet"]);

		if ($potion["caracteristique"] == 'AGI') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur AGI avant = ".$monstre["agilite_bm_monstre"]);
			$monstre["agilite_bm_monstre"] = $monstre["agilite_bm_monstre"] + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur AGI apres = ".$monstre["agilite_bm_monstre"]);
		} else if ($potion["caracteristique"] == 'FOR') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur FOR avant = ".$monstre["force_bm_monstre"]);
			$monstre["force_bm_monstre"] = $monstre["force_bm_monstre"] + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur FOR apres = ".$monstre["force_bm_monstre"]);
		} else if ($potion["caracteristique"] == 'PV') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur PV avant = ".$monstre["pv_restant_monstre"]);
			$monstre["pv_restant_monstre"] = $monstre["pv_restant_monstre"] + $coef * $retourPotion["nEffet"];
			if ($monstre["pv_restant_monstre"] > $monstre["pv_max_monstre"]) {
				$monstre["pv_restant_monstre"] = $monstre["pv_max_monstre"];
			}
			if ($monstre["pv_restant_monstre"] <= 0) {
				$monstre["pv_restant_monstre"] = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur PV apres = ".$monstre["pv_restant_monstre"]);
		} else if ($potion["caracteristique"] == 'VIG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur VIG apres = ".$monstre["vigueur_bm_monstre"]);
			$monstre["vigueur_bm_monstre"] = $monstre["vigueur_bm_monstre"] + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur VIG apres = ".$monstre["vigueur_bm_monstre"]);
		} else if ($potion["caracteristique"] == 'SAG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur SAG apres = ".$monstre["sagesse_bm_monstre"]);
			$monstre["sagesse_bm_monstre"] = $monstre["sagesse_bm_monstre"] + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur SAG apres = ".$monstre["sagesse_bm_monstre"]);
		} else {
			throw new Zend_Exception("Bral_Util_EffetsPotion - appliquePotionSurMonstre - type effet non gere =".$potion["caracteristique"] );
		}

		$data = array(
			'force_bm_monstre' => $monstre["force_bm_monstre"],
			'agilite_bm_monstre' => $monstre["agilite_bm_monstre"],
			'vigueur_bm_monstre' => $monstre["vigueur_bm_monstre"],
			'sagesse_bm_monstre' => $monstre["sagesse_bm_monstre"],
			'pv_restant_monstre' => $monstre["pv_restant_monstre"],
		);
		$where = "id_monstre=".$monstre["id_monstre"];

		if ($majTableMonstre === true) {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - maj du monstre ".$monstre["id_monstre"]. " en base");
			$monstreTable = new Monstre();
			$monstreTable->update($data, $where);
			unset($monstreTable);
		}

		if ($majTableEffetPotion === true) {
			Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - appliquePotionSurMonstre - maj table effet debut");
			$effetPotionMonstreTable = new EffetPotionMonstre();
			$effetPotionMonstreTable->enleveUnTour($potion);
			unset($effetPotionMonstreTable);
			Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - appliquePotionSurMonstre - maj table effet fin");
		}

		$retourPotion["nb_tour_restant"] = $potion["nb_tour_restant"] - 1;
		return $retourPotion;
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurBraldun - exit");
	}

	public static function calculNbTour($potion) {
		$nbTour = Bral_Util_De::get_1d3();
		if ($potion["nom_systeme_type_qualite"] == 'standard') {
			$nbTour = $nbTour + 1;
		} else if ($potion["nom_systeme_type_qualite"] == 'bonne') {
			$nbTour = $nbTour + 2;
		}
		$nbTour = $nbTour - 1; // tour courant
		if ($nbTour < 1) {
			$nbTour = 1;
		}
		return $nbTour;
	}
	
	public static function calculBM($potion) {
		return Bral_Util_De::getLanceDeSpecifique($potion["niveau"] + 2, 1, $potion["de"]);
	}
}
