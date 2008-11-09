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
class Bral_Competences_Attrapercharrette extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		
		$tabCharrettes = null;
		$this->view->possedeCharrette = false;
		$this->view->attraperCharrettePossible = false;
		
		$charretteTable = new Charrette();
		
		$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
		if ($nombre > 0) {
			$this->view->possedeCharrette = true;
		}
		
		$charrettes = $charretteTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		foreach ($charrettes as $c) {
			$this->view->attraperCharrettePossible = true;
			$tabCharrettes[] = array ("id_charrette" => $c["id_charrette"]);
		}
		$this->view->charrettes = $tabCharrettes;
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
		
		// Verification abattre arbre
		if ($this->view->possedeCharrette == true) {
			throw new Zend_Exception(get_class($this)." Possede deja charrette ");
		}
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Charrette invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->idCharrette = (int)$this->request->get("valeur_1");
		}
		
		$this->reloadInterface = true;
		$this->calculAttrapperCharrette($this->view->idCharrette);
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculAttrapperCharrette($idCharrette) {
		$charretteTable = new Charrette();
		$dataUpdate = array(
			"id_fk_hobbit_charrette" => $this->view->user->id_hobbit,
			"x_charrette" => null,
			"y_charrette" => null,
		);
		$where = "id_charrette = ".$idCharrette;
		$charretteTable->update($dataUpdate, $where);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_vue", "box_charrette", "box_evenements");
	}
}
