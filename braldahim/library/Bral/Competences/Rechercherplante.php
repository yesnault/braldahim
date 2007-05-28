<?php

class Bral_Competences_Rechercherplante extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->rayon = $this->view->config->game->competence->rechercherplante->rayon;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		$go = $this->request->get("valeur_1");

		if ($go != "go") {
			throw new Zend_Exception(get_class($this)." Rechercher Plante. Action invalide");
		}
		
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			Zend_Loader::loadClass('Plante');
			$planteTable = new Plante();
			$plante = $planteTable->findLaPlusProche($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->rayon);

			if (!empty($plante)) {
				$plante = array('categorie' => $plante["categorie_type_plante"],'x_plante' => $plante["x_plante"], 'y_plante' => $plante["y_plante"]);

				$this->view->plante = $plante;
				$this->view->trouve = true;
			} else {
				$this->view->trouve= false;
			}
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers");
	}
}