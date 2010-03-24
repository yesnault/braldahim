<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Champs_Voir extends Bral_Champs_Champ {


	function __construct($nomSystemeAction, $request, $view, $action, $id_champ = false) {
		Zend_Loader::loadClass("Champ");

		if ($id_champ !== false) {
			$this->idChamp = $id_champ;
		}
		parent::__construct($nomSystemeAction, $request, $view, $action);
	}

	function getNomInterne() {
		return "box_champ";
	}

	function render() {
		return $this->view->render("champs/voir.phtml");
	}

	function prepareCommun() {
		if (!isset($this->idChamp)) {
			$id_champ = (int)$this->request->get("valeur_1");
		} else {
			$id_champ = $this->idChamp;
		}

		$champTable = new Champ();
		$champs = $champTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->estSurChamp == false;

		$tabChamp = null;
		$id_metier = null;
		foreach ($champs as $e) {
			if ($e["id_champ"] == $id_champ) {
				$tabChamp = array(
					'id_champ' => $e["id_champ"],
					'nom_champ' => $e["nom_champ"],
					'nom_region' => $e["nom_region"],
					'x_champ' => $e["x_champ"],
					'y_champ' => $e["y_champ"],
					'z_champ' => $e["z_champ"],
					'phase_champ' => $e["phase_champ"],
					'date_seme_champ' => $e["date_seme_champ"],
					'date_fin_recolte_champ' => $e["date_fin_recolte_champ"],
					'date_fin_seme_champ' => $e["date_fin_seme_champ"],
					'quantite_champ' => $e["quantite_champ"],
				);

				if ($this->view->user->x_hobbit == $e["x_champ"] &&
				$this->view->user->y_hobbit == $e["y_champ"] &&
				$this->view->user->z_hobbit == $e["z_champ"]) {
					$this->view->estSurChamp = true;
				}
				$this->prepareChamp($e);
				break;
			}
		}

		if ($tabChamp == null) {
			throw new Zend_Exception(get_class($this)." Champ invalide idh:".$this->view->user->id_hobbit." ide:".$id_champ);
		}

		$this->prepareCompetences();
		$this->view->champ = $tabChamp;
	}

	private function prepareCompetences() {
		Zend_Loader::loadClass("HobbitsCompetences");
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);

		$competence = null;
		$tabCompetences = null;
		$possedeEntretenir = false;
		foreach($hobbitCompetences as $c) {
			if ($c["nom_systeme_competence"] == "semer" ||
			$c["nom_systeme_competence"] == "entretenir" ||
			$c["nom_systeme_competence"] == "recolter") {
				$tabCompetences[] = array("id_competence" => $c["id_fk_competence_hcomp"],
					"nom" => $c["nom_competence"],
					"pa_utilisation" => $c["pa_utilisation_competence"],
					"pourcentage" => Bral_Util_Commun::getPourcentage($c, $this->view->config),
					"nom_systeme" => $c["nom_systeme_competence"],
					"pourcentage_init" => $c["pourcentage_init_competence"],
				);
			}

			if ($c["nom_systeme_competence"] == "entretenir") {
				$possedeEntretenir = true;
			}
		}
		$this->view->competences = $tabCompetences;
		$this->view->possedeEntretenir = $possedeEntretenir;
	}

	private function prepareChamp($champ) {
		Zend_Loader::loadClass("ChampTaupe");
		$champTaupeTable = new ChampTaupe();
		$taupes = $champTaupeTable->findByIdChamp($champ["id_champ"]);

		$tabTaupes = array();
		if ($champ["phase_champ"] != "jachere") {
			if ($taupes != null) {
				foreach($taupes as $t) {
					if ($champ["phase_champ"] == 'a_recolter' ||
					$champ["phase_champ"] == 'seme' && $t["etat_champ_taupe"] != 'vivant') {
						$tabTaupes[$t["x_champ_taupe"].'t'.$t["y_champ_taupe"]] = $t;
					}
				}
			}
		}

		for ($y = 1; $y <= 10; $y++) {
			$change_level = true;
			for ($x = 1; $x <= 10; $x++) {

				$taupe = null;
				if ($tabTaupes != null) {
					$key = $x.'t'.$y;
					if (array_key_exists($key, $tabTaupes)) {
						$taupe = array(
							'etat_champ_taupe' => $tabTaupes[$key]["etat_champ_taupe"],
						);
					}
				}

				$tab = array (
					"css_champ" => $champ["phase_champ"],
					"x" => $x, 
					"y" => $y, //
					"change_level" => $change_level, // nouvelle ligne dans le tableau ;
					"taupe" => $taupe,
				);

				$tableau[] = $tab;
				if ($change_level) {
					$change_level = false;
				}
			}
		}
		$this->view->tableau = $tableau;
		$this->prepareTaupes($taupes);
		$this->prepareRecolte($champ);
	}

	private function prepareTaupes($taupes) {

		$taupesVivantes = array();
		foreach($taupes as $t) {
			if ($t["etat_champ_taupe"] == "vivant") {
				$taupesVivantes[$t["numero_champ_taupe"]]["taille"] = $t["taille_champ_taupe"];
				$taupesVivantes[$t["numero_champ_taupe"]]["morceaux"][] = $t;
			}
		}

		$taupesDetruites = array();
		foreach($taupes as $t) {
			if ($t["etat_champ_taupe"] == "detruit" && !array_key_exists($t["numero_champ_taupe"], $taupesVivantes)) {
				$taupesDetruites[$t["numero_champ_taupe"]] = $t["taille_champ_taupe"];
			}
		}

		$this->view->taupesVivantes = $taupesVivantes;
		$this->view->taupesDetruites = $taupesDetruites;

		$toutes = null;
		foreach($taupes as $t) {
			$toutes[$t["numero_champ_taupe"]][] = $t;
		}
		$this->view->toutes = $toutes;
	}

	private function prepareRecolte($champ) {
		if ($champ["phase_champ"] != 'jachere') {
			Zend_Loader::loadClass("TypeGraine");
			$typeGraineTable = new TypeGraine();
			$types = $typeGraineTable->findById($champ["id_fk_type_graine_champ"]);
			if ($types == null) {
				throw new Zend_Exception("Erreur Type Graine:".$champ["id_fk_type_graine_champ"]);
			}
			$this->view->typeGraine = $types->nom_type_graine;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}

	public function getIdChampCourant() {
		return false;
	}
}