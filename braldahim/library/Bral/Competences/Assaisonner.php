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
class Bral_Competences_Assaisonner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabLaban = null;

		//n => De 1 à niv FOR (le joueur choisit combien il veut transformer)
		$this->view->nbViande = $this->view->user->force_base_hobbit;
		if ($this->view->nbViande < 1) {
			$this->view->nbViande = 1;
		}

		foreach ($laban as $p) {
			$tabLaban = array(
				"nb_viande" => $p["quantite_viande_laban"],
				"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
			);
		}
		if (isset($tabLaban) && $tabLaban["nb_viande"] >= 1) {
			$this->view->assaisonnerNbViandeOk = true;
			
			if ($this->view->nbViande > $tabLaban["nb_viande"]) {
				$this->view->nbViande = $tabLaban["nb_viande"];
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

		// Verification assaisonner
		if ($this->view->assaisonnerNbViandeOk == false) {
			throw new Zend_Exception(get_class($this)." Assaisonnement interdit ");
		}

		if ((int)$this->request->get("valeur_1")."" != $this->request->get("valeur_1")."") {
			throw new Zend_Exception(get_class($this)." Nombre invalide");
		} else {
			$nombre = (int)$this->request->get("valeur_1");
		}

		if ($nombre > $this->view->nbViande) {
			throw new Zend_Exception(get_class($this)." Nombre invalide 2 n:".$nombre. " n1:".$this->view->nbViande);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculAssaisonner($nombre);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculAssaisonner($nombre) {
		Zend_Loader::loadClass("Laban");

		/*
		 * n unité de viandes en entrée donnent n*1DY viandes préparées en sortie
		 * n => De 1 à niv FOR (le joueur choisit combien il veut transformer)
		 * Y = arr inf (niv FOR/3) au mini 1
		 */
		$tirage = 0;

		$y = floor($this->view->user->force_base_hobbit / 3);
		if ($y < 1) {
			$y = 1;
		}

		for ($i=1; $i <= $nombre; $i++) {
			$tirage = $tirage + Bral_Util_De::get_de_specifique(1, $y);
		}
		$this->view->nbViandePreparee = $tirage;

		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		$poidsRestant = $poidsRestant - (Bral_Util_Poids::POIDS_VIANDE * $nombre);
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbViandePrepareePossible = floor($poidsRestant / Bral_Util_Poids::POIDS_VIANDE_PREPAREE);

		$this->view->nbViandeATerre = 0;
		if ($this->view->nbViandePreparee > $nbViandePrepareePossible) {
			$this->view->nbViandePrepareeLaban = $nbViandePrepareePossible;
			$this->view->nbViandeATerre = $this->view->nbViandePreparee - $this->view->nbViandePrepareeLaban;
		} else {
			$this->view->nbViandePrepareeLaban = $this->view->nbViandePreparee;
		}

		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_laban' => -$nombre,
			'quantite_viande_preparee_laban' => $this->view->nbViandePrepareeLaban,
		);
		$labanTable->insertOrUpdate($data);

		// on depose à terre si çà passe pas dans le laban
		if ($this->view->nbViandeATerre > 0) {
			Zend_Loader::loadClass("Element");
			$elementTable = new Element();
			$data = array(
			"quantite_viande_preparee_element" => $this->view->nbViandeATerre,
			"x_element" => $this->view->user->x_hobbit,
			"y_element" => $this->view->user->y_hobbit,
			);
			$elementTable->insertOrUpdate($data);
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_competences_metiers", "box_laban"));
	}
}
