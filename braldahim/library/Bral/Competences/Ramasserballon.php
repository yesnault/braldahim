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
class Bral_Competences_Ramasserballon extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->ramasserballonOk = false;

		Zend_Loader::loadClass("SouleMatch");
		$souleMatch = new SouleMatch();
		$matchsRowset = $souleMatch->findByXYBallon($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		if ($matchsRowset != null && count($matchsRowset) == 1) {
			$this->match = $matchsRowset[0];
			$this->view->ramasserballonOk = true;
		}

	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification ramasser
		if ($this->view->ramasserballonOk == false) {
			throw new Zend_Exception(get_class($this)." Ramasser ballon interdit ");
		}

		$this->detailEvenement = "";

		$this->calculRamasserballon();

		$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a ramassé le ballon";
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->soule);

		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}
	
	private function calculRamasserballon() {
		$souleMatch = new SouleMatch();
		$data = array(
			"x_ballon_soule_match" => null,
			"y_ballon_soule_match" => null,
			"id_fk_joueur_ballon_soule_match" => $this->view->user->id_hobbit,
		);
		$where = "id_soule_match = ".$this->match["id_soule_match"];
		$souleMatch->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_soule", "box_vue"));
	}

}
