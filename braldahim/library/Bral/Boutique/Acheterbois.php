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
class Bral_Boutique_Acheterbois extends Bral_Boutique_Boutique {
	
	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter du bois";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		
		if ($this->request->get("valeur_1") != "bois") {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Type invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->nomType = "bois";
		}
		
		$this->preparePrix();
		
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
		if ($this->view->possedeCharrette !== true) {
			throw new Zend_Exception(get_class($this)."::pas de charrette");
		}
		if ($this->view->acheterPossible !== true) {
			throw new Zend_Exception(get_class($this)."::achat impossible");
		}
		
		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide : ".$this->request->get("valeur_2"));
		} else {
			$this->view->quantiteAchetee = (int)$this->request->get("valeur_2");
		}
		
		if ($this->view->quantiteAchetee > $this->view->nombreMaximum) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide : ".$nombre. " max:".$this->view->nombreMaximum);
		}
		
		$this->transfert();
	}
	
	private function preparePrix() {
		
		$prixUnitaire = 12;
		
		$this->view->prixUnitaire = floor($prixUnitaire);
		
		$this->view->nombreMaximum = floor($this->view->user->castars_hobbit * $prixUnitaire);
		
		if ($this->view->nombreMaximum < 1 || $this->view->assezDePa == false) {
			$this->view->acheterPossible = false;
		} else {
			$this->view->acheterPossible = true;
		
			$charretteTable = new Charrette();
			$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
			if ($nombre == 1) {
				$this->view->acheterPossible = true;
				$this->view->possedeCharrette = true;
			} else {
				$this->view->acheterPossible = false;
				$this->view->possedeCharrette = false;
			}
		}
	}
	
	private function transfert() {
		Zend_Loader::loadClass("Charrette");
		$this->view->coutCastars = floor($this->view->quantiteAchetee * $this->view->prixUnitaire);
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		
		$charretteTable = new Charrette();
		$data = array(
			'quantite_rondin_charrette' => $this->view->quantiteAchetee,
			'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
		);
		$charretteTable->updateCharrette($data);
		unset($charretteTable);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_charrette", "box_evenements");
	}
}