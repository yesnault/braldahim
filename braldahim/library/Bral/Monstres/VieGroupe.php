<?php

class Bral_Monstres_VieGroupe {
	function __construct($view) {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass("Bral_Util_De");

		$this->view = $view;
	}

	function vieGroupesAction() {
		// recuperation des monstres a jouer
		$groupeMonstreTable = new GroupeMonstre();
		$groupes = $groupeMonstreTable->findGroupesAJouer($this->view->config->game->monstre->nombre_groupe_a_jouer);
		foreach($groupes as $g) {
			$this->vieGroupeAction($g);
			$this->updateGroupe($g);
		}
	}

	private function vieGroupeAction($groupe) {
		echo "Groupe ".$groupe["id_groupe_monstre"]. "\n";
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByGroupeId($groupe["id_groupe_monstre"]);

		if (count($monstres) == 0) {
			$this->suppressionGroupe($groupe);
			return;
		}

		$monstre_role_a = $this->majRoleA($groupe, $monstres);

		// on regarde s'il y a une cible en cours
		if ($groupe["id_cible_groupe_monstre"] != null) {
			$hobbitTable = new Hobbit();
			$cible = $hobbitTable->findHobbitAvecRayon($monstre_role_a["x_monstre"], $monstre_role_a["y_monstre"], $monstre_role_a["vue_monstre"], $groupe["id_cible_groupe_monstre"]);
			// si la cible n'est pas dans la vue, on se déplace
			if ($cible == null) {
				$this->deplacementGroupe($monstre_role_a, $groupe, $monstres);
			} else { // si la cible est dans la vue, on attaque
				$this->attaqueGroupe($monstre_role_a, $groupe, $monstres, $cible);
			}
		} else { // pas de cible en cours
			$cible = $this->rechercheNouvelleCible($monstre_role_a, $groupe);
			if ($cible != null) { // si une cible est trouvée, on attaque
				$this->attaqueGroupe($monstre_role_a, $groupe, $monstres, $cible);
			} else {
				$this->deplacementGroupe($monstre_role_a, $groupe, $monstres);
			}
		}
		$this->majDlaGroupe($groupe, $monstres);
	}

	private function majRoleA($groupe, $monstres) {
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
			echo "Nouveau role A =".$id_role_a."\n";
			$groupe["id_role_a_groupe_monstre"] = $id_role_a;
			$monstre_role_a = $monstres[$idx];
		}
		return $monstre_role_a;
	}

	private function attaqueGroupe($monstre_role_a, $groupe, $monstres, $cible) {
		$mort_cible = false;

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();

		foreach($monstres as $m) {
			// si le monstre n'a pas assez de PA, prochain monstre
			if ($m["pa_restants_monstre"] < 4) {
				continue;
			}

			if ($cible != null) {
				$vieMonstre->setMonstre($m);
				$mortCible = $vieMonstre->attaqueCible($cible);
				if ($mortCible == null) { // null => cible hors vue
					$vieMonstre->deplacementMonstre();
				} else if ($mortCible === true) {
					$cible == $this->rechercheNouvelleCible();
				}
			} else {
				$vieMonstre->deplacementMonstre();
			}
		}
	}

	private function deplacementGroupe($monstre_role_a, $groupe, $monstres) {

		// si le role_a est sur la direction, on déplacement le groupe
		if (($monstre_role_a["x_monstre"] == $groupe["x_direction_groupe_monstre"]) && //
		($monstre_role_a["y_monstre"] == $groupe["y_direction_groupe_monstre"])) {
			$groupe["y_direction_groupe_monstre"] = $groupe["y_direction_groupe_monstre"] + Bral_Util_De::get_1d20();
			$groupe["y_direction_groupe_monstre"] = $groupe["y_direction_groupe_monstre"] + Bral_Util_De::get_1d20();
			$this->updateGroupe($groupe);
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		foreach($monstres as $m) {
			if ($m["pa_monstre"] < 1) {
				continue;
			}
				
			$vieMonstre->setMonstre($m);
			$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
		}
	}

	private function rechercheNouvelleCible($monstre_role_a, $groupe) {
		$hobbitTable = new Hobbit();
		$cible = $hobbitTable->findLesPlusProches($monstre_role_a["x_monstre"], $monstre_role_a["y_monstre"], $monstre_role_a["vue_monstre"], 1);
		if ($cible != null) {
			$groupe["id_cible_groupe_monstre"] = $cible["id_hobbit"];
		}
		return $cible;
	}

	/**
	 * mise a jour de la DLA du groupe, suivant la dla la plus lointaine d'un
	 * membre du groupe
	 */
	private function majDlaGroupe($groupe, $monstres) {
		foreach($monstres as $m) {
			if ($groupe["date_fin_tour_groupe_monstre"] > $m["date_fin_tour_monstre"]) {
				$groupe["date_fin_tour_groupe_monstre"] = $m["date_fin_tour_monstre"];
			}
		}
	}

	/**
	 * Mise à jour du groupe en base.
	 */
	private function updateGroupe($groupe) {
		$groupeMonstreTable = new GroupeMonstre();
		$data = array(
		"id_role_a_groupe_monstre" => $groupe["id_role_a_groupe_monstre"],
		"x_direction_groupe_monstre" => $groupe["x_direction_groupe_monstre"],
		"y_direction_groupe_monstre" => $groupe["y_direction_groupe_monstre"],
		"date_fin_tour_groupe_monstre" => $groupe["date_fin_tour_groupe_monstre"],
		);
		$where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
		$groupeMonstreTable->update($data, $where);
	}

	/**
	 * Suppression du groupe de la base.
	 */
	private function suppressionGroupe($groupe) {
		echo "Suppression du groupe ".$groupe["id_groupe_monstre"];
		$groupeMonstreTable = new GroupeMonstre();
		$where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
		$groupeMonstreTable->delete($where);
	}
}