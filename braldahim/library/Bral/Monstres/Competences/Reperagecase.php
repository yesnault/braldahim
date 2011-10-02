<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Reperagecase extends Bral_Monstres_Competences_Reperage
{

	public function actionSpecifique()
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - reperageCible - enter - (idm:" . $this->monstre["id_monstre"] . ")");
		$cible = null;

		// on regarde s'il y a une cible en cours
		if ($this->monstre["id_fk_braldun_cible_monstre"] != null) {
			Bral_Util_Log::viemonstres()->trace(get_class($this) . " - (idm:" . $this->monstre["id_monstre"] . ") - cible en cours A");
			$braldunTable = new Braldun();
			$vue = 0;

			$cible = $braldunTable->findBraldunAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $vue, $this->monstre["id_fk_braldun_cible_monstre"], false);
			if (count($cible) > 0) {
				$cible = $cible[0];
				$this->monstre["x_direction_monstre"] = $cible["x_braldun"];
				$this->monstre["y_direction_monstre"] = $cible["y_braldun"];
				Bral_Util_Log::viemonstres()->debug(get_class($this) . " - (idm:" . $this->monstre["id_monstre"] . ") - cible trouvee:" . $cible["id_braldun"] . " x=" . $this->monstre["x_direction_monstre"] . " y=" . $this->monstre["y_direction_monstre"]);
			} else {
				$this->monstre["id_fk_braldun_cible_monstre"] = null;
				Bral_Util_Log::viemonstres()->debug(get_class($this) . " - (idm:" . $this->monstre["id_monstre"] . ") - cible non trouvee x=" . $this->monstre["x_direction_monstre"] . " y=" . $this->monstre["y_direction_monstre"]);
			}
		} else { // pas de cible en cours
			$cible = null;
		}

		// si la cible n'est pas dans la vue, on en recherche une autre ou l'on se deplace
		if ($cible == null) {
			Bral_Util_Log::viemonstres()->debug(get_class($this) . " - (idm:" . $this->monstre["id_monstre"] . ") - pas de cible en cours B");
			$this->monstre["id_fk_braldun_cible_monstre"] = null;
			Zend_Loader::loadclass("Bral_Monstres_Competences_Reperagestandard");
			$cible = Bral_Monstres_Competences_Reperagestandard::rechercheNouvelleCible($this->monstre, 0);
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - reperageCible - exit - (idm:" . $this->monstre["id_monstre"] . ")");
		return $cible;
	}

}