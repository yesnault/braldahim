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
class Bral_Util_Registre {

	private function __construct(){}

	public static function getNomUnite($unite, $systeme = false, $quantite = 0) {
		if (Zend_Registry::isRegistered("typesUnites") == false) {
			self::chargementTypeUnite();		
		} 
		$tabUnite = Zend_Registry::get('typesUnites');
		if ($unite != null && isset($tabUnite[$unite])) {
			if (!$systeme) {
				if ($quantite > 1) {
					return $tabUnite[$unite]["nom_pluriel"];
				} else {
					return $tabUnite[$unite]["nom"];
				}
			} else {
				return $tabUnite[$unite]["nom_systeme"];
			}
		}
	}
	
	private static function chargementTypeUnite() {
		Zend_Loader::loadClass("TypeUnite");
		$typeUniteTable = new TypeUnite();
		$typeUniteRowset = $typeUniteTable->fetchAll();
		$typeUniteRowset = $typeUniteRowset->toArray();
		foreach ($typeUniteRowset as $t) {
			$tabUnite[$t["id_type_unite"]] = array(
				"nom_systeme" => $t["nom_systeme_type_unite"], 
				"nom" => $t["nom_type_unite"],
				"nom_pluriel" => $t["nom_pluriel_type_unite"],
			);
		}
		Zend_Registry::set('typesUnites', $tabUnite);
	}
}
