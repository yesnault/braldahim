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
class Bral_Lieux_Bibliotheque extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabCompetences = null;

	function prepareCommun() {
		Zend_Loader::loadClass("HobbitsCompetences");
		Zend_Loader::loadClass("Competence");
		Zend_Loader::loadClass("Bral_Util_Niveau");

		$competenceTable = new Competence();
		$competenceRowset = $competenceTable->findCommunesByNiveau($this->view->user->niveau_hobbit);
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);
		$achatPiPossible = false;
		$possedeCdm = false;
		$pisterPossible = false;
		$possedeCompetenceCommune = false;

		foreach ($hobbitCompetences as $h) {
			if ($h["type_competence"] == "commun") {
				$possedeCompetenceCommune = true;
				break;
			}
		}

		foreach ($competenceRowset as $c) {
			$possible = true;
			foreach ($hobbitCompetences as $h) {
				if ($h["nom_systeme_competence"] == "connaissancemonstres"){
					$possedeCdm = true;
				}
				if ($h["id_competence"] == $c["id_competence"]) {
					$possible = false;
					break;
				}
			}

			if ($c["nom_systeme_competence"] == "pister" && $possedeCdm==true){
				$pisterPossible = true;
			}

			if ($possible === true) {
				$tropCher = true;
				$piCout = $c["pi_cout_competence"];

				if ($this->view->user->niveau_hobbit >= Bral_Util_Niveau::NIVEAU_MAX && $piCout <= $this->view->user->px_perso_hobbit) {
					$tropCher = false;
					$achatPiPossible = true;
				} elseif ($piCout <= $this->view->user->pi_hobbit) {
					$tropCher = false;
					$achatPiPossible = true;
				}
					
				$tab = array(
					"id_competence" => $c["id_competence"],
					"nom" => $c["nom_competence"],
					"nom_systeme" => $c["nom_systeme_competence"],
					"description" => $c["description_competence"],
					"niveau_requis" => $c["niveau_requis_competence"],
					"pi_cout" => $piCout,
					"trop_cher" => $tropCher,
				);
				$this->_tabCompetences[] = $tab;
			}
		}

		$this->_coutCastars = $this->calculCoutCastars($possedeCompetenceCommune);

		$this->view->achatPiPossible = $achatPiPossible;
		$this->view->tabCompetences = $this->_tabCompetences;
		$this->view->nCompetences = count($this->_tabCompetences);
		$this->view->coutCastars = $this->_coutCastars;
		$this->view->achatPossibleCastars = ($this->view->user->castars_hobbit - $this->_coutCastars >= 0);
		$this->view->pisterPossible = $pisterPossible;
		$this->view->possedeCompetenceCommune = $possedeCompetenceCommune;
	}

	function prepareFormulaire() {
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {
		// verification que la valeur recue est bien numerique
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		} else {
			$idCompetence = (int)$this->request->get("valeur_1");
		}

		if ($idCompetence == -1) {
			throw new Zend_Exception(get_class($this)." Valeur competence invalide:-1");
		}

		// verification qu'il a assez de PA
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PA:".$this->view->user->pa_hobbit);
		}

		// verification qu'il a assez de PI
		if ($this->view->achatPiPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PI:".$this->view->user->pi_hobbit);
		}

		// verification qu'il y a assez de castars
		if ($this->view->achatPossibleCastars == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
		}

		$comptenceOk = false;
		foreach ($this->view->tabCompetences as $c) {
			if ($idCompetence == $c["id_competence"]) {
				$this->view->nomCompetence = $c["nom"];
				$this->view->coutPi = $c["pi_cout"];
				$nomSysteme = $c["nom_systeme"];
				$comptenceOk = true;
				break;
			}
		}

		if ($comptenceOk == false) {
			throw new Zend_Exception(get_class($this)." competence non trouvee");
		}

		$data = array(
			'id_fk_hobbit_hcomp' => $this->view->user->id_hobbit,
			'id_fk_competence_hcomp'  => $idCompetence,
			'pourcentage_hcomp'  => 10,
			'date_debut_tour_hcomp'  => "0000-00-00 00:00:00",
			'nb_action_tour_hcomp' => 0,
			'nb_gain_tour_hcomp' => 0,
		);

		$hobbitCompetenceTable = new HobbitsCompetences();
		$hobbitCompetenceTable->insert($data);

		$hobbitTable = new Hobbit();
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->_coutCastars;

		if ($this->view->user->niveau_hobbit >= Bral_Util_Niveau::NIVEAU_MAX) {
			$this->view->user->px_perso_hobbit = $this->view->user->px_perso_hobbit -$this->view->coutPi;
		} else {
			$this->view->user->pi_hobbit = $this->view->user->pi_hobbit -$this->view->coutPi;	
		}

		Zend_Loader::loadClass("Bral_Util_Quete");
		$this->view->estQueteEvenement = Bral_Util_Quete::etapeApprendreIdentificationRune($this->view->user, $nomSysteme);

		$this->majHobbit();
	}


	function getListBoxRefresh() {
		$tab = array("box_competences_communes", "box_laban");
		return $this->constructListBoxRefresh($tab);
	}

	private function calculCoutCastars($possedeCompetenceCommune) {
		// la premiere competence commune est gratuite
		if ($possedeCompetenceCommune == false) {
			return 0;
		} else {
			return 50;
		}
	}
}