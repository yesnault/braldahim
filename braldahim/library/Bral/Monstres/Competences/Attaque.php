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
abstract class Bral_Monstres_Competences_Attaque {

	protected $monstre = null;
	protected $cible = null;
	protected $view = null;
	protected static $config = null;

	public function __construct(&$monstre, $cible, $config, $view) {
		$this->monstre = &$monstre;
		$this->cible = $cible;
		self::$config = $config;
		$this->view = $view;
	}

	public function action() {

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - action - (idm:".$this->monstre["id_monstre"].") - enter");
		
		// on regarde si la cible est dans la vue du monstre
		if (($this->cible["x_hobbit"] > $this->monstre["x_monstre"] + $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($this->cible["x_hobbit"] < $this->monstre["x_monstre"] - $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($this->cible["y_hobbit"] > $this->monstre["y_monstre"] + $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])
		|| ($this->cible["y_hobbit"] < $this->monstre["y_monstre"] - $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"])) {
			// cible en dehors de la vue du monstre
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible en dehors de la vue hx=".$this->cible["x_hobbit"] ." hy=".$this->cible["y_hobbit"]. " mx=".$this->monstre["x_monstre"]. " my=".$this->monstre["y_monstre"]. " vue=". $this->monstre["vue_monstre"]."");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
			return null; // pas de cible
		} else if (($this->cible["x_hobbit"] != $this->monstre["x_monstre"]) || ($this->cible["y_hobbit"] != $this->monstre["y_monstre"])) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - monstre (".$this->monstre["id_monstre"].") cible (".$this->cible["id_hobbit"].") sur une case differente");
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - monstre (".$this->monstre["id_monstre"].") attaqueCible - exit null");
			return null; // pas de cible
		} else if ($this->monstre["pa_monstre"] < $this->getNbPA()) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") insuffisant nb=".$this->monstre["pa_monstre"]);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - monstre (".$this->monstre["id_monstre"].") attaqueCible - exit false");
			return false; // cible non morte
		}

		Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") avant attaque nb=".$this->monstre["pa_monstre"]);
		$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - $this->getNbPA();

		$koCible = $this->actionSpecifique();

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - action - (idm:".$this->monstre["id_monstre"].") - exit");
		return $koCible;
	}

	abstract function actionSpecifique();
	
	private function getNbPA() {
		//TODO
	}

	protected function updateCible() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - enter (id_hobbit=".$this->cible["id_hobbit"].")");

		Bral_Util_Attaque::calculStatutEngage(&$this->cible, true);

		// Mise a jour de la cible
		$hobbitTable = new Hobbit();
		$data = array(
			'pv_restant_hobbit' => $this->cible["pv_restant_hobbit"],
			'est_ko_hobbit' => $this->cible["est_ko_hobbit"],
			'nb_ko_hobbit' => $this->cible["nb_ko_hobbit"],
			'agilite_bm_hobbit' => $this->cible["agilite_bm_hobbit"],
			'est_engage_hobbit' => $this->cible["est_engage_hobbit"],
			'est_engage_next_dla_hobbit' => $this->cible["est_engage_next_dla_hobbit"],
			'date_fin_tour_hobbit' => $this->cible["date_fin_tour_hobbit"],
			'est_quete_hobbit' => $this->cible["est_quete_hobbit"],
			'bm_attaque_hobbit' => $this->cible["bm_attaque_hobbit"],
			'bm_defense_hobbit' => $this->cible["bm_defense_hobbit"],
		);
		$where = "id_hobbit=".$this->cible["id_hobbit"];
		$hobbitTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateCible - exit");
	}

	protected function calculJetCible($cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetCible - enter");
		$jetCible = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->cible["agilite_base_hobbit"]);
		$jetCible = $jetCible + $this->cible["agilite_bm_hobbit"] + $this->cible["bm_defense_hobbit"] + $this->cible["agilite_bbdf_hobbit"];

		if ($jetCible < 0) {
			$jetCible = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetCible - exit (jet=".$jetCible.")");
		return $jetCible;
	}

	protected function calculJetAttaque() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - (idm:".$this->monstre["id_monstre"].") enter");
		$jetAttaquant = Bral_Util_De::getLanceDe6($this->monstre["agilite_base_monstre"]);
		$jetAttaquant = $jetAttaquant + $this->monstre["agilite_bm_monstre"] + $this->monstre["bm_attaque_monstre"];

		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculJetAttaque - (idm:".$this->monstre["id_monstre"].") exit (jet=".$jetAttaquant.")");
		return $jetAttaquant;
	}

	protected function calculDegat($estCritique) {
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