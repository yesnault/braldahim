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
class Bral_Util_Vie {

	function __construct() {
	}
	
	public static function calculRegenerationHobbit(&$hobbit, &$jetRegeneration) {
		$jetRegeneration = 0;
		
		if ($hobbit->pv_restant_hobbit < $hobbit->pv_max_hobbit + $hobbit->pv_max_bm_hobbit) {
			$jetRegeneration = Bral_Util_De::getLanceDe10($hobbit->regeneration_hobbit);
			$jetRegeneration = $jetRegeneration - $hobbit->regeneration_malus_hobbit;

			if ($jetRegeneration < 0) { // pas de regénération négative (même si le malus est important)
				$jetRegeneration = 0;
			}
			
			if ($hobbit->pv_restant_hobbit + $jetRegeneration > $hobbit->pv_max_hobbit + $hobbit->pv_max_bm_hobbit) {
				$jetRegeneration = $hobbit->pv_max_hobbit  + $hobbit->pv_max_bm_hobbit - $hobbit->pv_restant_hobbit;
			}
			
			$hobbit->pv_restant_hobbit = $hobbit->pv_restant_hobbit + $jetRegeneration;
		}
	}
	
	public static function calculRegenerationMonstre(&$monstre) {
		$jetRegeneration = 0;
		
		if ($monstre["pv_restant_monstre"] < $monstre["pv_max_monstre"]) {
			
			$jetRegeneration = Bral_Util_De::getLanceDe10($monstre["regeneration_monstre"]);
			$jetRegeneration = $jetRegeneration - $monstre["regeneration_malus_monstre"];
			
			if ($jetRegeneration < 0) { // pas de regénération négative (même si le malus est important)
				$jetRegeneration = 0;
			}
			
			if ($monstre["pv_restant_monstre"] + $jetRegeneration > $monstre["pv_max_monstre"]) {
				$jetRegeneration = $monstre["pv_max_monstre"] - $monstre["pv_restant_monstre"];
			}
			
			$monstre["pv_restant_monstre"] = $monstre["pv_restant_monstre"] + $jetRegeneration;
		}
		
		return $jetRegeneration;
	}
	
}