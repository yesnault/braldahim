<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Region
{

	public static function getRegionByXY($x, $y)
	{
		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$region = $regionTable->findByCase($x, $y);
		if ($region == null) {
			throw new Zend_Exception("Bral_Util_Region::getRegionByXY Region invalide x:" . $x . "y:" . $y);
		} else {
			return $region;
		}
	}

}
