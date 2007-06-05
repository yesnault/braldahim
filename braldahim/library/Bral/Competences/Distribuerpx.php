<?php

class Bral_Competences_Distribuerpx extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		$commun = new Bral_Util_Commun();

		// récupération des hobbits qui sont présents dans la vue
		$hobbitTable = new Hobbit();
		// s'il y a trop de hobbits, on prend que les plus proches
		$this->view->estMaxHobbits = false;

		$commun = new Bral_Util_Commun();
		$vue = $commun->getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		$hobbits = $hobbitTable->findLesPlusProches($this->view->user->x_hobbit, $this->view->user->y_hobbit, $vue, $this->view->config->game->competence->distribuerpx->nb_max_hobbit);

		foreach($hobbits as $h) {
			$nom = $h["nom_hobbit"];
			if ($h["id_hobbit"] == $this->view->user->id_hobbit) {
				$nom = " Vous-Même : ".$h["nom_hobbit"];
			}
			$tabHobbits[] = array("id_hobbit" => $h["id_hobbit"], "nom_hobbit" => $nom);
		}

		if (count($tabHobbits) >= $this->view->config->game->competence->distribuerpx->nb_max_hobbit) {
			$this->view->estMaxHobbits = true;
		}

		$this->view->tabHobbits = $tabHobbits;
		$this->view->n_hobbits = count($tabHobbits);
	}

	function prepareFormulaire() {
		// rien a faire ici
	}

	function prepareResultat() {
		// todo
	}

	function getListBoxRefresh() {
		return array("box_profil");
	}

}