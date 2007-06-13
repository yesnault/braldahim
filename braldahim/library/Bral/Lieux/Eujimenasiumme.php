<?php

class Bral_Lieux_Eujimenasiumme extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		$this->_coutCastars = $this->calculCoutCastars();
		
		$achatPiPossible = false;
		
		//TODO
		$coutForce =  ($this->view->user->force_hobbit+1 - 1) * $this->view->user->force_hobbit+1;
		$coutAgilite = $this->view->config->game->inscription->agilite_base;
		$coutVigueur = $this->view->config->game->inscription->vigueur_base;
		$coutSagesse = $this->view->config->game->inscription->sagesse_base;
		
		$this->view->achatPiPossible = $achatPiPossible;
	}

	function prepareFormulaire() {
		$this->view->coutCastars = $this->_coutCastars;
		// $this->view->utilisationPaPossible initialisé dans Bral_Lieux_Lieu
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