<?php

class Bral_Monstres_VieMonstre {
	private $instance = null;
	private $monstre = null;

	public function getInstance() {
		if ($this->instance == null) {
			return new $this();
		} else {
			return $this->instance;
		}
	}

	private function __construct($view) {
		$this->view = $view;
	}

	public function deplacementMonstre($x_destination, $y_destination) {
		if ($monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::deplacementMonstre, monstre inconnu");
		}
		// TODO
	}

	public function attaqueCible($cible) {
		Zend_Loader::loadClass("Bral_Util_De");

		$mortCible = false;

		if ($monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::attaqueCible, monstre inconnu");
		}

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

			$this->miseAJourCible($cible);
			$this->miseAJourMonstre();
		}
		return $mortCible;
	}

	public function setMonstre($m) {
		if ($m == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::setMonstre, monstre invalide");
		}
		$this->monstre = $m;
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

	private function miseAJourCible($cible) {
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

	private function miseAJourMonstre() {
		if ($monstre == null) {
			new Zend_Exception("Bral_Monstres_VieMonstre::miseAJourMonstre, monstre inconnu");
		}
		
		$monstreTable = new Monstre();
		$data = array(
		'pa_monstre' => $monstre["pa_monstre"],
		);
		$where = "id_monstre=".$monstre["id_monstre"];
		$monstreTable->update($data, $where);
	}
}