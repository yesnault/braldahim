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
class Bral_Boutique_Acheterpartieplantes extends Bral_Boutique_Boutique {
	
	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter des parties de plantes";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass('TypePartiePlante');
		Zend_Loader::loadClass('TypePlante');
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: Type plante invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->idTypePlante = (int)$this->request->get("valeur_1");
		}
		
		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: Type partie plante invalide : ".$this->request->get("valeur_2"));
		} else {
			$this->view->idTypePartiePlante = (int)$this->request->get("valeur_2");
		}
		
		
		$typePartiePlanteTable = new TypePartieplante();
		$typePartiePlanteRowset = $typePartiePlanteTable->findById($this->view->idTypePartiePlante);
		if (count($typePartiePlanteRowset) == 1) {
			$this->view->typePartiePlante = $typePartiePlanteRowset->toArray();
		} else {
			throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: PartiePlante invalide : Id=".$this->view->idTypePartiePlante);
		}
		
		$typePlanteTable = new TypePlante();
		$typePlanteRowset = $typePlanteTable->findById($this->view->idTypePlante);
		if (count($typePlanteRowset) == 1) {
			$this->view->typePlante = $typePlanteRowset->toArray();
		} else {
			throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: PartiePlante invalide : Id=".$this->view->idTypePlante);
		}
		
		if ($this->view->typePlante["id_fk_partieplante1_type_plante"] != $this->view->idTypePartiePlante &&
			$this->view->typePlante["id_fk_partieplante2_type_plante"] != $this->view->idTypePartiePlante &&
			$this->view->typePlante["id_fk_partieplante3_type_plante"] != $this->view->idTypePartiePlante &&
			$this->view->typePlante["id_fk_partieplante4_type_plante"] != $this->view->idTypePartiePlante) {
			throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: PartiePlante invalide : Id1=".$this->view->idTypePlante. " Id2=".$this->view->idTypePartiePlante);
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
			throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: Nombre invalide : ".$this->request->get("valeur_3"));
		} else {
			$this->view->quantiteAchetee = (int)$this->request->get("valeur_3");
		}
		
		if ($this->view->quantiteAchetee > $this->view->nombreMaximum) {
			throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: Nombre invalide : ".$nombre. " max:".$this->view->nombreMaximum);
		}
		
		$this->transfert();
	}
	
	private function preparePrix() {
		
		$prixUnitaire = 10;
		
		$this->view->prixUnitaire = floor($prixUnitaire);
		$this->view->nombreMaximum = floor($this->view->user->castars_hobbit / $prixUnitaire);
		
		$this->view->poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		
		$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
		
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
		Zend_Loader::loadClass("LabanPartieplante");
		$this->view->coutCastars = floor($this->view->quantiteAchetee * $this->view->prixUnitaire);
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		
		$data = array(
			"quantite_laban_partieplante" => $this->view->quantiteAchetee,
			"id_fk_type_laban_partieplante" => $this->view->idTypePartiePlante,
			"id_fk_type_plante_laban_partieplante" => $this->view->idTypePlante,
			"id_fk_hobbit_laban_partieplante" => $this->view->user->id_hobbit,
		);
		
		$labanPartiePlanteTable = new LabanPartieplante();
		$labanPartiePlanteTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_evenements");
	}
}