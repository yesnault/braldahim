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

		$this->majRoleA($groupe, $monstres);

		// on regarde s'il y a une cible en cours
		if ($groupe["id_cible_groupe_monstre"] != null) {
			// on regarde si la cible est dans la vue du role_a
			// si la cible est dans la vue, on attaque
			//TODO
			// si la cible n'est pas dans la vue, on se déplace
			// TODO
			$this->deplacementGroupe();
		} else {
			$this->deplacementGroupe();
		}

		// TODO
		//print_r($groupe);
	}

	private function majRoleA($groupe, $monstres) {
		// on regarde si le role_a est toujours vivant
		$id_role_a = $groupe["id_role_a_groupe_monstre"];
		$vivant = false;
		foreach($monstres as $m) {
			if ($m["id_monstre"] == $id_role_a) {
				$vivant = true;
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
		}
	}

	private function deplacementGroupe() {

	}

	private function suppressionGroupe($idGroupe) {
		echo "Suppression du groupe ".$idGroupe;
		$groupeMonstreTable = new GroupeMonstre();
		$where = "id_groupe_monstre=".$idGroupe;
		$groupeMonstreTable->delete($where);
	}
}