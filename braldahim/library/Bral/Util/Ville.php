<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Ville
{

	function __construct()
	{
	}

	/**
	 * Retourne la ville la plus proche à partir de x, y, null si c'est au dela de
	 * la distance Max.
	 * @param unknown_type $x Position X
	 * @param unknown_type $y Position Y
	 * @param unknown_type $distanceMax distance max
	 */
	public static function trouveVilleProche($x, $y, $distanceMax)
	{
		Zend_Loader::loadClass('Ville');
		$villeTable = new Ville();
		$ville = $villeTable->findLaPlusProche($x, $y);
		if ($ville['distance'] >= $distanceMax) {
			return null;
		} else {
			return $ville;
		}
	}
}