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
class Bral_Competences_Marcher extends Bral_Competences_Competence {

	function prepareCommun() {

		Zend_Loader::loadClass('Bral_Util_Marcher');
		Zend_Loader::loadClass('Bral_Util_Quete');

		$utilMarcher = new Bral_Util_Marcher();

		$selection = $this->request->get("valeur_1"); // si l'on vient de la vue (clic sur l'icone marcher)
		$calcul = $utilMarcher->calcul($this->view->user, $selection);

		$this->view->effetMot = $calcul["effetMot"];
		$this->view->assezDePa  = $calcul["assezDePa"];
		$this->view->nb_cases = $calcul["nb_cases"];
		$this->view->nb_pa  =  $calcul["nb_pa"];
		$this->view->tableau = $calcul["tableau"];
		$this->tableauValidation = $calcul["tableauValidation"];
		$this->view->environnement = $calcul["environnement"];
		$this->view->marcherPossible = $calcul["marcherPossible"];
		$this->view->estEngage = $calcul["estEngage"];
		$this->view->estSurRoute = $calcul["estSurRoute"];

		$this->view->x_min = $calcul["x_min"];
		$this->view->x_max = $calcul["x_max"];
		$this->view->y_min = $calcul["y_min"];
		$this->view->y_max = $calcul["y_max"];
	}

	function calculNbPa() {
		// fait dans UtilMarcher
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {

		if ($this->view->assezDePa == false) {
			return;
		}

		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = split("h", $x_y);

		if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Deplacement X impossible : ".$offset_x);
		}

		if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Deplacement Y impossible : ".$offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." Deplacement XY impossible : ".$offset_x.$offset_y);
		}

		$this->view->user->x_braldun = $this->view->user->x_braldun + $offset_x;
		$this->view->user->y_braldun = $this->view->user->y_braldun + $offset_y;
		
		Zend_Loader::loadClass("Bral_Util_Crevasse");
		$this->view->estCrevasseEvenement = Bral_Util_Crevasse::calculCrevasse($this->view->user);

		$id_type = $this->view->config->game->evenements->type->deplacement;
		$details = "[b".$this->view->user->id_braldun."] a marché";
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);

		$this->view->estQueteEvenement = Bral_Util_Quete::etapeMarcher($this->view->user);

		$this->calculBalanceFaim();
		$this->calculFinMatchSoule();
		$this->majBraldun();
		
		Zend_Loader::loadClass("Bral_Util_Filature");
		Bral_Util_Filature::action($this->view->user, $this->view);
	}
	

	function getListBoxRefresh() {
		$tab = array("box_vue", "box_lieu", "box_echoppes", "box_champs");
		if ($this->view->user->est_soule_braldun == "oui") {
			$tab[] = "box_soule";
		}
		return $this->constructListBoxRefresh($tab);
	}

}