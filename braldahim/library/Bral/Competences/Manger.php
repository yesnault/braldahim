<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Competences_Manger extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Ville");
		
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabLaban = null;
		foreach ($laban as $p) {
			$tabLaban = array(
				"nb_ration" => $p["quantite_ration_laban"],
			);
		}
		
		$this->view->mangerBalanceOk = true;
		if ($this->view->user->balance_faim_hobbit >= 100) {
			$this->view->mangerBalanceOk = false;
		}
		
		if (isset($tabLaban) && $tabLaban["nb_ration"] > 0) {
			$this->view->mangerNbRationOk = true;
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

		// Verification cuisiner
		if ($this->view->mangerNbRationOk == false) {
			throw new Zend_Exception(get_class($this)." Manger interdit ");
		}
		
		$this->calculManger();
		$this->setEvenementQueSurOkJet1(false);
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}
	
	/*
	 * PA : 1
	 * Balance : +80%
	 * Un repas préparé c'est +80% dans la balance
	 */
	private function calculManger() {
		Zend_Loader::loadClass("Laban");
		
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_ration_laban' => -1,
		);
		$labanTable->insertOrUpdate($data);
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$this->view->user->balance_faim_hobbit = $this->view->user->balance_faim_hobbit + 80;
		
		if ($this->view->user->balance_faim_hobbit > 100) {
			$this->view->user->balance_faim_hobbit = 100; 
		}
		
		$data = array(
			'balance_faim_hobbit' => $this->view->user->balance_faim_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_evenements");
	}
}
