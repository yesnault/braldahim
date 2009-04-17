<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Util_Conjoints {

	private function __construct(){}

	public static function getConjoint($sexe, $idHobbit) {
		Zend_Loader::loadClass('Couple');
		$coupleTable = new Couple();
		$conjointRowset = $coupleTable->findConjoint($sexe, $idHobbit);

		$conjoint = null;
		if (count($conjointRowset) > 1) {
			throw new Zend_Exception("Bral_Util_Conjoints::getConjoint nb conjoints invalide. idh:".$idHobbit);
		} else if (count($conjointRowset) == 1) {
			$c = $conjointRowset[0];
			$conjoint = array(
				"prenom" => $c["prenom_hobbit"],
				"nom" => $c["nom_hobbit"],
				"id_hobbit" => $c["id_hobbit"]
			);
		} else {
			$conjoint =  self::getAncienConjoint($sexe, $idHobbit);
		}

		return $conjoint;
	}

	private static function getAncienConjoint($sexe, $idHobbit) {
		$coupleTable = new Couple();
		$conjointRowset = $coupleTable->findConjoint($sexe, $idHobbit, true);

		$conjoint = null;
		if (count($conjointRowset) > 1) {
			throw new Zend_Exception("Bral_Util_Conjoints::getAncienConjoint nb conjoints invalide. idh:".$idHobbit);
		} else if (count($conjointRowset) == 1) {
			$c = $conjointRowset[0];
			$conjoint = array(
				"prenom" => $c["prenom_ancien_hobbit"],
				"nom" => $c["nom_ancien_hobbit"],
				"id_hobbit" => $c["id_ancien_hobbit"]
			);
		}
		
		return $conjoint;
	}
}