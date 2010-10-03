<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_VieSolitaire {

	public function __construct($view, $villes) {
		$this->config = Zend_Registry::get('config');
		$this->view = $view;
		$this->villes = $villes;
	}

	public function action() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitairesAction - enter");
		try {
			// recuperation des monstres a jouer
			$monstreTable = new Monstre();
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(true, $this->config->game->monstre->nombre_monstre_a_jouer, false);
			$this->traiteSolitaires($monstres, true);
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(false, $this->config->game->monstre->nombre_monstre_a_jouer, false);
			$this->traiteSolitaires($monstres, false);
		} catch (Exception $e) {
			Bral_Util_Log::erreur()->err(get_class($this)." - vieSolitairesAction - Erreur:".$e->getTraceAsString());
			throw new Zend_Exception($e);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitairesAction - exit");
	}

	private function traiteSolitaires($solitaires, $aleatoire1D2) {
		foreach($solitaires as $s) {
			if ($aleatoire1D2 == false || ($aleatoire1D2 == true && Bral_Util_De::get_1d2() == 1)) {
				$this->vieSolitaireAction($s);
			}
		}
	}

	private function vieSolitaireAction(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitaireAction - enter (id=".$monstre["id_monstre"].")");

		$estFuite = $this->calculFuiteSolitaire($monstre);
		if ($estFuite) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitaireAction - fuite - (idm:".$monstre["id_monstre"].")");
			$this->deplacementSolitaire($monstre, true);
		} else {

			// action pre Reperage

			$cible = $this->calculReperageSolitaire($monstre);
			if ($cible != null) { // si une cible est trouvee, on attaque
				$this->attaqueSolitaire($monstre, $cible);
			} else {
				$this->deplacementSolitaire($monstre);
			}

			$this->calculPostAllSolitaire($monstre);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitaireAction - exit - (idm:".$monstre["id_monstre"].")");
	}

	/**
	 * Attaque de la cible.
	 */
	protected function attaqueSolitaire(&$monstre, &$cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$monstre["id_monstre"].") attaqueSolitaire - enter");

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);

		if ($cible != null) {
			$koCible = false;
			// on regarde si la cible demandée est bien la cible du monstre
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - cible du monstre (".$monstre["id_monstre"].") : ".$cible["id_braldun"]);
			$koCible = $vieMonstre->attaqueCible($cible, $this->view);

			if ($koCible == null) { // null => cible hors vue
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - cible hors vue (idm:".$monstre["id_monstre"].")");
				$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
			} else if ($koCible === true) {
				$monstre = $vieMonstre->getMonstre();
				$monstre["id_fk_braldun_cible_monstre"] = null;
				$vieMonstre->setMonstre($monstre);
				Zend_loader::loadClass("Bral_Monstres_Competences_Reperagestandard");
				$cible = Bral_Monstres_Competences_Reperagestandard::rechercheNouvelleCible($monstre);
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - nouvelle cible du monstre (".$monstre["id_monstre"].") : ".$cible["id_braldun"]);
				$vieMonstre->attaqueCible($cible, $this->view); // seconde attaque, utilise pour souffle de feu par exemple, si la cible principale est tuée par le souffle et qu'il reste 4 PA pour l'attaque
			}
		} else {
			$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
		}
		$monstre = $vieMonstre->getMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$monstre["id_monstre"].") attaqueSolitaire - exit");
	}

	/**
	 * Deplacement du solitaire.
	 */
	protected function deplacementSolitaire(&$monstre, $fuite = false) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementSolitaire - enter - (idm:".$monstre["id_monstre"].")");

		if ($fuite ||
		(($monstre["x_monstre"] == $monstre["x_direction_monstre"]) && //
		($monstre["y_monstre"] == $monstre["y_direction_monstre"]))) {

			if ($fuite) {
				$ajoutFuite = 10;
			} else {
				$ajoutFuite = 0;
			}

			$dx = Bral_Util_De::get_1d12() + $ajoutFuite;
			$dy = Bral_Util_De::get_1d12() + $ajoutFuite;

			$plusMoinsX = Bral_Util_De::get_1d2();
			$plusMoinsY = Bral_Util_De::get_1d2();

			if ($plusMoinsX == 1) {
				$monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] - $dx;
			} else {
				$monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] + $dx;
			}

			if ($plusMoinsY == 1) {
				$monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] - $dy;
			} else {
				$monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] + $dy;
			}

			$tab = Bral_Monstres_VieMonstre::getTabXYRayon($monstre["id_fk_zone_nid_monstre"], $monstre["niveau_monstre"], $monstre["x_direction_monstre"], $monstre["y_direction_monstre"], $monstre["x_min_monstre"], $monstre["x_max_monstre"], $monstre["y_min_monstre"], $monstre["y_max_monstre"], $monstre["id_monstre"]);

			$monstre["x_direction_monstre"] = $tab["x_direction"];
			$monstre["y_direction_monstre"] = $tab["y_direction"];

			Bral_Util_Log::viemonstres()->debug(get_class($this)." monstre (".$monstre["id_monstre"].")- calcul nouvelle valeur direction x=".$monstre["x_direction_monstre"]." y=".$monstre["y_direction_monstre"]." ");
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
		$monstre = $vieMonstre->getMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementSolitaire - exit - (idm:".$monstre["id_monstre"].")");
	}

	/*
	 * Recherche competence de fuite.
	 */
	private function calculFuiteSolitaire(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuiteSolitaire - enter");
		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$estFuite = $vieMonstre->calculFuite($this->view);
		$monstre = $vieMonstre->getMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuiteSolitaire - exit");
		return $estFuite;
	}

	private function calculReperageSolitaire(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculReperageSolitaire - enter");
		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$cible = $vieMonstre->calculReperage($this->view);
		$monstre = $vieMonstre->getMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculReperageSolitaire - exit");
		return $cible;
	}

	private function calculPostAllSolitaire(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculPostAllSolitaire - enter");
		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$cible = $vieMonstre->calculPostAll($this->view);
		$monstre = $vieMonstre->getMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculPostAllSolitaire - exit");
		return $cible;
	}
}