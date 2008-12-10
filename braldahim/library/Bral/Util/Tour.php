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
	
	// TODO Rajouter les BM
	public static function getDureeProchainTour($hobbit, $config) {
		
		$minutesProchain = Bral_Util_ConvertDate::getMinuteFromHeure($config->game->tour->duree_base);
		$minutesProchain = $minutesProchain - (10 * $hobbit->sagesse_base_hobbit);
		
		return Bral_Util_ConvertDate::getHeureFromMinute($minutesProchain); // TODO Rajouter les BM
	}
}