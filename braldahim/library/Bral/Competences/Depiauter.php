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
/**
Enlève la peau d'un cadavre de monstre qui est au sol. 
(Pour les monstres qui ont une peau). 

La quantité de peau est fonction de la taille du monstre :
petit : 1D2 unité de peau
normal : 1D3 unité de peau
grand : 2D3 unité de peau
gigantesque : 3D3 unité de peau
(Directement dans le sac à dos)

*/
class Bral_Competences_Depiauter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Cadavre");
		Zend_Loader::loadClass("Laban");
		
		$this->preCalculPoids();
		if ($this->view->poidsPlaceDisponible !== true) {
			return;
		}
		
		$cadavreTable = new Cadavre();
		$cadavres = $cadavreTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$tabCadavres = null;
		foreach($cadavres as $c) {
			if ($c["genre_type_monstre"] == 'feminin') {
				$c_taille = $c["nom_taille_f_monstre"];
			} else {
				$c_taille = $c["nom_taille_m_monstre"];
			}
			$tabCadavres[] = array("id_cadavre" => $c["id_cadavre"], "nom_cadavre" => $c["nom_type_monstre"], 'taille_cadavre' => $c_taille, 'id_fk_taille_cadavre' => $c["id_fk_taille_cadavre"]);
		}
		
		$this->view->tabCadavres = $tabCadavres;
		$this->view->nCadavres = count($tabCadavres);
	
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Cadavre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idCadavre = (int)$this->request->get("valeur_1");
		}
		
		$attaqueCadavre = false;
		foreach ($this->view->tabCadavres as $c) {
			if ($c["id_cadavre"] == $idCadavre) {
				$attaqueCadavre = true;
				break;
			}
		}
		if ($attaqueCadavre === false) {
			throw new Zend_Exception(get_class($this)." Cadavre invalide (".$idCadavre.")");
		}
			
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculDepiauter($idCadavre);
		}
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 * La quantité de peau est fonction de la taille du monstre :
	 * petit : 1D2 unité de peau
	 * normal : 1D3 unité de peau
	 * grand : 2D3 unité de peau
	 * gigantesque : 3D3 unité de peau
	 */
	private function calculDepiauter($id_cadavre) {

		$cadavreTable = new Cadavre();
		$cadavreRowset = $cadavreTable->findById($id_cadavre);
		$cadavre = $cadavreRowset;
		
		if ($cadavre == null || $cadavre["id_cadavre"] == null || $cadavre["id_cadavre"] == "") {
			throw new Zend_Exception(get_class($this)."::calculDepiauter cadavre inconnu");
		} elseif ($this->view->poidsPlaceDisponible == false) {
			throw new Zend_Exception(get_class($this)." Poids invalide");
		}
		
		$this->view->nbPeau = 0;
		switch ($cadavre["id_fk_taille_cadavre"]) {
			case 1 : // petit
				$this->view->nbPeau = Bral_Util_De::get_1d2();
				break;
			case 2 : // normal
				$this->view->nbPeau = Bral_Util_De::get_1d3();
				break;
			case 3 : // grand
				$this->view->nbPeau = Bral_Util_De::get_2d3();
				break;
			case 4 : // gigantesque
				$this->view->nbPeau = Bral_Util_De::get_3d3();
				break;
		}
		
		$this->view->effetRune = false;
		
		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "FO")) { // s'il possède une rune FO
			$this->view->effetRune = true;
			$this->view->nbPeau = ceil($this->view->nbPeau * 1.5);
		}
		
		$this->view->nbPeau = $this->view->nbPeau + ($this->view->user->force_bm_hobbit + $this->view->user->force_bbdf_hobbit) / 2 ;
		$this->view->nbPeau = intval($this->view->nbPeau);
		if ($this->view->nbPeau < 0) {
			$this->view->nbPeau = 0;
		}
		
		if ($this->view->nbPeau > $this->view->nbElementPossible) {
			$this->view->nbPeau = $this->view->nbElementPossible;
		}
		
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_peau_laban' => $this->view->nbPeau,
		);
		$labanTable->insertOrUpdate($data);
		
		$where = "id_cadavre=".$id_cadavre;
		$cadavreTable->delete($where);
	}
	
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue", "box_laban"));
	}
	
	private function preCalculPoids() {
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		
		$this->view->nbElementPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_PEAU);
		
		if ($this->view->nbElementPossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
	}
}
