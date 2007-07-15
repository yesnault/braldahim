<?php

class Bral_Monstres_VieGroupe {
	function __construct($view) {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass("Bral_Util_De");
		Zend_Loader::loadClass("Bral_Util_Log");
		$this->view = $view;
	}

	function vieGroupesAction() {
		Bral_Util_Log::tech()->debug(get_class($this)." - vieGroupesAction - enter");
		// recuperation des monstres a jouer
		$groupeMonstreTable = new GroupeMonstre();
		$groupes = $groupeMonstreTable->findGroupesAJouer($this->view->config->game->monstre->nombre_groupe_a_jouer);
		foreach($groupes as $g) {
			$this->vieGroupeAction($g);
			$this->updateGroupe($g);
		}
		Bral_Util_Log::tech()->debug(get_class($this)." - vieGroupesAction - exit");
	}

	private function vieGroupeAction(&$groupe) {
		Bral_Util_Log::tech()->debug(get_class($this)." - vieGroupeAction - enter (id=".$groupe["id_groupe_monstre"].")");
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByGroupeId($groupe["id_groupe_monstre"]);

		if (count($monstres) == 0) {
			$this->suppressionGroupe($groupe);
			return;
		}

		Bral_Util_Log::tech()->debug(get_class($this)." - nb monstres dans le groupe = ".count($monstres));

		$monstre_role_a = $this->majRoleA($groupe, $monstres);

		// on regarde s'il y a une cible en cours
		if ($groupe["id_cible_groupe_monstre"] != null) {
			Bral_Util_Log::tech()->debug(get_class($this)." - cible en cours");
			$hobbitTable = new Hobbit();
			$cible = $hobbitTable->findHobbitAvecRayon($monstre_role_a["x_monstre"], $monstre_role_a["y_monstre"], $monstre_role_a["vue_monstre"], $groupe["id_cible_groupe_monstre"]);
			if (count($cible) > 0) {
				$cible = $cible[0];
			}
			// si la cible n'est pas dans la vue, on se déplace
			if ($cible == null) {
				Bral_Util_Log::tech()->debug(get_class($this)." - cible hors de vue");
				$groupe["id_cible_groupe_monstre"] = null;
				$this->deplacementGroupe($monstre_role_a, $groupe, $monstres);
			} else { // si la cible est dans la vue, on attaque
				$this->attaqueGroupe($monstre_role_a, $groupe, $monstres, $cible);
			}
		} else { // pas de cible en cours
			Bral_Util_Log::tech()->debug(get_class($this)." - pas de cible en cours");
			$cible = $this->rechercheNouvelleCible($monstre_role_a, $groupe);
			if ($cible != null) { // si une cible est trouvée, on attaque
				$this->attaqueGroupe($monstre_role_a, $groupe, $monstres, $cible);
			} else {
				$this->deplacementGroupe($monstre_role_a, $groupe, $monstres);
			}
		}
		$this->majDlaGroupe($groupe, $monstres);
		Bral_Util_Log::tech()->debug(get_class($this)." - vieGroupeAction - exit");
	}

	private function majRoleA(&$groupe, &$monstres) {
		Bral_Util_Log::tech()->debug(get_class($this)." - majRoleA - enter");
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
		// si le role_a est mort, il faut le recréer
		if ($vivant === false) {
			$idx = Bral_Util_De::get_de_specifique(0, count($monstres)-1);
			$id_role_a = $monstres[$idx]["id_monstre"];
			Bral_Util_Log::tech()->debug(get_class($this)." - Nouveau role A =".$id_role_a."");
			$groupe["id_role_a_groupe_monstre"] = $id_role_a;
			$monstre_role_a = $monstres[$idx];
		}
		Bral_Util_Log::tech()->debug(get_class($this)." - majRoleA - exit");
		return $monstre_role_a;
	}

	private function attaqueGroupe(&$monstre_role_a, &$groupe, &$monstres, &$cible) {
		Bral_Util_Log::tech()->debug(get_class($this)." - attaqueGroupe - enter");
		$mort_cible = false;

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();

		foreach($monstres as $m) {
			if ($cible != null) {
				$vieMonstre->setMonstre($m);
				$mortCible = $vieMonstre->attaqueCible($cible);
				if ($mortCible == null) { // null => cible hors vue
					$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
				} else if ($mortCible === true) {
					$groupe["id_cible_groupe_monstre"] = null;
					$cible = $this->rechercheNouvelleCible($monstre_role_a, $groupe);
				}
			} else {
				$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
			}
		}
		Bral_Util_Log::tech()->debug(get_class($this)." - attaqueGroupe - exit");
	}

