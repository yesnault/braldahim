<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Deplacementsolitaire extends Bral_Monstres_Competences_Deplacement
{

	public function actionSpecifique()
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Deplacementsolitaire - enter - (idm:" . $this->monstre["id_monstre"] . ")");

		if ($this->estFuite ||
			(($this->monstre["x_monstre"] == $this->monstre["x_direction_monstre"]) && //
				($this->monstre["y_monstre"] == $this->monstre["y_direction_monstre"]))
		) {

			if ($this->estFuite) {
				$ajoutFuite = 10;
			} else {
				$ajoutFuite = 0;
			}

			if ($this->monstre["z_monstre"] < 0) {
				$dx = Bral_Util_De::get_1d1();
				$dy = Bral_Util_De::get_1d1();
			} else {
				$dx = Bral_Util_De::get_1d10() + $ajoutFuite;
				$dy = Bral_Util_De::get_1d10() + $ajoutFuite;
			}

			$plusMoinsX = Bral_Util_De::get_1d2();
			$plusMoinsY = Bral_Util_De::get_1d2();

			if ($plusMoinsX == 1) {
				$this->monstre["x_direction_monstre"] = $this->monstre["x_direction_monstre"] - $dx;
			} else {
				$this->monstre["x_direction_monstre"] = $this->monstre["x_direction_monstre"] + $dx;
			}

			if ($plusMoinsY == 1) {
				$this->monstre["y_direction_monstre"] = $this->monstre["y_direction_monstre"] - $dy;
			} else {
				$this->monstre["y_direction_monstre"] = $this->monstre["y_direction_monstre"] + $dy;
			}

			$tab = Bral_Monstres_VieMonstre::getTabXYRayon($this->monstre["id_fk_zone_nid_monstre"], $this->monstre["niveau_monstre"], $this->monstre["x_direction_monstre"], $this->monstre["y_direction_monstre"], $this->monstre["x_min_monstre"], $this->monstre["x_max_monstre"], $this->monstre["y_min_monstre"], $this->monstre["y_max_monstre"], $this->monstre["id_monstre"]);

			$this->monstre["x_direction_monstre"] = $tab["x_direction"];
			$this->monstre["y_direction_monstre"] = $tab["y_direction"];

			Bral_Util_Log::viemonstres()->debug(get_class($this) . " monstre (" . $this->monstre["id_monstre"] . ")- calcul nouvelle valeur direction x=" . $this->monstre["x_direction_monstre"] . " y=" . $this->monstre["y_direction_monstre"] . " ");
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this) . " monstre (" . $this->monstre["id_monstre"] . ")- pas de nouvelle direction direction:x=" . $this->monstre["x_direction_monstre"] . " y=" . $this->monstre["y_direction_monstre"] . " ");
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($this->monstre);
		$vieMonstre->deplacementMonstre($this->monstre["x_direction_monstre"], $this->monstre["y_direction_monstre"]);

		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Deplacementsolitaire - exit - (idm:" . $this->monstre["id_monstre"] . ")");
		return;
	}

}