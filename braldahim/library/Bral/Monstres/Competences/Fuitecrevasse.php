<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Fuitecrevasse extends Bral_Monstres_Competences_Fuite {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - (idm:".$this->monstre["id_monstre"].") - enter");

		$retour = false;

		if (($this->monstre["pv_restant_monstre"] * 100 / $this->monstre["pv_max_monstre"]) <= 50) {
			
			Zend_Loader::loadClass("Crevasse");
			$crevasseTable = new Crevasse();
			$x_min = $this->monstre["x_monstre"] - ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]);
			$x_max = $this->monstre["x_monstre"] + ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]);
			$y_min = $this->monstre["y_monstre"] - ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]);
			$y_max = $this->monstre["y_monstre"] + ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]);
			$crevasses = $crevasseTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"]);
				
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - Fuite du monstre - recherche crevasse - (idm:".$this->monstre["id_monstre"].") : $x_min, $y_min, $x_max, $y_max");
			
			if (count($crevasses) > 0) {
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - (idm:".$this->monstre["id_monstre"].") Fuite du monstre - crevasse - (idm:".$this->monstre["id_monstre"].")");
				$this->monstre["x_monstre"] = $crevasses[0]["x_crevasse"];
				$this->monstre["y_monstre"] = $crevasses[0]["y_crevasse"];
				$this->monstre["z_monstre"] = $crevasses[0]["z_crevasse"] - 1;
				$retour = true;
				$this->majEvenement();
			}

			$this->monstre["id_fk_braldun_cible_monstre"] = null;
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - Pas de Fuite du monstre - (idm:".$this->monstre["id_monstre"].")");
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - (idm:".$this->monstre["id_monstre"].") - exit");
		return $retour;
	}

	private function majEvenement() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->special;
		$details = "[m".$this->monstre["id_monstre"]."] a sauté dans une crevasse";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

}