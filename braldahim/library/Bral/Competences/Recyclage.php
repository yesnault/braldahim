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

class Bral_Competences_Recyclage extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("HobbitEquipement");
		Zend_Loader::loadClass("LabanEquipement");
		
		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}
		
		// on va chercher l'équipement présent dans le laban
		$tabEquipementLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipementLabanRowset = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		foreach ($equipementLabanRowset as $e) {
			$equipements[] = array(
				"id_equipement" => $e["id_laban_equipement"],
				"nom" => $e["nom_type_equipement"],
				"qualite" => $e["nom_type_qualite"],
				"niveau" => $e["niveau_recette_equipement"],
			);
		}
		$this->view->tabEquipementLaban = $equipements;
		$this->view->nbEquipementLaban = count ($equipements);
		
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
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Equipement invalide : ".$this->request->get("valeur_1"));
		} else {
			$idEquipement = (int)$this->request->get("valeur_1");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_evenements", "box_competences_communes");
	}
}
