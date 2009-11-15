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
class Bral_Competences_Recolter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Champ");

		if ($this->verificationChamp() == false) {
			return null;
		}

		$this->verificationChamp();
	}

	private function verificationChamp() {
		$this->view->recolterChampOk = false;

		$champTable = new Champ();
		$champs = $champTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit, $this->view->user->id_hobbit);

		$retour = false;
		if (count($champs) == 1) {
			$this->view->champ = $champs[0];
			if ($this->view->champ["phase_champ"] == "a_recolter") {
				$this->view->recolterChampOk = true;
				$retour = true;
			}
		}

		return $retour;
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

		// Verification semer
		if ($this->view->recolterChampOk == false) {
			throw new Zend_Exception(get_class($this)." Recolter Champ interdit");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->entretenir();
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function recolter() {
		
		//TODO Récolte
		
		// mise à jour du champ
		$champTable = new Champ();
		$data = array(
			'phase_champ' => 'jachere',
			'date_seme_champ' => null,
			'date_fin_recolte_champ' => null,
			//'id_fk_type_graine_champ' => null, ==> on ne vide pas, c'est utile pour le % quantité à la prochaine action semer
			'quantite_champ' => 0,
		);

		$where = 'id_champ='.$this->view->champ["id_champ"];
		$champTable->update($data, $where);

		// suppression des taupes et résultats d'entretenir s'il y en a
		Zend_Loader::loadClass("ChampTaupe");
		$champTaupeTable = new ChampTaupe();
		$where = 'id_fk_champ_taupe='.$this->champ["id_champ"];
		$champTaupeTable->delete($where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes", "box_champs"));
	}
}