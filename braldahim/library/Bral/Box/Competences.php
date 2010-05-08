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
				$this->chargementInBoxes = true;
				$this->titreOnglet = "Basiques";
				$this->nomInterne = "box_competences_basiques";
				$this->render = "interface/competences_basiques.phtml";
				break;
			case "commun":
				$this->chargementInBoxes = false;
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
			case "soule":
				$this->chargementInBoxes = false;
				$this->titreOnglet = "Soule";
				$this->nomInterne = "box_competences_soule";
				$this->render = "interface/competences_soule.phtml";
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
			$tabCompetences = Bral_Util_Registre::get('competencesBasiques');
		} else if ($this->type == 'soule') {
			$tabCompetences = Bral_Util_Registre::get('competencesSoule');
		} else if ($this->type == 'metier') {
			Zend_Loader::loadClass("BraldunsCompetences");
			Zend_Loader::loadClass("BraldunsMetiers");
			$braldunsMetiersTable = new BraldunsMetiers();
			$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);
			$braldunsCompetencesTables = new BraldunsCompetences();
			$braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($this->view->user->id_braldun);
				
			foreach($braldunsMetierRowset as $m) {
				if ($this->view->user->sexe_braldun == 'feminin') {
					$nom_metier = $m["nom_feminin_metier"];
				} else {
					$nom_metier = $m["nom_masculin_metier"];
				}
				$competence = null;
				foreach($braldunCompetences as $c) {
					if ($c["type_competence"] == $this->type && $m["id_metier"] == $c["id_fk_metier_competence"]) {

						$pourcentage = Bral_Util_Commun::getPourcentage($c, $this->view->config);
							
						$pa_texte = $c["pa_utilisation_competence"];
						if ($c["nom_systeme_competence"] == "cuisiner") {
							$pa_texte = "2 ou 4";
						}
						 
						$pa = $c["pa_utilisation_competence"];

						$competence[] = array("id_competence" => $c["id_fk_competence_hcomp"],
							"nom" => $c["nom_competence"],
							"pa_utilisation" => $pa,
							"pa_texte" => $pa_texte,
							"pourcentage" => $pourcentage,
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
			Zend_Loader::loadClass("BraldunsCompetences");
			$braldunsCompetencesTables = new BraldunsCompetences();
			$braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($this->view->user->id_braldun);

			foreach($braldunCompetences as $c) {
				if ($c["type_competence"] == $this->type) {
					$pa_texte = $c["pa_utilisation_competence"];
					if ($c["nom_systeme_competence"] == "marcher") {
						$pa_texte = "1 ou 2";
					}
					$pa =  $c["pa_utilisation_competence"];
						
					$pourcentage = Bral_Util_Commun::getPourcentage($c, $this->view->config);
						
					$tabCompetences[] = array(
						"id_competence" => $c["id_fk_competence_hcomp"],
						"nom" => $c["nom_competence"],
						"pa_utilisation" => $pa,
						"pa_texte" => $pa_texte,
						"pourcentage" => $pourcentage,
						"nom_systeme" => $c["nom_systeme_competence"],
						"pourcentage_init" => $c["pourcentage_init_competence"],
					);
				}
			}
		}
		$this->view->competences = $tabCompetences;
	}
}
