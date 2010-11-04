<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Monstres_VieGroupes {

	public function __construct($view, $villes) {
		Zend_Loader::loadClass("Ville");
		$this->config = Zend_Registry::get('config');
		$this->view = $view;
		$this->dateCourante = date("Y-m-d H:i:s");
		$this->villes = $villes;
	}

	abstract function action();

	public function vieGroupesAction($type) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupesAction - enter");
		try {
			// recuperation des monstres a jouer
			$groupeMonstreTable = new GroupeMonstre();
			$groupes = $groupeMonstreTable->findGroupesAJouer(true, $this->config->game->monstre->nombre_groupe_a_jouer, $type);
			$this->traiteGroupes($groupes, true);
			$groupes = $groupeMonstreTable->findGroupesAJouer(false, $this->config->game->monstre->nombre_groupe_a_jouer, $type);
			$this->traiteGroupes($groupes, false);
		} catch (Exception $e) {
			Bral_Util_Log::erreur()->err(get_class($this)." - vieGroupesAction - Erreur:".$e->getTraceAsString());
			throw new Zend_Exception($e);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupesAction - exit");
	}

	private function traiteGroupes($groupes, $aleatoire1D2) {
		foreach($groupes as $g) {
			if ($aleatoire1D2 == false || ($aleatoire1D2 == true && Bral_Util_De::get_1d2() == 1)) {
				$this->vieGroupeAction($g);
			}
			$this->updateGroupe($g);
		}
	}

	private function vieGroupeAction(&$groupe) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupeAction - enter (id=".$groupe["id_groupe_monstre"].")");
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByGroupeId($groupe["id_groupe_monstre"]);

		if (count($monstres) == 0) {
			$this->suppressionGroupe($groupe);
			return;
		}

		Bral_Util_Log::viemonstres()->debug(get_class($this)." - nb monstres dans le groupe (".$groupe["id_groupe_monstre"].") = ".count($monstres));

		$monstre_role_a = $this->majRoleA($groupe, $monstres);
		$suitePrereperage = $this->calculPreReperageGroupe($monstre_role_a, $groupe, $monstres);

		$cible = null;
		if ($suitePrereperage == Bral_Monstres_Competences_Prereperage::SUITE_DISPARITION) {
			$this->suppressionGroupe($groupe);
		} elseif ($suitePrereperage == Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD) {
			$cible = $this->calculReperageGroupe($monstre_role_a, $groupe, $monstres);
		} elseif ($suitePrereperage == Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_CASE) {
			$cible = $this->calculReperageGroupe($monstre_role_a, $groupe, $monstres, true);
		}

		if ($cible != null) { // si une cible est trouvee, on attaque
			$this->attaqueGroupe($monstre_role_a, $groupe, $monstres, $cible);
		} elseif ($suitePrereperage != Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_CASE) {
			$this->deplacementGroupe($monstre_role_a, $groupe, $monstres);
		}

		$this->majDlaGroupe($groupe, $monstres);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupeAction - exit");
	}

	private function calculPreReperageGroupe(&$monstre_role_a, &$groupe, &$monstres) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculPreReperageGroupe (idm:".$monstre_role_a["id_monstre"].") - enter");
		$enchainementReperage = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;

		$typeMonstreMCompetence = new TypeMonstreMCompetence();
		$competences = $typeMonstreMCompetence->findPreReperageByIdTypeGroupe($monstre_role_a["id_fk_type_monstre"]);
		$foo = null;
		if ($competences != null) {
			foreach($competences as $c) {
				$actionReperage = Bral_Monstres_Competences_Factory::getAction($c, $monstre_role_a, $foo, $this->view);
				$enchainementReperage = $actionReperage->action();
				if ($enchainementReperage == Bral_Monstres_Competences_Prereperage::SUITE_DEPLACEMENT) {
					$groupe["x_direction_groupe_monstre"] = $monstre_role_a["x_direction_monstre"];
					$groupe["y_direction_groupe_monstre"] = $monstre_role_a["y_direction_monstre"];
				}
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculPreReperageGroupe - (idm:".$monstre_role_a["id_monstre"].") - exit :".$enchainementReperage);
		return $enchainementReperage;
	}

	private function calculReperageGroupe(&$monstre_role_a, &$groupe, &$monstres, $reperageCase = false) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculReperageGroupe (idm:".$monstre_role_a["id_monstre"].") - enter");
		$cible = null;

		if ($reperageCase == false) {
			$typeMonstreMCompetence = new TypeMonstreMCompetence();
			// Choix de l'action dans mcompetences
			$competences = $typeMonstreMCompetence->findReperageByIdTypeGroupe($monstre_role_a["id_fk_type_monstre"]);
			$foo = null;
			if ($competences != null) {
				foreach($competences as $c) {
					$actionReperage = Bral_Monstres_Competences_Factory::getAction($c, $monstre_role_a, $foo, $this->view);
					$cible = $actionReperage->action();
				}
			}
		} else {
			$actionReperage = Bral_Monstres_Competences_Factory::getActionReperageCase($monstre_role_a, $foo, $this->view);
			$cible = $actionReperage->action();
		}

		if ($cible != null) {
			$groupe["id_fk_braldun_cible_groupe_monstre"] = $cible["id_braldun"];
			$groupe["x_direction_groupe_monstre"] = $cible["x_braldun"];
			$groupe["y_direction_groupe_monstre"] = $cible["y_braldun"];
			$monstre_role_a["id_fk_braldun_cible_monstre"] = null;
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible trouvee:".$cible["id_braldun"]. " x=".$groupe["x_direction_groupe_monstre"]. " y=".$groupe["y_direction_groupe_monstre"]);
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible non trouvee: x=".$groupe["x_direction_groupe_monstre"]. " y=".$groupe["y_direction_groupe_monstre"]);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculReperageGroupe - (idm:".$monstre_role_a["id_monstre"].") - exit");
		return $cible;
	}

	/**
	 * Mise a jour du role A.
	 */
	private function majRoleA(&$groupe, &$monstres) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majRoleA - enter");
		// on regarde si le role_a est toujours vivant
		$id_role_a = $groupe["id_role_a_groupe_monstre"];
		$vivant = false;
		foreach($monstres as $m) {
			if ($m["id_monstre"] == $id_role_a) {
				$vivant = true;
				$monstre_role_a = $m;
				break;
			}
		}
		// si le role_a est mort, il faut le recreer
		if ($vivant === false) {
			$idx = Bral_Util_De::get_de_specifique(0, count($monstres)-1);
			$id_role_a = $monstres[$idx]["id_monstre"];
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - Nouveau role A =".$id_role_a."");
			$groupe["id_role_a_groupe_monstre"] = $id_role_a;
			$monstre_role_a = $monstres[$idx];
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majRoleA - exit");
		return $monstre_role_a;
	}

	/**
	 * Recherche d'une nouvelle cible.
	 *
	 * @param monstre $monstre_role_a
	 * @param groupeMonstre $groupe
	 * @return braldun : nouvelle cible ou null si non trouvee
	 */
	protected function rechercheNouvelleCible(&$monstre_role_a, &$groupe, &$monstres) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - exit");
		$braldunTable = new Braldun();

		foreach($monstres as $monstre) {
			$cibles = $braldunTable->findLesPlusProches($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $monstre["vue_monstre"], 1, $monstre["id_type_monstre"], false);
			if ($cibles != null) {
				$cible = $cibles[0];
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - nouvelle cible trouvee:".$cible["id_braldun"]);
				$groupe["id_fk_braldun_cible_groupe_monstre"] = $cible["id_braldun"];
				$groupe["x_direction_groupe_monstre"] = $cible["x_braldun"];
				$groupe["y_direction_groupe_monstre"] = $cible["y_braldun"];
				$monstre_role_a = $monstre;
				$groupe["id_role_a_groupe_monstre"] = $monstre["id_monstre"];
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - nouveau role A defini:".$monstre["id_monstre"]);
				break;
			} else {
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - aucune cible trouvee x=".$monstre["x_monstre"]." y=".$monstre["y_monstre"]." vue=".$monstre_role_a["vue_monstre"]);
				$cible = null;
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - exit");
		return $cible;
	}

	/**
	 * mise a jour de la DLA du groupe, suivant la dla la plus lointaine d'un
	 * membre du groupe.
	 */
	private function majDlaGroupe(&$groupe, &$monstres) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDlaGroupe - enter");
		foreach($monstres as $m) {
			if ($groupe["date_fin_tour_groupe_monstre"] < $m["date_fin_tour_monstre"]) {
				$groupe["date_fin_tour_groupe_monstre"] = $m["date_fin_tour_monstre"];
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - maj :".$m["date_fin_tour_monstre"]);
			}
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDlaGroupe - exit");
	}

	/**
	 * Mise à jour du groupe en base.
	 */
	private function updateGroupe(&$groupe) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateGroupe - enter");
		$groupeMonstreTable = new GroupeMonstre();
		$data = array(
            "id_fk_braldun_cible_groupe_monstre" => $groupe["id_fk_braldun_cible_groupe_monstre"],
            "id_role_a_groupe_monstre" => $groupe["id_role_a_groupe_monstre"],
            "x_direction_groupe_monstre" => $groupe["x_direction_groupe_monstre"],
            "y_direction_groupe_monstre" => $groupe["y_direction_groupe_monstre"],
            "date_fin_tour_groupe_monstre" => $groupe["date_fin_tour_groupe_monstre"],
        	"date_a_jouer_groupe_monstre" => null,
        	"phase_tactique_groupe_monstre" => $groupe["phase_tactique_groupe_monstre"],
        	"date_phase_tactique_groupe_monstre" => $groupe["date_phase_tactique_groupe_monstre"],
		);
		$where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
		$groupeMonstreTable->update($data, $where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateGroupe - exit");
	}

	/**
	 * Suppression du groupe de la base.
	 */
	private function suppressionGroupe(&$groupe) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - suppressionGroupe - enter (id_groupe=".$groupe["id_groupe_monstre"].")");
		$monstreTable = new Monstre();
		$monstreTable->delete("id_fk_groupe_monstre = ". $groupe["id_groupe_monstre"]);
		$groupeMonstreTable = new GroupeMonstre();
		$where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
		$groupeMonstreTable->delete($where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - suppressionGroupe - exit");
	}
}