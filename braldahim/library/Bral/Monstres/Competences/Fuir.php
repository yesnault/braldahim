<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Fuir extends Bral_Monstres_Competences_Fuite {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - (idm:".$this->monstre["id_monstre"].") - enter");

		$retour = false;

		if (($this->monstre["pv_restant_monstre"] * 100 / $this->monstre["pv_max_monstre"]) <= 20) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." (idm:".$this->monstre["id_monstre"].") - Fuite du monstre");

			$retour = true;
			$this->monstre["id_fk_braldun_cible_monstre"] = null;
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." (idm:".$this->monstre["id_monstre"].") - pas de fuite du monstre");
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - (idm:".$this->monstre["id_monstre"].") - exit");
		return $retour;
	}
}