<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Hiberner extends Bral_Competences_Competence {

	function prepareCommun() {
		
		$this->view->hibernerPossible = false;
		
		Zend_Loader::loadClass("SouleEquipe");
		$souleEquipeTable = new SouleEquipe();
		$nombre = $souleEquipeTable->countNonDebuteByIdBraldun($this->view->user->id_braldun);
		
		if ($this->view->user->est_soule_braldun == "non" && $nombre == 0) {
			$this->view->hibernerPossible = true;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		
		if ($this->view->hibernerPossible == false) {
			throw new Zend_Exception(get_class($this)." Hiberner impossible : ".$this->view->user->id_braldun);
		}
		
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();
		
		$now = date("Y-m-d 0:0:0");
		$this->view->user->date_fin_hibernation_braldun = Bral_Util_ConvertDate::get_date_add_day_to_date($now, 5);
		
		$data = array( 
			'date_fin_hibernation_braldun' => $this->view->user->date_fin_hibernation_braldun,
		); 
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);
		
		$id_type = $this->view->config->game->evenements->type->special;
		$details = "[b".$this->view->user->id_braldun."] rentre en hibernation demain";
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

}