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
class Bral_Util_Famille {

	private function __construct() {}

	static function getTabPossedeParentsActif($hobbit) {
		$hobbitTable = new Hobbit();

		$pere = null;
		$mere = null;

		$retour = array(
			"est_orphelin" => false,
			"est_pere_actif" => false,
			"est_mere_actif" => false,
		);

		if ($hobbit->id_fk_mere_hobbit != null && $hobbit->id_fk_pere_hobbit != null &&
		$hobbit->id_fk_mere_hobbit != 0 && $hobbit->id_fk_pere_hobbit != 0 ) {
				
			$retour["est_orphelin"] = false;

			$pere = $hobbitTable->findById($hobbit->id_fk_pere_hobbit);
			$mere = $hobbitTable->findById($hobbit->id_fk_mere_hobbit);

			if ($pere != null) {
				$retour["est_pere_actif"] = true;
			} 
			
			if ($mere != null) {
				$retour["est_mere_actif"] = true;
			} 
			
		} else {
			$retour["est_orphelin"] = true;
		}

		return $retour;
	}
}