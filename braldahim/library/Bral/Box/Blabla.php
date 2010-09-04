<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Profil.php 2833 2010-08-13 22:04:20Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-08-14 00:04:20 +0200 (sam., 14 août 2010) $
 * $LastChangedRevision: 2833 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Box_Blabla extends Bral_Box_Box {
	
	const NB_TOUR_MESSAGE_MAX = 5;
	const NB_CASES_MAX = 3;

	function getTitreOnglet() {
		return "Le Blabla";
	}

	function getNomInterne() {
		return "box_blabla";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->prepareMessages();
		}
		$this->view->nom_interne = $this->getNomInterne();
		if ($this->view->user->nb_tour_blabla_braldun >= self::NB_TOUR_MESSAGE_MAX) {
			$this->view->nouveauPossible = false;
		} else {
			$this->view->nouveauPossible = true;
		}
		$this->view->nb_messages_max = self::NB_TOUR_MESSAGE_MAX;
		$this->view->nb_cases_max = self::NB_CASES_MAX;
		return $this->view->render("interface/blabla.phtml");
	}

	private function prepareMessages() {
		Zend_Loader::loadClass("Blabla");
		$blablaTable = new Blabla();
		$tab = null;

		$rowset = $blablaTable->findByPosition($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		foreach($rowset as $r) {
			$braldun = $r["prenom_braldun"]." ".$r["nom_braldun"]." (".$r["id_braldun"].")";
			$tab[] = array ("date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y à H:i:s ',$r["date_blabla"]),
							"braldun" => $braldun,
							"message" => $r["message_blabla"],
							"x" => $r["x_blabla"],
							"y" => $r["y_blabla"],
							"z" => $r["z_blabla"]);
		}
		$this->view->blablaMessages = $tab;
	}
}
