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
	
	public static function calculPotionHobbit($hobbitCible, $appliqueEffet) {
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionHobbit - enter - appliqueEffet:".$appliqueEffet. " idH:".$hobbitCible->id_hobbit);
		
		Zend_Loader::loadClass("EffetPotionHobbit");
		$effetPotionHobbitTable = new EffetPotionHobbit();
		$effetPotionHobbitRowset = $effetPotionHobbitTable->findByIdHobbitCible($hobbitCible->id_hobbit);
		unset($effetPotionHobbitTable);
		
		$potions = null;
		foreach ($effetPotionHobbitRowset as $p) {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - calculPotionHobbit - potion ".$p["id_effet_potion_hobbit"]. " trouvee");
			$potion = array(
					"id_potion" => $p["id_effet_potion_hobbit"],
					"id_fk_type_potion" => $p["id_fk_type_potion_effet_potion_hobbit"],
					"id_fk_type_qualite_potion" => $p["id_fk_type_qualite_effet_potion_hobbit"],
					"nb_tour_restant" => $p["nb_tour_restant_effet_potion_hobbit"],
					"nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_effet_potion_hobbit"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"bm_effet_potion" => $p["bm_effet_potion_hobbit"],
					"nb_tour_restant" => $p["nb_tour_restant_effet_potion_hobbit"]);
			
			$retourPotion = null;
			if ($appliqueEffet) {
				Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - calculPotionHobbit - application de l'effet potion ".$p["id_effet_potion_hobbit"]);
				$retourPotion = self::appliquePotionSurHobbit($potion, $p["id_fk_hobbit_lanceur_effet_potion_hobbit"], $hobbitCible, true, false);
				if ($retourPotion != null) {
					$potions[] = array('potion' => $potion, 'retourPotion' => $retourPotion);
				}
			} else { 
				$potions[] = array('potion' => $potion, 'retourPotion' => $retourPotion);
			}
		}
		
		unset($effetPotionHobbitRowset);
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionHobbit - exit");
		return $potions;
	}

	public static function calculPotionMonstre($monstreCible) {
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionMonstre - enter");
		
		Zend_Loader::loadClass("EffetPotionMonstre");
		$effetPotionMonstreTable = new EffetPotionMonstre();
		$effetPotionMonstreRowset = $effetPotionMonstreTable->findByIdMonstreCible($monstreCible->id_monstre);
		unset($effetPotionMonstreTable);
		
		$potions = null;
		foreach ($effetPotionMonstreRowset as $p) {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - calculPotionMonstre - potion ".$p["id_effet_potion_monstre"]. " trouvee");
			$potion = array(
					"id_potion" => $p["id_effet_potion_monstre"],
					"id_fk_type_potion" => $p["id_fk_type_potion_effet_potion_monstre"],
					"id_fk_type_qualite_potion" => $p["id_fk_type_qualite_effet_potion_monstre"],
					"nb_tour_restant" => $p["nb_tour_restant_effet_potion_monstre"],
					"nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_effet_potion_monstre"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"bm_effet_potion" => $p["bm_effet_potion_monstre"],
			);
			
			$retourPotion = self::appliquePotionSurMonstre($potion, $p["id_fk_hobbit_lanceur_effet_potion_monstre"], $monstreCible, true, false);
			$potions[] = array('potion' => $potion, 'retourPotion' => $retourPotion);
		}
		
		unset($effetPotionMonstreRowset);
		Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - calculPotionMonstre - exit");
		return $potions;
	}
	
	public static function appliquePotionSurHobbit($potion, $idHobbitSource, $hobbitCible, $majTableEffetPotion = true, $majTableHobbit = true) {
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - enter");
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - idHobbitSource = ".$idHobbitSource);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - hobbitCible->id_hobbit = ".$hobbitCible->id_hobbit);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - majTableEffetPotion = ".$majTableEffetPotion);
		
		Zend_Loader::loadClass("EffetPotionHobbit");
		
		$retourPotion["nb_tour_restant"] = $potion["nb_tour_restant"];
		
		if ($majTableEffetPotion === true) {
			Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - appliquePotionSurHobbit - maj table effet debut");
			$effetPotionHobbitTable = new EffetPotionHobbit();
			$estSupprime = $effetPotionHobbitTable->enleveUnTour($potion);
			$retourPotion["nb_tour_restant"] = $potion["nb_tour_restant"] - 1;
			unset($effetPotionHobbitTable);
			Bral_Util_Log::potion()->trace("Bral_Util_EffetsPotion - appliquePotionSurHobbit - maj table effet fin");
			if ($estSupprime) {
				return null;
			}
		}
		
		if ($potion["bm_type"] == 'malus') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - malus");
			$coef = -1;
		} else { // bonus
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - bonus");
			$coef = 1;
		}
		
		$retourPotion["nEffet"] = $potion["bm_effet_potion"];
		
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - nEffet = ".$retourPotion["nEffet"]);
		
		if ($potion["caracteristique"] == 'AGI') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur AGI avant = ".$hobbitCible->agilite_bm_hobbit);
			$hobbitCible->agilite_bm_hobbit = $hobbitCible->agilite_bm_hobbit + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur AGI apres = ".$hobbitCible->agilite_bm_hobbit);
		} else if ($potion["caracteristique"] == 'FOR') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur FOR avant = ".$hobbitCible->force_bm_hobbit);
			$hobbitCible->force_bm_hobbit = $hobbitCible->force_bm_hobbit + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur FOR apres = ".$hobbitCible->force_bm_hobbit);
		} else if ($potion["caracteristique"] == 'PV') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur PV avant = ".$hobbitCible->pv_restant_hobbit);
			$hobbitCible->pv_restant_hobbit = $hobbitCible->pv_restant_hobbit + $coef * $retourPotion["nEffet"];
			if ($hobbitCible->pv_restant_hobbit > $hobbitCible->pv_max_hobbit) {
				$hobbitCible->pv_restant_hobbit = $hobbitCible->pv_max_hobbit;
			}
			if ($hobbitCible->pv_restant_hobbit <= 0) {
				$hobbitCible->pv_restant_hobbit = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur PV apres = ".$hobbitCible->pv_restant_hobbit);
		} else if ($potion["caracteristique"] == 'VIG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur VIG avant = ".$hobbitCible->vigueur_bm_hobbit);
			$hobbitCible->vigueur_bm_hobbit = $hobbitCible->vigueur_bm_hobbit + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur VIG apres = ".$hobbitCible->vigueur_bm_hobbit);
		} else if ($potion["caracteristique"] == 'SAG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur SAG avant = ".$hobbitCible->sagesse_bm_hobbit);
			$hobbitCible->sagesse_bm_hobbit = $hobbitCible->sagesse_bm_hobbit + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - effet sur SAG apres = ".$hobbitCible->sagesse_bm_hobbit);
		} else {
			throw new Zend_Exception("Bral_Util_EffetsPotion - appliquePotionSurHobbit - type effet non gere =".$potion["caracteristique"]);
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
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - maj du hobbit ".$hobbitCible->id_hobbit. " en base");
			$hobbitTable = new Hobbit();
			$hobbitTable->update($data, $where);
			unset($hobbitTable);
		}
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - exit");
		return $retourPotion;
	}
	
	public static function appliquePotionSurMonstre($potion, $idHobbitSource, $monstre, $majTableEffetPotion = true, $majTableMonstre = true) {
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - enter");
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - idHobbitSource = ".$idHobbitSource);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - monstre->id_monstre= ".$monstre->id_monstre);
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - majTableEffetPotion = ".$majTableEffetPotion);
		
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("EffetPotionMonstre"); 
		
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
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur AGI avant = ".$monstre->agilite_bm_monstre);
			$monstre->agilite_bm_monstre = $monstre->agilite_bm_monstre + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur AGI apres = ".$monstre->agilite_bm_monstre);
		} else if ($potion["caracteristique"] == 'FOR') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur FOR avant = ".$monstre->force_bm_monstre);
			$monstre->force_bm_monstre = $monstre->force_bm_monstre + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur FOR apres = ".$monstre->force_bm_monstre);
		} else if ($potion["caracteristique"] == 'PV') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur PV avant = ".$monstre->pv_restant_monstre);
			$monstre->pv_restant_monstre = $monstre->pv_restant_monstre + $coef * $retourPotion["nEffet"];
			if ($monstre->pv_restant_monstre > $monstre->pv_max_monstre) {
				$monstre->pv_restant_monstre = $monstre->pv_max_monstre;
			}
			if ($monstre->pv_restant_monstre <= 0) {
				$monstre->pv_restant_monstre = 1;
			}
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur PV apres = ".$monstre->pv_restant_monstre);
		} else if ($potion["caracteristique"] == 'VIG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur VIG apres = ".$monstre->vigueur_bm_monstre);
			$monstre->vigueur_bm_monstre = $monstre->vigueur_bm_monstre + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur VIG apres = ".$monstre->vigueur_bm_monstre);
		} else if ($potion["caracteristique"] == 'SAG') {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur SAG apres = ".$monstre->sagesse_bm_monstre);
			$monstre->sagesse_bm_monstre = $monstre->sagesse_bm_monstre + $coef * $retourPotion["nEffet"];
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - effet sur SAG apres = ".$monstre->sagesse_bm_monstre);
		} else {
			throw new Zend_Exception("Bral_Util_EffetsPotion - appliquePotionSurMonstre - type effet non gere =".$potion["caracteristique"] );
		}
		
		$data = array(
			'force_bm_monstre' => $monstre->force_bm_monstre,
			'agilite_bm_monstre' => $monstre->agilite_bm_monstre,
			'vigueur_bm_monstre' => $monstre->vigueur_bm_monstre,
			'sagesse_bm_monstre' => $monstre->sagesse_bm_monstre,
			'pv_restant_monstre' => $monstre->pv_restant_monstre,
		);
		$where = "id_monstre=".$monstre->id_monstre;
		
		if ($majTableMonstre === true) {
			Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurMonstre - maj du monstre ".$monstre->id_monstre. " en base");
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
		Bral_Util_Log::potion()->debug("Bral_Util_EffetsPotion - appliquePotionSurHobbit - exit");
	}
}
