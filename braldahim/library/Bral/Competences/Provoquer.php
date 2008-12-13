<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Competences_Provoquer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('Bral_Util_Attaque');
		
		$tabMonstres = null;

		// recuperation des monstres qui sont presents sur la case
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		foreach($monstres as $m) {
			if ($m["genre_type_monstre"] == 'feminin') {
				$m_taille = $m["nom_taille_f_monstre"];
			} else {
				$m_taille = $m["nom_taille_m_monstre"];
			}
			$tabMonstres[$m["id_monstre"]] = array(
				"id_monstre" => $m["id_monstre"], 
				"nom_monstre" => $m["nom_type_monstre"], 
				'taille_monstre' => $m_taille, 
				'niveau_monstre' => $m["niveau_monstre"], 
				'vigueur_base_monstre' => $m["vigueur_base_monstre"],
				'vigueur_bm_monstre' => $m["vigueur_bm_monstre"],
			);
		}

		$this->view->tabMonstres = $tabMonstres;
		$this->view->nMonstres = count($tabMonstres);
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Monstre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_1");
		}

		if ($idMonstre == -1) {
			throw new Zend_Exception(get_class($this)." Montre invalide (==-1)");
		}

		$monstre = null;
		if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
			foreach ($this->view->tabMonstres as $m) {
				if ($m["id_monstre"] == $idMonstre) {
					$monstre = $m;
					break;
				}
			}
		}
		if ($monstre == null) {
			throw new Zend_Exception(get_class($this)." Monstre invalide (".$idMonstre.")");
		}

		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculProvoquer($monstre);
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_evenements", "box_competences_communes");
	}

	private function calculProvoquer($monstre) {
		// jet VIG hobbit > jet de SAG
		
		$jetHobbit = 0;
		for ($i = 1; $i <= $this->view->config->game->base_vigueur + $this->view->user->vigueur_base_hobbit; $i++) {
			$jetHobbit = $jetHobbit + Bral_Util_De::get_1d6();
		}
		$this->view->jetHobbit = $jetHobbit + $this->view->user->vigueur_bm_hobbit + $this->view->user->vigueur_bbdf_hobbit;
		
		$jetMonstre = 0;
		for ($i = 1; $i <= $monstre["vigueur_base_monstre"]; $i++) {
			$jetMonstre = $jetMonstre + Bral_Util_De::get_1d6();
		}
		$this->view->jetMonstre = $jetMonstre + $monstre["vigueur_bm_monstre"];
		
		if ($this->view->jetHobbit > $this->view->jetMonstre) {
			$this->view->provoquerOk = true;
			$this->changeCible($monstre);
		} else {
			$this->view->provoquerOk = false;
		}
		
		$this->view->monstre = $monstre;
	}
	
	private function changeCible($monstre) {
		$data = array("id_fk_hobbit_cible_monstre" => $this->view->user->id_hobbit);
		$where = "id_monstre=".$monstre["id_monstre"];
		$monstreTable = new Monstre();
		$monstreTable->update($data, $where);
	}
}