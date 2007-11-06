<?php

class Bral_Competences_Ramasser extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Rune");
		
		$this->view->ramasserOk = false;
		
		$runeTable = new Rune();
		$runeRowset = $runeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		
		$tabRunes = null;
		if (count($runeRowset) > 0) {
			foreach($runeRowset as $r) {
				$tabRunes[] = array("id_rune" => $r["id_rune"]);
				$this->view->ramasserOk = true;
			}
		}
		$this->view->tabRunes = $tabRunes;
		$this->view->nRunes = count($tabRunes);
	
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

		// Verification depiauter
		if ($this->view->ramasserOk == false) {
			throw new Zend_Exception(get_class($this)." Ramasser interdit ");
		}
		
		$this->calculRamasser();
		$this->majEvenementsStandard();

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculRamasser() {
		Zend_Loader::loadClass("Bral_Util_De");
		Zend_Loader::loadClass("LabanRune");

		$runeTable = new Rune();
		$runeRowset = $runeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->nbRunes = Bral_Util_De::get_1d3();
		
		if ($this->view->nbRunes > count($runeRowset)) {
			$this->view->nbRunes = count($runeRowset);
		}
		
		$tirage = null;
		if ($this->view->nbRunes == 1) {
			$tirage[] = 0;		
		} else {
			for($i = 1; $i<= $this->view->nbRunes; $i++) {
				$tirage[] = Bral_Util_De::get_de_specifique_hors_liste(0, count($runeRowset)-1, $tirage);
			}
		}

		$labanRuneTable = new LabanRune();
		
		foreach($tirage as $t) {
			if (!isset($runeRowset[$t]) || !isset($runeRowset[$t]["id_rune"])) {
				throw new Exception("calculRamasser : tirage invalide");
			}
			$data = array(
				'id_hobbit_laban_rune' => $this->view->user->id_hobbit,
				'id_rune_laban_rune' => $runeRowset[$t]["id_rune"],
				'id_fk_type_laban_rune' => $runeRowset[$t]["id_fk_type_rune"],
			);
			$labanRuneTable->insert($data);
			
			$where = "id_rune=".$runeRowset[$t]["id_rune"];
			$runeTable->delete($where);
		}
	}
	
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
