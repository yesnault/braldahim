<?php

class Bral_Lieux_Eujimenasiumme extends Bral_Lieux_Lieu {

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
		
		// verification qu'il y a assez de castars
		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
		}
		
		// TODO
	}


	function getListBoxRefresh() {
		return array("box_profil", "box_metier", "box_laban", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_vue", "box_lieu");
	}

	private function calculCoutCastars() {
		return 50;
	}
}