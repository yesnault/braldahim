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
class Bral_Competences_Defricher extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->defricherOk = true;
		$this->view->routeTrouvee = false;

		Zend_Loader::loadClass("Route");
		$routeTable = new Route();
		$routes = $routeTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, "toutes");

		$this->route = null;
		if (count($routes) > 0) {
			if ($routes[0]["est_visible_route"] == "non") {
				$this->route = $routes[0];
			} else {
				$this->view->defricherOk = false;
			}
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

		$this->calculDefricher();

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	private function calculDefricher() {

		if ($this->route != null) {
			$routeTable = new Route();
			$data = array("est_visible_route" => "oui");
			$where = "id_route = ".$this->route["id_route"];
			$routeTable->update($data, $where);
			$this->view->routeTrouvee = true;

			$idType = $this->view->config->game->evenements->type->competence;
			$details = "[h".$this->view->user->id_braldun."] a défriché une route";
			$this->setDetailsEvenement($details, $idType);
			$this->setEvenementQueSurOkJet1(false);
			
			$this->view->okJet1 = true;
			$this->view->nbGainCommunParDlaOk = true;
		} else {
			$this->view->routeTrouvee = false;
		}
	}

	function getListBoxRefresh() {
		$tab = array("box_competences_communes", "box_competences_basiques", "box_competences_metiers");
		if ($this->view->routeTrouvee === true) {
			$tab[] = "box_vue";
		}
		return $this->constructListBoxRefresh($tab);
	}
}
