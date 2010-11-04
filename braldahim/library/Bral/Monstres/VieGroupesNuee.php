<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_VieGroupesNuee extends Bral_Monstres_VieGroupes {

	const PHASE_NOMINAL = 1;
	const PHASE_ATTAQUE_1 = 2;
	const PHASE_ATTAQUE_2 = 3;
	const PHASE_ATTAQUE_3 = 4;

	public function action() {
		$this->vieGroupesAction($this->config->game->groupe_monstre->type->nuee);
	}

	/**
	 * Attaque de la cible.
	 */
	protected function attaqueGroupe(&$monstre_role_a, &$groupe, &$monstres, &$cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - enter");

		if ($groupe["date_phase_tactique_groupe_monstre"] < $this->dateCourante) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - changement phase tactique (".$groupe["date_phase_tactique_groupe_monstre"].") (".$groupe["phase_tactique_groupe_monstre"].")");
			if ($groupe["phase_tactique_groupe_monstre"] <= self::PHASE_NOMINAL) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_1;
			} else if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_1) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_2;
			} else if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_2) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_3;
			} else if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_3) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_1;
			}
		}

		if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_3) {
			$this->phaseTactique3($monstre_role_a, $groupe, $monstres, $cible);
			$this->majDateTactique($groupe, $monstres);
			return;
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();

		foreach($monstres as $m) {
			if ($cible != null) {
				$vieMonstre->setMonstre($m);
				$koCible = false;
				$cibleDuMonstre = null;

				// s'il un monstre à une cible à lui, on verifie qu'elle peut être atteinte
				if ($m["id_fk_braldun_cible_monstre"] != null) {
					$braldunTable = new Braldun();
					$cibleDuMonstre = $braldunTable->findBraldunAvecRayon($m["x_monstre"], $m["y_monstre"], $m["z_monstre"], $m["vue_monstre"] + $m["vue_malus_monstre"], $m["id_fk_braldun_cible_monstre"], false);
					if (count($cibleDuMonstre) > 0) {
						$cibleDuMonstre = $cibleDuMonstre[0];
						Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - cible du monstre (".$m["id_monstre"].") : ".$m["id_fk_braldun_cible_monstre"]);
						$koCibleMonstre = $vieMonstre->attaqueCible($cibleDuMonstre, $this->view);
						if ($koCibleMonstre == null) { // cible en dehors de la case mais dans la vue (puisque $cibleDuMonstre trouvee) , on tente un deplacement puis une attaque
							Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - cible du monstre (".$m["id_monstre"].") : ".$m["id_fk_braldun_cible_monstre"]. " koCibleMonstre == null");
							$vieMonstre->deplacementMonstre($cibleDuMonstre["x_braldun"], $cibleDuMonstre["y_braldun"]);
							$koCibleMonstre = $vieMonstre->attaqueCible($cibleDuMonstre, $this->view);
						}
						if ($koCibleMonstre === true || $koCibleMonstre === null) {
							$m["id_fk_braldun_cible_monstre"] = null;
							Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - Ko cible ou retour null pour le monstre (".$m["id_monstre"].")");
						}
					} else {
						$m["id_fk_braldun_cible_monstre"] = null;
						Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - Aucune cible pour le monstre (".$m["id_monstre"].")");
					}
				}

				// on regarde si la cible demandée est bien la cible du groupe
				if ($cible["id_braldun"] == $m["id_fk_braldun_cible_monstre"] || $m["id_fk_braldun_cible_monstre"] == null) {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - cible du groupe (".$groupe["id_groupe_monstre"].") monstre (".$m["id_monstre"].") : ".$cible["id_braldun"]);
					$koCible = $vieMonstre->attaqueCible($cible, $this->view);
				}

				if ($cibleDuMonstre != null) {
					$vieMonstre->deplacementMonstre($cibleDuMonstre["x_braldun"], $cibleDuMonstre["y_braldun"]);
				} else if ($koCible == null) { // null => cible hors vue
					$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
				} else if ($koCible === true) {
					$groupe["id_fk_braldun_cible_groupe_monstre"] = null;
					$cible = $this->rechercheNouvelleCible($monstre_role_a, $groupe, $monstres);
				}
			} else {
				$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
			}
		}

		$this->majDateTactique(&$groupe, &$monstres);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - exit");
	}

	private function phaseTactique3(&$monstre_role_a, &$groupe, &$monstres, $cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - phaseTactique3 - enter");
		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();

		foreach($monstres as $m) {
			if ($cible != null) {
				// si le monstre est sur la même case que la cible, deplacement a une case
				if ($m["x_monstre"] == $cible["x_braldun"] && $m["y_monstre"] == $cible["y_braldun"]) {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - phaseTactique3 - deplacement du monstre a une case");
					// deplacement
					$dx = -1;
					$dy = -1;

					if (Bral_Util_De::get_1d2() == 1) {
						$dx = 1;
					}
					if (Bral_Util_De::get_1d2() == 1) {
						$dy = 1;
					}

					$vieMonstre->setMonstre($m);
					$vieMonstre->deplacementMonstre($cible["x_braldun"] + $dx, $cible["y_braldun"] + $dy);
				} else {
					// rien a faire ici
				}
			}
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - phaseTactique3 - exit");
	}

	private function majDateTactique(&$groupe, &$monstres) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDateTactique - enter");

		foreach($monstres as $m) {
			if ($groupe["id_role_a_groupe_monstre"] == $m["id_monstre"]) {
				$groupe["date_phase_tactique_groupe_monstre"] = $m["date_fin_tour_monstre"];
				break;
			}
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDateTactique - exit");
	}

	/**
	 * Deplacement du groupe.
	 */
	protected function deplacementGroupe(&$monstre_role_a, &$groupe, &$monstres) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementGroupe - enter");
		// si le role_a est sur la direction, on deplacement le groupe

		$groupe["phase_tactique_groupe_monstre"] = self::PHASE_NOMINAL;

		if ((($monstre_role_a["x_monstre"] == $groupe["x_direction_groupe_monstre"]) && //
		($monstre_role_a["y_monstre"] == $groupe["y_direction_groupe_monstre"])) ||
		($groupe["x_direction_groupe_monstre"] == 0 && $groupe["y_direction_groupe_monstre"] == 0)) {

			$dx = Bral_Util_De::get_1d12();
			$dy = Bral_Util_De::get_1d12();

			$plusMoinsX = Bral_Util_De::get_1d2();
			$plusMoinsY = Bral_Util_De::get_1d2();

			if ($plusMoinsX == 1) {
				$groupe["x_direction_groupe_monstre"] = $monstre_role_a["x_monstre"] - $dx;
			} else {
				$groupe["x_direction_groupe_monstre"] = $monstre_role_a["x_monstre"] + $dx;
			}

			if ($plusMoinsY == 1) {
				$groupe["y_direction_groupe_monstre"] = $monstre_role_a["y_monstre"] - $dy;
			} else {
				$groupe["y_direction_groupe_monstre"] = $monstre_role_a["y_monstre"] + $dy;
			}

			$tab = Bral_Monstres_VieMonstre::getTabXYRayon($monstre_role_a["id_fk_zone_nid_monstre"], $monstre_role_a["niveau_monstre"], $groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"], $monstre_role_a["x_min_monstre"], $monstre_role_a["x_max_monstre"], $monstre_role_a["y_min_monstre"], $monstre_role_a["y_max_monstre"], $monstre_role_a["id_monstre"]);

			$groupe["x_direction_groupe_monstre"] = $tab["x_direction"];
			$groupe["y_direction_groupe_monstre"] = $tab["y_direction"];

			Bral_Util_Log::viemonstres()->debug(get_class($this)." - calcul nouvelle valeur direction x=".$groupe["x_direction_groupe_monstre"]." y=".$groupe["y_direction_groupe_monstre"]." ");
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		foreach($monstres as $m) {
			$vieMonstre->setMonstre($m);
			$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementGroupe - exit");
	}
}