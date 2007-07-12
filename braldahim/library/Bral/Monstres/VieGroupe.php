<?php

class Bral_Monstres_VieGroupe {
	function __construct($view) {
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
		} else {
			$this->deplacementGroupe($groupe, $monstres);
		}

		//print_r($groupe);
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
	// boucle sur les monstres
		// si le monstre n'a pas de PA, prochain monstre
		
		// si la cible est morte (mort_cible == true), recherche d'une nouvelle cible
		

		// si le monstre n''est pas sur la cible, deplacement vers la cible
		// Bral_Monstres_VieMonstre::deplacementMonstre
		
		// si le monstre est sur la cible, attaque de la cible
		// mort_cible = Bral_Monstres_VieMonstre::attaqueCible
		
	// fin boucle
		$this->majDlaGroupe($groupe, $monstres);
	}
	
	private function deplacementGroupe($groupe, $monstres) {
	// boucle sur les monstres
		// si le monstre n'a pas de PA, prochain monstre
	// fin boucle
		$this->majDlaGroupe($groupe, $monstres);
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