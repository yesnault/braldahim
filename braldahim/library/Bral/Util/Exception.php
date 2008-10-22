<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Util_Exception {

	private function __construct(){}

	public static function traite($e) {
		echo "Une erreur est survenue. L'equipe Braldahim est prevenue.";
		echo " Si le probleme persiste, merci de prendre contact via le forum Anomalies ";
		echo " en indiquant cette heure ".date("Y-m-d H:m:s");
		Bral_Util_Log::exception()->alert($e);
	}
}