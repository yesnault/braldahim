<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Famille
{

	private function __construct()
	{
	}

	static function getTabPossedeParentsActif($braldun)
	{
		$braldunTable = new Braldun();

		$pere = null;
		$mere = null;

		$retour = array(
			"est_orphelin" => false,
			"est_pere_actif" => false,
			"est_mere_actif" => false,
		);

		if ($braldun->id_fk_mere_braldun != null && $braldun->id_fk_pere_braldun != null &&
			$braldun->id_fk_mere_braldun != 0 && $braldun->id_fk_pere_braldun != 0
		) {

			$retour["est_orphelin"] = false;

			$pere = $braldunTable->findById($braldun->id_fk_pere_braldun);
			$mere = $braldunTable->findById($braldun->id_fk_mere_braldun);

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