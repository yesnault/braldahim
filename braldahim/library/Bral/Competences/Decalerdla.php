<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Decalerdla extends Bral_Competences_Competence
{

	private $_tabHeures = null;
	private $_tabMinutes = null;

	function prepareCommun()
	{
		for ($i = 0; $i < 24; $i++) {
			$this->_tabHeures[] = $i;
		}

		for ($i = 0; $i < 60; $i++) {
			$this->_tabMinutes[] = $i;
		}
	}

	function prepareFormulaire()
	{
		$dlaActuelle["texte"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("H:i:s, \l\e d/m/Y", $this->view->user->date_fin_tour_braldun);
		$dlaActuelle["heure"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("H", $this->view->user->date_fin_tour_braldun);
		$dlaActuelle["min"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("i", $this->view->user->date_fin_tour_braldun);
		$dlaActuelle["seconde"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("s", $this->view->user->date_fin_tour_braldun);
		$dlaActuelle["jour"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("d", $this->view->user->date_fin_tour_braldun);
		$dlaActuelle["mois"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("m", $this->view->user->date_fin_tour_braldun);
		$dlaActuelle["annee"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("Y", $this->view->user->date_fin_tour_braldun);
		$this->view->dlaActuelle = $dlaActuelle;
		$this->view->tabHeures = $this->_tabHeures;
		$this->view->tabMinutes = $this->_tabMinutes;
	}

	function prepareResultat()
	{
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
			throw new Zend_Exception(get_class($this) . " Heure invalide : " . $newHeure);
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
			throw new Zend_Exception(get_class($this) . " Minutes invalides : " . $newMinutes);
		}

		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();

		$ajout = $newHeure . ":" . $newMinutes . ":0";
		$nouvelleDla = Bral_Util_ConvertDate::get_date_add_time_to_date($this->view->user->date_fin_tour_braldun, $ajout);
		$this->view->user->date_fin_tour_braldun = $nouvelleDla;

		$data = array(
			'date_fin_tour_braldun' => $this->view->user->date_fin_tour_braldun,
		);
		$where = "id_braldun=" . $this->view->user->id_braldun;
		$braldunTable->update($data, $where);

		$this->view->heures = $newHeure;
		$this->view->minutes = $newMinutes;
		$dlaActuelle["texte"] = Bral_Util_ConvertDate::get_datetime_mysql_datetime("H:i:s, \l\e d/m/Y", $this->view->user->date_fin_tour_braldun);
		$this->view->dlaActuelle = $dlaActuelle;

	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_profil"));
	}

}