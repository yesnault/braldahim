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
			for ($i=1; $i <= $hobbit->regeneration_hobbit; $i++) {
				$jetRegeneration = $jetRegeneration + Bral_Util_De::get_1d6();
			}
	
			$jetRegeneration = $jetRegeneration - $hobbit->regeneration_malus_hobbit;
			if ($jetRegeneration < 0) { // pas de regénération négative (même si le malus est important)
				$jetRegeneration = 0;
			}
			
			$hobbit->pv_restant_hobbit = $hobbit->pv_restant_hobbit + $jetRegeneration;
			
			if ($hobbit->pv_restant_hobbit > $hobbit->pv_max_hobbit + $hobbit->pv_max_bm_hobbit) {
				$jetRegeneration = ($hobbit->pv_max_hobbit  + $hobbit->pv_max_bm_hobbit)- $hobbit->pv_restant_hobbit;
				if ($jetRegeneration < 0) { // pas de regénération négative (même si le malus est important)
					$jetRegeneration = 0;
				}
				$hobbit->pv_restant_hobbit = $hobbit->pv_max_hobbit + $hobbit->pv_max_bm_hobbit;
			}
			
			if ($hobbit->pv_restant_hobbit > $hobbit->pv_max_hobbit + $hobbit->pv_max_hobbit) {
				$hobbit->pv_restant_hobbit = $hobbit->pv_max_hobbit + $hobbit->pv_max_hobbit;
			}
		}
	}
	
}