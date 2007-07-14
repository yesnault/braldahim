<?php

class Bral_Monstres_VieGroupe {
	function __construct($view) {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		$this->view = $view;
	}

	function vieGroupesAction() {
		// recuperation des monstres a jouer
		$groupeMonstreTable = new GroupeMonstre();
		$groupes = $groupeMonstreTable->findGroupesAJouer($this->view->config->game->monstre->nombre_groupe_a_jouer);
		foreach($groupes as $g) {
			$this->vieGroupeAction($g);
		}
	}

	private function vieGroupeAction($groupe) {
		echo "Groupe ".$groupe["id_groupe_monstre"]. "\n";
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByGroupeId($groupe["id_groupe_monstre"]);

		if (count($monstres) == 0) {
			$this->suppressionGroupe($groupe["id_groupe_monstre"]);
			return;
		}

		$monstre_role_a = $this->majRoleA($groupe, $monstres);

		// on regarde s'il y a une cible en cours
		if ($groupe["id_cible_groupe_monstre"] != null) {
			$hobbitTable = new Hobbit();
			$cible = $hobbitTable->findHobbitAvecRayon($monstre_role_a["x_monstre"], $monstre_role_a["y_monstre"], $monstre_role_a["vue_monstre"], $groupe["id_cible_groupe_monstre"]);
			// si la cible n'est pas dans la vue, on se déplace
			if ($cible == null) {
				$this->deplacementGroupe($groupe, $monstres);
			} else { // si la cible est dans la vue, on attaque
				$this->attaqueGroupe($groupe, $monstres, $cible);
			}
		} else { // pas de cible en cours
			$cible = $this->rechercheNouvelleCible($groupe);
			if ($cible != null) { // si une cible est trouvée, on attaque
				$this->attaqueGroupe($groupe, $monstres, $cible);
			} else {
				$this->deplacementGroupe($groupe, $monstres);
			}
		}
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
			$data = array("id_role_a_groupe_monstre" => $id_role_a);
			$groupeMonstreTable = new GroupeMonstre();
			$where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
			$groupeMonstreTable->update($data, $where);
			$monstre_role_a = $monstres[$idx];
		}
		return $monstre_role_a;
	}

	private function attaqueGroupe($groupe, $monstres, $cible) {
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

		$this->majDlaGroupe($groupe, $monstres);
	}

	private function deplacementGroupe($groupe, $monstres) {
		// TODO : si le role_a est sur la direction, Maj de 
		// $groupe["x_direction_groupe_monstre"],
		// $groupe["y_direction_groupe_monstre"]
		// update du groupe
		
		foreach($monstres as $m) {
			if ($m["pa_monstre"] < 1) {
				continue;
			}
			$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
			$vieMonstre->setMonstre($m);
			$vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
		}
		$this->majDlaGroupe($groupe, $monstres);
	}

	private function rechercheNouvelleCible($groupe) {
		$cibleTrouvee = false;
		// TODO
		//$groupe["id_cible_monstre"]
		return $cibleTrouvee;
	}

	/**
	 * mise a jour de la DLA du groupe, suivant la dla la plus lointaine d'un
	 * membre du groupe
	 */
	private function majDlaGroupe($groupe, $monstres) {
		// TODO
	}
	private function suppressionGroupe($idGroupe) {
		echo "Suppression du groupe ".$idGroupe;
		$groupeMonstreTable = new GroupeMonstre();
		$where = "id_groupe_monstre=".$idGroupe;
		$groupeMonstreTable->delete($where);
	}
}