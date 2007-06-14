<?php

class Bral_Lieux_Eujimenasiumme extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		$this->_coutCastars = $this->calculCoutCastars();

		$achatPiPossible = false;

		$coutForce =  $this->calculCoutAmelioration($this->view->user->force_base_hobbit);
		$coutAgilite = $this->calculCoutAmelioration($this->view->user->agilite_base_hobbit);
		$coutVigueur = $this->calculCoutAmelioration($this->view->user->vigueur_base_hobbit);
		$coutSagesse = $this->calculCoutAmelioration($this->view->user->sagesse_base_hobbit);

		if ($coutForce <= $this->view->user->pi_hobbit
		&& $coutAgilite <= $this->view->user->pi_hobbit
		&& $coutVigueur <= $this->view->user->pi_hobbit
		&& $coutSagesse <= $this->view->user->pi_hobbit ) {
			$achatPiPossible = true;
		}

		$this->view->coutForce = $coutForce;
		$this->view->coutAgilite = $coutAgilite;
		$this->view->coutVigueur = $coutVigueur;
		$this->view->coutSagesse = $coutSagesse;
		$this->view->achatPiPossible = $achatPiPossible;
		$this->view->coutCastars = $this->_coutCastars;
		$this->view->achatPossibleCastars = ($this->view->user->castars_hobbit - $this->_coutCastars >= 0);
		// $this->view->utilisationPaPossible initialisé dans Bral_Lieux_Lieu
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {

		// verification qu'il a assez de PA
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PA:".$this->view->user->pa_hobbit);
		}

		// verification qu'il a assez de PI
		if ($this->view->achatPiPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PI:".$this->view->user->pi_hobbit);
		}

		// verification qu'il y a assez de castars
		if ($this->view->achatPossibleCastars == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
		}
		$this->view->nomCaracteristique = $this->request->get("valeur_1");
		// verification que la valeur recue est bien connue
		switch($this->request->get("valeur_1")) {
			case "FOR":
				if ($this->view->coutForce > $this->view->user->pi_hobbit) {
					throw new Zend_Exception(get_class($this)." Achat FOR invalide : pi=".$this->view->user->pi_hobbit. " cout=".$this->view->coutForce);
				} else {
					$this->view->user->force_base_hobbit = $this->view->user->force_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutForce;
					$this->view->coutPi = $this->view->coutForce;
				}
				break;
			case "SAG":
				if ($this->view->coutSagesse > $this->view->user->pi_hobbit) {
					throw new Zend_Exception(get_class($this)." Achat SAG invalide : pi=".$this->view->user->pi_hobbit. " cout=".$this->view->coutSagesse);
				} else {
					$this->view->user->sagesse_base_hobbit = $this->view->user->sagesse_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutSagesse;
					$this->view->coutPi = $this->view->coutSagesse;
				}
				break;
			case "VIG":
				if ($this->view->coutVigueur > $this->view->user->pi_hobbit) {
					throw new Zend_Exception(get_class($this)." Achat VIG invalide : pi=".$this->view->user->pi_hobbit. " cout=".$this->view->coutVigueur);
				} else {
					$this->view->user->vigueur_base_hobbit = $this->view->user->vigueur_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutVigueur;
					$this->view->coutPi = $this->view->coutVigueur;
				}
				break;
			case "AGI":
				if ($this->view->coutAgilite > $this->view->user->pi_hobbit) {
					throw new Zend_Exception(get_class($this)." Achat AGI invalide : pi=".$this->view->user->pi_hobbit. " cout=".$this->view->coutAgilite);
				} else {
					$this->view->user->agilite_base_hobbit = $this->view->user->agilite_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutAgilite;
					$this->view->coutPi = $this->view->coutAgilite;
				}
				break;
			default:
				throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		}

		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_metier", "box_laban", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_vue", "box_lieu");
	}

	private function calculCoutCastars() {
		return 50;
	}

	private function calculCoutAmelioration($nbDeActuel) {
		if ($nbDeActuel <= 1) {
			return 1;
		} else {
			return (($nbDeActuel - 1) * $nbDeActuel);
		}
	}
}