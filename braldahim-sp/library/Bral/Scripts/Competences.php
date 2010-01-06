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
class Bral_Scripts_Competences extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_STATIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 1;
	}

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Competences - calculScriptImpl - enter -");

		$retour = null;
		$this->calculCompetences($retour);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Competences - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculCompetences(&$retour) {

		$retour1 = 'idHobbit;typeCompetence;idCompetence;nom;nom_systeme;maitrise;id_fk_metier_competence'.PHP_EOL;
		$retour2 = '';

		Zend_Loader::loadClass("HobbitsCompetences");
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->hobbit->id_hobbit);

		foreach($hobbitCompetences as $c) {
			$retour2 .= $this->hobbit->id_hobbit.';'.$c["type_competence"].';'.$c["id_fk_competence_hcomp"].';'.$c["nom_competence"].';'.$c["nom_systeme_competence"].';'.$c["pourcentage_hcomp"].';'.$c["id_fk_metier_competence"].PHP_EOL;
		}

		$retour .= $retour1;
		$retour .= $retour2;
	}
}