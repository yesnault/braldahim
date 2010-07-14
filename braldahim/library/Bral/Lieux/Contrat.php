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
class Bral_Lieux_Contrat extends Bral_Lieux_Lieu {

	function prepareCommun() {
		$this->view->utilisationPossible = false;
		$this->view->contratDisponible = false;

		Zend_Loader::loadClass("Contrat");
		$tableContrat = new Contrat();

		$contratEnCours = $tableContrat->findEnCoursByIdBraldun($this->view->user->id_braldun);
		if ($contratEnCours != null) {
			$this->view->contratEnCours = $contratEnCours;
		} else {
			$this->view->contratEnCours = null;
		}

		Zend_Loader::loadClass("Ville");
		$villeTable = new Ville();
		$ville = $villeTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun);

		if ($ville == null || count($ville) != 1) {
			throw new Zend_Exception("Erreur parametrage contrat. Maison hors Ville");
		} else {
			$ville = $ville[0];
		}

		$bralduns = null;
		if ($this->view->contratEnCours == null) {

			$tableBraldun = new Braldun();
			
			$niveauMin = $this->view->user->niveau_braldun - 1;
			$niveauMax = $this->view->user->niveau_braldun + 1;

			$this->view->type = null;
			if ($ville["id_ville"] == Ville::ID_VILLE_FICHETROUSSE) {
				$this->view->type = "gredin";
				if ($this->view->user->points_gredin_braldun > 0) {
					$this->view->utilisationPossible = true;
					$bralduns = $tableBraldun->findAllRedresseurs($niveauMin, $niveauMax);
					if ($bralduns != null && count($bralduns) > 0) {
						$this->view->contratDisponible = true;
					}
				}
			} else {
				$this->view->type = "redresseur";

				if ($this->view->user->points_redresseur_braldun > 0) {
					$bralduns = $tableBraldun->findAllGredins($niveauMin, $niveauMax);
					$this->view->utilisationPossible = true;
					if ($bralduns != null && count($bralduns) > 0) {
						$this->view->contratDisponible = true;
					}
				}
			}
		}

		$this->bralduns = $bralduns;

	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA");
		}
		
		if ($this->view->utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Contrat impossible");
		}

		if ($this->view->contratEnCours != null) {
			throw new Zend_Exception(get_class($this)." Contrat impossible 2");
		}

		if ($this->view->contratDisponible != true) {
			throw new Zend_Exception(get_class($this)." Contrat impossible 3");
		}

		Zend_Loader::loadClass("Bral_Util_Lien");
		$this->calculContrat();
		$this->majBraldun();
	}

	private function calculContrat() {

		shuffle($this->bralduns);
		$cible = $this->bralduns[0];

		$tableContrat = new Contrat();
		$data = array(
			'id_fk_braldun_contrat' =>  $this->view->user->id_braldun,
			'id_fk_cible_braldun_contrat' => $cible["id_braldun"],
			'date_creation_contrat' => date("Y-m-d H:i:s"), 
			'date_fin_contrat' => null,
			'gain_contrat' => null,
			'type_contrat' => $this->view->type,
			'etat_contrat' => 'en cours',
		);

		$idContrat = $tableContrat->insert($data);
		
		$this->view->cible = $cible;
		$this->view->idContrat = $idContrat;
		
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_contrats"));
	}

}