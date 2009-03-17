<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Equipement.php 1085 2009-01-27 21:13:21Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-27 22:13:21 +0100 (Tue, 27 Jan 2009) $
 * $LastChangedRevision: 1085 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Util_Region {

	public static function getRegionByXY($x, $y) {
		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$region = $regionTable->findByCase($x, $y);
		if ($region == null) {
			throw new Zend_Exception("Bral_Util_Region::getRegionByXY Region invalide x:".$x."y:".$y);
		} else {
			return $region;
		}
	}
	
	
}
