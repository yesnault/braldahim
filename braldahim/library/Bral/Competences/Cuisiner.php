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
class Bral_Competences_Cuisiner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Ville");
		
		// On regarde si le hobbit n'est pas dans une ville
		$villeTable = new Ville();
		$villes = $villeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		if (count($villes) == 0) {
			$this->view->cuisinerLieuOk = true;
		}
		
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);

		// Le joueur tente de transformer n+1 gigots marinés ou n est son niveau de SAG
		$this->view->nbViandePreparee = $this->view->user->sagesse_base_hobbit + 1;
		
		$tabLaban = null;
		foreach ($laban as $p) {
			$tabLaban = array(
				"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
				"nb_ration" => $p["quantite_ration_laban"],
			);
		}
		if (isset($tabLaban) && $tabLaban["nb_viande_preparee"] > 0) {
			$this->view->cuisinerNbViandeOk = true;
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
		if ($this->view->cuisinerNbViandeOk == false || $this->view->cuisinerLieuOk == false) {
			throw new Zend_Exception(get_class($this)." Cuisiner interdite ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculCuisiner();
		}
		
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 * Transforme 1 unité de viande préparée en 1D2+1 ration (conservation illimitée) 
	 * 1 ration fait 1 repas complet. 
	 * Un repas complet fait +80% dans la balance de faim.
	 * Peut être utilisé partout sauf en ville
	 */	
	private function calculCuisiner() {
		Zend_Loader::loadClass("Laban");
		
		Zend_Loader::loadClass('Bral_Util_Commun');
		$this->view->effetRune = false;
		
		// Le joueur tente de transformer n+1 rondins ou n est son niveau de SAG
		$nb = $this->view->nbViandePreparee;
		
		// A partir de la quantité choisie on a un % de perte de gigots marinés : p=0,5-0,002*(jet SAG + BM)
		$tirage = 0;
		for ($i=1; $i <= ($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit) ; $i++) {
			$tirage = $tirage + Bral_Util_De::get_1d6();
		}
		$perte = 0.5-0.002 * ($tirage + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit);
	
		// Et arrondi ((n+1)-(n+1)*p) rations en sortie
		$this->view->nbRation = round($nb - $nb * $perte);
		
		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "RU")) { // s'il possède une rune RU
			$this->view->nbRation = $this->view->nbRation + 1;
			$this->view->effetRune = true;
		} else {
			$this->view->nbRation = $this->view->nbRation + 0;
		}
		
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_ration_laban' => $this->view->nbRation,
			'quantite_viande_preparee_laban' => -$nb,
		);
		$labanTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
