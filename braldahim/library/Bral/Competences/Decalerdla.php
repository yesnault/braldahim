<?php

class Bral_Competences_Decalerdla extends Bral_Competences_Competence {

	private $_tabHeures = null;
	private $_tabMinutes = null;
	function prepareCommun() {
		for ($i = 0; $i< 24; $i ++) {
			$this->_tabHeures[] = $i;
		}

		for ($i = 0; $i< 60; $i ++) {
			$this->_tabMinutes[] = $i;
		}
	}

	function prepareFormulaire() {
		$convertDate = new Bral_Util_ConvertDate();
		$this->view->dlaActuelle = $convertDate->get_datetime_mysql_datetime("H:i:s, \l\e d/m/Y", $this->view->user->date_fin_tour_hobbit);
		$this->view->tabHeures = $this->_tabHeures;
		$this->view->tabMinutes = $this->_tabMinutes;
	}

	function prepareResultat() {
		$newHeure = intval($this->request->get("valeur_1"));
		$newMinutes = intval($this->request->get("valeur_2"));

		// Verification des heures
		$heureOk = false;
		foreach ($this->_tabHeures as $h) {
			if ($h == $newHeure) {
				$heureOk = true;
				break;
			}
		}

		if ($heureOk === false) {
			throw new Zend_Exception(get_class($this)." Heure invalide : ".$newHeure);
		}
		
		// Verification des minutes
		$minutesOk = false;
		foreach ($this->_tabMinutes as $m) {
			if ($m == $newMinutes) {
				$minutesOk = true;
				break;
			}
		}

		if ($minutesOk === false) {
			throw new Zend_Exception(get_class($this)." Minutes invalides : ".$newMinutes);
		}
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();
		
		$convertDate = new Bral_Util_ConvertDate();
		$ajout = $newHeure.":".$newMinutes.":0";
		$nouvelleDla = $convertDate->get_date_add_time_to_date($this->view->user->date_fin_tour_hobbit, $ajout);
		$this->view->user->date_fin_tour_hobbit = $nouvelleDla;
		
		$data = array( 
			'date_fin_tour_hobbit' => $this->view->user->date_fin_tour_hobbit,
		); 
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
		
		$this->view->heures = $newHeure;
		$this->view->minutes = $newMinutes;
		$this->view->dlaActuelle = $convertDate->get_datetime_mysql_datetime("H:i:s, \l\e d/m/Y", $this->view->user->date_fin_tour_hobbit);
	}

	function getListBoxRefresh() {
		return array("box_profil");
	}

}