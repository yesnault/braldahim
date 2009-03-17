<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Equipement.php 1336 2009-03-17 21:19:40Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-03-17 22:19:40 +0100 (Tue, 17 Mar 2009) $
 * $LastChangedRevision: 1336 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Util_Equipement {

	public static function getNomByIdRegion($typeEquipement, $idRegion) {
		switch($idRegion) {
			case 1:
				return $typeEquipement["region_1_nom_type_equipement"];
				break;
			case 2:
				return $typeEquipement["region_2_nom_type_equipement"];
				break;
			case 3:
				return $typeEquipement["region_3_nom_type_equipement"];
				break;
			case 4:
				return $typeEquipement["region_4_nom_type_equipement"];
				break;
			case 5:
				return $typeEquipement["region_5_nom_type_equipement"];
				break;
			default:
				throw new Zend_Exception("Bral_Util_Equipement::getNomByIdRegion Region invalide id:".$idRegion);
				break;
		}
	}
}
