<?php

class Bral_Monstres_VieMonstre {
	private static $instance = null;
	private $monstre = null;

	public static function getInstance() {
		if (self::$instance == null) {
			Zend_Loader::loadClass("Bral_Util_De");
			return new self();
		} else {
			return $this->instance;
		}
	}

	private function __construct() {
	}

	/**
	 * Déplacement du monstre une une position.
	 *
	 * @param int $x_destination
	 * @param int $y_destination
	 * @return boolean : le monstre a bougé (true) ou non (false)
	 */
	public function deplacementMonstre($x_destination, $y_destination) {
		if ($monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::deplacementMonstre, monstre inconnu");
		}

		$this->calculTour();

		// on regarde si le monstre est déjà dans la position
		if (($x_destination == $monstre["x_monstre"]) && ($y_destination == $monstre["y_monstre"])) {
			return false;
		}
		$modif = false;
		while (($x_destination != $monstre["x_monstre"]) && ($y_destination != $monstre["y_monstre"]) && ($monstre["pa_monstre"] > 0)) {
			if ($monstre["x_monstre"] < $x_destination) {
				$monstre["x_monstre"] = $monstre["x_monstre"] + 1;
				$modif = true;
			} else if ($monstre["x_monstre"] > $x_destination) {
				$monstre["x_monstre"] = $monstre["x_monstre"] - 1;
				$modif = true;
			}
			if ($monstre["y_monstre"] < $y_destination) {
				$monstre["y_monstre"] = $monstre["y_monstre"] + 1;
				$modif = true;
			} else if ($monstre["y_monstre"] > $y_destination) {
				$monstre["y_monstre"] = $monstre["y_monstre"] - 1;
				$modif = true;
			}

			if ($modif === true) {
				$monstre["pa_monstre"] = $monstre["pa_monstre"] - 1;
			}
		}
		if ($modif === true) {
			return true;
			$this->updateMonstre();
		} else
		return false;
	}

	public function attaqueCible($cible) {
		$mortCible = false;

		if ($monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::attaqueCible, monstre inconnu");
		}

		$this->calculTour();

		// on regarde si la cible est dans la vue du monstre
		if (($cible["x_hobbit"] > $monstre["x_monstre"] + $monstre["vue_monstre"])
		|| ($cible["x_hobbit"] < $monstre["x_monstre"] - $monstre["vue_monstre"])
		|| ($cible["y_hobbit"] > $monstre["y_monstre"] + $monstre["vue_monstre"])
		|| ($cible["y_hobbit"] > $monstre["y_monstre"] - $monstre["vue_monstre"])) {
			// cible en dehors de la vue du monstre
			return null;
		}

		$monstre["pa_monstre"] = $monstre["pa_monstre"] - 4;

		$jetAttaquant = $this->calculJetAttaque();
		$jetCible = $this->calculJetCible($cible);

		//Pour que l'attaque touche : jet AGI attaquant > jet AGI attaqué
		if ($jetAttaquant > $jetCible) {
			$jetDegat = $this->calculDegat();

			$cible["pv_restant_hobbit"] = $cible["pv_restant_hobbit"] - $jetDegat;
			$nb_kills = $monstre["nb_kill_monstre"];
			$nb_morts = $cible["nb_mort_hobbit"];
			if ($pv <= 0) {
				$monstre["nb_kill_monstre"] = $monstre["nb_kill_monstre"] + 1;
				$cible["nb_mort_hobbit"] = $cible["nb_mort_hobbit"] + 1;
				$cible["est_mort_hobbit"] = "oui";
			} else {
				$cible["est_mort_hobbit"] = "non";
			}

			$this->updateCible($cible);
			$this->updateMonstre();
		}
		return $mortCible;
	}

	public function setMonstre($m) {
		if ($m == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::setMonstre, monstre invalide");
		}
		$this->monstre = $m;
	}

	private function calculTour() {
		if ($m == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::calculTour, monstre invalide");
		}

		$date_courante = date("Y-m-d H:i:s");

		if ($date_courante > $monstre["date_fin_tour_monstre"]) {
			$monstre["date_fin_tour_monstre"] = Bral_Util_ConvertDate::get_date_add_time_to_date($monstre["date_fin_tour_monstre"], $monstre["duree_prochain_tour_monstre"]);
			$monstre["duree_prochain_tour_monstre"] = $monstre["duree_base_tour_monstre"];
			$this->updateMonstre();
		}
	}
	
	private function calculJetCible($cible) {
		$jetCible = 0;
		for ($i=1; $i<=$cible["agilite_base_hobbit"]; $i++) {
			$jetCible = $jetCible + Bral_Util_De::get_1d6();
		}
		$jetCible = $jetCible + $cible["agilite_bm_hobbit"];
		return $jetCible;
	}

	private function calculJetAttaque() {
		$jetAttaquant = 0;
		for ($i=1; $i<=$this->monstre["agilite_base_monstre"]; $i++) {
			$jetAttaquant = $jetAttaquant + Bral_Util_De::get_1d6();
		}
		$jetAttaquant = $jetAttaquant + $this->monstre["agilite_bm_monstre"];
		return $jetAttaquant;
	}

	private function calculDegat() {
		$jetDegat = 0;
		for ($i=1; $i<=$this->monstre["force_base_monstre"]; $i++) {
			$jetDegat = $jetDegat + Bral_Util_De::get_1d3();
		}
		$jetDegat = $jetDegat + $this->monstre["force_bm_monstre"];
		return $jetDegat;
	}

	private function updateCible($cible) {
		// Mise a jour de la cible
		$hobbitTable = new Hobbit();
		$data = array(
		'pv_restant_hobbit' => $cible["pv_restant_hobbit"],
		'est_mort_hobbit' => $cible["est_mort_hobbit"],
		'nb_mort_hobbit' => $cible["nb_mort_hobbit"],
		'date_fin_tour_hobbit' => date("Y-m-d H:i:s"),
		);
		$where = "id_hobbit=".$cible["id_hobbit"];
		$hobbitTable->update($data, $where);
	}

	private function majMonstre() {
		if ($monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::miseAJourMonstre, monstre inconnu");
		}

		$monstreTable = new Monstre();
		$data = array(
		'pa_monstre' => $monstre["pa_monstre"],
		'x_monstre' => $monstre["x_monstre"],
		'y_monstre' => $monstre["y_monstre"],
		);
		$where = "id_monstre=".$monstre["id_monstre"];
		$monstreTable->update($data, $where);
	}
}