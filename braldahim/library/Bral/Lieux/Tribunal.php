<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Tribunal.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Lieux_Tribunal extends Bral_Lieux_Lieu {

	function prepareCommun() {
		$utilisationPointPossible = false;
		$travauxOk = false;
		$cautionOk = false;

		if ($this->view->user->pa_braldun >= 4) {
			$travauxOk = true;
		}
		
		Zend_Loader::loadClass("Contrat");
		$tableContrat = new Contrat();
		$contrat = $tableContrat->findEnCoursByIdBraldunCible($this->view->user->id_braldun);
		
		$contratEnCours = false;
		if ($contrat != null && count($contrat) > 0) {
			$contratEnCours = true;
		}

		if ($this->view->user->pa_braldun >= 1 && $this->view->user->castars_braldun >= 100) {
			$cautionOk = true;
		}

		if ($this->view->user->points_gredin_braldun > 0) {
			$utilisationPointPossible = true;
		}

		$this->view->utilisationPossible = $utilisationPointPossible && ($travauxOk || $cautionOk);

		$this->view->utilisationPointPossible = $utilisationPointPossible;
		$this->view->travauxOk = $travauxOk;
		$this->view->cautionOk = $cautionOk;
		$this->view->contratEnCours = $contratEnCours;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		// verification qu'il y a assez de castars
		if ($this->view->utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Tribunal impossible");
		}

		if ($this->view->contratEnCours == true) {
			throw new Zend_Exception(get_class($this)." Tribunal contrat impossible");
		}
		
		if ($this->view->utilisationPointPossible == false) {
			throw new Zend_Exception(get_class($this)." Tribunal impossible 2");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		} else {
			$choix = (int)$this->request->get("valeur_1");
		}

		if ($choix == 1 && $this->view->travauxOk != true) {
			throw new Zend_Exception(get_class($this)." Valeur invalide b : val=".$this->request->get("valeur_1"));
		} elseif ($choix == 2 && $this->view->cautionOk != true) {
			throw new Zend_Exception(get_class($this)." Valeur invalide c : val=".$this->request->get("valeur_1"));
		}

		
		if ($choix == 1) {
			$this->view->paUtilisationLieu = 4;
			$this->view->choix = 1;
		} else { //if ($choix == 2) {
			$this->view->paUtilisationLieu = 1;
			$this->view->user->castars_braldun = $this->view->user->castars_braldun - 100;
			$this->view->choix = 2;
		}
		$this->view->user->points_gredin_braldun = $this->view->user->points_gredin_braldun - 1;
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban", "box_titres"));
	}

}