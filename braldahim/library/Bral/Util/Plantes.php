<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Plantes {

	public static function getTabPlantes() {
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);

		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();

		$tabTypePlantes = null;
		$tabTypePlantesRetour = null;

		foreach($typePlantesRowset as $t) {
			foreach($typePartiePlantesRowset as $p) {
				$val = false;
				$idChamp = "";

				if ($t["id_fk_partieplante1_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante2_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante3_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante4_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}

				if (!isset($tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]."-".$p["nom_type_partieplante"]]) && $val == true) {
					$tab = array(
						'nom_type_plante' => $t["nom_type_plante"],
						'nom_type_partieplante' => $p["nom_type_partieplante"],
						'nom_systeme_type_plante' => $t["nom_systeme_type_plante"],
						'categorie_type_plante' => $t["categorie_type_plante"],
						'id_type_partieplante' => $p["id_type_partieplante"],
						'id_type_plante' => $t["id_type_plante"],
					);
					$tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]] = $tab;
					$tabTypePlantesRetour[] = $tab;
				}
			}
		}

		return $tabTypePlantesRetour;
	}

}
