<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Box_Competences extends Bral_Box_Box {

	function __construct($request, $view, $interne, $type) {
		$this->_request = $request;
		$this->view = $view;
		$this->type = $type;
		$this->view->affichageInterne = $interne;

		// chargement des competences
		switch($this->type) {
			case "basic":
				$this->chargementInBoxes = false;
				$this->titreOnglet = "Basiques";
				$this->nomInterne = "box_competences_basiques";
				$this->render = "interface/competences_basiques.phtml";
				break;
			case "commun":
				$this->chargementInBoxes = true;
				$this->titreOnglet = "Communes";
				$this->nomInterne = "box_competences_communes";
				$this->render = "interface/competences_communes.phtml";
				break;
			case "metier":
				$this->chargementInBoxes = false;
				$this->titreOnglet = "M&eacute;tiers";
				$this->nomInterne = "box_competences_metiers";
				$this->render = "interface/competences_metiers.phtml";
				break;
			default:
				throw new Zend_Exception(get_class($this)." type inconnu=" + $this->type);
		}
	}

	function getTitreOnglet() {
		return $this->titreOnglet;
	}

	function getChargementInBoxes() {
		return $this->chargementInBoxes;
	}
	
	function getNomInterne() {
		return $this->nomInterne;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne || $this->chargementInBoxes === true) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render($this->render);
	}
	
	function data() {
		$tabCompetences = null;
		$this->view->nom_interne = $this->getNomInterne();

		if ($this->type == 'basic') {
			$tabCompetences = Zend_Registry::get('competencesBasiques');
		} else if ($this->type == 'metier') {
			Zend_Loader::loadClass("HobbitsCompetences");
			Zend_Loader::loadClass("HobbitsMetiers");
			$hobbitsMetiersTable = new HobbitsMetiers();
			$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
			$hobbitsCompetencesTables = new HobbitsCompetences();
			$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);
			
			foreach($hobbitsMetierRowset as $m) {
				if ($this->view->user->sexe_hobbit == 'feminin') {
					$nom_metier = $m["nom_feminin_metier"];
				} else {
					$nom_metier = $m["nom_masculin_metier"];
				}
				$competence = null;
				foreach($hobbitCompetences as $c) {
					if ($c["type_competence"] == $this->type && $m["id_metier"] == $c["id_fk_metier_competence"]) {
						$competence[] = array("id_competence" => $c["id_fk_competence_hcomp"],
							"nom" => $c["nom_competence"],
							"pa_utilisation" => $c["pa_utilisation_competence"],
							"pourcentage" => $c["pourcentage_hcomp"],
							"nom_systeme" => $c["nom_systeme_competence"],
							"pourcentage_init" => $c["pourcentage_init_competence"],
						);
					}
				}
				
				$tabCompetences[] = array("id_metier" => $m["id_metier"],
					"nom_metier" => $nom_metier,
					"nom_systeme_metier" => $m["nom_systeme_metier"],
					"competences" => $competence
				);
			}
			
		} else {
			Zend_Loader::loadClass("HobbitsCompetences");
			$hobbitsCompetencesTables = new HobbitsCompetences();
			$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);

			foreach($hobbitCompetences as $c) {
				if ($c["type_competence"] == $this->type ) {
					if ($c["nom_systeme_competence"] == "marcher") {
						$pa = "1 ou 2";
					} else {
						$pa =  $c["pa_utilisation_competence"];
					}
						
					$tabCompetences[] = array(
						"id_competence" => $c["id_fk_competence_hcomp"],
						"nom" => $c["nom_competence"],
						"pa_utilisation" => $pa,
						"pourcentage" => $c["pourcentage_hcomp"],
						"nom_systeme" => $c["nom_systeme_competence"],
						"pourcentage_init" => $c["pourcentage_init_competence"],
					);
				}
			}
		}
		$this->view->competences = $tabCompetences;
	}
}
