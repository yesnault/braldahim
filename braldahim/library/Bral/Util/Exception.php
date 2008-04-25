<?php

class Bral_Util_Exception {

	private function __construct(){}

	public static function traite($e) {
		echo "Une erreur est survenue. L'&eacute;quipe Braldahim est pr&eacute;venue.";
		echo " Si le probl&egrave;me persiste, merci de contacter l'&eacute;quipe."; 
		Bral_Util_Log::exception()->alert($e);
	}
}