	private function deplacementGroupe(&$monstre_role_a, &$groupe, &$monstres) {
		Bral_Util_Log::tech()->debug(get_class($this)." - deplacementGroupe - enter");
		// si le role_a est sur la direction, on déplacement le groupe
		if (($monstre_role_a["x_monstre"] == $groupe["x_direction_groupe_monstre"]) && //
		($monstre_role_a["y_monstre"] == $groupe["y_direction_groupe_monstre"])) {
			$groupe["x_direction_groupe_monstre"] = $groupe["x_direction_groupe_monstre"] + Bral_Util_De::get_1d20();
			$groupe["y_direction_groupe_monstre"] = $groupe["y_direction_groupe_monstre"] + Bral_Util_De::get_1d20();
			Bral_Util_Log::tech()->debug(get_class($this)." - calcul nouvelle valeur direction x=".$groupe["x_direction_groupe_monstre"]." y=".$groupe["y_direction_groupe_monstre"]." ");
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		foreach($monstres as $m) {
			$vieMonstre->setMonstre($m);
			$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
		}
		Bral_Util_Log::tech()->debug(get_class($this)." - deplacementGroupe - exit");
	}

	private function rechercheNouvelleCible(&$monstre_role_a, &$groupe) {
		Bral_Util_Log::tech()->debug(get_class($this)." - rechercheNouvelleCible - exit");
		$hobbitTable = new Hobbit();
		$cibles = $hobbitTable->findLesPlusProches($monstre_role_a["x_monstre"], $monstre_role_a["y_monstre"], $monstre_role_a["vue_monstre"], 1);

		if ($cibles != null) {
			$cible = $cibles[0];
			Bral_Util_Log::tech()->debug(get_class($this)." - nouvelle cible trouvee:".$cible["id_hobbit"]."");
			$groupe["id_cible_groupe_monstre"] = $cible["id_hobbit"];
			$groupe["x_direction_groupe_monstre"] = $cible["x_hobbit"];
			$groupe["y_direction_groupe_monstre"] = $cible["y_hobbit"];
		} else {
			Bral_Util_Log::tech()->debug(get_class($this)." - aucune cible trouvee");
			$cible = null;
		}
		
		Bral_Util_Log::tech()->debug(get_class($this)." - rechercheNouvelleCible - exit");
		return $cible;
	}

	/**
	 * mise a jour de la DLA du groupe, suivant la dla la plus lointaine d'un
	 * membre du groupe
	 */
	private function majDlaGroupe(&$groupe, &$monstres) {
		Bral_Util_Log::tech()->debug(get_class($this)." - majDlaGroupe - enter");
		foreach($monstres as $m) {
			if ($groupe["date_fin_tour_groupe_monstre"] < $m["date_fin_tour_monstre"]) {
				$groupe["date_fin_tour_groupe_monstre"] = $m["date_fin_tour_monstre"];
				Bral_Util_Log::tech()->debug(get_class($this)." - maj :".$m["date_fin_tour_monstre"]);
			}
		}
		Bral_Util_Log::tech()->debug(get_class($this)." - majDlaGroupe - exit");
	}

	/**
	 * Mise à jour du groupe en base.
	 */
	private function updateGroupe(&$groupe) {
		Bral_Util_Log::tech()->debug(get_class($this)." - updateGroupe - enter");
		$groupeMonstreTable = new GroupeMonstre();
		$data = array(
		"id_cible_groupe_monstre" => $groupe["id_cible_groupe_monstre"],
		"id_role_a_groupe_monstre" => $groupe["id_role_a_groupe_monstre"],
		"x_direction_groupe_monstre" => $groupe["x_direction_groupe_monstre"],
		"y_direction_groupe_monstre" => $groupe["y_direction_groupe_monstre"],
		"date_fin_tour_groupe_monstre" => $groupe["date_fin_tour_groupe_monstre"],
		);
		$where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
		$groupeMonstreTable->update($data, $where);
		Bral_Util_Log::tech()->debug(get_class($this)." - updateGroupe - exit");
	}

	/**
	 * Suppression du groupe de la base.
	 */
	private function suppressionGroupe(&$groupe) {
		Bral_Util_Log::tech()->debug(get_class($this)." - suppressionGroupe - enter (id_groupe=".$groupe["id_groupe_monstre"].")");
		$groupeMonstreTable = new GroupeMonstre();
		$where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
		$groupeMonstreTable->delete($where);
		Bral_Util_Log::tech()->debug(get_class($this)." - suppressionGroupe - exit");
	}
}