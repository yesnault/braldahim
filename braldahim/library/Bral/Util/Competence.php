<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Competence
{

	const NOM_SYSTEME_IDENTIFIER_RUNE = "identifierrune";

	private function __construct()
	{
	}

	public static function updateCompetence1d2($nomSystemeCompetence, $idBraldun)
	{
		$braldunsCompetencesTable = new BraldunsCompetences();

		$competences = $braldunsCompetencesTable->findByIdBraldunAndNomSysteme($idBraldun, $nomSystemeCompetence);
		if ($competences == null || count($competences) != 1) {
			throw new Zend_Exception(get_class($this) . " Competences invalides :" . $idBraldun . "," . $nomSystemeCompetence);
		}

		$tabCompetenceAmelioree = null;

		$c = $competences[0];
		if ($c["pourcentage_hcomp"] < 50) {
			$gain = Bral_Util_De::get_1d2();
			$pourcentage = $c["pourcentage_hcomp"] + $gain;
			if ($pourcentage > $c["pourcentage_max_competence"]) { // % comp maximum
				$pourcentage = $c["pourcentage_max_competence"];
			}
			$data = array('pourcentage_hcomp' => $pourcentage);
			$where = array("id_fk_competence_hcomp = " . $c["id_fk_competence_hcomp"] . " AND id_fk_braldun_hcomp = " . $idBraldun);
			$braldunsCompetencesTable->update($data, $where);
			$tabCompetenceAmelioree["competence"] = $c;
			$tabCompetenceAmelioree["gain"] = $gain;
		}
		return $tabCompetenceAmelioree;
	}
}