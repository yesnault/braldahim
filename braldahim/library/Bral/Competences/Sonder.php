<?php

class Bral_Competences_Sonder extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		$this->view->rayon_max = $this->view->config->game->competence->sonder->rayon_max;
		$this->view->rayon_precis =  Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) * 2;
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
			$filonRow = $filonTable->findLePlusProche($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->rayon_max);

			if (!empty($filonRow)) {
				$f = array(
					'type_minerai' => $filonRow["nom_type_minerai"],
					'x_filon' => $filonRow["x_filon"],
					'y_filon' => $filonRow["y_filon"]
				);
				$this->view->trouve = true;
				$this->view->filon = $f;
				if ($filonRow["distance"] <= $this->view->rayon_precis) {
					$this->view->proche = true;
				} else {
					$this->view->proche = false;
				}

			} else {
				$this->view->trouve= false;
			}
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_evenements");
	}
}