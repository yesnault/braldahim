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
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByGroupeId($groupe["id_groupe_monstre"]);

		if (count($monstres) == 0) {
			$this->suppressionGroupe($groupe["id_groupe_monstre"]);
		}

		// TODO
		print_r($monstres);
	}

	private function suppressionGroupe($idGroupe) {
		echo "Suppression du groupe ".$idGroupe;
		$groupeMonstreTable = new GroupeMonstre();
		$where = "id_groupe_monstre=".$idGroupe;
		$groupeMonstreTable->delete($where);
	}
}