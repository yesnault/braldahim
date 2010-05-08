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
class Bral_Competences_Ramasserballon extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->ramasserballonOk = false;

		$this->view->estIntangible = false;
		if ($this->view->user->est_intangible_braldun == "oui") {
			$this->view->estIntangible = true;
			return;
		}
		
		Zend_Loader::loadClass("SouleMatch");
		$souleMatch = new SouleMatch();
		$matchsRowset = $souleMatch->findByXYBallon($this->view->user->x_braldun, $this->view->user->y_braldun);
		
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
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// Verification ramasser
		if ($this->view->ramasserballonOk == false) {
			throw new Zend_Exception(get_class($this)." Ramasser ballon interdit ");
		}

		$this->detailEvenement = "";

		$this->calculRamasserballon();

		$this->idMatchSoule = $this->match["id_soule_match"];
		$this->detailEvenement = "[h".$this->view->user->id_braldun."] a ramassé le ballon";
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->soule);
		
		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}
	
	private function calculRamasserballon() {
		$souleMatch = new SouleMatch();
		$data = array(
			"x_ballon_soule_match" => null,
			"y_ballon_soule_match" => null,
			"id_fk_joueur_ballon_soule_match" => $this->view->user->id_braldun,
		);
		$where = "id_soule_match = ".$this->match["id_soule_match"];
		$souleMatch->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_soule", "box_vue"));
	}

}
