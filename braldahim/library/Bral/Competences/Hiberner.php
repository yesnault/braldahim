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
class Bral_Competences_Hiberner extends Bral_Competences_Competence {

	function prepareCommun() {
		
		for ($i = 5; $i<= 100; $i ++) {
			$tabJours[] = $i;
		}
		$this->view->tabJours = $tabJours;
		
		$this->view->hibernerPossible = false;
		
		Zend_Loader::loadClass("SouleEquipe");
		$souleEquipeTable = new SouleEquipe();
		$nombre = $souleEquipeTable->countNonDebuteByIdHobbit($this->view->user->id_hobbit);
		
		if ($this->view->user->est_soule_hobbit == "non" && $nombre == 0) {
			$this->view->hibernerPossible = true;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		
		if ($this->view->hibernerPossible == false) {
			throw new Zend_Exception(get_class($this)." Hiberner impossible : ".$this->view->user->id_hobbit);
		}
		
		$nbJours = intval($this->request->get("valeur_1"));

		// Verification des jours
		$joursOk = false;
		foreach ($this->view->tabJours as $j) {
			if ($j == $nbJours) {
				$joursOk = true;
				break;
			}
		}

		if ($joursOk === false) {
			throw new Zend_Exception(get_class($this)." Jours invalides : ".$nbJours);
		}
		
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();
		
		$now = date("Y-m-d 0:0:0");
		$this->view->user->date_fin_hibernation_hobbit = Bral_Util_ConvertDate::get_date_add_day_to_date($now, $nbJours);
		
		$data = array( 
			'date_fin_hibernation_hobbit' => $this->view->user->date_fin_hibernation_hobbit,
		); 
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
		
		$this->view->nbJours = $nbJours;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

}