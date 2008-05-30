<?php

class Bral_Competences_Sonder extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass("Ville");
		
		$this->view->sonderOK = false;
		
		// Position précise avec (Vue+BM) de vue *2
		$this->view->rayon_precis =  (Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit ) * 2;
		
		// On regarde si le hobbit n'est pas dans une ville
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($villeTable);
		
		if (count($villes) == 0) {
			$this->view->sonderOK = true;
		}
	
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
	
		// Verification Sonder
		if ($this->view->sonderOK == false) {
			throw new Zend_Exception(get_class($this)." Sonder interdit ");
		}
		
		$go = $this->request->get("valeur_1");

		// La distance max de repérage d'un filon est : jet VIG+BM
		$tirageRayonMax = 0;
		for ($i=1; $i<= ($this->view->config->game->base_vigueur + $this->view->user->vigueur_base_hobbit) ; $i++) {
			$tirageRayonMax = $tirageRayonMax + Bral_Util_De::get_1d6();
		}
		$this->view->rayon_max = $tirageRayonMax + $this->view->user->vigueur_bm_hobbit + $this->view->user->vigueur_bbdf_hobbit;
		
		
		if ($go != "go") {
			throw new Zend_Exception(get_class($this)." Sonder un filon. Action invalide");
		}

		$this->calculJets();

		if ($this->view->okJet1 === true) {
			Zend_Loader::loadClass('Filon');
			$filonTable = new Filon();
			$filonRow = $filonTable->findLePlusProche($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->rayon_max);
			unset($filonTable);
			
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