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
class Bral_Boutique_Acheterminerais extends Bral_Boutique_Boutique {
	
	private $potion = null;
	private $idBoutique = null;

	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter du minerai";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass('TypeMinerai');
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Type invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->idTypeMinerai = (int)$this->request->get("valeur_1");
		}
		
		if ($this->request->get("valeur_2") != "brut" && $this->request->get("valeur_2") != "lingot") {
			throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Type invalide 2: ".$this->request->get("valeur_2"));
		} else {
			$this->view->nomTypeMinerai = $this->request->get("valeur_2");
		}
		
		$typeMineraiTable = new TypeMinerai();
		$typeMineraiRowset = $typeMineraiTable->findById($this->view->idTypeMinerai);
		if (count($typeMineraiRowset) == 1) {
			$this->view->typeMinerai = $typeMineraiRowset->toArray();
		} else {
			throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Minerai invalide : ".$this->view->idTypeMinerai);
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
		
		if (((int)$this->request->get("valeur_3").""!=$this->request->get("valeur_3")."")) {
			throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Nombre invalide : ".$this->request->get("valeur_3"));
		} else {
			$this->view->quantiteAchetee = (int)$this->request->get("valeur_3");
		}
		
		if ($this->view->quantiteAchetee > $this->view->nombreMaximum) {
			throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Nombre invalide : ".$nombre. " max:".$this->view->nombreMaximum);
		}
		
		$this->transfert();
	}
	
	private function preparePrix() {
		
		$prixUnitaire = 10;
		
		$this->view->prixUnitaire = floor($prixUnitaire);
		
		$this->view->nombreMaximum = floor($this->view->user->castars_hobbit * $prixUnitaire);
		
		$this->view->poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		
		if ($this->view->nomTypeMinerai == "brut") {
			$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_MINERAI);
		} else { // lingot
			$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_LINGOT);
		}
		
		if ($this->view->nombreMaximum > $nbPossible) {
			$this->view->nombreMaximum = $nbPossible;
		}
		
		if ($this->view->nombreMaximum < 1) {
			$this->view->acheterPossible = false;
		} else {
			$this->view->acheterPossible = true;
		}
	}
	
	private function transfert() {
		Zend_Loader::loadClass("LabanMinerai");
		$this->view->coutCastars = floor($this->view->quantiteAchetee * $this->view->prixUnitaire);
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		
		$data = array(
			"id_fk_type_laban_minerai" => $this->view->idTypeMinerai,
			"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
		);
		
		if ($this->view->nomTypeMinerai == "brut") {
			$data["quantite_brut_laban_minerai"] = $this->view->quantiteAchetee;
		} else { // Lingot
			$data["quantite_lingot_laban_minerai"] = $this->view->quantiteAchetee;
		}
		
		$labanMineraiTable = new LabanMinerai();
		$labanMineraiTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_evenements");
	}
}