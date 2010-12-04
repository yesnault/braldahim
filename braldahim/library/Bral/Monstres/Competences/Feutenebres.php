<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Feutenebres extends Bral_Monstres_Competences_Deplacement {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Feutenebres - enter - (idm:".$this->monstre["id_monstre"].")");
			

		// Recuperation des infos de la cible
		if ($this->monstre["id_fk_braldun_cible_monstre"] != null) {
			// S'il y a une cible,
			$braldunTable = new Braldun();
			$cibles = $braldunTable->findBraldunAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $vue, $this->monstre["id_fk_braldun_cible_monstre"], false);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Feutenebres - cible en cours - (idm:".$this->monstre["id_monstre"].")");
			if (count($cibles) == 1) {
				$cible = $cibles[0];
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Feutenebres - cible en cours trouvee:(idh:".$cible["id_braldun"].") - (idm:".$this->monstre["id_monstre"].")");
				if ($cible["x_braldun"] == $this->monstre["x_monstre"] &&
				$cible["y_braldun"] == $this->monstre["y_monstre"]) {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - Feutenebres - cible sur la même case, pas de feu des tenebres - exit - (idm:".$this->monstre["id_monstre"].")");
					return; // Cible sur la même case
				}
			}
		}

		// S'il y a une case non creusée autour, on creuse.
		Zend_Loader::loadClass("Tunnel");
		$tunnelTable = new Tunnel();
		
		$tunnels = $tunnelTable->selectVue($this->monstre["x_monstre"]-1, $this->monstre["y_monstre"]-1, $this->monstre["x_monstre"]+1, $this->monstre["y_monstre"]+1, $this->monstre["z_monstre"]);

		if (count($tunnels) == 9) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Feutenebres - il y a deja 9 tunnels autour, pas de feu des tenebres - exit - (idm:".$this->monstre["id_monstre"].")");
			return;// Toutes les cases sont creusées autour
		}

		//TODO Insérer les tunnels
		//Voir si les tunnels ne se rebouchent pas automatiquement au bout de n jours
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Feutenebres - exit - (idm:".$this->monstre["id_monstre"].")");
		return;
	}

	private function majEvenement($braldun, $malus, $nbTours, $jetMonstre, $jetBraldun) {
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun) {
	}
}