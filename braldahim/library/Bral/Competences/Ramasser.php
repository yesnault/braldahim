<?php

class Bral_Competences_Ramasser extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Castar");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("ElementRune");
		
		$this->view->ramasserOk = false;
		
		$runeTable = new Rune();
		$runeRowset = $runeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->tabObjets = null;
		$tabRunes = null;
		if (count($runeRowset) > 0) {
			foreach($runeRowset as $r) {
				$tabRunes[] = array("id_rune" => $r["id_rune"]);
				$this->tabObjets[] = "rune";
				$this->view->ramasserOk = true;
			}
		}
		$this->view->tabRunes = $tabRunes;
		$this->view->nRunes = count($tabRunes);
		
		$castarTable = new Castar();
		$castarRowset = $castarTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$nbCastars = 0;
		if (count($castarRowset) > 0) {
			foreach($castarRowset as $c) {
				$nbCastars = $c["nb_castar"];
				$this->tabObjets[] = "castars";
				$this->view->ramasserOk = true;
				break;
			}
		}
		$this->view->nbCastars = $nbCastars;
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
		$this->setEvenementQueSurOkJet1(false);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculRamasser() {
		Zend_Loader::loadClass("LabanRune");
		
		$this->view->nbCastarsRamasses = 0;
		$this->view->nbRunesRamassees = 0;
		
		$nbObjets = count($this->tabObjets);
		$tirage = Bral_Util_De::get_1d3();
		
		if ($tirage > $nbObjets) {
			$tirage = $nbObjets;
		}
		
		srand((float)microtime()*1000000);
		shuffle($this->tabObjets);
		for ($i = 0; $i < $tirage; $i++) {
			if ($this->tabObjets[$i] == "castars") {
				$this->calculRamasserCastars();
				$this->view->nbCastarsRamasses = $this->view->nbCastars;
			} else {
				$this->calculRamasserRune();
				$this->view->nbRunesRamassees = $this->view->nbRunesRamasse  + 1;
			}
		}
		$this->calculPoids();
	}

	private function calculRamasserCastars() {
		Zend_Loader::loadClass("Castar");
		
		$castarTable = new Castar();
		$where = 'x_castar = '.$this->view->user->x_hobbit;
		$where .= ' AND y_castar = '.$this->view->user->y_hobbit;
		$castarTable->delete($where);
		
		$hobbitTable = new Hobbit();
		$data = array(
			'castars_hobbit' => $this->view->user->castars_hobbit + $this->view->nbCastars,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}
	
	private function calculRamasserRune() {
		$runeTable = new Rune();
		$runeRowset = $runeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$nbRunes = count($runeRowset);
		
		$tirage = null;
		if ($nbRunes == 1) {
			$tirage[] = 0;		
		} else {
			for($i = 1; $i<= $nbRunes; $i++) {
				$tirage[] = Bral_Util_De::get_de_specifique_hors_liste(0, count($runeRowset)-1, $tirage);
			}
		}

		$labanRuneTable = new LabanRune();
		
		foreach($tirage as $t) {
			if (!isset($runeRowset[$t]) || !isset($runeRowset[$t]["id_rune"])) {
				throw new Exception("calculRamasser : tirage invalide");
			}
			$data = array(
				'id_fk_hobbit_laban_rune' => $this->view->user->id_hobbit,
				'id_rune_laban_rune' => $runeRowset[$t]["id_rune"],
				'id_fk_type_laban_rune' => $runeRowset[$t]["id_fk_type_rune"],
				'est_identifiee_rune' => 'non',
			);
			$labanRuneTable->insert($data);
			
			$where = "id_rune=".$runeRowset[$t]["id_rune"];
			$runeTable->delete($where);
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_laban", "box_evenements");
	}
}
