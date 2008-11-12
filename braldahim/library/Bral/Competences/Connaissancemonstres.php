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
class Bral_Competences_Connaissancemonstres extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass("Monstre");
		
		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}
		
		$vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		$this->view->distance = $vue_nb_cases;
		
		$x_min = $this->view->user->x_hobbit - $this->view->distance;
		$x_max = $this->view->user->x_hobbit + $this->view->distance;
		$y_min = $this->view->user->y_hobbit - $this->view->distance;
		$y_max = $this->view->user->y_hobbit + $this->view->distance;
		
		// recuperation des monstres qui sont presents sur la vue
		$tabMonstres = null;
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max);
		foreach($monstres as $m) {
			if ($m["genre_type_monstre"] == 'feminin') {
				$m_taille = $m["nom_taille_f_monstre"];
			} else {
				$m_taille = $m["nom_taille_m_monstre"];
			}
			$tabMonstres[] = array(
				'id_monstre' => $m["id_monstre"], 
				'nom_monstre' => $m["nom_type_monstre"], 
				'taille_monstre' => $m_taille, 
				'x_monstre' => $m["x_monstre"],
				'y_monstre' => $m["y_monstre"],
			);
		}
		
		$this->view->tabMonstres = $tabMonstres;
		$this->view->nMonstres = count($tabMonstres);
		
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
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_1");
		}
		
		$cdmMonstre = false;
		if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
			foreach ($this->view->tabMonstres as $m) {
				if ($m["id_monstre"] == $idMonstre) {
					$cdmMonstre = true;
					break;
				}
			}
		}
		if ($cdmMonstre === false) {
			throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculCDM($idMonstre);
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculCDM($idMonstre) {
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($idMonstre);
		$monstre = $monstreRowset;
		$tabCDM["id_monstre"] = $monstre["id_monstre"];
		$tabCDM["nom_monstre"] = $monstre["nom_type_monstre"];
		if ($monstre["genre_type_monstre"] == "feminin")
			$tabCDM["taille_monstre"] = $monstre["nom_taille_f_monstre"];
		else
			$tabCDM["taille_monstre"] = $monstre["nom_taille_m_monstre"];
		//TODO
		//Calculer l'intervalle pour chaque caractèristique en fonction de la distance.
		$tabCDM["vue_monstre"] = $monstre["vue_monstre"];
		
		$this->view->tabCDM = $tabCDM;
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_evenements", "box_competences_communes");
	}
}
?>