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
				);

				if ($this->view->user->x_hobbit == $e["x_champ"] &&
				$this->view->user->y_hobbit == $e["y_champ"] &&
				$this->view->user->z_hobbit == $e["y_champ"]) {
					$this->view->estSurChamp = true;
				}
				break;
			}
		}
		if ($tabChamp == null) {
			throw new Zend_Exception(get_class($this)." Champ invalide idh:".$this->view->user->id_hobbit." ide:".$id_champ);
		}

		Zend_Loader::loadClass("HobbitsCompetences");
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);

		$competence = null;
		$tabCompetences = null;
		foreach($hobbitCompetences as $c) {
			$tabCompetences[] = array("id_competence" => $c["id_fk_competence_hcomp"],
					"nom" => $c["nom_competence"],
					"pa_utilisation" => $c["pa_utilisation_competence"],
					"pourcentage" => Bral_Util_Commun::getPourcentage($c, $this->view->config),
					"nom_systeme" => $c["nom_systeme_competence"],
					"pourcentage_init" => $c["pourcentage_init_competence"],
			);
		}

		$this->view->competences = $tabCompetences;
		$this->view->champ = $tabChamp;
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