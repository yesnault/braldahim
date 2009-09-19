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
class Bral_Util_Equipement {
	
	public static function getNomByIdRegion($typeEquipement, $idRegion) {
		$template = "";
		if ($typeEquipement["vernis_template_equipement"] != null) {
			$template = " [".$typeEquipement["vernis_template_equipement"]."]";
		}

		switch($idRegion) {
			case 1:
				return $typeEquipement["region_1_nom_type_equipement"].$template;
				break;
			case 2:
				return $typeEquipement["region_2_nom_type_equipement"].$template;
				break;
			case 3:
				return $typeEquipement["region_3_nom_type_equipement"].$template;
				break;
			case 4:
				return $typeEquipement["region_4_nom_type_equipement"].$template;
				break;
			case 5:
				return $typeEquipement["region_5_nom_type_equipement"].$template;
				break;
			default:
				throw new Zend_Exception("Bral_Util_Equipement::getNomByIdRegion Region invalide id:".$idRegion);
				break;
		}
	}

}
