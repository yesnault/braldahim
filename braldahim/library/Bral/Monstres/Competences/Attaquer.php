<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Attaquer extends Bral_Monstres_Competences_Attaque {

	public function actionSpecifique() {
		return $this->attaque();
	}

	public function calculJetAttaque() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - (idm:".$this->monstre["id_monstre"].") enter");
		$jetAttaquant = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->monstre["agilite_base_monstre"]);
		$jetAttaquant = $jetAttaquant + $this->monstre["agilite_bm_monstre"] + $this->monstre["bm_attaque_monstre"];

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - (idm:".$this->monstre["id_monstre"].") exit (bm_attaque_monstre=".$this->monstre["bm_attaque_monstre"].")");
		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - (idm:".$this->monstre["id_monstre"].") exit (jetAttaque=".$jetAttaquant.")");
		return $jetAttaquant;
	}

	public function calculDegat($estCritique) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDegat - (idm:".$this->monstre["id_monstre"].") enter (critique=".$estCritique.")");
		$coefCritique = 1;
		if ($estCritique === true) {
			$coefCritique = 1.5;
		}

		$jetDegat = Bral_Util_De::getLanceDe6((self::$config->game->base_force + $this->monstre["force_base_monstre"])  * $coefCritique);
		$jetDegat = $jetDegat + $this->monstre["force_bm_monstre"] + $this->monstre["bm_degat_monstre"];

		if ($jetDegat < 0) {
			$jetDegat = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDegat - (idm:".$this->monstre["id_monstre"].") exit (jet=$jetDegat)");
		return $jetDegat;
	}

}