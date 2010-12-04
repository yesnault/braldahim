<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Cheminement extends Bral_Monstres_Competences_Deplacement {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - enter - (idm:".$this->monstre["id_monstre"].")");
			
		//TODO
		// S'il y a une cible, direction sur la cible ==> Toutes les cases sont forcément creusées autour
		// par le feu des ténèbres
		
		// S'il n'y a pas de cible, on avance d'une case 
		
		
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - exit - (idm:".$this->monstre["id_monstre"].")");
		return;
	}

	private function majEvenement($braldun, $malus, $nbTours, $jetMonstre, $jetBraldun) {
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun) {
	}
}