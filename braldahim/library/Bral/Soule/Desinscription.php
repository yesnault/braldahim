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
class Bral_Soule_Desinscription extends Bral_Soule_Soule {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Désinscription au match de soule";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('SouleEquipe');
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('SouleTerrain');

		$this->view->deinscriptionPossible = false;
		$this->matchDesinscription = null;

		$this->calculNbPa();
		$this->calculNbCastars();

		if ($this->view->assezDePa && $this->view->user->est_engage_hobbit == "non") {
			$this->prepareTerrain();
		}
	}

	private function prepareTerrain() {

		$souleMatchTable = new SouleMatch();
		$matchs = $souleMatchTable->findNonDebuteByIdHobbit($this->view->user->id_hobbit);

		if ($matchs != null && count($matchs) == 1) { // s'il n'y a pas de match en cours
			$match = $matchs[0];

			// on regarde s'il le quota n'est pas atteint (enfin non en cours ie: == 0)
			if ($match["nb_jours_quota_soule_match"] == 0) {
				$this->view->match = $match;
				$this->view->desinscriptionPossible = true;
			} else {
				throw new Zend_Exception(get_class($this)." deinscriptionPossible impossible quota");
			}
		} else {
			throw new Zend_Exception(get_class($this)." Erreur terrain, idh:".$this->view->user->id_hobbit);
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {

		if ($this->view->desinscriptionPossible !== true) {
			throw new Zend_Exception(get_class($this)." Erreur deinscriptionPossible == false");
		}

		if ($this->view->match == null) {
			throw new Zend_Exception(get_class($this)."match invalide");
		}

		$this->calculDesinscription();
		$this->majHobbit();
	}

	public function calculNbPa() {
		$this->view->nb_pa = 2;
		if ($this->view->user->pa_hobbit - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}

	public function calculNbCastars() {
		$this->view->nb_castars = 10;
		if ($this->view->user->castars_hobbit - $this->view->nb_castars < 0) {
			$this->view->assezDeCastars = false;
		} else {
			$this->view->assezDeCastars = true;
		}
	}

	private function calculDesinscription() {

		$where = "id_fk_match_soule_equipe = ".(int)$this->view->match["id_soule_match"];
		$where .= " AND id_fk_hobbit_soule_equipe = ".(int)$this->view->user->id_hobbit;

		$souleEquipeTable = new SouleEquipe();
		$souleEquipeTable->delete($where);

		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->nb_castars;
		if ($this->view->user->castars_hobbit < 0) {
			$this->view->user->castars_hobbit = 0;
		}

		$details = "[h".$this->view->user->id_hobbit."] s'est désinscrit du match sur le ".$this->view->match["nom_soule_terrain"];
		$idType = $this->view->config->game->evenements->type->soule;
		$this->setDetailsEvenement($details, $idType);
	}

	function getListBoxRefresh() {
		$tab = array("box_soule", "box_laban");
		return $this->constructListBoxRefresh($tab);
	}
}