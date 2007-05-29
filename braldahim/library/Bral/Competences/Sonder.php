<?php

class Bral_Competences_Sonder extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->rayon = $this->view->config->game->competence->sonder->rayon;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		$go = $this->request->get("valeur_1");

		if ($go != "go") {
			throw new Zend_Exception(get_class($this)." Sonder un filon. Action invalide");
		}
		
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			Zend_Loader::loadClass('Filon');
			$filonTable = new Filon();
			$filon = $filonTable->findLePlusProche($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->rayon);

			if (!empty($filon)) {
				$f = array('type_minerai' => $filon["nom_type_minerai"],
				'x_filon' => $filon["x_filon"],
				'y_filon' => $filon["y_filon"]);

				$this->view->filon = $f;
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