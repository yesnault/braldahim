<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Tribunal extends Bral_Lieux_Lieu
{

	function prepareCommun()
	{
		$utilisationPointPossible = false;
		$travauxOk = false;
		$cautionOk = false;
		$soudoyerOk = false;

		if ($this->view->user->pa_braldun >= 4) {
			$travauxOk = true;
		}

		$this->view->utilisationPaPossible = false;

		Zend_Loader::loadClass("Contrat");
		$tableContrat = new Contrat();
		$contrat = $tableContrat->findEnCoursByIdBraldunCible($this->view->user->id_braldun);

		$contratEnCours = false;
		if ($contrat != null && count($contrat) > 0) {
			$contratEnCours = true;
		}

		$type = null;
		$coutCastarsSoudoyer = 0;
		$soudoyerOk = false;
		if ($this->view->user->points_gredin_braldun > 0) {
			$type = "gredin";
			$utilisationPointPossible = true;
			if ($this->view->user->pa_braldun >= 1 && $this->view->user->castars_braldun >= 100) {
				$cautionOk = true;
				$this->view->utilisationPaPossible = true;
			}
			$this->view->utilisationPossible = $utilisationPointPossible && ($travauxOk || $cautionOk);
			if ($this->view->user->pa_braldun >= 4) {
				$this->view->utilisationPaPossible = true;
			}

		} elseif ($this->view->user->points_redresseur_braldun > 0) {
			$type = "redresseur";
			$utilisationPointPossible = true;
			$coutCastarsSoudoyer = 50 * $this->view->user->points_redresseur_braldun;
			if ($this->view->user->pa_braldun >= 4 && $this->view->user->castars_braldun > 50 * $this->view->user->points_redresseur_braldun) {
				$soudoyerOk = true;
				$this->view->utilisationPaPossible = true;
			}
			$this->view->utilisationPossible = true;
		}

		$this->view->utilisationPointPossible = $utilisationPointPossible;
		$this->view->travauxOk = $travauxOk;
		$this->view->cautionOk = $cautionOk;
		$this->view->soudoyerOk = $soudoyerOk;
		$this->view->coutCastarsSoudoyer = $coutCastarsSoudoyer;
		$this->view->type = $type;

		$this->view->contratEnCours = $contratEnCours;
	}

	function prepareFormulaire()
	{
	}

	function prepareResultat()
	{
		// verification qu'il y a assez de castars
		if ($this->view->utilisationPossible == false) {
			throw new Zend_Exception(get_class($this) . " Tribunal impossible");
		}

		if ($this->view->contratEnCours == true) {
			throw new Zend_Exception(get_class($this) . " Tribunal contrat impossible");
		}

		if ($this->view->utilisationPointPossible == false) {
			throw new Zend_Exception(get_class($this) . " Tribunal impossible 2");
		}

		if ($this->view->type == "gredin") {
			if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide : val=" . $this->request->get("valeur_1"));
			} else {
				$choix = (int)$this->request->get("valeur_1");
			}

			if ($choix == 1 && $this->view->travauxOk != true) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide b : val=" . $this->request->get("valeur_1"));
			} elseif ($choix == 2 && $this->view->cautionOk != true) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide c : val=" . $this->request->get("valeur_1"));
			}
		} else {
			if (!$this->view->soudoyerOk) {
				throw new Zend_Exception(get_class($this) . " Tribunal Soudoyer KO");
			}
		}


		if ($this->view->type == "gredin") {
			if ($choix == 1) {
				$this->view->paUtilisationLieu = 4;
				$this->view->choix = 1;
			} else { //if ($choix == 2) {
				$this->view->paUtilisationLieu = 1;
				$this->view->user->castars_braldun = $this->view->user->castars_braldun - 100;
				$this->view->choix = 2;
			}
			$this->view->user->points_gredin_braldun = $this->view->user->points_gredin_braldun - 1;
		} else {
			$this->view->paUtilisationLieu = 4;
			$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->coutCastarsSoudoyer;
			$this->view->user->points_redresseur_braldun = 0;
		}

		if ($this->view->user->pa_braldun < $this->view->paUtilisationLieu) {
			// dernier contrôle
			throw new Zend_Exception(get_class($this) . " Tribunal Pas assez de PA braldun:" . $this->view->user->pa_braldun . " lieu:" . $this->view->paUtilisationLieu);
		}

		$this->majBraldun();
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_laban", "box_titres"));
	}

}