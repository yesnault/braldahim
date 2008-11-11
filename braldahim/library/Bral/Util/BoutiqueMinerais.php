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
class Bral_Util_BoutiqueMinerais {
	
	public static function construireTabPrix($estFormulaire) {
		Zend_Loader::loadClass('TypeMinerai');
		
		$typeMineraiTable = new TypeMinerai();
		$typeMineraiRowset = $typeMineraiTable->fetchAll();
		
		$numChamp = 0;
		
		foreach ($typeMineraiRowset as $t) {
			$prixUnitaire = 11;
			
			$numChamp++;
			$idChamp = "valeur_".$numChamp;
					
			$tabBrut = array(
				"id_type_minerai" => $t->id_type_minerai, 
				"nom_systeme" => $t->nom_systeme_type_minerai, 
				"description" => $t->description_type_minerai,
				"prixUnitaire" => $prixUnitaire,
				"type" => $t->nom_type_minerai,
			);
			
			if ($estFormulaire) {
				$tabBrut["id_champ"] = $idChamp;
			}
			
			$minerais[] = $tabBrut;
		}
		return $minerais;
	}
}