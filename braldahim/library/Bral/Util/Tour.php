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
class Bral_Util_Tour {
	
	public static function getDureeBaseProchainTour($hobbit, $config) {
		
		$minutesProchain = Bral_Util_ConvertDate::getMinuteFromHeure($config->game->tour->duree_base);
		$minutesProchain = $minutesProchain - (10 * $hobbit->sagesse_base_hobbit);
		
		return Bral_Util_ConvertDate::getHeureFromMinute($minutesProchain);
	}
	
	public static function getTabMinutesProchainTour($hobbit) {
		$retour = null;
		$retour["minutesBase"] = Bral_Util_ConvertDate::getMinuteFromHeure($hobbit->duree_prochain_tour_hobbit);
		$retour["minutesBlessures"] = 0;
		$retour["minutesBM"] = $hobbit->duree_bm_tour_hobbit;
		
		if (($hobbit->pv_max_hobbit + $hobbit->pv_max_bm_hobbit) >= $hobbit->pv_restant_hobbit) {
			$retour["minutesBlessures"]  = floor($retour["minutesBase"] / (4 * $hobbit->pv_max_hobbit)) * ($hobbit->pv_max_hobbit - $hobbit->pv_restant_hobbit);
			$retour["heureMinuteTotal"] = Bral_Util_ConvertDate::getHeureFromMinute($retour["minutesBase"] + $retour["minutesBlessures"] + $retour["minutesBM"]);
		} else {
			$retour["heureMinuteTotal"] = Bral_Util_ConvertDate::getHeureFromMinute($retour["minutesBase"] + $retour["minutesBM"]);
		}
		return $retour;
	}
	
	public static function updateTourTabac($hobbit) {
		Zend_Loader::loadClass("HobbitsCompetences");
		
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($hobbit->id_hobbit);
			
		foreach($hobbitCompetences as $c) {
			if ($c["nb_tour_restant_bonus_tabac_hcomp"] > 0) {
				
				$nb = $c["nb_tour_restant_bonus_tabac_hcomp"] - 1;
				if ($nb < 0) {
					$nb = 0;
				}
				$data = array('nb_tour_restant_bonus_tabac_hcomp' => $nb);
				$where = "id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]. " AND id_fk_hobbit_hcomp=".$hobbit->id_hobbit;
				$hobbitsCompetencesTables->update($data, $where);
			} else if ($c["nb_tour_restant_malus_tabac_hcomp"] > 0) {
				$nb = $c["nb_tour_restant_malus_tabac_hcomp"] - 1;
				if ($nb < 0) {
					$nb = 0;
				}
				$data = array('nb_tour_restant_malus_tabac_hcomp' => $nb);
				$where = "id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]. " AND id_fk_hobbit_hcomp=".$hobbit->id_hobbit;
				$hobbitsCompetencesTables->update($data, $where);
			}
		}
	}
}