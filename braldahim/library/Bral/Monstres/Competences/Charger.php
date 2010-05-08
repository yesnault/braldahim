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
class Bral_Monstres_Competences_Charger extends Bral_Monstres_Competences_Attaque {

	public function actionSpecifique() {
		/* on va à la position de la cible. */
		$this->monstre["x_monstre"] = $this->cible["x_braldun"];
		$this->monstre["y_monstre"] = $this->cible["y_braldun"];
		return $this->attaque();
	}

	protected function verificationCible() {
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - verificationCible - PA Monstre (".$this->monstre["id_monstre"].") avant action nb=".$this->monstre["pa_monstre"]);

		// on regarde si la cible est dans la vue du monstre
		if (($this->cible["x_braldun"] > $this->monstre["x_monstre"] + ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))
		|| ($this->cible["x_braldun"] < $this->monstre["x_monstre"] - ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))
		|| ($this->cible["y_braldun"] > $this->monstre["y_monstre"] + ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))
		|| ($this->cible["y_braldun"] < $this->monstre["y_monstre"] - ($this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]))) {
			// cible en dehors de la vue du monstre
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible en dehors de la vue hx=".$this->cible["x_braldun"] ." hy=".$this->cible["y_braldun"]. " mx=".$this->monstre["x_monstre"]. " my=".$this->monstre["y_monstre"]. " vue=". $this->monstre["vue_monstre"]."");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
			return null; // pas de cible
		} else if ($this->monstre["pa_monstre"] < $this->competence["pa_utilisation_mcompetence"]) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") insuffisant nb=".$this->monstre["pa_monstre"]." requis=".$this->competence["pa_utilisation_mcompetence"]);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit false");
			return false; // cible non morte
		}
		//else if (($this->cible["x_braldun"] != $this->monstre["x_monstre"]) || ($this->cible["y_braldun"] != $this->monstre["y_monstre"])) {
		//	Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre (".$this->monstre["id_monstre"].") cible (".$this->cible["id_braldun"].") sur une case differente");
		//	Bral_Util_Log::viemonstres()->trace(get_class($this)." - verificationCible -  monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
		//	return null; // pas de cible

		$chargeNbCases = floor($this->monstre["vigueur_base_monstre"] / 3) + 1;
		
		// borne à la vue
		if ($chargeNbCases > $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"]) {
			$chargeNbCases = $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"];
		}

		if ($chargeNbCases < 0) {
			$chargeNbCases = 0;
		}

		Zend_Loader::loadClass("Bral_Util_Dijkstra");
		$dijkstra = new Bral_Util_Dijkstra();
		$dijkstra->calcul($chargeNbCases, $this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);

		$x_min = $this->monstre["x_monstre"] - $chargeNbCases;
		$x_max = $this->monstre["x_monstre"] + $chargeNbCases;
		$y_min = $this->monstre["y_monstre"] - $chargeNbCases;
		$y_max = $this->monstre["y_monstre"] + $chargeNbCases;

		$tabValide = null;
		$numero = -1;
		for ($j = $y_max ; $j >= $y_min ; $j--) {
			for ($i = $x_min ; $i <= $x_max ; $i++) {
				$numero++;
				$tabValide[$i][$j] = true;
				if ($dijkstra->getDistance($numero) > $chargeNbCases) {
					$tabValide[$i][$j] = false;
				}
			}
		}
		
		// on ne charge pas sur la case
		$tabValide[$this->monstre["x_monstre"]][$this->monstre["y_monstre"]] = false;
		
		if (array_key_exists($this->cible["x_braldun"], $tabValide) &&
		array_key_exists($this->cible["y_braldun"], $tabValide[$this->cible["x_braldun"]]) && 
		$tabValide[$this->cible["x_braldun"]][$this->cible["y_braldun"]] === true) {
			return true; // controle cible OK
		} else {
			return null; // pas de cible
		}

	}

	public function calculJetAttaque() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - (idm:".$this->monstre["id_monstre"].") enter");

		$jetAttaquant = 0;

		$jetAttaquant = Bral_Util_De::getLanceDe6($this->monstre["agilite_base_monstre"]);
		$jetAttaquant = floor(0.5 * $jetAttaquant + $this->monstre["agilite_bm_monstre"] + $this->monstre["bm_attaque_monstre"]);

		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - (idm:".$this->monstre["id_monstre"].") exit (jet=".$jetAttaquant.")");
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
		$jetDegat = $jetDegat + Bral_Util_De::getLanceDe6(self::$config->game->base_vigueur + $this->monstre["vigueur_base_monstre"]);
		$jetDegat = $jetDegat + $this->monstre["vigueur_bm_monstre"];

		if ($jetDegat < 0) {
			$jetDegat = 0;
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculDegat - (idm:".$this->monstre["id_monstre"].") exit (jet=$jetDegat)");
		return $jetDegat;
	}

}