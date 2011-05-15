<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Blabla {

	const NB_TOUR_MESSAGE_MAX = 5;
	const NB_CASES_MAX = 3;
	
	private function __construct() {
	}
	
	public static function render(&$view) {
		if ($view->affichageInterne) {
			self::prepareMessages($view);
		}
		
		if ($view->user->nb_tour_blabla_braldun >= self::NB_TOUR_MESSAGE_MAX) {
			$view->nouveauPossible = false;
		} else {
			$view->nouveauPossible = true;
		}
		$view->nb_messages_max = self::NB_TOUR_MESSAGE_MAX;
		$view->nb_cases_max = self::NB_CASES_MAX;
		return $view->render("interface/blabla.phtml");
	}

	private static function prepareMessages(&$view) {
		Zend_Loader::loadClass("Blabla");
		Zend_Loader::loadClass("Bral_Util_Lien");
		
		$blablaTable = new Blabla();
		$tab = null;

		$rowset = $blablaTable->findByPosition($view->user->x_braldun, $view->user->y_braldun, $view->user->z_braldun);

		foreach($rowset as $r) {
			$braldun = Bral_Util_Lien::remplaceBaliseParNomEtJs("[b".$r["id_braldun"]."]");
			$tab[] = array ("date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y à H:i:s ',$r["date_blabla"]),
							"braldun" => $braldun,
							"message" => $r["message_blabla"],
							"x" => $r["x_blabla"],
							"y" => $r["y_blabla"],
							"z" => $r["z_blabla"]);
		}
		$view->blablaMessages = $tab;
	}

}