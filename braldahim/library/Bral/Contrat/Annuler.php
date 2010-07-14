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
class Bral_Contrat_Annuler extends Bral_Contrat_Contrat {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Annuler le contrat en cours";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Contrat");

		$this->refresh = false;

		$tableContrat = new Contrat();
		$contratEnCours = $tableContrat->findEnCoursByIdBraldun($this->view->user->id_braldun);
		if ($contratEnCours != null) {
			$this->view->contratEnCours = $contratEnCours;
		} else {
			$this->view->contratEnCours = null;
		}

	}

	function prepareFormulaire() {}

	function prepareResultat() {
		if ($this->view->contratEnCours == null) {
			throw new Zend_Exception(get_class($this)." Annuler Contrat invalide");
		}

		$this->annuler();
		$this->refresh = true;
	}

	private function annuler() {
		$tableContrat = new Contrat();
		$data = array(
			'date_fin_contrat' => date("Y-m-d H:i:s"),
			'etat_contrat' => 'annulé',
		);
		$where = "id_contrat = ".$this->view->contratEnCours["id_contrat"];
		$tableContrat->update($data, $where);
		
		if ($this->view->contratEnCours["type_contrat"] == "gredin") {
			$this->view->user->points_gredin_braldun = $this->view->user->points_gredin_braldun - 5;
			if ($this->view->user->points_gredin_braldun < 0) {
				$this->view->user->points_gredin_braldun = 0;
			} 
			$perte = " 5 points de Gredin";
		} else {
			$this->view->user->points_redresseur_braldun = $this->view->user->points_redresseur_braldun - 5;
			if ($this->view->user->points_redresseur_braldun < 0) {
				$this->view->user->points_redresseur_braldun = 0;
			}
			$perte = " 5 points de Redresseur de Tors"; 
		}
		$this->majBraldun();
		$this->view->perte = $perte;
	}	

	function getListBoxRefresh() {
		return array("box_contrats");
	}

}