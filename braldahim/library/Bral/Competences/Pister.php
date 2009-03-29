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
class Bral_Competences_Pister extends Bral_Competences_Competence {
	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Commun");
		Zend_Loader::loadClass("HobbitsCdm");
		Zend_Loader::loadClass("TypeMonstre");
		
		$typeMonstreTable = new TypeMonstre();
		$typeMonstreRowset = $typeMonstreTable->fetchall(null, "nom_type_monstre");
		$typeMonstreRowset = $typeMonstreRowset->toArray();
		$tabTypeMonstre = null;
		$hobbitsCdmTable = new HobbitsCdm();
		foreach($typeMonstreRowset as $t) {
			if ($hobbitsCdmTable->findByIdHobbitAndIdTypeMonstre($this->view->user->id_hobbit,$t["id_type_monstre"]) == true){
				$tabTypeMonstre[] = array(
					'id_type_monstre' => $t["id_type_monstre"],
					'nom_type_monstre' => $t["nom_type_monstre"],
				);	
			}	
		}
		$this->view->tabTypeMonstre = $tabTypeMonstre;
	}
	
	function prepareFormulaire() {
		
	}
	
	function prepareResultat() {
		
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Type de monstre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idTypeMonstre = (int)$this->request->get("valeur_1");
		}
		
		$pister = false;
		if (isset($this->view->tabTypeMonstre) && count($this->view->tabTypeMonstre) > 0) {
			foreach ($this->view->tabTypeMonstre as $m) {
				if ($m["id_type_monstre"] == $idTypeMonstre) {
					$pister = true;
					break;
				}
			}
		}
		if ($pister === false) {
			throw new Zend_Exception(get_class($this)." Type de monstre invalide (".$idTypeMonstre.")");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculPister($idTypeMonstre);
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculPister($idTypeMonstre){
		
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes"));
	}
}