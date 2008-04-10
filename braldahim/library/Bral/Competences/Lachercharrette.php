<?php

class Bral_Competences_Lachercharrette extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		
		$tabCharrettes = null;
		$this->view->possedeCharrette = false;
		
		$charretteTable = new Charrette();
		
		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
		if ($charrette != null && count($charrette) > 0) {
			foreach ($charrette as $c) {
				$this->view->idCharrette = $c["id_charrette"];
				$this->view->possedeCharrette = true;
				break;
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
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}
		
		// Verification abattre arbre
		if ($this->view->possedeCharrette == false) {
			throw new Zend_Exception(get_class($this)." Possede aucune charrette ");
		}
		
		// calcul des jets
		$this->calculJets();
		
		if ($this->view->okJet1 === true) {
			$this->reloadInterface = true;
			$this->calculLacherCharrette();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	

	private function calculLacherCharrette() {
		Zend_Loader::loadClass("Charrette");
		
		$charretteTable = new Charrette();
		$dataUpdate = array(
			"id_fk_hobbit_charrette" => null,
			"x_charrette" => $this->view->user->x_hobbit,
			"y_charrette" => $this->view->user->y_hobbit,
		);
		$where = "id_fk_hobbit_charrette = ".$this->view->user->id_hobbit;
		$charretteTable->update($dataUpdate, $where);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_vue", "box_charrette", "box_evenements");
	}
}
