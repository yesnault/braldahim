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
class Bral_Util_Titre {

	function __construct() {
	}
	
	public static function prepareTitre($idHobbit, $sexeHobbit) {
		Zend_Loader::loadClass("HobbitsTitres");
		$hobbitsTitresTable = new HobbitsTitres();
		$hobbitsTitreRowset = $hobbitsTitresTable->findTitresByHobbitId($idHobbit);
		unset($hobbitsTitresTable);
		$tabTitres = null;
		$possedeTitre = false;

		foreach($hobbitsTitreRowset as $t) {
			$possedeTitre = true;
			
			if ($sexeHobbit == 'feminin') {
				$nom_titre = $t["nom_feminin_type_titre"];
			} else {
				$nom_titre = $t["nom_masculin_type_titre"];
			}

			$tabTitres[] = array(
				"nom" => $nom_titre,
				"nom_systeme" => $t["nom_systeme_type_titre"],
				"description" => $t["description_type_titre"],
				"date_acquis_htitre" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $t["date_acquis_htitre"]),
				"niveau_acquis_htitre" => $t["niveau_acquis_htitre"],
			);
			
		}
		unset($hobbitsTitreRowset);
		
		$retour["tabTitres"] = $tabTitres;
		$retour["possedeTitre"] = $possedeTitre;
		return $retour;
	}
	
	public static function calculBMTitres(&$hobbit) {
		Zend_Loader::loadClass("HobbitsTitres");
		$hobbitsTitresTable = new HobbitsTitres();
		$hobbitsTitreRowset = $hobbitsTitresTable->findTitresByHobbitId($hobbit->id_hobbit);
		unset($hobbitsTitresTable);
		$tabTitres = null;
		if ($hobbitsTitreRowset != null && count($hobbitsTitreRowset) > 0) {
			foreach($hobbitsTitreRowset as $t) {
				
				switch($t["nom_systeme_type_titre"]) {
					case "sagesse" :
						$hobbit->sagesse_bm_hobbit = $hobbit->sagesse_bm_hobbit + 1;
						break;
					case "vigueur":
						$hobbit->vigueur_bm_hobbit = $hobbit->vigueur_bm_hobbit + 1;
						break;
					case "force" :
						$hobbit->force_bm_hobbit = $hobbit->force_bm_hobbit + 1;
						break;
					case "agilite" : 
						$hobbit->agilite_bm_hobbit = $hobbit->agilite_bm_hobbit + 1;
						break;
					default:
						throw new Zend_Exception("Titre nom systeme inconnu :".$t["nom_systeme_type_titre"]);
					
				}
			}
		}
		unset($hobbitsTitreRowset);
	}
}