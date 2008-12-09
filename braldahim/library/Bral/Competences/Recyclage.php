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

class Bral_Competences_Recyclage extends Bral_Competences_Competence {
	
	function prepareCommun() {
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
			$tabEquipementLaban[] = array(
				"id_equipement" => $e["id_laban_equipement"],
				"nom" => $e["nom_type_equipement"],
				"qualite" => $e["nom_type_qualite"],
				"niveau" => $e["niveau_recette_equipement"],
				"id_type" => $e["id_type_equipement"],
			);
		}
		$this->view->tabEquipementLaban = $tabEquipementLaban;
		$this->view->nbEquipementLaban = count ($tabEquipementLaban);
		
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
		
		$recyclage = false;
		if (isset($this->view->tabEquipementLaban) && $this->view->nbEquipementLaban > 0) {
			foreach ($this->view->tabEquipementLaban as $e) {
				if ($e["id_equipement"] == $idEquipement) {
					$idTypeEquipement = $e["id_type"];
					$nivEquipement = $e["niveau"];
					$recyclage = true;
					break;
				}
			}
		}
		if ($recyclage === false) {
			throw new Zend_Exception(get_class($this)." Equipement invalide (".$idEquipement.")");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculRecyclage ($idEquipement, $idTypeEquipement, $nivEquipement);
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}
	
	private function calculRecyclage($idEquipement, $idTypeEquipement, $nivEquipement){
		Zend_Loader::loadClass("RecetteCout");
		Zend_Loader::loadClass("RecetteCoutMinerai");
		Zend_Loader::loadClass("Laban");
		
		$nbCuir = 0;
		$nbFourrure = 0;
		$nbPlanche = 0;
		$tabMinerai = null;		
		
		$recetteCoutTable = new RecetteCout();
		$recetteCout = $recetteCoutTable->findByIdTypeEquipementAndNiveau($idTypeEquipement, $nivEquipement);

		foreach($recetteCout as $r) {
			$nbCuir = $r["cuir_recette_cout"];
			$nbFourrure = $r["fourrure_recette_cout"];
			$nbPlanche = $r["planche_recette_cout"];
		}
		
		$recetteCoutMineraiTable = new RecetteCoutMinerai();
		$recetteCoutMinerai = $recetteCoutMineraiTable->findByIdTypeEquipementAndNiveau($idTypeEquipement, $nivEquipement);
		
		foreach($recetteCoutMinerai as $r){
			$tabMinerai[] = array (
				"nom" => $r["nom_type_minerai"],
				"quantite" => $r["quantite_recette_cout_minerai"],
			);
		}
		
		/*
		 * TODO
		 * Calculer le nombre de matière à garder
		 * Mettre à jour le laban avec les matières
		 * Controler le poids
		 * Supprimer l'equipement
		 * Afficher le résultat
		 */
		
		$this->view->nbCuir = $nbCuir;
		$this->view->nbFourrure = $nbFourrure;
		$this->view->nbPlanche = $nbPlanche;
		$this->view->minerai = $tabMinerai;
		
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_evenements", "box_competences_communes", "box_laban");
	}
}
