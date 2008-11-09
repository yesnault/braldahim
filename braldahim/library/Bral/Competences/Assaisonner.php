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
		
		//Le joueur tente de transformer n+1 gigots ou n est son niveau de FOR
		$this->view->nbViande = $this->view->user->force_base_hobbit + 1;
		
		foreach ($laban as $p) {
			$tabLaban = array(
				"nb_viande" => $p["quantite_viande_laban"],
				"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
			);
		}
		if (isset($tabLaban) && $tabLaban["nb_viande"] >= $this->view->nbViande) {
			$this->view->assaisonnerNbViandeOk = true;
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
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculAssaisonner();
		}
		
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 *  Transforme 2 unités de viande en 1D2 unité de viande préparée
	 */
	private function calculAssaisonner() {
		Zend_Loader::loadClass("Laban");
		
		// Le joueur tente de transformer n+1 gigots ou n est son niveau de FOR
		$nb = $this->view->nbViande;
		
		// A partir de la quantité choisie on a un % de perte de gigots : p=0,5-0,002*(jet FOR + BM)
		$tirage = 0;
		for ($i=1; $i <= ($this->view->config->game->base_force + $this->view->user->force_base_hobbit) ; $i++) {
			$tirage = $tirage + Bral_Util_De::get_1d6();
		}
		$perte = 0.5-0.002 * ($tirage + $this->view->user->force_bm_hobbit + $this->view->user->force_bbdf_hobbit);
	
		// Et arrondi ((n+1)-(n+1)*p) gigots marinés en sortie
		$this->view->nbViandePreparee = round($nb - $nb * $perte);
		
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_viande_laban' => -$nb,
			'quantite_viande_preparee_laban' => $this->view->nbViandePreparee,
		);
		$labanTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
