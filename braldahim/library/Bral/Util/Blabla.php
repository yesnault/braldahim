<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Blabla
{

	const NB_TOUR_MESSAGE_MAX = 5;
	const NB_CASES_MAX = 3;

	private function __construct()
	{
	}

	public static function render(&$view)
	{
		if ($view->user->nb_tour_blabla_braldun >= self::NB_TOUR_MESSAGE_MAX) {
			$view->nouveauPossible = false;
		} else {
			$view->nouveauPossible = true;
		}
		$view->nb_messages_max = self::NB_TOUR_MESSAGE_MAX;
		$view->nb_cases_max = self::NB_CASES_MAX;
		return $view->render("interface/blabla.phtml");
	}

	public static function getJsonCount(&$view)
	{
		Zend_Loader::loadClass("Blabla");
		$blablaTable = new Blabla();
		$retour = array();
		$x = $view->user->x_braldun;
		$y = $view->user->y_braldun;
		$z = $view->user->z_braldun;
		$bm = $view->user->vue_bm_braldun;
		$vue_nb_cases = Bral_Util_Commun::getVueBase($x, $y, $z) + $bm;
		if ($vue_nb_cases < 0) {
			$vue_nb_cases = 0;
		}
		$nombre = $blablaTable->countByPositionAndDate($view->user->x_braldun, $view->user->y_braldun, $view->user->z_braldun, $vue_nb_cases, $view->user->dateConnexion);
		$retour["nbNouveaux"] = $nombre;
		return $retour;
	}

	public static function getJsonData(&$view)
	{
		return self::data($view);
	}

	private static function data(&$view)
	{
		Zend_Loader::loadClass("Blabla");
		Zend_Loader::loadClass("Bral_Util_Lien");

		$blablaTable = new Blabla();
		$tab = array();
		$retour = array();

		$x = $view->user->x_braldun;
		$y = $view->user->y_braldun;
		$z = $view->user->z_braldun;
		$bm = $view->user->vue_bm_braldun;

		$vue_nb_cases = Bral_Util_Commun::getVueBase($x, $y, $z) + $bm;
		if ($vue_nb_cases < 0) {
			$vue_nb_cases = 0;
		}

		$rowset = $blablaTable->findByPosition($view->user->x_braldun, $view->user->y_braldun, $view->user->z_braldun, $vue_nb_cases);

		foreach ($rowset as $r) {
			$braldun = Bral_Util_Lien::remplaceBaliseParNomEtJs("[b" . $r["id_braldun"] . "]");
			$tab[] = array(
				"id_blabla" => $r["id_blabla"],
				"date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y à H:i:s ', $r["date_blabla"]),
				"braldun" => $braldun,
				"message" => Bral_Util_BBParser::bbcodeReplace($r["message_blabla"]),
				"x" => $r["x_blabla"],
				"y" => $r["y_blabla"],
				"z" => $r["z_blabla"]);
		}
		$retour["messages"] = $tab;
		return $retour;
	}

}