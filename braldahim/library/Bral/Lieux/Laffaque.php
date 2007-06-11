<?php

class Bral_Lieux_Laffaque extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabCompetences = null;

	function prepareCommun() {
		Zend_Loader::loadClass("HobbitsCompetences");

		$this->_coutCastars = $this->calculCoutCastars();

		$competenceTable = new Competence();
		$comptenceRowset = $competenceTable->findCommunesByNiveau($this->view->user->niveau_hobbit);

		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);
			
		$achatPiPossible = false;

		foreach ($comptenceRowset as $c) {

			$possible = true;
			foreach ($hobbitCompetences as $h) {
				if ($h["id_competence"] == $c->id_competence) {
					$possible = false;
					break;
				}
			}

			if ($possible === true) {
				$tropCher = true;
				if ($c->pi_cout_competence <= $this->view->user->pi_hobbit) {
					$tropCher = false;
					$achatPiPossible = true;
				}
					
				$tab = array(
				"id_competence" => $c->id_competence,
				"nom" => $c->nom_competence,
				"nom_systeme" => $c->nom_systeme_competence,
				"description" => $c->description_competence,
				"niveau_requis" => $c->niveau_requis_competence,
				"pi_cout" => $c->pi_cout_competence,
				"trop_cher" => $tropCher,
				);
				$this->_tabCompetences[] = $tab;
			}
		}
		$this->view->achatPiPossible = $achatPiPossible;
		$this->view->tabCompetences = $this->_tabCompetences;
		$this->view->coutCastars = $this->_coutCastars;
		$this->view->achatPossibleCastars = "TODO";
		// $this->view->utilisationPaPossible initialisé dans Bral_Lieux_Lieu
	}

	function prepareFormulaire() {
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {

		if ($this->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PA:".$this->view->user->pa_hobbit);
		}

		if ($this->achatPiPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PI:".$this->view->user->pi_hobbit);
		}

		// verification qu'il y a assez de castars
		if ($this->achatPossibleCastars == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
		}


		// TODO
		$this->view->coutPi = "TODO";
		$this->view->nomCompetence = "TODO";
	}


	function getListBoxRefresh() {
		return array("box_profil", "box_metier", "box_laban", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_vue", "box_lieu");
	}

	private function calculCoutCastars() {
		return 50;
	}
}