<?php

class Bral_Lieux_Auberge extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_hobbit -  $this->_coutCastars) > 0);
	}

	function prepareFormulaire() {
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {
		$idDestination = intval($this->request->get("valeur_1"));
		
		// verification qu'il y a assez de castars
		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
		}
		
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->_coutCastars;
		$this->view->user->balance_faim_hobbit = $this->view->user->balance_faim_hobbit + 80;
		
		if ($this->view->user->balance_faim_hobbit > 100) {
			$this->view->user->balance_faim_hobbit = 100; 
		}
		$this->majHobbit();
	}


	function getListBoxRefresh() {
		return array("box_profil", "box_laban");
	}

	private function calculCoutCastars() {
		return 5;
	}
}