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
class Bral_Competences_Fumer extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("LabanTabac");
		Zend_Loader::loadClass("Ville");
		
		$labanTabacTable = new LabanTabac();
		$labanTabac = $labanTabacTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$this->view->fumerNbFeuilleOk = false;
		
		$tabLabanTabac = null;
		foreach ($labanTabac as $t) {
			if ($t["quantite_feuille_laban_tabac"] > 0) {
				$this->view->fumerNbFeuilleOk = true;
				$tabLabanTabac[$t["id_fk_type_laban_tabac"]] = $t;
			}
		}
		
		$this->view->tabLabanTabac = $tabLabanTabac;
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

		// Verification cuisiner
		if ($this->view->fumerNbFeuilleOk == false) {
			throw new Zend_Exception(get_class($this)." Fumer interdit ");
		}
		
		$idTabac = intval($this->request->get("valeur_1"));
		
		$tabacValide = false;
		$tabac = null;
		foreach($this->view->tabLabanTabac as $t) {
			if ($t["id_fk_type_laban_tabac"] == $idTabac) {
				$tabac = $t;
				$tabacValide = true;
				break;
			}
		}
		if ($tabacValide == false || $tabac == null) {
			throw new Zend_Exception(get_class($this)." Fumer : tabac invalide:".$idTabac);
		}
		
		$this->calculFumer($tabac);
		$this->setEvenementQueSurOkJet1(false);
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
		
		$this->view->tabac = $tabac;
	}
	
	private function calculFumer($tabac) {
		Zend_Loader::loadClass("LabanTabac");
		
		$labanTabacTable = new LabanTabac();
		$data["id_fk_hobbit_laban_tabac"] = $this->view->user->id_hobbit;
		$data["id_fk_type_laban_tabac"] = $tabac["id_fk_type_laban_tabac"];
		$data["quantite_feuille_laban_tabac"] = -1;
		
		$labanTabacTable->insertOrUpdate($data);
		
		Zend_Loader::loadClass("HobbitsCompetences");
		
		$hobbitsCompetencesTables = new HobbitsCompetences();
		$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->nbToursBonus = Bral_Util_De::get_1d2();
		$this->view->nbToursMalus = Bral_Util_De::get_1d2();
		$tabCompetences = null;
		foreach($hobbitCompetences as $c) {
			if ($c["id_fk_type_tabac_competence"] == $tabac["id_fk_type_laban_tabac"]) {
				$data = array('nb_tour_restant_bonus_tabac_hcomp' => $this->view->nbToursBonus,
							  'nb_tour_restant_malus_tabac_hcomp' => $this->view->nbToursMalus);
				$where = "id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]. " AND id_fk_hobbit_hcomp=".$this->view->user->id_hobbit;
				$hobbitsCompetencesTables->update($data, $where);
				$tabCompetences[] = $c;
			} else {
				$data = array('nb_tour_restant_bonus_tabac_hcomp' => 0,
							  'nb_tour_restant_malus_tabac_hcomp' => 0);
				$where = "id_fk_competence_hcomp = ".$c["id_fk_competence_hcomp"]. " AND id_fk_hobbit_hcomp=".$this->view->user->id_hobbit;
				$hobbitsCompetencesTables->update($data, $where);
			}
		}
		if ($tabCompetences == null) {
			throw new Zend_Exception(get_class($this)." Fumer competences invalides");
		}
		$this->view->competences = $tabCompetences;
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban", "box_competences_communes", "box_competences_basiques", "box_competences_metiers"));
	}
}
