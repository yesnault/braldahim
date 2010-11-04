<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Bouchertunnel extends Bral_Monstres_Competences_Attaque {

	// prereperage
	public function actionSpecifique() {
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - enter - (idm:".$this->monstre["id_monstre"].")");

		$retour = Bral_Monstres_Competences_Prereperage::SUITE_DISPARITION;
		
		//abat un tunnel et disparait s'il n'a personne (Braldûn) dans sa vue.
		
		//TODO
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - exit - (idm:".$this->monstre["id_monstre"].")");
		return $retour;
	}

	public function enchainerAvecReperageStandard() {
		return false;
	}

	private function majEvenement() {
		//TODO
	}
}