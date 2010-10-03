<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Conjoints {

	private function __construct(){}

	public static function getConjoint($sexe, $idBraldun) {
		Zend_Loader::loadClass('Couple');
		$coupleTable = new Couple();
		$conjointRowset = $coupleTable->findConjoint($sexe, $idBraldun);

		$conjoint = null;
		if (count($conjointRowset) > 1) {
			throw new Zend_Exception("Bral_Util_Conjoints::getConjoint nb conjoints invalide. idh:".$idBraldun);
		} else if (count($conjointRowset) == 1) {
			$c = $conjointRowset[0];
			$conjoint = array(
				"prenom" => $c["prenom_braldun"],
				"nom" => $c["nom_braldun"],
				"id_braldun" => $c["id_braldun"]
			);
		} else {
			$conjoint =  self::getAncienConjoint($sexe, $idBraldun);
		}

		return $conjoint;
	}

	private static function getAncienConjoint($sexe, $idBraldun) {
		$coupleTable = new Couple();
		$conjointRowset = $coupleTable->findConjoint($sexe, $idBraldun, true);

		$conjoint = null;
		if (count($conjointRowset) > 1) {
			throw new Zend_Exception("Bral_Util_Conjoints::getAncienConjoint nb conjoints invalide. idh:".$idBraldun);
		} else if (count($conjointRowset) == 1) {
			$c = $conjointRowset[0];
			$conjoint = array(
				"prenom" => $c["prenom_ancien_braldun"],
				"nom" => $c["nom_ancien_braldun"],
				"id_braldun" => $c["id_ancien_braldun"]
			);
		}
		
		return $conjoint;
	}
}