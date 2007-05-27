<?php

class Bral_Box_Competences {

	function __construct($request, $view, $interne, $type) {
		$this->_request = $request;
		$this->view = $view;
		$this->type = $type;
		$this->view->affichageInterne = $interne;

		// chargement des competences
		switch($this->type) {
			case "basic":
				$this->titreOnglet = "Basiques";
				$this->nomInterne = "box_competences_basiques";
				$this->render = "interface/competences_basiques.phtml";
				break;
			case "commun":
				$this->titreOnglet = "Communes";
				$this->nomInterne = "box_competences_communes";
				$this->render = "interface/competences_communes.phtml";
				break;
			case "metier":
				$this->titreOnglet = "M&eacute;tiers";
				$this->nomInterne = "box_competences_metiers";
				$this->render = "interface/competences_metiers.phtml";
				break;
		}
	}

	function getTitreOnglet() {
		return $this->titreOnglet;
	}

	function getNomInterne() {
		return $this->nomInterne;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {

		$tabCompetences = null;
		$this->view->nom_interne = $this->getNomInterne();

		if ($this->type == 'basic') {
			$tabCompetences = Zend_Registry::get('competencesBasiques');
		} else {
			Zend_Loader::loadClass("HobbitsCompetences");
			$hobbitsCompetencesTables = new HobbitsCompetences();
			$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);

			foreach($hobbitCompetences as $c) {
				if ($c["type_competence"] == $this->type ) {
					$tabCompetences[] = array("id_competence" => $c["id_competence_hcomp"],
					"nom" => $c["nom_competence"],
					"pa_utilisation" => $c["pa_utilisation_competence"],
					"pourcentage" => $c["pourcentage_hcomp"],
					"nom_systeme" => $c["nom_systeme_competence"]);
				}
			}
		}

		$this->view->competences = $tabCompetences;
		return $this->view->render($this->render);
	}

}
