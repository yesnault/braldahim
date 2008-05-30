<?php

class Bral_Competences_Rechercherplante extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		
		// Position précise avec (Vue+BM) de vue *2
		$this->view->rayon_precis =  (Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit ) * 2;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		$go = $this->request->get("valeur_1");

		// La distance max de repérage d'une plante est : jet SAG+BM
		$tirageRayonMax = 0;
		for ($i=1; $i <= ($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit) ; $i++) {
			$tirageRayonMax = $tirageRayonMax + Bral_Util_De::get_1d6();
		}
		$this->view->rayon_max = $tirageRayonMax + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;
		
		if ($go != "go") {
			throw new Zend_Exception(get_class($this)." Rechercher Plante. Action invalide");
		}

		$this->calculJets();

		if ($this->view->okJet1 === true) {
			Zend_Loader::loadClass('Plante');
			$planteTable = new Plante();
			$planteRow = $planteTable->findLaPlusProche($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->rayon_max);

			if (!empty($planteRow)) {
				$plante = array('categorie' => $planteRow["categorie_type_plante"],'x_plante' => $planteRow["x_plante"], 'y_plante' => $planteRow["y_plante"]);
				$this->view->trouve = true;
				$this->view->plante = $plante;
				if ($planteRow["distance"] <= $this->view->rayon_precis) {
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