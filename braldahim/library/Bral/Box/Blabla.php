<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
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
		Zend_Loader::loadClass("Bral_Util_Lien");
		
		$blablaTable = new Blabla();
		$tab = null;

		$rowset = $blablaTable->findByPosition($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		foreach($rowset as $r) {
			$braldun = Bral_Util_Lien::remplaceBaliseParNomEtJs("[b".$r["id_braldun"]."]");
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
