<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Provoquer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('Bral_Util_Attaque');
		
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}
		
		if ($this->view->user->est_intangible_braldun == "oui") {
			return;
		}
		
		$tabMonstres = null;

		// recuperation des monstres qui sont presents sur la case
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
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
				'sagesse_base_monstre' => $m["sagesse_base_monstre"],
				'sagesse_bm_monstre' => $m["sagesse_bm_monstre"],
				'genre_type_monstre' => $m["genre_type_monstre"],
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
			throw new Zend_Exception(get_class($this)." Monstre invalide (==-1)");
		}

		$monstre = null;
		if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
			foreach ($this->view->tabMonstres as $m) {
				if ($m["id_monstre"] == $idMonstre) {
					$monstre = $m;
					if ($monstre["genre_type_monstre"] == "feminin") {
						$article = "une";
					} else {
						$article = "un";
					}
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
		
		$id_type = $this->view->config->game->evenements->type->competence;
		$details = "[b".$this->view->user->id_braldun."] a réussi l'utilisation d'une compétence sur ".$article." [m".$monstre["id_monstre"]."]";
		$this->setDetailsEvenement($details, $id_type);
		$this->setDetailsEvenementCible($idMonstre, "monstre", $monstre["niveau_monstre"]);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences"));
	}

	private function calculProvoquer($monstre) {
		// jet VIG braldun > jet de SAG
		
		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_vigueur + $this->view->user->vigueur_base_braldun);
		$this->view->jetBraldun = $jetBraldun + $this->view->user->vigueur_bm_braldun + $this->view->user->vigueur_bbdf_braldun;
		
		$jetMonstre = Bral_Util_De::getLanceDe6($this->view->config->game->base_vigueur + $monstre["sagesse_base_monstre"]);
		$this->view->jetMonstre = $jetMonstre + $monstre["sagesse_bm_monstre"];
		
		if ($this->view->jetBraldun > $this->view->jetMonstre) {
			$this->view->provoquerOk = true;
			$this->changeCible($monstre);
		} else {
			$this->view->provoquerOk = false;
		}
		
		$this->view->monstre = $monstre;
	}
	
	private function changeCible($monstre) {
		$data = array("id_fk_braldun_cible_monstre" => $this->view->user->id_braldun);
		$where = "id_monstre=".$monstre["id_monstre"];
		$monstreTable = new Monstre();
		$monstreTable->update($data, $where);
	}
}